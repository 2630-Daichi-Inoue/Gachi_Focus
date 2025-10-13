<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CustomNotification;
use App\Models\User;

class CustomNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        CustomNotification::truncate();

        $user = User::first();

        $notifications = [
            [
                'type' => 'Reservation Approved',
                'message' => 'Your reservation was approved.',
                'reservation_id' => null,
                'read_at' => null,
            ],
            
            [
                'type' => 'Cancelation Approved',
                'message' => 'Your cancelation was approved.',
                'reservation_id' => null,
                'read_at' => now(),
            ],
            [
                'type' => 'Review Request',
                'message' => 'Please review your recent room.',
                'reservation_id' => null,
                'read_at' => null,
            ],
            [
                'type' => 'Review Request',
                'message' => 'Please review your recent room.',
                'reservation_id' => null,
                'read_at' => null,
            ],
            [
                'type' => 'Review Request',
                'message' => 'Please review your recent room.',
                'reservation_id' => null,
                'read_at' => now(),
            ],
            [
                'type' => 'Review Request',
                'message' => 'Please review your recent room.',
                'reservation_id' => null,
                'read_at' => now(),
            ],
            [
                'type' => 'Cancelation Approved',
                'message' => 'Your cancelation was approved.',
                'reservation_id' => null,
                'read_at' => now(),
            ],
            [
                'type' => 'Reservation Approved',
                'message' => 'Your reservation was approved.',
                'reservation_id' => null,
                'read_at' => null,
            ],
            [
                'type' => 'Cancelation Approved',
                'message' => 'Your cancelation was approved.',
                'reservation_id' => null,
                'read_at' => now(),
            ],
        ];

        foreach($notifications as $data){
            CustomNotification::create(array_merge($data,['user_id' => $user->id,
        ]));
        }
    }
}
