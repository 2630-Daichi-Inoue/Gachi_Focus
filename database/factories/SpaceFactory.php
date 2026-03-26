<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Space>
 */
class SpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prefectures = array_merge(
            config('constants.prefectures.Major Prefectures'),
            config('constants.prefectures.Other Prefectures'),
        );
        return [
            'name' => fake()->company(),
            'prefecture' => fake()->randomElement($prefectures),
            'city' => fake()->city(),
            'address_line' => fake()->streetAddress(),
            'capacity' => fake()->numberBetween(1, 100),
            'open_time' => fake()->randomElement(['06:00:00', '07:00:00', '08:00:00', '09:00:00', '10:00:00']),
            'close_time' => fake()->randomElement(['20:00:00', '21:00:00', '22:00:00', '23:00:00']),
            'weekday_price_yen' => $weekdayPrice = fake()->numberBetween(500, 850),
            'weekend_price_yen' => $weekdayPrice * 1.2, // 20% more expensive on weekends
            'description' => fake()->paragraph(),
            'image_path' => 'spaces/default.jpg',
            'is_public' => fake()->boolean(90), // 90% chance to be public
        ];
    }
}
