<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Reservation;
use Carbon\Carbon;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Verify signature and dispatch Stripe webhooks.
     * Route is already registered and CSRF-exempt in routes/web.php.
     */
    public function handle(Request $request)
    {
        // 1) Read raw payload & signature header
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        // 2) Verify the event with Stripe library
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload: ' . $e->getMessage());
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook invalid signature: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        // 3) Dispatch by event type
        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    // Fired when Checkout completes successfully (paid or authorized)
                    $this->onCheckoutSessionCompleted($event->data->object);
                    break;

                case 'checkout.session.expired':
                    // User abandoned; session expired
                    $this->onCheckoutSessionExpired($event->data->object);
                    break;

                case 'payment_intent.succeeded':
                    // PaymentIntent has succeeded (captured/settled)
                    $this->onPaymentIntentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    // PaymentIntent failed
                    $this->onPaymentIntentFailed($event->data->object);
                    break;

                case 'charge.refunded':
                case 'charge.refund.updated':
                    // Charge was fully/partially refunded
                    $this->onChargeRefunded($event->data->object);
                    break;

                default:
                    // Not explicitly handled; log and ack
                    Log::info('Stripe webhook received (ignored): ' . $event->type);
                    break;
            }
        } catch (\Throwable $e) {
            // 5xx makes Stripe retry; only do this if you want retries on errors
            Log::error('Stripe webhook handler error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response('Webhook handler error', 500);
        }

        return response('OK', 200);
    }

    /**
     * Handle checkout.session.completed
     * - Use metadata.reservation_id to locate the reservation.
     * - Update payment_status and amounts (integer in smallest unit).
     */
    protected function onCheckoutSessionCompleted($session): void
    {
        $paymentIntentId = $session->payment_intent ?? null;
        $reservationId   = $session->metadata->reservation_id ?? null;

        $reservation = $this->findReservation($reservationId, $paymentIntentId);
        if (!$reservation) {
            Log::warning('Reservation not found (checkout.session.completed)', compact('reservationId', 'paymentIntentId'));
            return;
        }

        // Stripe amounts are already in smallest unit (e.g., JPY = yen)
        $attrs = [
            'payment_status'    => 'paid',
            'payment_intent_id' => $paymentIntentId ?: $reservation->payment_intent_id,
            'amount_paid'       => isset($session->amount_total) ? (int)$session->amount_total : ($reservation->amount_paid ?? null),
            'currency'          => isset($session->currency) ? strtoupper($session->currency) : ($reservation->currency ?? null),
            'paid_at'           => Carbon::now(),
        ];

        $reservation->fill(array_filter($attrs, fn($v) => !is_null($v)))->save();
        Log::info('Reservation updated (checkout.session.completed)', ['id' => $reservation->id] + $attrs);
    }

    /**
     * Handle checkout.session.expired (user abandoned)
     */
    protected function onCheckoutSessionExpired($session): void
    {
        $paymentIntentId = $session->payment_intent ?? null;
        $reservationId   = $session->metadata->reservation_id ?? null;

        $reservation = $this->findReservation($reservationId, $paymentIntentId);
        if (!$reservation) return;

        $reservation->update(['payment_status' => 'expired']);
        Log::info('Reservation updated (checkout.session.expired)', ['id' => $reservation->id, 'payment_status' => 'expired']);
    }

    /**
     * Handle payment_intent.succeeded
     */
    protected function onPaymentIntentSucceeded($pi): void
    {
        $paymentIntentId = $pi->id ?? null;
        $reservationId   = $pi->metadata->reservation_id ?? null;

        $reservation = $this->findReservation($reservationId, $paymentIntentId);
        if (!$reservation) return;

        // Prefer amount_received; fallback to amount
        $amount = $pi->amount_received ?? $pi->amount ?? null;

        $attrs = [
            'payment_status'    => 'paid',
            'payment_intent_id' => $paymentIntentId ?: $reservation->payment_intent_id,
            'amount_paid'       => isset($amount) ? (int)$amount : ($reservation->amount_paid ?? null),
            'currency'          => isset($pi->currency) ? strtoupper($pi->currency) : ($reservation->currency ?? null),
            'paid_at'           => Carbon::now(),
        ];

        $reservation->fill(array_filter($attrs, fn($v) => !is_null($v)))->save();
        Log::info('Reservation updated (payment_intent.succeeded)', ['id' => $reservation->id] + $attrs);
    }

    /**
     * Handle payment_intent.payment_failed
     */
    protected function onPaymentIntentFailed($pi): void
    {
        $paymentIntentId = $pi->id ?? null;
        $reservationId   = $pi->metadata->reservation_id ?? null;

        $reservation = $this->findReservation($reservationId, $paymentIntentId);
        if (!$reservation) return;

        $reservation->update(['payment_status' => 'failed']);
        Log::info('Reservation updated (payment_intent.payment_failed)', ['id' => $reservation->id, 'payment_status' => 'failed']);
    }

    /**
     * Handle charge.refunded / charge.refund.updated
     */
    protected function onChargeRefunded($charge): void
    {
        $paymentIntentId = $charge->payment_intent ?? null;
        if (!$paymentIntentId) return;

        $reservation = Reservation::where('payment_intent_id', $paymentIntentId)->first();
        if (!$reservation) return;

        $reservation->update(['payment_status' => 'refunded']);
        Log::info('Reservation updated (charge.refunded)', ['id' => $reservation->id, 'payment_status' => 'refunded']);
    }

    /**
     * Locate reservation:
     * 1) Prefer metadata reservation_id
     * 2) Fallback to payment_intent_id
     */
    protected function findReservation($reservationId, $paymentIntentId): ?Reservation
    {
        if ($reservationId) {
            $res = Reservation::find($reservationId);
            if ($res) return $res;
        }
        if ($paymentIntentId) {
            $res = Reservation::where('payment_intent_id', $paymentIntentId)->first();
            if ($res) return $res;
        }
        return null;
    }
}
