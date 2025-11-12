<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        // --- Date anchors (use DATE column, not TIME) ---
        $today        = now()->startOfDay();
        $startOfWeek  = $today->copy()->startOfWeek();
        $endOfWeek    = $today->copy()->endOfWeek();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth   = $today->copy()->endOfMonth();
        $startOfYear  = $today->copy()->startOfYear();
        $endOfYear    = $today->copy()->endOfYear();

        // Last 10 years
        $years = range($today->year - 9, $today->year);

        // ===== Yearly sales (by reservation DATE) =====
        $salesYearRaw = DB::table('reservations')
            ->selectRaw('YEAR(`date`) as year, SUM(total_price) as total')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->groupBy('year')
            ->pluck('total', 'year')
            ->toArray();

        $salesYear = [];
        foreach ($years as $y) {
            $salesYear[$y] = (float) ($salesYearRaw[$y] ?? 0);
        }

        // ===== Monthly sales (current year) =====
        $months = range(1, 12);
        $salesMonthRaw = DB::table('reservations')
            ->selectRaw('MONTH(`date`) as month, SUM(total_price) as total')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->whereYear('date', $today->year)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $salesMonth = [];
        foreach ($months as $m) {
            $salesMonth[$m] = (float) ($salesMonthRaw[$m] ?? 0);
        }

        // ===== Weekly sales (Mon..Sun) =====
        $weekDays = [];
        for ($d = 0; $d < 7; $d++) {
            $date = $startOfWeek->copy()->addDays($d);
            $weekDays[$date->format('D')] = 0; // Mon, Tue, ...
        }

        $salesWeekRaw = DB::table('reservations')
            ->selectRaw('`date` as d, SUM(total_price) as total')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->groupBy('d')
            ->pluck('total', 'd')
            ->toArray();

        foreach ($salesWeekRaw as $d => $total) {
            $weekDays[\Carbon\Carbon::parse($d)->format('D')] = (float) $total;
        }
        $salesWeek = $weekDays;

        // ===== Region key: prefer payment_region; fallback to currency =====
        // No join to spaces; spaces.country_code does not exist.
        $regionExpr = "COALESCE(NULLIF(payment_region, ''), currency)";

        // ----- By Region (Year) -----
        $salesByCountryYear = DB::table('reservations')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->selectRaw("YEAR(`date`) as year, {$regionExpr} as region, SUM(total_price) as total")
            ->groupBy('year', 'region')
            ->orderBy('year')
            ->get()
            ->groupBy('region')
            ->map(fn($rows) => $rows->pluck('total', 'year'));

        // ----- By Region (Month of current year) -----
        $salesByCountryMonth = DB::table('reservations')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->whereYear('date', $today->year)
            ->selectRaw("MONTH(`date`) as month, {$regionExpr} as region, SUM(total_price) as total")
            ->groupBy('month', 'region')
            ->orderBy('month')
            ->get()
            ->groupBy('region')
            ->map(fn($rows) => $rows->pluck('total', 'month'));

        // ----- By Region (Week; per DATE) -----
        $salesByCountryWeek = DB::table('reservations')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->selectRaw("`date` as d, {$regionExpr} as region, SUM(total_price) as total")
            ->groupBy('d', 'region')
            ->orderBy('d')
            ->get()
            ->groupBy('region')
            ->map(fn($rows) => $rows->pluck('total', 'd'));

        // ===== Summary cards =====
        $summary = [
            'today' => DB::table('reservations')
                ->whereNull('deleted_at')
                ->where('payment_status', 'paid')
                ->whereDate('date', $today->toDateString())
                ->sum('total_price'),

            'week' => DB::table('reservations')
                ->whereNull('deleted_at')
                ->where('payment_status', 'paid')
                ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->sum('total_price'),

            'month' => DB::table('reservations')
                ->whereNull('deleted_at')
                ->where('payment_status', 'paid')
                ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->sum('total_price'),

            'year' => DB::table('reservations')
                ->whereNull('deleted_at')
                ->where('payment_status', 'paid')
                ->whereBetween('date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
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