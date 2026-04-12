<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreReservationRequest;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
        /**
     * Display a listing of the reservations.
     */
    public function index(Request $request)
    {
        $reservation_status_list = ['booked', 'canceled'];
        $sort_list = ['date_future_to_past', 'date_past_to_future'];

        $request->validate([
            'name'               => ['nullable','string','max:50'],
            'reservation_status' => ['nullable', Rule::in(array_merge(['all'], $reservation_status_list))],
            'sort'               => ['nullable', Rule::in($sort_list)],
            'rows_per_page'      => ['nullable', 'integer', 'in:20,50,100']
        ]);

        $query = Reservation::query()
                                ->where('user_id', Auth::id())
                                ->with('space');

        // Filter by name
        if ($request->filled('name')) {
            $query->whereHas('space', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->name . '%');
            });
        }
        // Filter by reservation_status
        $reservationStatus = $request->input('reservation_status', 'all');
        if($reservationStatus !== 'all') {
            $query->where('reservation_status', $reservationStatus);
        }

        // upcoming / past / cancelled can be implemented later if needed by checking start_at, end_at and reservation_status
        $rowsPerPage = (int)$request->input('rows_per_page', 20);

        // Default: date present → past
        $this->applySort($query, $request->input('sort', 'date_future_to_past'));

        $reservations = $query
                        ->paginate($rowsPerPage)
                        ->withQueryString();

        $reservations->load([
            'review' => function($q) {
                $q->withTrashed();
            }
        ]);

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
            'filters' => [
                'name' => $request->name,
                'reservation_status' => $request->input('reservation_status', 'all'),
                'sort' => $request->input('sort', 'date_future_to_past'),
                'rows_per_page' => $rowsPerPage,
            ]
        ]);
    }

    public function applySort(\Illuminate\Database\Eloquent\Builder $q, ?string $sort): void
    {
        switch ($sort ?? 'date_future_to_past') {
            case 'date_future_to_past':
                $q->orderBy('start_at', 'desc')
                    ->latest('id');
                break;

            case 'date_past_to_future':
                $q->orderBy('start_at', 'asc')
                    ->latest('id');
                break;

            default:
                $q->orderBy('start_at', 'desc')
                    ->latest('id');
        }
    }

    /**
     * Show reservation form for a specific space.
     */
    public function create(Request $request, Space $space)
    {
        if (!$space->isPublic()) {
            return redirect()->route('spaces.index')
                            ->with('error', 'Sorry, but this space is not currently available.');
        }

        // Default to today, can be overridden by users' input
        $date = $request->input('date', Carbon::today()->toDateString());

        // Space's open-close times (not depending on date)
        $openTime = Carbon::createFromFormat("Y-m-d H:i:s", "$date {$space->open_time}");
        $closeTime = Carbon::createFromFormat("Y-m-d H:i:s", "$date {$space->close_time}");

        $cursorOpenTime = $openTime->copy();
        $lastStartAt = $closeTime->copy()->subMinutes(30); // Last possible start time is 30 min before close

        // If the reservation date is today, we need to adjust the cursorOpenTime to the next possible 30 min slot
        if(Carbon::parse($date)->isToday()) {
            $now = Carbon::now();
            $minute = $now->minute;

            if($minute === 0 || $minute === 30) {
                $roundedNow = $now->copy()->second(0)->microsecond(0);
            } else if ($minute < 30) {
                $roundedNow = $now->copy()->minute(30)->second(0)->microsecond(0);
            } else {
                $roundedNow = $now->copy()->addHour()->minute(0)->second(0)->microsecond(0);
            }

            if($roundedNow->gt($openTime)) {
                $cursorOpenTime = $roundedNow->copy();
            }
        }

        // Candidates for reservation start time (every 30 min slot between open and close)
        $startCandidates = [];
        while ($cursorOpenTime->lte($lastStartAt)) {
            $formattedCursorTime = $cursorOpenTime->format('H:i');
            $startCandidates[] = $formattedCursorTime;
            $cursorOpenTime->addMinutes(30);
        }

        return Inertia::render('Reservations/Create', [
            'space' => $space,
            'startCandidates' => $startCandidates,
            'lastStartAt' => $lastStartAt->format('H:i'),
            'date' => $date,
        ]);
    }

    /**
     * Confirmation and payment for a reservation
     */
    public function payment(StoreReservationRequest $request, Space $space)
    {
        $data = $request->validated();

        // normalize time to HH:mm
        $newStartAt  = Carbon::parse($data['date'] . ' ' . $data['start_at']);
        $newEndAt    = Carbon::parse($data['date'] . ' ' . $data['end_at']);

        $checkedSpace = Space::whereKey($space->id)->firstOrFail();

        $overlappingQuantity = Reservation::query()
                                            ->where('space_id', $checkedSpace->id)
                                            ->where('reservation_status', 'booked')
                                            ->where('start_at', '<', $newEndAt)
                                            ->where('end_at', '>', $newStartAt)
                                            ->sum('quantity');

        if ($overlappingQuantity + $data['quantity'] > $checkedSpace->capacity) {
            throw ValidationException::withMessages([
                'quantity' => 'Sorry, but there are not enough spaces available for the selected time slot.',
            ]);
        }

        $unit_price_yen = Carbon::parse($data['date'])->isWeekend()
        ? $checkedSpace->weekend_price_yen
        : $checkedSpace->weekday_price_yen;

        $slot_count = $newStartAt->diffInMinutes($newEndAt) / 30;

        return Inertia::render('Reservations/Payment', [
            'space' => $checkedSpace,
            'reservationData' => [
                'date' => $data['date'],
                'start_at' => $data['start_at'],
                'end_at' => $data['end_at'],
                'quantity' => $data['quantity'],
                'total_price_yen' => $unit_price_yen * $data['quantity'] * $slot_count,
            ],
        ]);
    }

    /**
     * Store a reservation then go to index page.
     */
    public function store(StoreReservationRequest $request, Space $space)
    {
        $data = $request->validated();

        // normalize time to HH:mm
        $newStartAt  = Carbon::parse($data['date'] . ' ' . $data['start_at']);
        $newEndAt    = Carbon::parse($data['date'] . ' ' . $data['end_at']);

        $reservation = DB::transaction(function() use ($space, $data, $newStartAt, $newEndAt) {
            $checkedSpace = Space::whereKey($space->id)->lockForUpdate()->firstOrFail();

            $overlappingQuantity = Reservation::query()
                                    ->where('space_id', $checkedSpace->id)
                                    ->where('reservation_status', 'booked')
                                    ->where('start_at', '<', $newEndAt)
                                    ->where('end_at', '>', $newStartAt)
                                    ->sum('quantity');

            if ($overlappingQuantity + $data['quantity'] > $checkedSpace->capacity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Sorry, but there are not enough spaces available for the selected time slot.',
                ]);
            }

            $unit_price_yen = Carbon::parse($data['date'])->isWeekend()
            ? $checkedSpace->weekend_price_yen
            : $checkedSpace->weekday_price_yen;

            $slot_count = $newStartAt->diffInMinutes($newEndAt) / 30;

            return Reservation::create([
                'user_id'            => Auth::id(),
                'space_id'           => $checkedSpace->id,
                'reservation_status' => 'booked',  // For MVP, reservation is booked immediately. In production, this should become pending_payment until payment succeeds.
                'start_at'           => $newStartAt,
                'end_at'             => $newEndAt,
                'quantity'           => $data['quantity'],
                'slot_count'         => $slot_count,
                'unit_price_yen'     => $unit_price_yen,
                'total_price_yen'    => $unit_price_yen * $data['quantity'] * $slot_count,
            ]);
        });

        // Create notification to admin
        // $admin = User::where('role_id', 1)->first();
        // if($admin) {
        //     CustomNotification::create([
        //         'sender_id' => Auth::id(),
        //         'receiver_id' => $admin->id,
        //         'type' => 'New Reservation',
        //         'message' => Auth::user()->name . ' has made a new reservation for space ' . $space->name . '.',
        //         'reservation_id' => $reservation->id,
        //     ]);
        // }

        return redirect()->route('spaces.show', $space)
        ->with('ok', 'Your reservation has been successfully made!');
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) {
            abort(403);
        }

        if ($reservation->reservation_status === 'canceled') {
            return back()->with('error', 'This reservation has already been canceled.');
        }

        if (Carbon::parse($reservation->start_at)->subHour()->lte(now())) {
            return back()->with('error', 'You cannot cancel within 1 hour of the reservation start time.');
        }

        $reservation->update(['reservation_status' => 'canceled']);

        return back()->with('ok', 'Your reservation has been canceled.');

        }
}
