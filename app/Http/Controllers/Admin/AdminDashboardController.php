<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Reservation;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today       = now();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $reservations = Reservation::booked()->with('space:id,prefecture')->get();

        $summary = [
            'today' => $reservations->filter(fn($r) => $r->start_at->isSameDay($today))->sum('total_price_yen'),
            'week'  => $reservations->filter(fn($r) => $r->start_at->betweenIncluded($startOfWeek, $endOfWeek))->sum('total_price_yen'),
            'month' => $reservations->filter(fn($r) => $r->start_at->isSameMonth($today))->sum('total_price_yen'),
            'year'  => $reservations->filter(fn($r) => $r->start_at->isSameYear($today))->sum('total_price_yen'),
        ];

        $salesYear             = [];
        $salesByPrefectureYear = [];
        foreach (range($today->year - 4, $today->year) as $year) {
            $filtered         = $reservations->filter(fn($r) => $r->start_at->year === $year);
            $salesYear[$year] = $filtered->sum('total_price_yen');
            foreach ($filtered as $r) {
                $pref = $r->space->prefecture ?? 'Unknown';
                $salesByPrefectureYear[$pref][$year] = ($salesByPrefectureYear[$pref][$year] ?? 0) + $r->total_price_yen;
            }
        }

        $salesMonth             = [];
        $salesByPrefectureMonth = [];
        foreach (range(1, 12) as $month) {
            $filtered           = $reservations->filter(fn($r) => $r->start_at->year === $today->year && $r->start_at->month === $month);
            $salesMonth[$month] = $filtered->sum('total_price_yen');
            foreach ($filtered as $r) {
                $pref = $r->space->prefecture ?? 'Unknown';
                $salesByPrefectureMonth[$pref][$month] = ($salesByPrefectureMonth[$pref][$month] ?? 0) + $r->total_price_yen;
            }
        }

        $weekDays              = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $salesWeek             = array_fill_keys($weekDays, 0);
        $salesByPrefectureWeek = [];
        foreach ($reservations->filter(fn($r) => $r->start_at->betweenIncluded($startOfWeek, $endOfWeek)) as $r) {
            $day             = $r->start_at->format('D');
            $salesWeek[$day] += $r->total_price_yen;
            $pref = $r->space->prefecture ?? 'Unknown';
            $salesByPrefectureWeek[$pref][$day] = ($salesByPrefectureWeek[$pref][$day] ?? 0) + $r->total_price_yen;
        }

        return view('admin.dashboard', compact(
            'summary',
            'salesYear',
            'salesMonth',
            'salesWeek',
            'salesByPrefectureYear',
            'salesByPrefectureMonth',
            'salesByPrefectureWeek',
        ));
    }
}
