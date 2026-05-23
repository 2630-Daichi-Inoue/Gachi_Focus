<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpirePendingPayments extends Command
{
    protected $signature = 'payments:expire-pending';

    protected $description = 'Cancel reservations whose pending payment has exceeded 30 minutes without completion.';

    public function handle(): void
    {
        Log::info('payments:expire-pending started', ['now' => now()->toDateTimeString()]);

        $expiredPayments = Payment::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        Log::info('payments:expire-pending found', ['count' => $expiredPayments->count()]);

        foreach ($expiredPayments as $payment) {
            DB::transaction(function () use ($payment) {
                // Re-fetch with lock to avoid race condition with webhook
                $locked = Payment::lockForUpdate()->find($payment->id);
                if (! $locked || $locked->status !== 'pending') {
                    return;
                }

                $locked->update(['status' => 'expired']);

                $locked->reservation()->update([
                    'reservation_status' => 'canceled',
                    'canceled_at' => Carbon::now(),
                ]);
            });
        }

        $this->info("Expired {$expiredPayments->count()} pending payment(s).");
    }
}
