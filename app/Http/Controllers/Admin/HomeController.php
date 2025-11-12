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
        $today        = now();
        $startOfWeek  = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek    = $today->copy()->endOfWeek(Carbon::SUNDAY);
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth   = $today->copy()->endOfMonth();
        $startOfYear  = $today->copy()->startOfYear();
        $endOfYear    = $today->copy()->endOfYear();

        // ---- Region Mapping ----
        $regionMap = [
            'JP' => 'Asia',
            'PH' => 'Asia',
            'US' => 'North America',
            'AU' => 'Oceania',
        ];
        $regions = array_unique(array_values($regionMap));

        // ---- Paid Reservations JOIN spaces ----
        $paidData = Reservation::join('spaces', 'reservations.space_id', '=', 'spaces.id')
            ->whereNull('reservations.deleted_at')
            ->where('reservations.payment_status', 'paid')
            ->whereNotNull('reservations.date')
            ->where('reservations.date', '!=', '0000-00-00')
            ->select('reservations.date', 'reservations.total_price', 'spaces.country_code')
            ->get();

        // ---- Initialize structures ----
        $years = range($today->year - 9, $today->year);
        $months = range(1, 12);
        $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        $initYear  = array_fill_keys($years, 0.0);
        $initMonth = array_fill_keys($months, 0.0);
        $initWeek  = array_fill_keys($weekDays, 0.0);

        $salesByRegionYear  = [];
        $salesByRegionMonth = [];
        $salesByRegionWeek  = [];

        $countriesYear  = [];
        $countriesMonth = [];
        $countriesWeek  = [];

        foreach ($regions as $r) {
            $salesByRegionYear[$r]  = $initYear;
            $salesByRegionMonth[$r] = $initMonth;
            $salesByRegionWeek[$r]  = $initWeek;

            $countriesYear[$r]  = [];
            $countriesMonth[$r] = [];
            $countriesWeek[$r]  = [];
        }

        // ---- Aggregate Loop ----
        foreach ($paidData as $row) {
            $region  = $regionMap[$row->country_code] ?? null;
            if (!$region) continue;

            $country = $row->country_code;
            $date    = Carbon::parse($row->date);

            $y = (int) $date->year;
            $m = (int) $date->month;
            $d = $date->format('D'); // 'Mon'..'Sun'

            $amount = (float) $row->total_price;

            // Year
            if (isset($salesByRegionYear[$region][$y])) {
                $salesByRegionYear[$region][$y] += $amount;
            }

            $countriesYear[$region][$country] = $countriesYear[$region][$country] ?? array_fill_keys($years, 0.0);
            if (isset($countriesYear[$region][$country][$y])) {
                $countriesYear[$region][$country][$y] += $amount;
            }

            // Month
            if (isset($salesByRegionMonth[$region][$m])) {
                $salesByRegionMonth[$region][$m] += $amount;
            }

            $countriesMonth[$region][$country] = $countriesMonth[$region][$country] ?? array_fill_keys($months, 0.0);
            if (isset($countriesMonth[$region][$country][$m])) {
                $countriesMonth[$region][$country][$m] += $amount;
            }

            // Week
            if ($date->betweenIncluded($startOfWeek, $endOfWeek)) {
                if (isset($salesByRegionWeek[$region][$d])) {
                    $salesByRegionWeek[$region][$d] += $amount;
                }
                $countriesWeek[$region][$country] = $countriesWeek[$region][$country] ?? array_fill_keys($weekDays, 0.0);
                if (isset($countriesWeek[$region][$country][$d])) {
                    $countriesWeek[$region][$country][$d] += $amount;
                }
            }
        }

        // ---- Total per year/month/week ----
        $salesYear = [];
        foreach ($years as $y) {
            $salesYear[$y] = collect($salesByRegionYear)->sum(fn($region) => $region[$y] ?? 0);
        }

        $salesMonth = [];
        foreach ($months as $m) {
            $salesMonth[$m] = collect($salesByRegionMonth)->sum(fn($region) => $region[$m] ?? 0);
        }

        $salesWeek = [];
        foreach ($weekDays as $d) {
            $salesWeek[$d] = collect($salesByRegionWeek)->sum(fn($region) => $region[$d] ?? 0);
        }

        // ---- Summary ----
        $summary = [
            'today' => round(
                $paidData->filter(fn($r) => Carbon::parse($r->date)->isSameDay($today))->sum('total_price'),
                2
            ),
            'week'  => round(
                $paidData->filter(fn($r) => Carbon::parse($r->date)->betweenIncluded($startOfWeek, $endOfWeek))->sum('total_price'),
                2
            ),
            'month' => round(
                $paidData->filter(fn($r) => Carbon::parse($r->date)->isSameMonth($today))->sum('total_price'),
                2
            ),
            'year'  => round(
                $paidData->filter(fn($r) => Carbon::parse($r->date)->isSameYear($today))->sum('total_price'),
                2
            ),
        ];

        // ---- Pass to View ----
        return view('admin.home', [
            'salesYear'          => $salesYear,
            'salesMonth'         => $salesMonth,
            'salesWeek'          => $salesWeek,

            // region => [label=>sum]
            'salesByRegionYear'  => $salesByRegionYear,
            'salesByRegionMonth' => $salesByRegionMonth,
            'salesByRegionWeek'  => $salesByRegionWeek,

            'countriesYear'  => $countriesYear,
            'countriesMonth' => $countriesMonth,
            'countriesWeek'  => $countriesWeek,

            'summary'            => $summary,
        ]);
    }
}
