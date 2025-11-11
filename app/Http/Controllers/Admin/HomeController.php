<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $today       = now();
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek   = $today->copy()->endOfWeek();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth  = $today->copy()->endOfMonth();
        $startOfYear = $today->copy()->startOfYear();
        $endOfYear   = $today->copy()->endOfYear();

        $years = range($today->year - 9, $today->year);

        $salesYearRaw = DB::table('reservations')
            ->selectRaw("YEAR(start_time) as year, SUM(total_price) as total")
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->groupBy('year')
            ->pluck('total', 'year')
            ->toArray();

        $salesYear = [];
        foreach ($years as $y) {
            $salesYear[$y] = $salesYearRaw[$y] ?? 0;
        }

        $months = range(1, 12);
        $salesMonthRaw = DB::table('reservations')
            ->selectRaw("MONTH(start_time) as month, SUM(total_price) as total")
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->whereYear('start_time', $today->year)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $salesMonth = [];
        foreach ($months as $m) {
            $salesMonth[$m] = $salesMonthRaw[$m] ?? 0;
        }

        $weekDays = [];
        for ($d = 0; $d < 7; $d++) {
            $date = $startOfWeek->copy()->addDays($d);
            $weekDays[$date->format('D')] = 0; // Mon, Tue...
        }

        $salesWeekRaw = DB::table('reservations')
            ->selectRaw("DATE(start_time) as date, SUM(total_price) as total")
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->groupByRaw("DATE(start_time)")
            ->pluck('total', 'date')
            ->toArray();

        foreach ($salesWeekRaw as $date => $total) {
            $day = Carbon::parse($date)->format('D'); // Mon/Tue...
            $weekDays[$day] = $total;
        }
        $salesWeek = $weekDays;

        $salesByCountryYear = Reservation::join('spaces', 'reservations.space_id', '=', 'spaces.id')
            ->whereNull('reservations.deleted_at')
            ->where('reservations.payment_status', 'paid')
            ->selectRaw('YEAR(reservations.start_time) as year, spaces.country_code, SUM(reservations.total_price) as total')
            ->groupByRaw('YEAR(reservations.start_time), spaces.country_code')
            ->orderBy('year')
            ->get()
            ->groupBy('country_code')
            ->map(fn($rows) => $rows->pluck('total', 'year'));

        $salesByCountryMonth = Reservation::join('spaces', 'reservations.space_id', '=', 'spaces.id')
            ->whereNull('reservations.deleted_at')
            ->where('reservations.payment_status', 'paid')
            ->whereYear('reservations.start_time', $today->year)
            ->selectRaw('MONTH(reservations.start_time) as month, spaces.country_code, SUM(reservations.total_price) as total')
            ->groupByRaw('MONTH(reservations.start_time), spaces.country_code')
            ->orderBy('month')
            ->get()
            ->groupBy('country_code')
            ->map(fn($rows) => $rows->pluck('total', 'month'));

        $salesByCountryWeek = Reservation::join('spaces', 'reservations.space_id', '=', 'spaces.id')
            ->whereNull('reservations.deleted_at')
            ->where('reservations.payment_status', 'paid')
            ->whereBetween('reservations.start_time', [$startOfWeek, $endOfWeek])
            ->selectRaw('DATE(reservations.start_time) as date, spaces.country_code, SUM(reservations.total_price) as total')
            ->groupByRaw('DATE(reservations.start_time), spaces.country_code')
            ->orderBy('date')
            ->get()
            ->groupBy('country_code')
            ->map(fn($rows) => $rows->pluck('total', 'date'));

        $summary = [
            'today' => DB::table('reservations')
                ->whereNull('deleted_at')
                ->where('payment_status', 'paid')
                ->whereDate('start_time', $today->toDateString())
                ->sum('total_price'),

            'week' => DB::table('reservations')
                ->whereNull('deleted_at')
                ->where('payment_status', 'paid')
                ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
                ->sum('total_price'),

            'month' => DB::table('reservations')
                ->whereNull('deleted_at')
                ->where('payment_status', 'paid')
                ->whereBetween('start_time', [$startOfMonth, $endOfMonth])
                ->sum('total_price'),

            'year' => DB::table('reservations')
                ->whereNull('deleted_at')
                ->where('payment_status', 'paid')
                ->whereBetween('start_time', [$startOfYear, $endOfYear])
                ->sum('total_price'),
        ];

        return view('admin.home', [
            'salesYear'          => $salesYear,
            'salesMonth'         => $salesMonth,
            'salesWeek'          => $salesWeek,
            'salesByRegionYear'  => $salesByCountryYear,
            'salesByRegionMonth' => $salesByCountryMonth,
            'salesByRegionWeek'  => $salesByCountryWeek,
            'summary'            => $summary,
        ]);
    }
}
