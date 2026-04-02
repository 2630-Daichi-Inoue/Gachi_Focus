<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\Amenity;
use App\Support\Pricing;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\CustomNotification;
use App\Models\User;
use Inertia\Inertia;

class ReservationController extends Controller
{
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

        // Candidates for reservation start time (every 30 min slot between open and close)
        $startCandidates = [];
        $cursorOpenTime = $openTime->copy();
        $lastStartAt = $closeTime->copy()->subMinutes(30); // Last possible start time is 30 min before close
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
}
