<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Validation\Rule;

class ReservationsController extends Controller
{

    public function index(Request $request)
    {

        $statusKeys = array_keys(Reservation::RESERVATION_STATUS_MAP);
        $request->validate([
            'user_name' => ['nullable','string','max:50'],
            'space_name' => ['nullable','string','max:50'],
            'date_from' => ['nullable','date'],
            'date_to'   => ['nullable','date','after_or_equal:date_from'],
            'status' => ['nullable', Rule::in(array_merge(['all'], $statusKeys))],
            'rows_per_page' => ['nullable', 'integer', 'in:20,50,100']
        ]);

        $query = Reservation::query()
                ->with(['user','space']);

        // Filter by user name
        if ($userName = trim((string)$request->input('user_name', ''))) {
            $query->whereHas('user', fn($uq) =>
                $uq->where('name', 'like', "%{$userName}%")
            );
        }

        // Filter by space name
        if ($spaceName = trim((string)$request->input('space_name', ''))) {
            $query->whereHas('space', fn($sq) =>
                $sq->where('name', 'like', "%{$spaceName}%")
            );
        }

        // Filter by date range
        if ($from = $request->input('date_from')) {
            $query->where('end_at', '>=', "{$from} 00:00:00");
        }
        if ($to = $request->input('date_to')) {
            $query->where('start_at', '<=', "{$to} 23:59:59");
        }

        // Filter by status
        $status = $request->input('status', 'all');
        if($status !== 'all') {
            $query->where('reservation_status', $status);
        }

        $rowsPerPage = (int)$request->input('rows_per_page', 20);

        $reservations = $query
                        ->orderBy('start_at', 'asc')
                        ->paginate($rowsPerPage);

        return view('admin.reservations.index', compact('reservations'));
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->end_at < now()) {
            return redirect()->route('admin.reservations.index')
                ->with('error', 'The reservation has already ended.');
        }

        # 1. Update the reservation data in the reservations table
        $reservation->fill ([
            'reservation_status' => 'canceled',
        ]);

        $reservation->save();

        # 2. redirect to the index
        return redirect()->route('admin.reservations.index')
                        ->with('status', 'Successfully canceled.');
    }

    # cancel or refund
    public function action(Request $request, $id) {
        $request->validate([
            'action' => ['required', 'in:cancel,refund']
        ]);

        $reservation = Reservation::with('payment')->findOrFail($id);

        // $reservation->load('payment');

        if ($request->action === 'cancel') {
            // 予約をキャンセル状態に
            $reservation->reservation_status = 'canceled';

            // 支払いがPaidなら、返金待ち状態に変更
            if ($reservation->payment && $reservation->payment->status === 'Paid') {
                $reservation->payment->status = 'Refund Pending';
                $reservation->payment->save();
            }

            $reservation->save();
            return back()->with('ok', 'Cancellation was done.');
        }

        if ($request->action === 'refund') {
            if (!$reservation->payment) {
                return back()->with('error', 'No payments for this reservation.');
            }

            // 支払いを返金済みに変更
            $reservation->payment->status = 'Refunded';
            $reservation->payment->save();

            // 予約もキャンセル済みに寄せる（運用に応じて任意）
            if ($reservation->reservation_status !== 'canceled') {
                $reservation->reservation_status = 'canceled';
                $reservation->save();
            }

            return back()->with('ok', 'Refund was done.');
        }

        return back()->with('error', 'An error occurred.');
    }
}
