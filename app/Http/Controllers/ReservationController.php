<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreReservationRequest;
use App\Services\RefundService;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
        /**
     * Display a listing of the reservations.
     */
    public function index(Request $request)
    {
        $reservationStatusList = ['pending', 'booked', 'canceled'];
        $sortList = ['date_future_to_past', 'date_past_to_future'];

        $request->validate([
            'name'               => ['nullable','string','max:50'],
            'reservation_status' => ['nullable', Rule::in(array_merge(['all'], $reservationStatusList))],
            'sort'               => ['nullable', Rule::in($sortList)],
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

        // upcoming / past / canceled can be implemented later if needed by checking started_at, ended_at and reservation_status
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

    private function applySort(Builder $q, ?string $sort): void
    {
        switch ($sort ?? 'date_future_to_past') {
            case 'date_future_to_past':
                $q->orderBy('started_at', 'desc')
                    ->latest('id');
                break;

            case 'date_past_to_future':
                $q->orderBy('started_at', 'asc')
                    ->latest('id');
                break;

            default:
                $q->orderBy('started_at', 'desc')
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
        $lastStartedAt = $closeTime->copy()->subMinutes(30); // Last possible start time is 30 min before close

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
        while ($cursorOpenTime->lte($lastStartedAt)) {
            $formattedCursorTime = $cursorOpenTime->format('H:i');
            $startCandidates[] = $formattedCursorTime;
            $cursorOpenTime->addMinutes(30);
        }

        return Inertia::render('Reservations/Create', [
            'space' => $space,
            'startCandidates' => $startCandidates,
            'lastStartedAt' => $lastStartedAt->format('H:i'),
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
        $newStartedAt  = Carbon::parse($data['date'] . ' ' . $data['started_at']);
        $newEndedAt    = Carbon::parse($data['date'] . ' ' . $data['ended_at']);

        $checkedSpace = Space::whereKey($space->id)->firstOrFail();

        $overlappingQuantity = Reservation::query()
                                            ->where('space_id', $checkedSpace->id)
                                            ->whereIn('reservation_status', ['booked', 'pending'])
                                            ->where('started_at', '<', $newEndedAt)
                                            ->where('ended_at', '>', $newStartedAt)
                                            ->sum('quantity');

        if ($overlappingQuantity + $data['quantity'] > $checkedSpace->capacity) {
            throw ValidationException::withMessages([
                'quantity' => 'Sorry, but there are not enough spaces available for the selected time slot.',
            ]);
        }

        $conflictingReservations = Reservation::query()
            ->where('user_id', Auth::id())
            ->whereIn('reservation_status', ['booked', 'pending'])
            ->where('ended_at', '>', now())
            ->where('started_at', '<', $newEndedAt)
            ->where('ended_at', '>', $newStartedAt)
            ->with('space:id,name')
            ->get(['id', 'space_id', 'started_at', 'ended_at']);

        $unitPriceYen = Carbon::parse($data['date'])->isWeekend()
        ? $checkedSpace->weekend_price_yen
        : $checkedSpace->weekday_price_yen;

        $slotCount = $newStartedAt->diffInMinutes($newEndedAt) / 30;

        return Inertia::render('Reservations/Payment', [
            'space' => $checkedSpace,
            'reservationData' => [
                'date' => $data['date'],
                'started_at' => $data['started_at'],
                'ended_at' => $data['ended_at'],
                'quantity' => $data['quantity'],
                'total_price_yen' => $unitPriceYen * $data['quantity'] * $slotCount,
            ],
            'conflictingReservations' => $conflictingReservations,
        ]);
    }

    /**
     * Store a reservation then go to index page.
     */
    public function store(StoreReservationRequest $request, Space $space)
    {
        $data = $request->validated();

        // normalize time to HH:mm
        $newStartedAt  = Carbon::parse($data['date'] . ' ' . $data['started_at']);
        $newEndedAt    = Carbon::parse($data['date'] . ' ' . $data['ended_at']);

        $reservation = DB::transaction(function() use ($space, $data, $newStartedAt, $newEndedAt) {
            $checkedSpace = Space::whereKey($space->id)->lockForUpdate()->firstOrFail();

            $overlappingQuantity = Reservation::query()
                                    ->where('space_id', $checkedSpace->id)
                                    ->whereIn('reservation_status', ['booked', 'pending'])
                                    ->where('started_at', '<', $newEndedAt)
                                    ->where('ended_at', '>', $newStartedAt)
                                    ->sum('quantity');

            if ($overlappingQuantity + $data['quantity'] > $checkedSpace->capacity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Sorry, but there are not enough spaces available for the selected time slot.',
                ]);
            }

            $unitPriceYen = Carbon::parse($data['date'])->isWeekend()
            ? $checkedSpace->weekend_price_yen
            : $checkedSpace->weekday_price_yen;

            $slotCount = $newStartedAt->diffInMinutes($newEndedAt) / 30;

            return Reservation::create([
                'user_id'            => Auth::id(),
                'space_id'           => $checkedSpace->id,
                'reservation_status' => 'pending',
                'started_at'         => $newStartedAt,
                'ended_at'           => $newEndedAt,
                'quantity'           => $data['quantity'],
                'slot_count'         => $slotCount,
                'unit_price_yen'     => $unitPriceYen,
                'total_price_yen'    => $unitPriceYen * $data['quantity'] * $slotCount,
            ]);
        });

        return redirect()->route('payments.checkout', $reservation);
    }

    public function cancel(Reservation $reservation, RefundService $refundService)
    {
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to cancel this reservation.');
        }

        if ($reservation->reservation_status === 'canceled') {
            return back()->with('error', 'This reservation has already been canceled.');
        }

        // Pending reservations can be canceled freely (payment not yet completed)
        if ($reservation->reservation_status === 'booked') {
            if (Carbon::parse($reservation->started_at)->subHour()->lte(now())) {
                return back()->with('error', 'You cannot cancel within 1 hour of the reservation start time.');
            }

            // Issue a full refund and cancel atomically
            $refundService->refundAndCancel($reservation);

            return back()->with('ok', 'Your reservation has been canceled and a full refund has been initiated.');
        }

        // Pending status: no payment captured yet — just cancel
        DB::transaction(function () use ($reservation) {
            $reservation->update([
                'reservation_status' => 'canceled',
                'canceled_at'        => now(),
            ]);

            $reservation->payments()
                ->where('status', 'pending')
                ->update(['status' => 'canceled']);
        });

        return back()->with('ok', 'Your reservation has been canceled.');
    }
}
