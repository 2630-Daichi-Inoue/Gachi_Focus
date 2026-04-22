<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'user@mail.com'],
            [
                'name' => 'User',
                'is_admin' => false,
                'password' => Hash::make('user12345'),
                'user_status' => 'active',
            ]
        );
    }
}
