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
            'reservation_id' => ['nullable', 'string', 'max:26'], // For admin's contact index page to link to specific reservation
            'user_name'      => ['nullable', 'string', 'max:50'],
            'space_name'     => ['nullable', 'string', 'max:50'],
            'date_from'      => ['nullable', 'date'],
            'date_to'        => ['nullable', 'date','after_or_equal:date_from'],
            'status'         => ['nullable', Rule::in(array_merge(['all'], $statusKeys))],
            'rows_per_page'  => ['nullable', 'integer', 'in:20,50,100']
        ]);

        $query = Reservation::query()
                ->with(['user', 'space']);

        // Filter by reservation ID from admin's contact index page
        if ($reservationId = trim((string)$request->input('reservation_id', ''))) {
            $query->where('id', $reservationId);
        } else {
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
                $query->where('ended_at', '>=', "{$from} 00:00:00");
            }
            if ($to = $request->input('date_to')) {
                $query->where('started_at', '<=', "{$to} 23:59:59");
            }
            // Filter by status
            $status = $request->input('status', 'all');
            if($status !== 'all') {
                $query->where('reservation_status', $status);
            }
        }

        $rowsPerPage = (int)$request->input('rows_per_page', 20);

        $reservations = $query
                        ->orderBy('started_at', 'asc')
                        ->paginate($rowsPerPage);

        return view('admin.reservations.index', compact('reservations'));
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->ended_at < now()) {
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

}
