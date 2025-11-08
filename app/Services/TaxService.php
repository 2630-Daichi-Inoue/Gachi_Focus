<?php

namespace App\Services;

/**
 * Tax and currency utility
 *
 * Returns tax rate and currency based on country code.
 */
class TaxService
{
    public static function getTaxAndCurrency(string $country): array
    {
        $map = [
            'JP' => ['tax_rate' => 0.10, 'currency' => 'JPY'], // Japan 10%
            'US' => ['tax_rate' => 0.07, 'currency' => 'USD'], // U.S. avg 7%
            'FR' => ['tax_rate' => 0.20, 'currency' => 'EUR'], // France 20%
            'AU' => ['tax_rate' => 0.10, 'currency' => 'AUD'], // Australia 10%
            'SG' => ['tax_rate' => 0.08, 'currency' => 'SGD'], // Singapore 8%
        ];

        return $map[$country] ?? $map['JP'];
    }
}
