<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Pricing utility
 *
 * Calculates total reservation fee based on:
 *  - room type
 *  - time range
 *  - optional facilities
 *
 * Returns both detailed breakdown and total amount.
 */
class Pricing
{
    /**
     * Calculate reservation price.
     *
     * @param  array $data  [
     *     'room_name'  => 'Room B',
     *     'type'       => 'Meeting',
     *     'date'       => '2025-10-20',
     *     'time_from'  => '09:00',
     *     'time_to'    => '11:00',
     *     'facilities' => ['Monitor', 'Whiteboard'],
     * ]
     * @return array breakdown (minutes, rounded, hours, roomPart, facPart, total, unit)
     */
    public static function calc(array $data): array
    {
        // config/pricing.php に定義されている前提（なくても fallback）
        $cfg = config('pricing', [
            'min_slot_minutes' => 30,
            'rooms' => [
                'Room B' => 1200, // ¥1200/hour
            ],
            'types' => [
                'Focus Booth' => 1.0,
                'Meeting'     => 1.2,
                'Phone Call'  => 0.8,
            ],
            'facilities' => [
                'Monitor'     => 300,
                'Whiteboard'  => 200,
                'Power Outlet'=> 0,
                'HDMI'        => 0,
                'USB-C'       => 0,
            ],
        ]);

        // Extract
        $roomName = $data['room_name'] ?? 'Room B';
        $type     = $data['type'] ?? null;
        $fac      = $data['facilities'] ?? [];

        $date     = $data['date'] ?? now()->toDateString();
        $from     = $data['time_from'] ?? '09:00';
        $to       = $data['time_to'] ?? '09:30';

        // 1️⃣ Duration
        $start = Carbon::parse($date . ' ' . $from);
        $end   = Carbon::parse($date . ' ' . $to);
        $minutes = max(0, $start->diffInMinutes($end));

        // Round up to slot (e.g. 30min)
        $slot = $cfg['min_slot_minutes'];
        $roundedMinutes = (int) ceil($minutes / $slot) * $slot;
        $hours = $roundedMinutes / 60;

        // 2️⃣ Base rate
        $roomUnit = $cfg['rooms'][$roomName] ?? 0;
        $typeCoef = $cfg['types'][$type] ?? 1.0;

        // 3️⃣ Facilities hourly cost
        $facTotalPerHour = 0;
        foreach ($fac as $f) {
            $facTotalPerHour += ($cfg['facilities'][$f] ?? 0);
        }

        // 4️⃣ Totals
        $roomPart = (int) round($roomUnit * $typeCoef * $hours);
        $facPart  = (int) round($facTotalPerHour * $hours);
        $total    = $roomPart + $facPart;

        return [
            'minutes'  => $minutes,
            'rounded'  => $roundedMinutes,
            'hours'    => $hours,
            'roomPart' => $roomPart,
            'facPart'  => $facPart,
            'total'    => $total,
            'unit'     => [
                'room'       => $roomUnit,
                'typeCoef'   => $typeCoef,
                'facPerHour' => $facTotalPerHour,
            ],
        ];
    }
}
