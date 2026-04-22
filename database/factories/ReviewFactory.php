<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Reservation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rating' => match (true) {
                ($r = fake()->numberBetween(1, 100)) <= 5 => 1, // 5% chance
                $r > 5 && $r <= 15 => 2, // 10% chance
                $r > 15 && $r <= 30 => 3, // 15% chance
                $r > 30 && $r <= 60 => 4, // 30% chance
                default => 5, // 40% chance
            },
            'comment' => fake()->optional()->paragraph(),
            'is_public' => fake()->boolean(95), // 95% chance to be public
        ];
    }

    public function forReservation(Reservation $reservation) :static
    {
        return $this->state(function () use ($reservation) {
            return [
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
            ];
        });
    }
}
