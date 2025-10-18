<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Reservation;
use App\Services\TaxService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    private Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

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
        $room = $this->room();
        return view('rooms.reserve', compact('room'));
    }

    /** save（POST /room-b）→ show */
    public function store(Request $request)
    {
        // time_from/time_to → start_time/end_time 
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

        $typeKey = $this->typeLabelToKey()[$data['type']] ?? $data['type'];

        $total = $this->calcTotal(
            $typeKey,
            $data['start_time'],
            $data['end_time'],
            (int)$data['adults'],
            $data['facilities'] ?? []
        );

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

    /** show（/reservations/{id}） */
    public function show($id)
    {
        $reservation = $this->reservation->findOrFail($id);
        return view('rooms.show', compact('reservation'));
    }

    /** edit（/reservations/{reservation}/edit） */
    public function edit(Reservation $reservation)
    {
        $room = $this->room();
        $typeKey2Label = array_flip($this->typeLabelToKey());
        $currentTypeLabel = $typeKey2Label[$reservation->type] ?? $reservation->type;

        return view('rooms.edit', compact('room', 'reservation', 'currentTypeLabel'));
    }

    /** update（PUT /reservations/{reservation}） */
    public function update(Request $request, Reservation $reservation)
    {
        // time_from/time_to 
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

        $typeKey = $label2key[$data['type']] ?? $data['type'];

        $total = $this->calcTotal(
            $typeKey,
            $data['start_time'],
            $data['end_time'],
            (int)$data['adults'],
            $data['facilities'] ?? []
        );

        $reservation->update([
            'type'        => $typeKey,
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


    // delete
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('reservations.current')
            ->with('success', 'Reservation cancelled successfully.');
    }


    /** cal */
    private function calcTotal(string $type, string $start, string $end, int $adults, array $fac): float
    {
        $typePrice = $this->typePrices()[$type] ?? 0;
        $facPrice  = $this->facilityPrices();

        $minutes = Carbon::parse($start)->diffInMinutes(Carbon::parse($end));
        $hours   = max(0, $minutes / 60);
        $base    = $typePrice * $hours;
        $facSum  = array_sum(array_map(fn($k) => $facPrice[$k] ?? 0, $fac));
        return ($base + $facSum) * max(1, $adults);
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

        $pdf = Pdf::loadView('reservations.invoice-pdf', [
            'reservation' => $reservation,
            'user' => $reservation->user,
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
