<?php

namespace App\Support;

use App\Models\Space;
use Carbon\Carbon;

/**
 * Pricing utility (country-aware, Stripe-ready)
 *
 * - Uses Space weekday/weekend price
 * - Type multiplier & optional facilities
 * - Rounds duration to slot minutes
 * - Currency & tax are auto-selected by country_code (with optional overrides)
 * - Returns Stripe-ready amount (smallest unit) and currency
 */
class Pricing
{
    public static function calc(array $data): array
    {
        // ---------- Config ----------
        $cfg = [
            'min_slot_minutes' => 30, // round up to 30-min slots
            'types' => [
                'Standard'    => 1.0,
                'Meeting'     => 1.2,
                'Focus Booth' => 1.0,
                'Phone Call'  => 0.8,
            ],
            // per-hour add-ons (same currency as space pricing)
            'facilities' => [
                'Monitor'      => 300,
                'Whiteboard'   => 200,
                'Power Outlet' => 0,
                'HDMI'         => 0,
                'USB-C'        => 0,
            ],
            // country -> currency (ISO 4217)
            'country_to_currency' => [
                'JP' => 'JPY',
                'US' => 'USD',
                'EU' => 'EUR', // treat EU as generic VAT region
                'GB' => 'GBP',
                'AU' => 'AUD',
                'CA' => 'CAD',
                'SG' => 'SGD',
                'FR' => 'EUR',
                'DE' => 'EUR',
                'IT' => 'EUR',
                'ES' => 'EUR',
            ],
            // country -> tax rate (decimal, e.g., 0.10 = 10%)
            'country_to_tax' => [
                'JP' => 0.10,
                'US' => 0.08, // typical example; vary by state in real life
                'EU' => 0.20,
                'GB' => 0.20,
                'AU' => 0.10,
                'CA' => 0.13,
                'SG' => 0.09,
                'FR' => 0.20,
                'DE' => 0.19,
                'IT' => 0.22,
                'ES' => 0.21,
            ],
        ];

        // ---------- Extract input ----------
        $spaceId    = $data['space_id'] ?? null;
        $space      = $spaceId ? Space::find($spaceId) : null;

        $type       = $data['type'] ?? 'Standard';
        $date       = $data['date'] ?? now()->toDateString();
        $from       = $data['time_from'] ?? '09:00';
        $to         = $data['time_to'] ?? '09:30';
        $facilities = (array)($data['facilities'] ?? []);

        // Optional overrides
        $countryOverride    = isset($data['country_code']) ? strtoupper($data['country_code']) : null;
        $currencyOverride   = isset($data['currency_override']) ? strtoupper($data['currency_override']) : null;
        $taxOverrideDecimal = $data['tax_override_decimal'] ?? null;       // e.g., 0.10
        $taxOverridePercent = $data['tax_override_percent'] ?? null;       // e.g., 10.0

        // ---------- Duration ----------
        $start = Carbon::parse($date.' '.$from);
        $end   = Carbon::parse($date.' '.$to);
        // guard: ensure end > start; minimum 1 slot
        if ($end->lessThanOrEqualTo($start)) {
            $end = (clone $start)->addMinutes($cfg['min_slot_minutes']);
        }
        $minutes         = $start->diffInMinutes($end);
        $slot            = (int)($cfg['min_slot_minutes'] ?? 30);
        $roundedMinutes  = (int)ceil($minutes / $slot) * $slot;
        $hours           = $roundedMinutes / 60;

        // ---------- Base pricing from Space ----------
        $basePerHour = 0.0;
        $country     = 'JP';  // default fallback
        $currency    = 'JPY';
        $taxRate     = 0.0;   // decimal (0.10 = 10%)

        if ($space) {
            // weekend? ISO: Mon=1 ... Sun=7
            $dowIso    = Carbon::parse($date)->dayOfWeekIso;
            $isWeekend = in_array($dowIso, [6, 7], true);
            $basePerHour = (float)($isWeekend ? ($space->weekend_price ?? $space->weekday_price) : $space->weekday_price);

            // decide country (priority: override > space->country_code > detect from location)
            $country = $countryOverride
                ?: (strtoupper((string)$space->country_code) ?: self::detectCountry($space->location_for_details));

            // currency & tax via map (with overrides)
            $currency = $currencyOverride
                ?: ($cfg['country_to_currency'][$country] ?? 'JPY');

            if ($taxOverrideDecimal !== null) {
                $taxRate = (float)$taxOverrideDecimal;
            } elseif ($taxOverridePercent !== null) {
                $taxRate = ((float)$taxOverridePercent) / 100.0;
            } else {
                $taxRate = $cfg['country_to_tax'][$country] ?? 0.0;
            }
        }

        // ---------- Type multiplier ----------
        $typeCoef = (float)($cfg['types'][$type] ?? 1.0);

        // ---------- Facilities per-hour ----------
        $facPerHour = 0.0;
        foreach ($facilities as $f) {
            $facPerHour += (float)($cfg['facilities'][$f] ?? 0);
        }

        // ---------- Amounts (before tax) ----------
        // keep as float for display; Stripe conversion will handle smallest-unit
        $roomPart = round($basePerHour * $typeCoef * $hours, 2);
        $facPart  = round($facPerHour * $hours, 2);
        $subtotal = round($roomPart + $facPart, 2);

        // ---------- Tax & total ----------
        $taxAmount = round($subtotal * $taxRate, 2);
        $total     = round($subtotal + $taxAmount, 2);

        // ---------- Stripe conversion ----------
        $stripeCurrency = strtolower($currency);
        $stripeAmount   = self::isZeroDecimalCurrency($currency)
            ? (int) round($total)          // e.g., JPY
            : (int) round($total * 100);   // e.g., USD/EUR/etc.

        // ---------- Response ----------
        return [
            // timing
            'minutes' => $minutes,
            'rounded' => $roundedMinutes,
            'hours'   => $hours,

            // geo & money
            'country'   => $country,
            'currency'  => $currency,
            'tax_rate'  => $taxRate,       // decimal (0.10 = 10%)
            'tax_amount'=> $taxAmount,
            'total'     => $total,

            // breakdown
            'parts' => [
                'base'      => $roomPart,
                'facilities'=> $facPart,
                'subtotal'  => $subtotal,
            ],
            'unit' => [
                'basePerHour' => $basePerHour,
                'typeCoef'    => $typeCoef,
                'facPerHour'  => $facPerHour,
                'baseSource'  => $space ? 'space-table' : 'none',
            ],

            // Stripe-ready
            'stripe_amount'   => $stripeAmount,   // smallest unit
            'stripe_currency' => $stripeCurrency, // lowercase for Stripe API
            'is_zero_decimal' => self::isZeroDecimalCurrency($currency),
        ];
    }

    /**
     * Lightweight country detection from free-text address (fallback only).
     */
    private static function detectCountry(?string $location): string
    {
        if (!$location) return 'JP';
        $loc = strtolower($location);
        return match (true) {
            str_contains($loc, 'japan') || str_contains($loc, 'tokyo') || str_contains($loc, 'osaka') => 'JP',
            str_contains($loc, 'united states') || str_contains($loc, 'usa') || str_contains($loc, 'new york') => 'US',
            str_contains($loc, 'france') || str_contains($loc, 'paris') => 'FR',
            str_contains($loc, 'germany') || str_contains($loc, 'berlin') => 'DE',
            str_contains($loc, 'italy') || str_contains($loc, 'rome') => 'IT',
            str_contains($loc, 'spain') || str_contains($loc, 'madrid') => 'ES',
            str_contains($loc, 'australia') || str_contains($loc, 'sydney') => 'AU',
            str_contains($loc, 'singapore') => 'SG',
            default => 'JP',
        };
    }

    /**
     * Whether a currency is zero-decimal on Stripe.
     * See: https://stripe.com/docs/currencies#zero-decimal
     */
    public static function isZeroDecimalCurrency(string $currency): bool
    {
        $zero = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
        return in_array(strtoupper($currency), $zero, true);
    }

    /**
     * Simple formatting helper for UI (not i18n aware).
     */
    public static function format(float $amount, string $currency): string
    {
        $c = strtoupper($currency);
        return self::isZeroDecimalCurrency($c)
            ? number_format($amount, 0) . ' ' . $c
            : number_format($amount, 2) . ' ' . $c;
    }
}
