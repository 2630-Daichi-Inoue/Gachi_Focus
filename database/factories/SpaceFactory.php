<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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

        $brands = [
            'Focus Hub',
            'Work Nest',
            'Silent Booth',
            'Creative Base',
            'Biz Lounge',
            'Urban Desk',
            'Prime Office',
            'Study Terrace',
            'Co-Work Lab',
            'Station Work',
        ];

        $locations = [
            [
                'prefecture' => 'Hokkaido',
                'city' => 'Sapporo',
                'area' => 'Sapporo',
                'address_line' => '1-2-3 Sapporo',
            ],
            [
                'prefecture' => 'Miyagi',
                'city' => 'Sendai',
                'area' => 'Sendai',
                'address_line' => '1-2-3 Sendai',
            ],
            [
                'prefecture' => 'Tokyo',
                'city' => 'Shibuya',
                'area' => 'Shibuya',
                'address_line' => '1-2-3 Dogenzaka',
            ],
            [
                'prefecture' => 'Tokyo',
                'city' => 'Shinjuku',
                'area' => 'Shinjuku',
                'address_line' => '1-2-3 Nishishinjuku',
            ],
            [
                'prefecture' => 'Tokyo',
                'city' => 'Tachikawa',
                'area' => 'Tachikawa',
                'address_line' => '1-2-3 Tachikawa',
            ],
            [
                'prefecture' => 'Kanagawa',
                'city' => 'Yokohama',
                'area' => 'Yokohama',
                'address_line' => '1-2-3 Yokohama',
            ],
            [
                'prefecture' => 'Aichi',
                'city' => 'Nagoya',
                'area' => 'Nagoya',
                'address_line' => '1-2-3 Nagoya',
            ],
            [
                'prefecture' => 'Osaka',
                'city' => 'Osaka',
                'area' => 'Umeda',
                'address_line' => '1-2-3 Umeda',
            ],
            [
                'prefecture' => 'Hyogo',
                'city' => 'Kobe',
                'area' => 'Kobe',
                'address_line' => '1-2-3 Kobe',
            ],
            [
                'prefecture' => 'Fukuoka',
                'city' => 'Fukuoka',
                'area' => 'Hakata',
                'address_line' => '1-2-3 Hakata',
            ],
        ];

        $images = [
            'spaces/demo1.png',
            'spaces/demo2.png',
            'spaces/demo3.png',
            'spaces/demo4.png',
        ];

    static $usedLocationIndexes = [];

    do {
        $locationIndex = fake()->numberBetween(0, count($locations) - 1);
    } while (in_array($locationIndex, $usedLocationIndexes, true));
    $usedLocationIndexes[] = $locationIndex;
    $brand = fake()->randomElement($brands);
    $location = $locations[$locationIndex];

        return [
            'name' => $brand . ' ' . $location['area'],
            'prefecture' => $location['prefecture'],
            'city' => $location['city'],
            'address_line' => $location['address_line'],
            'capacity' => fake()->numberBetween(20, 100),
            'open_time' => fake()->randomElement(['06:00:00', '07:00:00', '08:00:00', '09:00:00', '10:00:00']),
            'close_time' => fake()->randomElement(['20:00:00', '21:00:00', '22:00:00', '23:00:00']),
            'weekday_price_yen' => $weekdayPrice = fake()->numberBetween(500, 850),
            'weekend_price_yen' => $weekdayPrice * 1.2, // 20% more expensive on weekends
            'description' => fake()->paragraph(),
            'image_path' => fake()->randomElement($images),
            'is_public' => fake()->boolean(90), // 90% chance to be public
        ];
    }
}
