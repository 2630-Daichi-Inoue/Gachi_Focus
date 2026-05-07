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
        $fiveYearsAgo = Carbon::create($today->year - 4)->startOfYear();

        $summary = [
            'today' => Reservation::booked()->whereDate('started_at', $today->toDateString())->sum('total_price_yen'),
            'week'  => Reservation::booked()->whereBetween('started_at', [$startOfWeek, $endOfWeek])->sum('total_price_yen'),
            'month' => Reservation::booked()->whereYear('started_at', $today->year)->whereMonth('started_at', $today->month)->sum('total_price_yen'),
            'year'  => Reservation::booked()->whereYear('started_at', $today->year)->sum('total_price_yen'),
        ];

        // Annual totals for past 5 years
        $rawByYear = Reservation::booked()
            ->where('started_at', '>=', $fiveYearsAgo)
            ->selectRaw('YEAR(started_at) as yr, SUM(total_price_yen) as total')
            ->groupByRaw('YEAR(started_at)')
            ->pluck('total', 'yr');

        $salesYear = [];
        foreach (range($today->year - 4, $today->year) as $year) {
            $salesYear[$year] = $rawByYear[$year] ?? 0;
        }

        // Annual totals by prefecture for past 5 years
        $rawByPrefYear = Reservation::booked()
            ->join('spaces', 'reservations.space_id', '=', 'spaces.id')
            ->where('reservations.started_at', '>=', $fiveYearsAgo)
            ->selectRaw("COALESCE(spaces.prefecture, 'Unknown') as pref, YEAR(reservations.started_at) as yr, SUM(reservations.total_price_yen) as total")
            ->groupByRaw("COALESCE(spaces.prefecture, 'Unknown'), YEAR(reservations.started_at)")
            ->get();

        $salesByPrefectureYear = [];
        foreach ($rawByPrefYear as $row) {
            $salesByPrefectureYear[$row->pref][$row->yr] = $row->total;
        }

        // Monthly totals for current year
        $rawByMonth = Reservation::booked()
            ->whereYear('started_at', $today->year)
            ->selectRaw('MONTH(started_at) as mo, SUM(total_price_yen) as total')
            ->groupByRaw('MONTH(started_at)')
            ->pluck('total', 'mo');

        $salesMonth = [];
        foreach (range(1, 12) as $month) {
            $salesMonth[$month] = $rawByMonth[$month] ?? 0;
        }

        // Monthly totals by prefecture for current year
        $rawByPrefMonth = Reservation::booked()
            ->join('spaces', 'reservations.space_id', '=', 'spaces.id')
            ->whereYear('reservations.started_at', $today->year)
            ->selectRaw("COALESCE(spaces.prefecture, 'Unknown') as pref, MONTH(reservations.started_at) as mo, SUM(reservations.total_price_yen) as total")
            ->groupByRaw("COALESCE(spaces.prefecture, 'Unknown'), MONTH(reservations.started_at)")
            ->get();

        $salesByPrefectureMonth = [];
        foreach ($rawByPrefMonth as $row) {
            $salesByPrefectureMonth[$row->pref][$row->mo] = $row->total;
        }

        // Weekly totals by day — DAYOFWEEK: 1=Sun, 2=Mon, ..., 7=Sat
        $dowMap    = [1 => 'Sun', 2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat'];
        $weekDays  = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $salesWeek = array_fill_keys($weekDays, 0);
        $salesByPrefectureWeek = [];

        $rawByPrefWeek = Reservation::booked()
            ->join('spaces', 'reservations.space_id', '=', 'spaces.id')
            ->whereBetween('reservations.started_at', [$startOfWeek, $endOfWeek])
            ->selectRaw("COALESCE(spaces.prefecture, 'Unknown') as pref, DAYOFWEEK(reservations.started_at) as dow, SUM(reservations.total_price_yen) as total")
            ->groupByRaw("COALESCE(spaces.prefecture, 'Unknown'), DAYOFWEEK(reservations.started_at)")
            ->get();

        foreach ($rawByPrefWeek as $row) {
            $day = $dowMap[$row->dow];
            $salesWeek[$day] += $row->total;
            $salesByPrefectureWeek[$row->pref][$day] = ($salesByPrefectureWeek[$row->pref][$day] ?? 0) + $row->total;
        }

        $weekLabels = array_map(
            fn($i) => [$startOfWeek->copy()->addDays($i)->format('D'), $startOfWeek->copy()->addDays($i)->format('n/j')],
            range(0, 6)
        );

        return view('admin.dashboard', compact(
            'summary',
            'salesYear',
            'salesMonth',
            'salesWeek',
            'weekLabels',
            'salesByPrefectureYear',
            'salesByPrefectureMonth',
            'salesByPrefectureWeek',
        ));
    }
}
