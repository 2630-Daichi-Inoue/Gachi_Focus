<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $today = now();
        $startOfWeek  = $today->copy()->startOfWeek();
        $endOfWeek    = $today->copy()->endOfWeek();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth   = $today->copy()->endOfMonth();
        $startOfYear  = $today->copy()->startOfYear();
        $endOfYear    = $today->copy()->endOfYear();

        // Yearly 
        $years = range($today->year - 9, $today->year);
        $salesYearRaw = DB::table('reservations')
            ->selectRaw("YEAR(reservations.created_at) as year, SUM(reservations.price) as total")
            ->groupBy('year')
            ->pluck('total', 'year')
            ->toArray();

        $salesYear = [];
        foreach ($years as $y) {
            $salesYear[$y] = $salesYearRaw[$y] ?? 0;
        }

        //  Monthly  
        $months = range(1, 12);
        $salesMonthRaw = DB::table('reservations')
            ->selectRaw("MONTH(reservations.created_at) as month, SUM(reservations.price) as total")
            ->whereYear('reservations.created_at', $today->year)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $salesMonth = [];
        foreach ($months as $m) {
            $salesMonth[$m] = $salesMonthRaw[$m] ?? 0;
        }

        // Weekly (Mon〜Sun)
        $weekDays = [];
        for ($d = 0; $d < 7; $d++) {
            $date = $startOfWeek->copy()->addDays($d);
            $weekDays[$date->format('D')] = 0;
        }

        $salesWeekRaw = DB::table('reservations')
            ->selectRaw("DATE(reservations.created_at) as date, SUM(reservations.price) as total")
            ->whereBetween('reservations.created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        foreach ($salesWeekRaw as $date => $total) {
            $day = Carbon::parse($date)->format('D');
            $weekDays[$day] = $total;
        }
        $salesWeek = $weekDays;

        // -------- Region --------
        $regions = ['JPN', 'PHL', 'AUS', 'USA'];

        // year
        $salesByRegionYear = [];
        foreach ($regions as $region) {
            $raw = DB::table('reservations')
                ->join('spaces', 'reservations.space_id', '=', 'spaces.id')
                ->selectRaw("YEAR(reservations.created_at) as year, SUM(reservations.price) as total")
                ->where('spaces.region', $region)
                ->groupBy('year')
                ->pluck('total', 'year')
                ->toArray();

            foreach ($years as $y) {
                $salesByRegionYear[$region][$y] = $raw[$y] ?? 0;
            }
        }

        // month
        $salesByRegionMonth = [];
        foreach ($regions as $region) {
            $raw = DB::table('reservations')
                ->join('spaces', 'reservations.space_id', '=', 'spaces.id')
                ->selectRaw("MONTH(reservations.created_at) as month, SUM(reservations.price) as total")
                ->whereYear('reservations.created_at', $today->year)
                ->where('spaces.region', $region)
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            foreach ($months as $m) {
                $salesByRegionMonth[$region][$m] = $raw[$m] ?? 0;
            }
        }

        // week
        $salesByRegionWeek = [];
        foreach ($regions as $region) {
            $raw = DB::table('reservations')
                ->join('spaces', 'reservations.space_id', '=', 'spaces.id')
                ->selectRaw("DATE(reservations.created_at) as date, SUM(reservations.price) as total")
                ->whereBetween('reservations.created_at', [$startOfWeek, $endOfWeek])
                ->where('spaces.region', $region)
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();

            foreach ($weekDays as $day => $val) {
                $salesByRegionWeek[$region][$day] = 0;
            }
            foreach ($raw as $date => $total) {
                $day = Carbon::parse($date)->format('D');
                $salesByRegionWeek[$region][$day] = $total;
            }
        }

        // summary
        $summary = [
            'today' => DB::table('reservations')
                ->whereDate('created_at', $today->toDateString())
                ->sum('price'),
            'week' => DB::table('reservations')
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->sum('price'),
            'month' => DB::table('reservations')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('price'),
            'year' => DB::table('reservations')
                ->whereBetween('created_at', [$startOfYear, $endOfYear])
                ->sum('price'),
        ];


        return view('admin.home', [
            'salesYear'   => $salesYear,
            'salesMonth'  => $salesMonth,
            'salesWeek'   => $salesWeek,
            'salesByRegionYear'  => $salesByRegionYear,
            'salesByRegionMonth' => $salesByRegionMonth,
            'salesByRegionWeek'  => $salesByRegionWeek,
            'summary' => $summary,
        ]);
    }
}
