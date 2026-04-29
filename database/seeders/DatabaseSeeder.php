<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Space;
use App\Models\Reservation;
use App\Models\Review;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
            AmenitySeeder::class,
        ]);

        $users = User::factory(100)->create();
        $spaces = Space::factory(10)->create();
        // User::factory()->suspended()->create();

        foreach(range(1, 100) as $i) {
            $user = $users->random();
            $space = $spaces->random();
            Reservation::factory()
                        ->for($user, 'user')
                        ->forSpace($space)
                        ->create();
        }

        $reservations = Reservation::inRandomOrder()->limit(50)->get();
        foreach($reservations as $reservation) {
            Review::factory()
                    ->forReservation($reservation)
                    ->create();
        }
    }
}
