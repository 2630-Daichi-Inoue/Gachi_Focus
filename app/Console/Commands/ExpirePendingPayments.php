<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpirePendingPayments extends Command
{
    protected $signature = 'payments:expire-pending';

    protected $description = 'Cancel reservations whose pending payment has exceeded 30 minutes without completion.';

    public function handle(): void
    {
        $expiredReservations = Reservation::where('reservation_status', 'pending')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        foreach ($expiredReservations as $reservation) {
            DB::transaction(function () use ($reservation) {
                // Re-fetch with lock to avoid race condition with webhook
                $locked = Reservation::lockForUpdate()->find($reservation->id);
                if (! $locked || $locked->reservation_status !== 'pending') {
                    return;
                }

                $locked->update([
                    'reservation_status' => 'canceled',
                    'canceled_at' => Carbon::now(),
                ]);

                $locked->payments()
                    ->where('status', 'pending')
                    ->update(['status' => 'expired']);
            });
        }

        $this->info("Expired {$expiredReservations->count()} pending reservation(s).");
    }
}
