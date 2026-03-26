<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Space;
use App\Models\Reservation;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            AmenitySeeder::class,
        ]);

        $users = User::factory(50)->create();
        $spaces = Space::factory(20)->create();
        // User::factory()->suspended()->create();

        foreach(range(1, 100) as $i) {
            $user = $users->random();
            $space = $spaces->random();
            Reservation::factory()
                        ->for($user, 'user')
                        ->forSpace($space)
                        ->create();
        }
    }
}
