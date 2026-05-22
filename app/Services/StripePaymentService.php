<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentService
{
    public function __construct()
    {
        $secret = trim((string) config('services.stripe.secret'));

        if ($secret && str_starts_with($secret, 'sk_')) {
            Stripe::setApiKey($secret);
        }
    }

    private function ensureConfigured(): void
    {
        $secret = trim((string) config('services.stripe.secret'));

        if (! $secret || ! str_starts_with($secret, 'sk_')) {
            abort(500, 'Stripe secret key is missing or invalid.');
        }
    }

    public function revalidateAvailability(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            $space = $reservation->space()->lockForUpdate()->firstOrFail();

            $overlappingQuantity = Reservation::query()
                ->where('space_id', $space->id)
                ->whereIn('reservation_status', ['booked', 'pending'])
                ->where('id', '!=', $reservation->id)
                ->where('started_at', '<', $reservation->ended_at)
                ->where('ended_at', '>', $reservation->started_at)
                ->sum('quantity');

            if ($overlappingQuantity + $reservation->quantity > $space->capacity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Sorry, the time slot is no longer available.',
                ]);
            }
        });
    }

    public function createCheckoutSession(Reservation $reservation, string $userEmail): string
    {
        $this->ensureConfigured();
        $space = $reservation->space;
        $spaceName = $space->name ?? "Space #{$reservation->space_id}";
        $productName = sprintf(
            '%s / %s %s-%s',
            $spaceName,
            $reservation->started_at->format('Y-m-d'),
            $reservation->started_at->format('H:i'),
            $reservation->ended_at->format('H:i')
        );

        $successUrl = route('payments.success', $reservation).'?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('payments.cancel', $reservation);

        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $userEmail,
            'expires_at' => now()->addMinutes(31)->timestamp,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => $reservation->total_price_yen,
                    'product_data' => ['name' => $productName],
                ],
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'reservation_id' => (string) $reservation->id,
                'user_id' => (string) $reservation->user_id,
            ],
        ]);

        Payment::create([
            'reservation_id' => $reservation->id,
            'payment_method' => 'stripe_checkout',
            'status' => 'pending',
            'stripe_session_id' => $session->id,
            'stripe_session_url' => $session->url,
            'amount' => $reservation->total_price_yen,
        ]);

        return $session->url;
    }

    // Called from both the success redirect and the checkout.session.completed webhook.
    public function markSessionPaid(string $sessionId, ?string $paymentIntentId): void
    {
        DB::transaction(function () use ($sessionId, $paymentIntentId) {
            $payment = Payment::where('stripe_session_id', $sessionId)
                ->lockForUpdate()
                ->first();

            if (! $payment || $payment->status !== 'pending') {
                return;
            }

            $payment->update([
                'status' => 'paid',
                'payment_intent_id' => $paymentIntentId,
                'paid_at' => Carbon::now(),
            ]);

            $payment->reservation()->update(['reservation_status' => 'booked']);
        });
    }

    public function markSessionExpired(string $sessionId): void
    {
        DB::transaction(function () use ($sessionId) {
            $payment = Payment::where('stripe_session_id', $sessionId)
                ->lockForUpdate()
                ->first();

            if (! $payment || $payment->status !== 'pending') {
                return;
            }

            $payment->update(['status' => 'expired']);
            $payment->reservation()->update([
                'reservation_status' => 'canceled',
                'canceled_at' => Carbon::now(),
            ]);
        });
    }

    public function markPaymentFailed(string $intentId): void
    {
        DB::transaction(function () use ($intentId) {
            $payment = Payment::where('payment_intent_id', $intentId)
                ->lockForUpdate()
                ->first();

            if (! $payment || $payment->status !== 'pending') {
                return;
            }

            $payment->update(['status' => 'failed']);
        });
    }

    public function markChargeRefunded(string $intentId): void
    {
        DB::transaction(function () use ($intentId) {
            $payment = Payment::where('payment_intent_id', $intentId)
                ->lockForUpdate()
                ->first();

            if (! $payment || $payment->status === 'refunded') {
                return;
            }

            $payment->update(['status' => 'refunded']);
        });
    }

    public function cancelPendingPayment(Reservation $reservation): void
    {
        $this->ensureConfigured();
        $payment = $reservation->payments()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (! $payment) {
            return;
        }

        if ($payment->stripe_session_id) {
            try {
                $session = Session::retrieve($payment->stripe_session_id);
                $session->expire();
            } catch (\Throwable $e) {
                Log::warning('Failed to expire Stripe session via cancelPendingPayment', [
                    'stripe_session_id' => $payment->stripe_session_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $payment->update(['status' => 'canceled']);
    }

    // Expire all pending Stripe sessions for a reservation so the checkout page
    // becomes unusable immediately after the reservation is canceled.
    public function expirePendingSessions(Reservation $reservation): void
    {
        $this->ensureConfigured();
        $payments = $reservation->payments()
            ->where('status', 'pending')
            ->whereNotNull('stripe_session_id')
            ->get();

        $payments->each(function ($payment) {
            try {
                $session = Session::retrieve($payment->stripe_session_id);
                $session->expire();
            } catch (\Throwable $e) {
                Log::warning('Failed to expire Stripe session', [
                    'stripe_session_id' => $payment->stripe_session_id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}
