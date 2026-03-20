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
            Amenity::updateOrCreate(['name' => 'Complimentary Drinks'],);
            Amenity::updateOrCreate(['name' => 'Ergonomic Seats']);
            Amenity::updateOrCreate(['name' => 'Printers']);
            Amenity::updateOrCreate(['name' => 'Projectors']);
            Amenity::updateOrCreate(['name' => 'Whiteboards']);
            Amenity::updateOrCreate(['name' => 'Lockers']);
            Amenity::updateOrCreate(['name' => 'Natural Light']);
            Amenity::updateOrCreate(['name' => 'Quiet Zone']);
            Amenity::updateOrCreate(['name' => 'Private Phone Booths']);
            Amenity::updateOrCreate(['name' => 'Standing Desks']);
            Amenity::updateOrCreate(['name' => 'Wheelchair Accessible']);
            Amenity::updateOrCreate(['name' => 'Headset Rental']);
            Amenity::updateOrCreate(['name' => 'Conference Rooms']);
            Amenity::updateOrCreate(['name' => 'Outdoor Seats']);
        }
}
