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
            'name' => ['nullable','string','max:50'],
            'space' => ['nullable','string','max:50'],
            'date_from' => ['nullable','date'],
            'date_to'   => ['nullable','date','after_or_equal:date_from'],
            'status' => ['nullable','in:all,' . implode(',', $statusKeys)],
            'payment' => ['nullable','in:all,' . implode(',', $paymentKeys)],
            'rows_per_page' => ['nullable', 'integer', 'in:20,50,100']
        ]);

        $q = \App\Models\Reservation::query()->withTrashed();

        if($name = trim($request->input('name', ''))) {
            $q->where('name', 'like', "%{$name}%");
        }

        if($space = trim($request->input('space', ''))) {
            $q->where('space', 'like', "%{$space}%");
        }

        $status = $request->input('status', 'all');
        if($status !== 'all') {
            $q->where('status', $status);
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

    # ban
    public function deactivate($id)
    {
        $this->user->destroy($id);

        return back();
    }

    # activate
    public function activate($id)
    {
        $this->user->onlyTrashed()->findOrFail($id)->restore();

        return back();
    }
}
