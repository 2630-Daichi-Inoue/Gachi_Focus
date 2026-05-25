<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Space;
use App\Services\RefundService;
use App\Services\ReservationService;
use App\Services\StripePaymentService;
use App\Traits\AppliesChronologicalSort;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ReservationController extends Controller
{
    use AppliesChronologicalSort;

    public function __construct(
        private ReservationService $reservationService,
        private StripePaymentService $stripePaymentService,
    ) {}

    public function index(Request $request)
    {
        $reservationStatusList = ['pending', 'booked', 'canceled'];
        $sortList = ['date_future_to_past', 'date_past_to_future'];

        $request->validate([
            'name' => ['nullable', 'string', 'max:50'],
            'reservation_status' => ['nullable', Rule::in(array_merge(['all'], $reservationStatusList))],
            'sort' => ['nullable', Rule::in($sortList)],
            'rows_per_page' => ['nullable', 'integer', 'in:20,50,100'],
        ]);

        $query = Reservation::query()
            ->where('user_id', Auth::id())
            ->with('space');

        if ($request->filled('name')) {
            $query->whereHas('space', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%'.$request->name.'%');
            });
        }

        $reservationStatus = $request->input('reservation_status', 'all');
        if ($reservationStatus !== 'all') {
            $query->where('reservation_status', $reservationStatus);
        }

        $rowsPerPage = (int) $request->input('rows_per_page', 20);

        $this->applyChronologicalSort($query, $request->input('sort', 'date_future_to_past'), 'started_at', 'date_past_to_future');

        $reservations = $query
            ->paginate($rowsPerPage)
            ->withQueryString();

        $reservations->load([
            'review' => function ($q) {
                $q->withTrashed();
            },
        ]);

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
            'filters' => [
                'name' => $request->name,
                'reservation_status' => $request->input('reservation_status', 'all'),
                'sort' => $request->input('sort', 'date_future_to_past'),
                'rows_per_page' => $rowsPerPage,
            ],
        ]);
    }

    public function create(Request $request, Space $space)
    {
        if (Auth::user()->isRestricted()) {
            return redirect()->route('spaces.show', $space)
                ->with('error', 'Your account is currently restricted. New reservations cannot be made.');
        }

        if (! $space->isPublic()) {
            return redirect()->route('spaces.index')
                ->with('error', 'Sorry, but this space is not currently available.');
        }

        $date = $request->input('date', Carbon::today()->toDateString());

        ['startCandidates' => $startCandidates, 'lastStartedAt' => $lastStartedAt]
            = $this->reservationService->buildStartCandidates($space, $date);

        return Inertia::render('Reservations/Create', [
            'space' => $space,
            'startCandidates' => $startCandidates,
            'lastStartedAt' => $lastStartedAt,
            'date' => $date,
        ]);
    }

    public function payment(StoreReservationRequest $request, Space $space)
    {
        if (! $space->isPublic()) {
            return redirect()->route('spaces.index')
                ->with('error', 'Sorry, but this space is not currently available.');
        }

        $checkedSpace = Space::whereKey($space->id)->firstOrFail();

        ['reservationData' => $reservationData, 'conflictingReservations' => $conflictingReservations]
            = $this->reservationService->calculatePaymentPreview($checkedSpace, $request->validated());

        return Inertia::render('Reservations/Payment', [
            'space' => $checkedSpace,
            'reservationData' => $reservationData,
            'conflictingReservations' => $conflictingReservations,
        ]);
    }

    public function store(StoreReservationRequest $request, Space $space)
    {
        if (Auth::user()->isRestricted()) {
            return redirect()->route('spaces.show', $space)
                ->with('error', 'Your account is currently restricted. New reservations cannot be made.');
        }

        $reservation = $this->reservationService->createPendingReservation($space, $request->validated());

        return redirect()->route('payments.checkout', $reservation);
    }

    public function cancel(Reservation $reservation, RefundService $refundService)
    {
        $this->authorize('cancel', $reservation);

        if ($reservation->reservation_status === 'canceled') {
            return back()->with('error', 'This reservation has already been canceled.');
        }

        if ($reservation->reservation_status === 'booked') {
            if (! $reservation->isCancelable()) {
                return back()->with('error', 'You cannot cancel within 1 hour of the reservation start time.');
            }

            $refundService->refundAndCancel($reservation);

            return back()->with('ok', 'Your reservation has been canceled and a full refund has been initiated.');
        }

        $this->stripePaymentService->expirePendingSessions($reservation);
        $this->reservationService->cancelPendingReservation($reservation);

        return back()->with('ok', 'Your reservation has been canceled.');
    }
}
