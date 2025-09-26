<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\USer;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    private $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function run(): void
    {
        $this->user->name = "Administrator";
        $this->user->email = "admin@mail.com";
        $this->user->password = Hash::make('admin123'); // hash password
        $this->user->role_id = User::ADMIN_ROLE_ID;
        $this->user->save();
    }
}
