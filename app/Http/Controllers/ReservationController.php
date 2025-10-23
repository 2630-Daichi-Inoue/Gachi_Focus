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
     * Create reservation (server-side price is ALWAYS recomputed with Pricing::calc()).
     * Then redirect to its show page.
     */
    public function store(Request $request)
    {
        // Normalize names from form (time_from/time_to -> start_time/end_time)
        $request->merge([
            'start_time' => $request->input('time_from', $request->input('start_time')),
            'end_time'   => $request->input('time_to',   $request->input('end_time')),
        ]);

        $room = $this->room();

        $data = $request->validate([
            'type'         => ['required', 'string', Rule::in($room->types)],
            'date'         => ['required', 'date', 'after_or_equal:today'],
            'start_time'   => ['required', 'date_format:H:i', 'regex:/^(?:[01]\d|2[0-3]):(?:00|30)$/'],
            'end_time'     => ['required', 'date_format:H:i', 'regex:/^(?:[01]\d|2[0-3]):(?:00|30)$/', 'after:start_time'],
            'adults'       => ['required', 'integer', 'min:1', 'max:20'],
            'facilities'   => ['array'],
            'facilities.*' => [Rule::in($room->facilities)],
        ]);

        // Server-side pricing (the only source of truth)
        $quote = Pricing::calc([
            'room_name'  => $room->name,
            'type'       => $data['type'],          // Pricing expects label
            'date'       => $data['date'],
            'time_from'  => $data['start_time'],
            'time_to'    => $data['end_time'],
            'facilities' => $data['facilities'] ?? [],
        ]);
        $total = $quote['total'];

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
     * Show saved reservation.
     */
    public function show($id)
    {
        $reservation = $this->reservation->findOrFail($id);
        return view('rooms.show', compact('reservation'));
    }

    /**
     * Show edit form for a reservation.
     * Provides the current type label for select default.
     */
    public function edit(Reservation $reservation)
    {
        $room = $this->room();
        $typeKey2Label = array_flip($this->typeLabelToKey());
        $currentTypeLabel = $typeKey2Label[$reservation->type] ?? $reservation->type;

        return view('rooms.edit', compact('room', 'reservation', 'currentTypeLabel'));
    }

    /**
     * Update reservation (server-side price recomputed with Pricing::calc()).
     */
    public function update(Request $request, Reservation $reservation)
    {
        // Normalize names from form
        $request->merge([
            'start_time' => $request->input('time_from', $request->input('start_time')),
            'end_time'   => $request->input('time_to',   $request->input('end_time')),
        ]);

        $room      = $this->room();
        $label2key = $this->typeLabelToKey();

        $data = $request->validate([
            'type'         => ['required', 'string', Rule::in(array_keys($label2key))],
            'date'         => ['required', 'date', 'after_or_equal:today'],
            'start_time'   => ['required', 'date_format:H:i', 'regex:/^(?:[01]\d|2[0-3]):(?:00|30)$/'],
            'end_time'     => ['required', 'date_format:H:i', 'regex:/^(?:[01]\d|2[0-3]):(?:00|30)$/', 'after:start_time'],
            'adults'       => ['required', 'integer', 'min:1', 'max:20'],
            'facilities'   => ['array'],
            'facilities.*' => [Rule::in($room->facilities)],
        ]);

        // Pricing expects label; convert key->label for calculation if needed
        $typeLabel = array_search($data['type'], $label2key, true) ?: $data['type'];

        $quote = Pricing::calc([
            'room_name'  => $room->name,
            'type'       => $typeLabel,
            'date'       => $data['date'],
            'time_from'  => $data['start_time'],
            'time_to'    => $data['end_time'],
            'facilities' => $data['facilities'] ?? [],
        ]);
        $total = $quote['total'];

        $reservation->update([
            'type'        => $data['type'], // already a key here per validation
            'date'        => $data['date'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'adults'      => (int)$data['adults'],
            'facilities'  => $data['facilities'] ?? [],
            'total_price' => $total,
        ]);

        return redirect()->route('reservations.show', $reservation)
            ->with('status', 'Reservation updated.');
    }

    /**
     * Soft delete a reservation and redirect to home.
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('reservations.current')
            ->with('success', 'Reservation cancelled successfully.');
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
        $reservations = Reservation::with('space.photos')
            ->where('user_id', Auth::id())
            ->where('start_time', '>=', Carbon::now())
            ->orderBy('start_time', 'asc')
            ->get();

        return view('reservations.current-show', compact('reservations'));
    }

    // cancel
    public function cancel($id)
    {
        $reservation = Reservation::findOrFail($id);

        $reservation->update(['status' => 'canceled']);

        return redirect()->route('reservations.current')
            ->with('success', 'Reservation canceled successfully.');
    }

    // rebook
    public function rebook($id)
    {
        $reservation = Reservation::with('space.photos')->findOrFail($id);

        $room = (object)[
            'name'       => $reservation->space->name ?? 'Room B',
            'image_path' => $reservation->space->photos->first()->path ?? 'images/room-b.jpg',
            'max_adults' => $reservation->space->capacity_max ?? 4,
            'types'      => ['Focus Booth', 'Meeting', 'Phone Call'],
            'facilities' => $reservation->space->facilities ?? ['Monitor', 'Whiteboard', 'Power Outlet', 'HDMI', 'USB-C'],
        ];

        return view('rooms.reserve', [
            'room' => $room,
            'previousReservation' => $reservation,
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
