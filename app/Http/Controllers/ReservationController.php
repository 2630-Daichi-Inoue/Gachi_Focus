<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Reservation;
use App\Services\TaxService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Support\Pricing;

/**
 * Reservation flow (public):
 * - GET  /room-b        -> create()  : show reservation form for "Room B"
 * - POST /room-b        -> store()   : validate + compute price (server-side) + save + redirect to show
 * - POST /rooms/reserve/preview -> preview() : validate + compute price (server-side) + show confirmation page
 * - GET  /reservations/{id}          -> show()   : show saved reservation
 * - GET  /reservations/{reservation}/edit -> edit()   : show edit form
 * - PUT  /reservations/{reservation} -> update() : validate + recompute + save
 * - DELETE /reservations/{reservation} -> destroy(): soft delete and redirect to home
 *
 * Optional (for live pricing on the form):
 * - POST /pricing/quote -> quote(): returns JSON pricing using Pricing::calc()
 */
class ReservationController extends Controller
{
    private Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * In-memory room definition for the demo.
     * In real apps, fetch this from DB (e.g., Room model).
     */
    private function room(): object
    {
        // NOTE: keep labels aligned with Pricing::$cfg['types']
        $types = config('booking.types', ['Standard', 'Meeting', 'Focus Booth', 'Phone Call']);
        $facilityOptions = Utility::orderBy('name')->pluck('name')->toArray();

    /**
     * Map display label -> key (used if you prefer storing keys in DB).
     */
    private function typeLabelToKey(): array
    {
        return [
            'Focus Booth' => 'focus_booth',
            'Meeting'     => 'meeting',
            'Phone Call'  => 'phone_call',
        ];
    }

        // prefill
        $prefillDate = request('date', Carbon::today()->toDateString());
        $prefillFrom = request('start_time', $fromTimes[0] ?? '09:00');
        $nextIdx     = array_search($prefillFrom, $fromTimes, true);
        $prefillTo   = $toTimes[($nextIdx === false ? 0 : $nextIdx)] ?? '10:00';

        return view('rooms.reserve', [
            'space'           => $space,
            'types'           => $types,
            'facilityOptions' => $facilityOptions,
            'fromTimes'       => $fromTimes,
            'toTimes'         => $toTimes,
            'displayName'     => $space->name,
            'prefill'         => [
                'date'       => $prefillDate,
                'start_time' => $prefillFrom,
                'end_time'   => $prefillTo,
                'type'       => $types[0] ?? 'Standard',
            ],
        ]);
    }

    /**
     * Store a reservation then go to show page.
     */
    public function store(Request $request, Space $space)
    {
        $data = $request->validate([
            'date'               => 'required|date',
            'start_time'         => 'required',
            'end_time'           => 'required',
            'type'               => 'nullable|string',
            'adults'             => 'required|integer|min:1',
            'facilities'         => 'nullable|array',
            'country_code'       => 'nullable|string|max:5',
            'currency_override'  => 'nullable|string|max:3',
        ]);

        // normalize time to HH:mm
        $startHHmm  = $this->normalizeTime($data['start_time']);
        $endHHmm    = $this->normalizeTime($data['end_time']);

        // Quote (type normalization is handled inside Pricing::calc)
        $quote = Pricing::calc([
            'space_id'          => $space->id,
            'date'              => $data['date'],
            'time_from'         => $startHHmm,
            'time_to'           => $endHHmm,
            'type'              => $data['type'] ?? 'Standard', // raw in, normalized inside
            'facilities'        => $data['facilities'] ?? [],
            'country_code'      => $request->input('country_code', $space->country_code ?? 'JP'),
            'currency_override' => $request->input('currency_override', $space->currency ?? null),
        ]);

        // Save authoritative values (use canonicalized type from Pricing)
        $reservation = Reservation::create([
            'user_id'        => Auth::id(),
            'space_id'       => $space->id,
            'date'           => $data['date'],
            'start_time'     => $startHHmm,
            'end_time'       => $endHHmm,
            'type'           => $quote['unit']['type'] ?? ($data['type'] ?? 'Standard'), // canonical
            'facilities'     => $data['facilities'] ?? [],
            'adults'         => $data['adults'],
            'total_price'    => $quote['total'],
            'currency'       => $quote['currency'],
            'payment_region' => $quote['country'] ?? 'JP',
            'tax_amount'     => $quote['tax_amount'] ?? null,
            'tax_rate'       => $quote['tax_rate'] ?? null, // decimal
            'payment_status' => 'unpaid',
        ]);

        return redirect()->route('rooms.show', [
            'space'          => $space->id,
            'reservation_id' => $reservation->id,
        ])->with('reservation_id', $reservation->id);
    }

    /**
     * Render the show page with latest reservation (from session/query).
     * No recalculation: show the saved amounts to avoid drift.
     */
    public function preview(Request $req)
    {
        $room = $this->room();

        $validated = $req->validate([
            'type'        => ['required','string'],
            'date'        => ['required','date'],
            'time_from'   => ['required','date_format:H:i'],
            'time_to'     => ['required','date_format:H:i','after:time_from'],
            'adults'      => ['required','integer','min:1'],
            'facilities'  => ['array'],
            'facilities.*'=> ['string'],
        ]);

        if ($rid) {
            // IMPORTANT: don't call ->find($rid) on a constrained builder; it ignores previous where()
            $reservation = Reservation::where('user_id', Auth::id())
                ->where('space_id', $space->id)
                ->where('id', $rid) // <- keep constraints
                ->first();
        }

        return view('checkout.preview', [
            'room'     => (object)['name' => $room->name],
            'input'    => $validated,
            'pricing'  => $pricing,
        ]);
    }

    /**
     * Resource: show a reservation (redirect to rooms.show).
     */
    public function show(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) abort(403);

        return redirect()->route('rooms.show', [
            'space'          => $reservation->space_id,
            'reservation_id' => $reservation->id,
        ]);
    }

    /**
     * Edit reservation form.
     */
    public function edit(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) abort(403);

        $types = config('booking.types', ['Standard', 'Meeting', 'Focus Booth', 'Phone Call']);
        $facilityOptions = Utility::orderBy('name')->pluck('name')->toArray();
        [$fromTimes, $toTimes] = $this->buildTimeOptions('09:00', '21:00', 30);

        // Alpine defaults (leave type as saved; Pricing will normalize again on update)
        $defaultType        = $reservation->type ?? ($types[0] ?? 'Standard');
        $defaultDate        = optional($reservation->date)->toDateString() ?? now()->toDateString();
        $defaultStart       = $this->normalizeTime($reservation->start_time ?? ($fromTimes[0] ?? '09:00'));
        $defaultEnd         = $this->normalizeTime($reservation->end_time   ?? ($toTimes[0] ?? '10:00'));
        $defaultAdults      = (int) ($reservation->adults ?? 1);
        $defaultFacilities  = is_array($reservation->facilities) ? $reservation->facilities : [];

        $space = $reservation->space ?? Space::find($reservation->space_id);

        return view('rooms.edit', compact(
            'reservation','space','types','facilityOptions','fromTimes','toTimes',
            'defaultType','defaultDate','defaultStart','defaultEnd','defaultAdults','defaultFacilities'
        ));
    }

    /**
     * Update reservation data.
     */
    public function update(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) abort(403);

        $data = $request->validate([
            'date'               => 'required|date',
            'start_time'         => 'required',
            'end_time'           => 'required',
            'type'               => 'nullable|string',
            'adults'             => 'required|integer|min:1',
            'facilities'         => 'nullable|array',
            'country_code'       => 'nullable|string|max:5',
            'currency_override'  => 'nullable|string|max:3',
        ]);

        $startHHmm = $this->normalizeTime($data['start_time']);
        $endHHmm   = $this->normalizeTime($data['end_time']);
        $space     = $reservation->space ?? Space::find($reservation->space_id);

        $quote = Pricing::calc([
            'space_id'          => $reservation->space_id,
            'date'              => $data['date'],
            'time_from'         => $startHHmm,
            'time_to'           => $endHHmm,
            'type'              => $data['type'] ?? $reservation->type ?? 'Standard',
            'facilities'        => $data['facilities'] ?? [],
            'country_code'      => $request->input('country_code', $reservation->payment_region ?: ($space->country_code ?? 'JP')),
            'currency_override' => $request->input('currency_override', $reservation->currency ?: ($space->currency ?? null)),
        ]);

        $reservation->update([
            'date'           => $data['date'],
            'start_time'     => $startHHmm,
            'end_time'       => $endHHmm,
            'type'           => $quote['unit']['type'] ?? ($data['type'] ?? 'Standard'), // canonical
            'facilities'     => $data['facilities'] ?? [],
            'adults'         => $data['adults'],
            'total_price'    => $quote['total'], // tax-in
            'currency'       => $quote['currency'],
            'payment_region' => $quote['country'] ?? $reservation->payment_region,
            'tax_amount'     => $quote['tax_amount'] ?? null,
            'tax_rate'       => $quote['tax_rate']   ?? null,
        ]);

        return redirect()->route('rooms.show', [
            'space'          => $reservation->space_id,
            'reservation_id' => $reservation->id,
        ])->with('status', 'Reservation updated.');
    }

    /**
     * Cancel reservation (status only).
     */
    public function cancel($id)
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $reservation->update(['payment_status' => 'canceled']);

        return redirect()->route('index')->with('status', 'Reservation canceled.');
    }

    /**
     * Soft delete reservation (if needed by UI).
     */
    public function destroy(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) abort(403);

        $reservation->delete();
        return redirect()->route('reservations.current')->with('status', 'Deleted.');
    }

    public function currentShow()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->whereDate('date', '>=', Carbon::today())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('reservations.current', compact('reservations'));
    }

    public function pastShow()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->whereDate('date', '<', Carbon::today())
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->get();

        return view('reservations.past', compact('reservations'));
    }

    public function rebook($id)
    {
        $old = Reservation::where('user_id', Auth::id())->findOrFail($id);

        return redirect()->route('rooms.reserve.form', [
            'space' => $old->space_id,
            'date'  => optional($old->date)->toDateString(),
        ]);
    }

    public function downloadInvoice($id)
    {
        $reservation = Reservation::where('user_id', Auth::id())->findOrFail($id);
        return view('reservations.invoice', compact('reservation'));
    }

    /**
     * Pricing quote API (AJAX).
     */
    public function quote(Request $request)
    {
        $data = $request->validate([
            'space_id'          => 'required|integer',
            'date'              => 'required|date',
            'time_from'         => 'required',
            'time_to'           => 'required',
            'type'              => 'nullable|string',
            'facilities'        => 'nullable|array',
            'country_code'      => 'nullable|string|max:5',
            'currency_override' => 'nullable|string|max:3',
        ]);

        $startHHmm = $this->normalizeTime($data['time_from']);
        $endHHmm   = $this->normalizeTime($data['time_to']);

        $quote = Pricing::calc([
            'space_id'          => (int) $data['space_id'],
            'date'              => $data['date'],
            'time_from'         => $startHHmm,
            'time_to'           => $endHHmm,
            'type'              => $data['type'] ?? 'Standard', // raw, normalized inside
            'facilities'        => $data['facilities'] ?? [],
            'country_code'      => $data['country_code'] ?? null,
            'currency_override' => $data['currency_override'] ?? null,
        ]);

        return response()->json($quote);
    }

    private function buildTimeOptions(string $start, string $end, int $stepMinutes = 30): array
    {
        $base = Carbon::today();
        $cur  = Carbon::parse($base->toDateString().' '.$start);
        $last = Carbon::parse($base->toDateString().' '.$end);

        $times = [];
        while ($cur <= $last) {
            $times[] = $cur->format('H:i'); // HH:mm
            $cur->addMinutes($stepMinutes);
        }

        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        if (empty($from)) $from = ['09:00'];
        if (empty($to))   $to   = ['10:00'];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reservations.invoice-pdf', [
            'reservation' => $reservation,
            'user' => $user,
            'issuedDate' => now()->format('Y/m/d'),
            'company' => [
                'name' => 'Gachi Focus Co-working',
                'address' => '2-1-1 Nishi-Shinjuku, Shinjuku-ku, Tokyo',
                'email' => 'dummy123@gachifocus.com',
                'signature' => 'Representative: Gachi Manager',
            ],
        ]);

        $fileName = 'invoice_' . $reservation->id . '.pdf';
        return "Invoice feature coming soon for reservation ID: {$id}";
    }

    private function normalizeTime(?string $v): string
    {
        if (!$v) return '00:00';
        return substr($v, 0, 5); // "HH:mm"
    }
}
