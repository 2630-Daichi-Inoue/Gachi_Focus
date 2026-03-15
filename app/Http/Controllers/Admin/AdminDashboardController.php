<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Reservation;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = now();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $startOfYear = $today->copy()->startOfYear();
        $endOfYear = $today->copy()->endOfYear();

        $reservations = Reservation::booked()->get();

        $summary = [
            'today' => $reservations->filter(
                fn ($r) => $r->created_at->isSameDay($today)
            )->sum('total_price_yen'),

            'week' => $reservations->filter(
                fn ($r) => $r->created_at->betweenIncluded($startOfWeek, $endOfWeek)
            )->sum('total_price_yen'),

            'month' => $reservations->filter(
                fn ($r) => $r->created_at->isSameMonth($today)
            )->sum('total_price_yen'),

            'year' => $reservations->filter(
                fn ($r) => $r->created_at->isSameYear($today)
            )->sum('total_price_yen'),
        ];

        $salesYear = [];
        foreach (range($today->year - 4, $today->year) as $year) {
            $salesYear[$year] = $reservations->filter(
                fn ($r) => $r->created_at->year === $year
            )->sum('total_price_yen');
        }

        $salesMonth = [];
        foreach (range(1, 12) as $month) {
            $salesMonth[$month] = $reservations->filter(
                fn ($r) => $r->created_at->year === $today->year && $r->created_at->month === $month
            )->sum('total_price_yen');
        }

        $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $salesWeek = array_fill_keys($weekDays, 0);

        foreach ($reservations->filter(
            fn ($r) => $r->created_at->betweenIncluded($startOfWeek, $endOfWeek)
        ) as $reservation) {
            $day = $reservation->created_at->format('D');
            $salesWeek[$day] += $reservation->total_price_yen;
        }

        return view('admin.dashboard', compact(
            'summary',
            'salesYear',
            'salesMonth',
            'salesWeek'
        ));
    }
}
