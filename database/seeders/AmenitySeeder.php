<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Amenity;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Amenity::updateOrCreate([
            'name' => 'Complimentary Drinks',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Ergonomic Seats',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Printers',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Projectors',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Whiteboards',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Lockers',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Natural Light',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Quiet Zone',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Private Phone Booths',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Standing Desks',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Wheelchair Accessible',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Headset Rental',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Conference Rooms',
            'created_at' => NOW(),
        ]);
        Amenity::updateOrCreate([
            'name' => 'Outdoor Seats',
            'created_at' => NOW(),
        ]);
    }
}
