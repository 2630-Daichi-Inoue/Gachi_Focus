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
        return (object)[
            'name'       => 'Room B',
            'image_path' => '/images/room-b.jpg',
            'max_adults' => 4,
            'types'      => ['Focus Booth', 'Meeting', 'Phone Call'],
            'facilities' => ['Monitor', 'Whiteboard', 'Power Outlet', 'HDMI', 'USB-C'],
        ];
    }

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

    private function typePrices(): array
    {
        return ['focus_booth' => 10, 'meeting' => 15, 'phone_call' => 8];
    }

    private function facilityPrices(): array
    {
        return ['Monitor' => 3, 'Whiteboard' => 2, 'Power Outlet' => 0, 'HDMI' => 0, 'USB-C' => 0];
    }

    /** show form（GET /room-b） */
    public function create()
    {
        $room  = $this->room();

        // Opening hours and slot size (minutes). Adjust per room if needed.
        $open  = '09:00';
        $close = '21:00';
        $slot  = 30;

        $fromTimes = [];
        $toTimes   = [];

        $from = Carbon::createFromTimeString($open);
        $to   = Carbon::createFromTimeString($close);

        // Start time options: 09:00 .. 20:30 (end - slot)
        for ($t = $from->copy(); $t->lt($to->copy()->subMinutes($slot)); $t->addMinutes($slot)) {
            $fromTimes[] = $t->format('H:i');
        }
        // End time options: 09:30 .. 21:00
        for ($t = $from->copy()->addMinutes($slot); $t->lte($to); $t->addMinutes($slot)) {
            $toTimes[] = $t->format('H:i');
        }

        // Blade: use $fromTimes/$toTimes (NOT $fromPeriod/$toPeriod)
        return view('rooms.reserve', compact('room','fromTimes','toTimes'));
    }

    /**
     * Store a reservation then go to show page.
     */
    public function store(Request $request, Space $space)
    {
        $data = $request->validate([
            'date'        => 'required|date',
            'start_time'  => 'required',
            'end_time'    => 'required',
            'type'        => 'nullable|string',
            'adults'      => 'required|integer|min:1',
            'facilities'  => 'nullable|array',
        ]);

        // price calc
        $quote = Pricing::calc([
            'space_id'   => $space->id,
            'date'       => $data['date'],
            'time_from'  => $data['start_time'],
            'time_to'    => $data['end_time'],
            'type'       => $data['type'] ?? 'Standard',
            'facilities' => $data['facilities'] ?? [],
        ]);

        // Optionally store a key (instead of label) for the "type" column
        $typeKey = $this->typeLabelToKey()[$data['type']] ?? $data['type'];

        $reservation = $this->reservation->create([
            'user_id'     => Auth::id(),
            'room'        => 'B',
            'type'        => $typeKey,
            'date'        => $data['date'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'adults'      => (int)$data['adults'],
            'facilities'  => $data['facilities'] ?? [],
            'total_price' => $total,
        ]);

        return redirect()->route('reservations.show', $reservation);
    }

    /**
     * Preview page before payment.
     * Validates inputs, recomputes price via Pricing::calc(), and shows a confirmation view.
     * NOTE: room_name is injected server-side (not trusted from client).
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

        $payload = $validated + ['room_name' => $room->name];
        $pricing = Pricing::calc($payload);

        return view('checkout.preview', [
            'room'     => (object)['name' => $room->name],
            'input'    => $validated,
            'pricing'  => $pricing,
        ]);
    }

    /**
     * Resource: show a reservation (fallback → redirect to rooms.show).
     */
    public function show(Reservation $reservation)
    {
        // ensure owner
        if ($reservation->user_id !== Auth::id()) {
            abort(403);
        }

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
        // ensure owner
        if ($reservation->user_id !== Auth::id()) {
            abort(403);
        }

        $types = config('booking.types', ['Standard', 'Meeting', 'Focus Booth', 'Phone Call']);
        $facilityOptions = Utility::orderBy('name')->pluck('name')->toArray();
        [$fromTimes, $toTimes] = $this->buildTimeOptions('09:00', '21:00', 30);

        // --- Normalize defaults for SSR & Alpine ---
        // date -> "YYYY-MM-DD"; time -> "HH:MM"
        $defaultType  = $reservation->type ?? ($types[0] ?? 'Standard');
        $defaultDate  = optional($reservation->date)->toDateString() ?? Carbon::today()->toDateString();

        // Ensure "HH:MM" (DB might store "HH:MM:SS")
        $defaultStart = $reservation->start_time ? substr((string)$reservation->start_time, 0, 5) : ($fromTimes[0] ?? '09:00');
        $defaultEnd   = $reservation->end_time   ? substr((string)$reservation->end_time,   0, 5) : ($toTimes[0]   ?? '10:00');

        // Facilities must be an array for both Blade @checked and Alpine x-model
        $defaultFacilities = is_array($reservation->facilities) ? $reservation->facilities : [];

        $defaultAdults = (int) ($reservation->adults ?? 1);

        $space = $reservation->space ?? Space::find($reservation->space_id);

        return view('rooms.edit', compact(
            'reservation',
            'space',
            'types',
            'facilityOptions',
            'fromTimes',
            'toTimes',
            'defaultType',
            'defaultDate',
            'defaultStart',
            'defaultEnd',
            'defaultAdults',
            'defaultFacilities'
        ));
    }

    /**
     * Update reservation data.
     */
    public function update(Request $request, Reservation $reservation)
    {
        // ensure owner
        if ($reservation->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'date'        => 'required|date',
            'start_time'  => 'required',
            'end_time'    => 'required',
            'type'        => 'nullable|string',
            'adults'      => 'required|integer|min:1',
            'facilities'  => 'nullable|array',
        ]);

        $quote = Pricing::calc([
            'space_id'   => $reservation->space_id,
            'date'       => $data['date'],
            'time_from'  => $data['start_time'],
            'time_to'    => $data['end_time'],
            'type'       => $data['type'] ?? 'Standard',
            'facilities' => $data['facilities'] ?? [],
        ]);

        $reservation->update([
            'date'        => $data['date'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'type'        => $data['type'] ?? 'Standard',
            'facilities'  => $data['facilities'] ?? [],
            'total_price' => $total,
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
        // ensure owner
        if ($reservation->user_id !== Auth::id()) {
            abort(403);
        }

        $reservation->delete();
        return redirect()->route('reservations.current')->with('status', 'Deleted.');
    }

    /**
     * OPTIONAL: Live pricing endpoint for the form.
     * Returns JSON using Pricing::calc().
     * Safe to expose publicly (protected by CSRF).
     */
    public function quote(Request $request)
    {
        $room = $this->room();

        $data = $request->validate([
            'type'        => ['required','string'],
            'date'        => ['required','date'],
            'time_from'   => ['required','date_format:H:i'],
            'time_to'     => ['required','date_format:H:i','after:time_from'],
            'facilities'  => ['array'],
            'facilities.*'=> ['string'],
        ]);

        $payload = $data + ['room_name' => $room->name];
        return response()->json(Pricing::calc($payload));
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

    /**
     * Past reservations for the logged-in user.
     */
    public function pastShow()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->whereDate('date', '<', Carbon::today())
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->get();

        return view('reservations.past', compact('reservations'));
    }

    /**
     * Rebook a past reservation (shortcut back to form).
     */
    public function rebook($id)
    {
        $old = Reservation::where('user_id', Auth::id())->findOrFail($id);

        return redirect()->route('rooms.reserve.form', [
            'space' => $old->space_id,
            'date'  => optional($old->date)->toDateString(),
        ]);
    }

    // Past reservations show
    public function pastShow()
    {
        $reservations = Reservation::with('space.photos')
            ->where('user_id', Auth::id())
            ->where('start_time', '<', Carbon::now())
            ->orderByDesc('end_time')
            ->get();

        return view('reservations.past-show', compact('reservations'));
    }

    public function downloadInvoice($id)
    {
        $reservation = Reservation::with(['space', 'user'])->findOrFail($id);

        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::user();

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

    // tax caluculate　TODO later / rio
    // private function __construct(Reservation $reservation, TaxService $taxService)
    // {
    //     $this->reservation = $reservation;
    //     $this->taxService = $taxService;
    // }

}
