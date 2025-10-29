<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\Utility;
use App\Support\Pricing;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Show reservation form for a specific space.
     */
    public function create(Space $space)
    {
        // options
        $types = config('booking.types', ['Standard', 'Meeting', 'Focus Booth', 'Phone Call']);
        $facilityOptions = Utility::orderBy('name')->pluck('name')->toArray();

        // time options
        [$fromTimes, $toTimes] = $this->buildTimeOptions('09:00', '21:00', 30);

        // prefill
        $prefillDate = request('date', Carbon::today()->toDateString());
        $prefillFrom = request('start_time', $fromTimes[0] ?? '09:00');
        $prefillTo   = request('end_time', $toTimes[array_search($prefillFrom, $fromTimes, true) + 1] ?? '10:00');

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

        // save
        $reservation = Reservation::create([
            'user_id'        => Auth::id(),
            'space_id'       => $space->id,
            'date'           => $data['date'],
            'start_time'     => $data['start_time'],
            'end_time'       => $data['end_time'],
            'type'           => $data['type'] ?? 'Standard',
            'facilities'     => $data['facilities'] ?? [],
            'adults'         => $data['adults'],
            'total_price'    => $quote['total'],
            'currency'       => $quote['currency'],
            'payment_region' => $quote['country'] ?? 'JP',
            'payment_status' => 'unpaid',
        ]);

        // go to /rooms/{space}/show (pass reservation_id)
        return redirect()->route('rooms.show', [
            'space'          => $space->id,
            'reservation_id' => $reservation->id,
        ])->with('reservation_id', $reservation->id);
    }

    /**
     * Render the show page with latest reservation (from session/query).
     */
    public function showRoom(Space $space, Request $request)
    {
        $rid = $request->session()->get('reservation_id', $request->query('reservation_id'));
        $reservation = null;

        if ($rid) {
            $reservation = Reservation::where('user_id', Auth::id())
                ->where('space_id', $space->id)
                ->find($rid);
        }

        return view('rooms.show', [
            'space'       => $space,
            'reservation' => $reservation,
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

        // alpine defaults
        $defaultType        = $reservation->type ?? ($types[0] ?? 'Standard');
        $defaultDate        = optional($reservation->date)->toDateString() ?? now()->toDateString();
        $defaultStart       = $reservation->start_time ?? ($fromTimes[0] ?? '09:00');
        $defaultEnd         = $reservation->end_time ?? ($toTimes[0] ?? '10:00');
        $defaultAdults      = (int) ($reservation->adults ?? 1);
        $defaultFacilities  = is_array($reservation->facilities) ? $reservation->facilities : [];

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
            'adults'      => $data['adults'],
            'total_price' => $quote['total'],
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
     * Current (today or future) reservations for the logged-in user.
     */
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

    /**
     * Invoice placeholder.
     */
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
            'space_id'  => 'required|integer',
            'date'      => 'required|date',
            'time_from' => 'required',
            'time_to'   => 'required',
        ]);

        $quote = Pricing::calc($data);
        return response()->json($quote);
    }

    /**
     * Build time select options.
     */
    private function buildTimeOptions(string $start, string $end, int $stepMinutes = 30): array
    {
        $base = Carbon::today();
        $cur  = Carbon::parse($base->toDateString().' '.$start);
        $last = Carbon::parse($base->toDateString().' '.$end);

        $times = [];
        while ($cur <= $last) {
            $times[] = $cur->format('H:i');
            $cur->addMinutes($stepMinutes);
        }

        $from = $times;
        $to   = $times;
        if (count($times) >= 2) {
            array_pop($from);
            array_shift($to);
        }

        // minimal fallback
        if (empty($from)) $from = ['09:00'];
        if (empty($to))   $to   = ['10:00'];

        return [$from, $to];
    }
}