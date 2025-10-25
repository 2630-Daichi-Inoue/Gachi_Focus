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

        $user = User::where('role_id', 2)->first();
        $admin = User::where('role_id', 1)->first();

        $notifications = [
            // =====For user====
            [
                'sender_id' => $admin->id,
                'receiver_id' => $user->id,
                'type' => 'Reservation Approved',
                'message' => 'Your reservation was approved.',
                'reservation_id' => null,
                'read_at' => null,
            ],
            
            [
                'sender_id' => $admin->id,
                'receiver_id' => $user->id,
                'type' => 'Cancelation Approved',
                'message' => 'Your cancelation was approved.',
                'reservation_id' => null,
                'read_at' => now(),
            ],
            [
                'sender_id' => $admin->id,
                'receiver_id' => $user->id,
                'type' => 'Review Request',
                'message' => 'Please review your recent room.',
                'reservation_id' => null,
                'read_at' => null,
            ],
            [
                'sender_id' => $admin->id,
                'receiver_id' => $user->id,
                'type' => 'Review Request',
                'message' => 'Please review your recent room.',
                'reservation_id' => null,
                'read_at' => null,
            ],
            [
                'sender_id' => $admin->id,
                'receiver_id' => $user->id,
                'type' => 'Review Request',
                'message' => 'Please review your recent room.',
                'reservation_id' => null,
                'read_at' => now(),
            ],
            [
                'sender_id' => $admin->id,
                'receiver_id' => $user->id,
                'type' => 'Review Request',
                'message' => 'Please review your recent room.',
                'reservation_id' => null,
                'read_at' => now(),
            ],
            [
                'sender_id' => $admin->id,
                'receiver_id' => $user->id,
                'type' => 'Cancelation Approved',
                'message' => 'Your cancelation was approved.',
                'reservation_id' => null,
                'read_at' => now(),
            ],
            [
                'sender_id' => $admin->id,
                'receiver_id' => $user->id,
                'type' => 'Reservation Approved',
                'message' => 'Your reservation was approved.',
                'reservation_id' => null,
                'read_at' => null,
            ],
            [
                'sender_id' => $admin->id,
                'receiver_id' => $user->id,
                'type' => 'Cancelation Approved',
                'message' => 'Your cancelation was approved.',
                'reservation_id' => null,
                'read_at' => now(),
            ],

            // ====For admin====
            [
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'type' => 'Reservation Request',
                'message' => 'User A requested a reservation for Coworking Space A.',
                'reservation_id' => null,
                'read_at'=> null,
            ],
            [
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'type' => 'Cancel Request',
                'message' => 'User B requested to cancel a reservation for Coworking Space B.',
                'reservation_id' => null,
                'read_at'=> null,
            ],
            [
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'type' => 'Change Reservaion',
                'message' => 'User C requested to change the date of a reservation for Coworking Space A.',
                'reservation_id' => null,
                'read_at'=> null,
            ],
            [
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'type' => 'Cancel Request',
                'message' => 'User C requested to cancel a reservation for Coworking Space B.',
                'reservation_id' => null,
                'read_at'=> null,
            ],
            [
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'type' => 'Reservation Request',
                'message' => 'User B requested a reservation for Coworking Space A.',
                'reservation_id' => null,
                'read_at'=> null,
            ],
            [
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'type' => 'Reservation Request',
                'message' => 'User B requested a reservation for Coworking Space A.',
                'reservation_id' => null,
                'read_at'=> null,
            ],
        ];

        foreach($notifications as $data){
            CustomNotification::create($data);
        }

        
    }
}
