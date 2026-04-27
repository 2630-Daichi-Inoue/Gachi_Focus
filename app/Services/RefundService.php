<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Notification;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundService
{
    /**
     * Cancel a booked reservation and issue a full Stripe refund.
     *
     * The DB transaction marks the reservation canceled and the payment as
     * refund_pending BEFORE touching Stripe. The Stripe call happens outside
     * the transaction so a slow network cannot hold the row lock.
     *
     * On refund success  → payment.status = 'refunded'
     * On refund failure  → payment.status = 'refund_failed'
     *                      + Notification for the user
     *                      + Contact created on behalf of the user to admin
     */
    public function refundAndCancel(Reservation $reservation): void
    {
        $payment = null;

        DB::transaction(function () use ($reservation, &$payment) {
            $reservation->refresh()->lockForUpdate();

            $payment = $reservation->payments()
                ->where('status', 'paid')
                ->lockForUpdate()
                ->latest()
                ->first();

            $reservation->update([
                'reservation_status' => 'canceled',
                'canceled_at'        => Carbon::now(),
            ]);

            if ($payment) {
                $payment->update(['status' => 'refund_pending']);
            }
        });

        if (!$payment || !$payment->payment_intent_id) {
            return;
        }

        $secret = trim((string) config('services.stripe.secret'));
        \Stripe\Stripe::setApiKey($secret);

        try {
            \Stripe\Refund::create([
                'payment_intent' => $payment->payment_intent_id,
            ]);

            $payment->update(['status' => 'refunded']);
        } catch (\Throwable $e) {
            Log::error('Stripe refund failed', [
                'payment_id'        => $payment->id,
                'payment_intent_id' => $payment->payment_intent_id,
                'error'             => $e->getMessage(),
            ]);

            $payment->update(['status' => 'refund_failed']);

            $this->notifyRefundFailure($reservation, $payment->id);
        }
    }

    private function notifyRefundFailure(Reservation $reservation, int $paymentId): void
    {
        $userId = $reservation->user_id;
        $space  = $reservation->space;
        $date   = $reservation->started_at->format('Y-m-d');

        Notification::create([
            'user_id'      => $userId,
            'type'         => 'refund_failed',
            'title'        => 'Refund Failed',
            'message'      => implode("\n", [
                'Your refund could not be processed automatically.',
                'Our team has been notified and will contact you shortly.',
                '',
                'This is an automated system notification.',
            ]),
            'related_type' => Reservation::class,
            'related_id'   => $reservation->id,
        ]);

        Contact::create([
            'user_id'        => $userId,
            'reservation_id' => $reservation->id,
            'title'          => '[Auto] Refund Failed',
            'message'        => implode("\n", [
                "An automatic refund for the following reservation could not be completed.",
                "",
                "Reservation ID : {$reservation->id}",
                "Space          : " . ($space->name ?? "Space #{$reservation->space_id}"),
                "Date           : {$date}",
                "Amount         : ¥" . number_format($reservation->total_price_yen),
                "Payment ID     : {$paymentId}",
                "",
                "Please process the refund manually via the Stripe dashboard.",
                "",
                "This is an automated system notification.",
            ]),
            'contact_status' => 'open',
        ]);
    }
}
