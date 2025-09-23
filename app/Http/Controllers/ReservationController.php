<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Reservation;
use Carbon\Carbon;

class ReservationController extends Controller
{
    private Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**show form（/room-b） */
    public function create()
    {
        $room = (object)[
            'name'       => 'Room B',
            'image_path' => '/images/room-b.jpg',
            'max_adults' => 4,
            'types'      => ['Focus Booth','Meeting','Phone Call'],
            'facilities' => ['Monitor','Whiteboard','Power Outlet','HDMI','USB-C'],
        ];
        return view('rooms.reserve', compact('room'));
    }

    /** save（POST /room-b）→ show */
    public function store(Request $request)
    {
        
        $request->merge([
            'start_time' => $request->input('time_from'),
            'end_time'   => $request->input('time_to'),
        ]);

        $data = $request->validate([
            'type'         => ['required','string', Rule::in(['Focus Booth','Meeting','Phone Call'])],
            'date'         => ['required','date','after_or_equal:today'],
            'start_time'   => ['required','date_format:H:i'],
            'end_time'     => ['required','date_format:H:i','after:start_time'],
            'adults'       => ['required','integer','min:1','max:20'],
            'facilities'   => ['array'],
            'facilities.*' => [Rule::in(['Monitor','Whiteboard','Power Outlet','HDMI','USB-C'])],
        ]);

        // calculate payment
        $typeKey = match($data['type']) {
            'Focus Booth' => 'focus_booth',
            'Meeting'     => 'meeting',
            'Phone Call'  => 'phone_call',
            default       => $data['type'],
        };

        $total = $this->calcTotal(
            $typeKey,
            $data['start_time'],
            $data['end_time'],
            (int)$data['adults'],
            $data['facilities'] ?? []
        );

        // ★ save to DB
        $reservation = $this->reservation->create([
            'user_id'     => auth()->id(),
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

    public function show($id)
    {
        $reservation = $this->reservation->findOrFail($id);
        return view('rooms.show', compact('reservation')); 
    }


    private function calcTotal(string $type, string $start, string $end, int $adults, array $fac): float
    {
        $typePrice = ['focus_booth'=>10,'meeting'=>15,'phone_call'=>8][$type] ?? 0;
        $facPrice  = ['Monitor'=>3,'Whiteboard'=>2,'Power Outlet'=>0,'HDMI'=>0,'USB-C'=>0];

        $minutes = Carbon::parse($start)->diffInMinutes(Carbon::parse($end));
        $hours   = max(0, $minutes / 60);
        $base    = $typePrice * $hours;
        $facSum  = array_sum(array_map(fn($k)=>$facPrice[$k] ?? 0, $fac));
        return ($base + $facSum) * max(1, $adults);
    }
}
