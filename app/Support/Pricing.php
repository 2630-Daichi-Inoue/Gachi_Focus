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
                // Canonical keys used everywhere
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
                'JP' => 'JPY','US' => 'USD','EU' => 'EUR','GB' => 'GBP','AU' => 'AUD',
                'CA' => 'CAD','SG' => 'SGD','FR' => 'EUR','DE' => 'EUR','IT' => 'EUR','ES' => 'EUR',
            ],
            // country -> tax rate (decimal)
            'country_to_tax' => [
                'JP' => 0.10,'US' => 0.08,'EU' => 0.20,'GB' => 0.20,'AU' => 0.10,
                'CA' => 0.13,'SG' => 0.09,'FR' => 0.20,'DE' => 0.19,'IT' => 0.22,'ES' => 0.21,
            ],
        ];

        // ---------- Extract input ----------
        $spaceId    = $data['space_id'] ?? null;
        $space      = $spaceId ? Space::find($spaceId) : null;

        // Normalize type BEFORE using it
        $rawType    = $data['type'] ?? 'Standard';
        $type       = self::normalizeType($rawType, array_keys($cfg['types'])); // <- key fix

        $date       = $data['date'] ?? now()->toDateString();
        $from       = $data['time_from'] ?? '09:00';
        $to         = $data['time_to'] ?? '09:30';
        $facilities = (array)($data['facilities'] ?? []);

        // Optional overrides
        $countryOverride    = isset($data['country_code']) ? strtoupper($data['country_code']) : null;
        $currencyOverride   = isset($data['currency_override']) ? strtoupper($data['currency_override']) : null;
        $taxOverrideDecimal = $data['tax_override_decimal'] ?? null;
        $taxOverridePercent = $data['tax_override_percent'] ?? null;

        // ---------- Duration ----------
        $start = Carbon::parse($date.' '.$from);
        $end   = Carbon::parse($date.' '.$to);
        if ($end->lessThanOrEqualTo($start)) {
            $end = (clone $start)->addMinutes($cfg['min_slot_minutes']);
        }
        $minutes        = $start->diffInMinutes($end);
        $slot           = (int)($cfg['min_slot_minutes'] ?? 30);
        $roundedMinutes = (int)ceil($minutes / $slot) * $slot;
        $hours          = $roundedMinutes / 60;

        // ---------- Base pricing from Space ----------
        $basePerHour = 0.0;
        $country     = 'JP';
        $currency    = 'JPY';
        $taxRate     = 0.0;

        if ($space) {
            $dowIso     = Carbon::parse($date)->dayOfWeekIso; // 1..7
            $isWeekend  = in_array($dowIso, [6,7], true);
            $basePerHour = (float)($isWeekend ? ($space->weekend_price ?? $space->weekday_price) : $space->weekday_price);

            $country = $countryOverride
                ?: (strtoupper((string)$space->country_code) ?: self::detectCountry($space->location_for_details));

            $currency = $currencyOverride ?: ($cfg['country_to_currency'][$country] ?? 'JPY');

            if ($taxOverrideDecimal !== null) {
                $taxRate = (float)$taxOverrideDecimal;
            } elseif ($taxOverridePercent !== null) {
                $taxRate = ((float)$taxOverridePercent) / 100.0;
            } else {
                $taxRate = $cfg['country_to_tax'][$country] ?? 0.0;
            }
        }

        // ---------- Type multiplier (use normalized key) ----------
        $typeCoef = (float)($cfg['types'][$type] ?? 1.0);

        // ---------- Facilities per-hour ----------
        $facPerHour = 0.0;
        foreach ($facilities as $f) {
            $facPerHour += (float)($cfg['facilities'][$f] ?? 0);
        }

        // ---------- Amounts (before tax) ----------
        $roomPart = round($basePerHour * $typeCoef * $hours, 2);
        $facPart  = round($facPerHour * $hours, 2);
        $subtotal = round($roomPart + $facPart, 2);

        // ---------- Tax & total ----------
        $taxAmount = round($subtotal * $taxRate, 2);
        $total     = round($subtotal + $taxAmount, 2);

        // ---------- Stripe conversion ----------
        $stripeCurrency = strtolower($currency);
        $stripeAmount   = self::isZeroDecimalCurrency($currency)
            ? (int) round($total)
            : (int) round($total * 100);

        // ---------- Response ----------
        return [
            'minutes' => $minutes,
            'rounded' => $roundedMinutes,
            'hours'   => $hours,

            'country'    => $country,
            'currency'   => $currency,
            'tax_rate'   => $taxRate,
            'tax_amount' => $taxAmount,
            'total'      => $total,

            'parts' => [
                'base'       => $roomPart,
                'facilities' => $facPart,
                'subtotal'   => $subtotal,
            ],
            'unit' => [
                'basePerHour' => $basePerHour,
                'type'        => $type,     // <- canonicalized label for DB/UI
                'typeCoef'    => $typeCoef,
                'facPerHour'  => $facPerHour,
                'baseSource'  => $space ? 'space-table' : 'none',
            ],

            'stripe_amount'   => $stripeAmount,
            'stripe_currency' => $stripeCurrency,
            'is_zero_decimal' => self::isZeroDecimalCurrency($currency),
        ];
    }

    /**
     * Normalize a raw type string to a canonical key.
     * - Case-insensitive
     * - Trims spaces
     * - Collapses spaces/hyphens/underscores ("phonecall" => "Phone Call")
     * - Provides common aliases
     */
    private static function normalizeType(string $raw, array $canonicalKeys): string
    {
        $needle = trim($raw);

        // 1) case-insensitive exact
        foreach ($canonicalKeys as $k) {
            if (strcasecmp($needle, $k) === 0) return $k;
        }

        // 2) squashed compare
        $squash = fn(string $s) => str_replace([' ', '-', '_'], '', strtolower($s));
        $sq = $squash($needle);

        $canonMap = [];
        foreach ($canonicalKeys as $k) $canonMap[$squash($k)] = $k;

        $aliases = [
            'phonecall'   => 'Phone Call',
            'phone_call'  => 'Phone Call',
            'phone-call'  => 'Phone Call',
            'focusbooth'  => 'Focus Booth',
            'focus_booth' => 'Focus Booth',
            'meeting'     => 'Meeting',
            'standard'    => 'Standard',
        ];

        if (isset($canonMap[$sq])) return $canonMap[$sq];
        if (isset($aliases[$sq]))  return $aliases[$sq];

        // 3) safe fallback
        return 'Standard'; // <- never store unknown labels
    }

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

    public static function isZeroDecimalCurrency(string $currency): bool
    {
        $zero = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
        return in_array(strtoupper($currency), $zero, true);
    }

    public static function format(float $amount, string $currency): string
    {
        $c = strtoupper($currency);
        return self::isZeroDecimalCurrency($c)
            ? number_format($amount, 0) . ' ' . $c
            : number_format($amount, 2) . ' ' . $c;
    }
}
