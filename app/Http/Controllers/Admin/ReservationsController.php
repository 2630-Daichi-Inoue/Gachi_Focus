<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Payment;


class ReservationsController extends Controller
{
    private $reservation;


    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }


    public function index(Request $request)
    {
        // 1. Validate the data
        $statusKeys = array_keys(Reservation::STATUS_MAP);
        $paymentKeys = array_keys(Reservation::PAYMENT_MAP);

        $request->validate([
            'user_name' => ['nullable','string','max:50'],
            'space_name' => ['nullable','string','max:50'],
            'date_from' => ['nullable','date'],
            'date_to'   => ['nullable','date','after_or_equal:date_from'],
            'status' => ['nullable','in:all,' . implode(',', $statusKeys)],
            'payment' => ['nullable','in:all,' . implode(',', $paymentKeys)],
            'rows_per_page' => ['nullable', 'integer', 'in:20,50,100']
        ]);


        $q = Reservation::query()
            ->with(['user','space','payment'])
            ->withTrashed();
        // $q = \App\Models\Reservation::query()->withTrashed();

        if ($userName = trim((string)$request->input('user_name', ''))) {
            $q->whereHas('user', fn($uq) =>
                $uq->where('name', 'like', "%{$userName}%")
            );
        }

        if ($spaceName = trim((string)$request->input('space_name', ''))) {
            $q->whereHas('space', fn($sq) =>
                $sq->where('name', 'like', "%{$spaceName}%")
            );
        }

        $status = $request->input('status', 'all');
        if($status !== 'all') {
            $q->where('status', $status);
        }

        if ($from = $request->input('date_from')) {
            $q->where('start_time', '>=', "{$from} 00:00:00");
        }
        if ($to = $request->input('date_to')) {
            $q->where('end_time', '<=', "{$to} 23:59:59");
        }

        $payment = $request->input('payment', 'all');
        if($payment !== 'all') {
            $q->whereHas('payment', fn($p) => $p->where('status', $payment));
        }

        $rowsPerPage = (int)$request->input('rows_per_page', 20);

        $all_reservations = $q->orderBy('id', 'desc')
                            ->paginate($rowsPerPage)
                            ->appends($request->query());

        return view('admin.reservations.index', compact('all_reservations'));
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
            $reservation->status = 'Cancelled';

            // 支払いがPaidなら、返金待ち状態に変更
            if ($reservation->payment && $reservation->payment->status === 'Paid') {
                $reservation->payment->status = 'Refund Pending';
                $reservation->payment->save();
            }

            $reservation->save();
            return back()->with('ok', '予約をキャンセルしました。');
        }

        if ($request->action === 'refund') {
            if (!$reservation->payment) {
                return back()->with('error', 'この予約には支払い情報がありません。');
            }

            // 支払いを返金済みに変更
            $reservation->payment->status = 'Refunded';
            $reservation->payment->save();

            // 予約もキャンセル済みに寄せる（運用に応じて任意）
            if ($reservation->status !== 'Cancelled') {
                $reservation->status = 'Cancelled';
                $reservation->save();
            }

            return back()->with('ok', '返金を完了しました。');
        }

        return back()->with('error', '不明な操作です。');
    }
}