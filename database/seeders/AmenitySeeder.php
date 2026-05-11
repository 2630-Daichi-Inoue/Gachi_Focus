<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            'Complimentary Drinks',
            'Ergonomic Seats',
            'Printers',
            'Projectors',
            'Whiteboards',
            'Lockers',
            'Natural Light',
            'Quiet Zone',
            'Private Phone Booths',
            'Standing Desks',
            'Wheelchair Accessible',
            'Headset Rental',
            'Conference Rooms',
            'Outdoor Seats',
        ];

        foreach ($amenities as $name) {
            Amenity::firstOrCreate(['name' => $name]);
        }
    }
}
