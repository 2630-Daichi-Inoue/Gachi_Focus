<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Space;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_status' => match (true) {
                ($r = fake()->numberBetween(1, 100)) <= 95 => 'booked', // 95% chance
                default => 'canceled', // 5% chance
            },
             // The following fields will be overridden when using forSpace() in DatabaseSeeder
        ];
    }

    public function forSpace(Space $space) :static
    {
        return $this->state(function () use ($space) {
            // Reseavation's date
            $date = fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d');

            // Space's open-close times (not depending on date)
            $openTime = Carbon::createFromFormat("Y-m-d H:i:s", "$date {$space->open_time}");
            $closeTime = Carbon::createFromFormat("Y-m-d H:i:s", "$date {$space->close_time}");

            // Candidates for reservation start time (every 30 min slot between open and close)
            $startCandidates = [];
            $cursorOpenTime = $openTime->copy();
            $lastStartAt = $closeTime->copy()->subMinutes(30); // Last possible start time is 30 min before close
            while ($cursorOpenTime->lte($lastStartAt)) {
                $startCandidates[] = $cursorOpenTime->copy();
                $cursorOpenTime->addMinutes(30);
            }

            // Random quantity between 1 and space capacity (max 5 for realism)
            $quantity = fake()->numberBetween(1, min(5, $space->capacity));

            // Pick a random start time from candidates
            $startAt = fake()->randomElement($startCandidates)->copy();

            // Max slots based on reservation's start time and space's close time
            $maxSlots = (int) ($startAt->diffInMinutes($closeTime) / 30);

            // Randomly determine slot count (1 to max possible)
            $slotCount = fake()->numberBetween(1, min(16, $maxSlots));

            // Calculate end time based on start time and slot count
            $endAt = $startAt->copy()->addMinutes($slotCount * 30);

            // Determine unit price based on whether the reservation date is a weekend
            $unitPrice = in_array($startAt->format('N'), [6, 7]) ? $space->weekend_price_yen : $space->weekday_price_yen;

            return [
                'space_id' => $space->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'quantity' => $quantity,
                'slot_count' => $slotCount,
                'unit_price_yen' => $unitPrice,
                'total_price_yen' => $unitPrice * $slotCount * $quantity,
            ];
        });
    }
}
