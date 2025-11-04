<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Support\Pricing;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Reservation flow (public):
 * - GET  /rooms/{space}/reserve -> create()
 * - POST /rooms/{space}/reserve -> store()
 * - POST /rooms/reserve/preview -> preview()
 * - GET  /reservations/{id} -> show()
 * - GET  /reservations/{reservation}/edit -> edit()
 * - PUT  /reservations/{reservation} -> update()
 * - POST /reservations/{id}/cancel -> cancel()
 * - GET  /reservations/{id}/rebook -> rebook()
 * - GET  /reservations/{id}/invoice -> downloadInvoice()
 */
class ReservationController extends Controller
{
    public function create(Space $space)
    {
        $open = '09:00';
        $close = '21:00';
        $slot = 30;

        $fromTimes = [];
        $toTimes = [];

        for ($t = Carbon::createFromTimeString($open); $t->lt(Carbon::createFromTimeString($close)->subMinutes($slot)); $t->addMinutes($slot)) {
            $fromTimes[] = $t->format('H:i');
        }

        for ($t = Carbon::createFromTimeString($open)->addMinutes($slot); $t->lte(Carbon::createFromTimeString($close)); $t->addMinutes($slot)) {
            $toTimes[] = $t->format('H:i');
        }

        return view('rooms.reserve', compact('space', 'fromTimes', 'toTimes'));
    }

    public function store(Request $request, Space $space)
    {
        $data = $request->validate([
            'date'        => 'required|date|after_or_equal:today',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'type'        => 'nullable|string',
            'adults'      => 'required|integer|min:1|max:20',
            'facilities'  => 'nullable|array',
        ]);

        $quote = Pricing::calc([
            'space_id'   => $space->id,
            'date'       => $data['date'],
            'time_from'  => $data['start_time'],
            'time_to'    => $data['end_time'],
            'type'       => $data['type'] ?? 'Standard',
            'facilities' => $data['facilities'] ?? [],
        ]);

        $reservation = Reservation::create([
            'user_id'        => Auth::id(),
            'space_id'       => $space->id,
            'date'           => $data['date'],
            'start_time'     => $data['start_time'],
            'end_time'       => $data['end_time'],
            'type'           => $data['type'] ?? 'Standard',
            'adults'         => $data['adults'],
            'facilities'     => $data['facilities'] ?? [],
            'total_price'    => $quote['total'] ?? 0,
            'currency'       => $quote['currency'] ?? 'USD',
            'payment_region' => $quote['country'] ?? 'US',
            'payment_status' => 'unpaid',
            'status'         => 'confirmed',
        ]);

        return redirect()->route('reservations.current')
            ->with('success', 'Reservation created successfully.');
    }

    public function edit(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) abort(403);

        $space = $reservation->space;
        $open = '09:00';
        $close = '21:00';
        $slot = 30;

        $fromTimes = [];
        $toTimes = [];

        for ($t = Carbon::createFromTimeString($open); $t->lt(Carbon::createFromTimeString($close)->subMinutes($slot)); $t->addMinutes($slot)) {
            $fromTimes[] = $t->format('H:i');
        }

        for ($t = Carbon::createFromTimeString($open)->addMinutes($slot); $t->lte(Carbon::createFromTimeString($close)); $t->addMinutes($slot)) {
            $toTimes[] = $t->format('H:i');
        }

        return view('rooms.edit', compact('reservation', 'space', 'fromTimes', 'toTimes'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) abort(403);

        $data = $request->validate([
            'date'        => 'required|date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'type'        => 'nullable|string',
            'adults'      => 'required|integer|min:1',
            'facilities'  => 'nullable|array',
        ]);

        $quote = Pricing::calc([
            'space_id'   => $reservation->space_id,
            'date'       => $data['date'],
            'time_from'  => $data['start_time'],
            'time_to'    => $data['end_time'],
            'type'       => $data['type'] ?? $reservation->type,
            'facilities' => $data['facilities'] ?? [],
        ]);

        $reservation->update([
            'date'        => $data['date'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'type'        => $data['type'] ?? 'Standard',
            'facilities'  => $data['facilities'] ?? [],
            'adults'      => $data['adults'],
            'total_price' => $quote['total'] ?? 0,
        ]);

        return redirect()->route('reservations.current')->with('success', 'Reservation updated.');
    }

    /*  Cancel */
    public function cancel($id)
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $reservation->update(['status' => 'canceled']);
        return redirect()->route('reservations.current')->with('success', 'Reservation canceled successfully.');
    }

    /* Rebook */
    public function rebook($id)
    {
        $reservation = Reservation::with('space')->findOrFail($id);
        $space = $reservation->space;

        return redirect()->route('rooms.reserve.form', [
            'space' => $space->id,
            'date'  => optional($reservation->date)->toDateString(),
        ]);
    }

    /*  Current Reservations */
    public function currentShow()
    {
        $reservations = Reservation::with('space.photos')
            ->where('user_id', Auth::id())
            ->where('start_time', '>=', Carbon::now())
            ->orderBy('start_time', 'asc')
            ->get();

        return view('reservations.current-show', compact('reservations'));
    }

    /*  Past Reservations */
    public function pastShow()
    {
        $reservations = Reservation::with('space.photos')
            ->where('user_id', Auth::id())
            ->where('start_time', '<', Carbon::now())
            ->orderByDesc('end_time')
            ->get();

        return view('reservations.past-show', compact('reservations'));
    }

    /*  Invoice (PDF) */
    public function downloadInvoice($id)
    {
        $reservation = Reservation::with(['space', 'user'])->findOrFail($id);
        if ($reservation->user_id !== Auth::id()) abort(403);

        $space = $reservation->space;
        $user = Auth::user();

        // Tax settings (simplified)
        $vatRate = match ($space->country_code) {
            'JP' => 10, 'PH' => 12, 'AU' => 10, 'US' => 8,
            default => 0,
        };

        $subtotal = $reservation->total_price ?? 0;
        $tax = $subtotal * ($vatRate / 100);
        $total = $subtotal + $tax;

        $pdf = Pdf::loadView('reservations.invoice-pdf', [
            'reservation' => $reservation,
            'user' => $user,
            'space' => $space,
            'vatRate' => $vatRate,
            'subtotalUSD' => $subtotal,
            'taxUSD' => $tax,
            'totalUSD' => $total,
        ]);

        return $pdf->download("invoice_{$reservation->id}.pdf");
    }
}
