<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Config, Log, DB};
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use App\Models\{Reservation, Payment};
use Carbon\Carbon;
use Stripe\Webhook;

class PaymentController extends Controller
{
    /**
     * Start a Stripe Checkout session for the given pending reservation.
     * If an active pending payment already exists, redirect to its existing session URL.
     */
    public function checkout(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) {
            abort(403);
        }

        // Reservation must be pending to proceed
        if ($reservation->reservation_status !== 'pending') {
            $message = $reservation->reservation_status === 'booked'
                ? 'This reservation has already been paid.'
                : 'This reservation is no longer available for payment.';
            return redirect()->route('reservations.index')->with('error', $message);
        }

        // If an active pending payment exists, redirect to its existing Stripe session
        $existingPayment = $reservation->payments()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($existingPayment?->stripe_session_url) {
            return Inertia::location($existingPayment->stripe_session_url);
        }

        // Re-check availability inside a transaction (count booked + pending, excluding self)
        try {
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
        } catch (ValidationException $e) {
            return redirect()->route('reservations.index')
                ->with('error', 'Sorry, the time slot is no longer available. Your reservation has been canceled.')
                ->withErrors($e->errors());
        }

        // Initialize Stripe
        $secret = trim((string) config('services.stripe.secret'));
        if (!$secret || !str_starts_with($secret, 'sk_')) {
            abort(500, 'Stripe secret key is missing or invalid.');
        }
        \Stripe\Stripe::setApiKey($secret);

        $space       = $reservation->space;
        $spaceName   = $space->name ?? "Space #{$reservation->space_id}";
        $productName = sprintf(
            '%s / %s %s-%s',
            $spaceName,
            $reservation->started_at->format('Y-m-d'),
            $reservation->started_at->format('H:i'),
            $reservation->ended_at->format('H:i')
        );

        $successUrl = route('payments.success', $reservation) . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = route('payments.cancel',  $reservation);

        $session = \Stripe\Checkout\Session::create([
            'mode'                 => 'payment',
            'payment_method_types' => ['card'],
            'customer_email'       => Auth::user()->email,
            'expires_at'           => now()->addMinutes(31)->timestamp,
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'jpy',
                    'unit_amount'  => $reservation->total_price_yen,
                    'product_data' => ['name' => $productName],
                ],
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl,
            'metadata'    => [
                'reservation_id' => (string) $reservation->id,
                'user_id'        => (string) Auth::id(),
            ],
        ]);

        Payment::create([
            'reservation_id'     => $reservation->id,
            'payment_method'     => 'stripe_checkout',
            'status'             => 'pending',
            'stripe_session_id'  => $session->id,
            'stripe_session_url' => $session->url,
            'amount'             => $reservation->total_price_yen,
            'currency'           => 'JPY',
        ]);

        return Inertia::location($session->url);
    }

    /**
     * Handle Stripe webhook events.
     * Stripe signature is verified before processing any event.
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');
        $secret  = Config::get('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        match ($event->type) {
            'checkout.session.completed'    => $this->handleSessionCompleted((array) $event->data->object),
            'checkout.session.expired'      => $this->handleSessionExpired((array) $event->data->object),
            'payment_intent.payment_failed' => $this->handlePaymentFailed((array) $event->data->object),
            'charge.refunded'               => $this->handleChargeRefunded((array) $event->data->object),
            default                         => null,
        };

        return response()->noContent();
    }

    /**
     * Stripe redirects here after successful payment.
     * Verifies the session with Stripe and updates the DB immediately.
     * The webhook handler is kept as a fallback (e.g. browser closed before redirect).
     */
    public function success(Request $request, Reservation $reservation)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            $secret = trim((string) config('services.stripe.secret'));
            \Stripe\Stripe::setApiKey($secret);

            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    DB::transaction(function () use ($sessionId, $session) {
                        $payment = Payment::where('stripe_session_id', $sessionId)
                            ->lockForUpdate()
                            ->first();

                        if (!$payment || $payment->status === 'paid') return;

                        $payment->update([
                            'status'             => 'paid',
                            'payment_intent_id'  => $session->payment_intent ?? null,
                            'paid_at'            => Carbon::now(),
                        ]);

                        $payment->reservation()->update(['reservation_status' => 'booked']);
                    });
                }
            } catch (\Throwable $e) {
                Log::warning('Stripe session retrieval failed on success redirect', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('reservations.index')
            ->with('ok', 'Payment completed! Your reservation is confirmed.');
    }

    /**
     * Stripe redirects here when the user cancels on the checkout page.
     * Marks the pending payment as canceled; reservation stays pending so the user can retry.
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        $reservation->payments()
            ->where('status', 'pending')
            ->latest()
            ->first()
            ?->update(['status' => 'canceled']);

        return redirect()->route('reservations.index')
            ->with('warning', 'Payment was canceled. Your reservation slot is held for 30 minutes — you can retry payment from your reservations list.');
    }

    // -------------------------------------------------------------------------
    // Private webhook handlers
    // -------------------------------------------------------------------------

    private function handleSessionCompleted(array $obj): void
    {
        $sessionId = $obj['id'] ?? null;
        if (!$sessionId) return;

        DB::transaction(function () use ($obj, $sessionId) {
            $payment = Payment::where('stripe_session_id', $sessionId)
                ->lockForUpdate()
                ->first();

            if (!$payment || $payment->status === 'paid') return;

            $payment->update([
                'status'            => 'paid',
                'payment_intent_id' => $obj['payment_intent'] ?? null,
                'payment_region'    => $obj['payment_method_details']['card']['country'] ?? null,
                'paid_at'           => Carbon::now(),
            ]);

            $payment->reservation()->update(['reservation_status' => 'booked']);
        });
    }

    private function handleSessionExpired(array $obj): void
    {
        $sessionId = $obj['id'] ?? null;
        if (!$sessionId) return;

        DB::transaction(function () use ($sessionId) {
            $payment = Payment::where('stripe_session_id', $sessionId)
                ->lockForUpdate()
                ->first();

            if (!$payment || $payment->status !== 'pending') return;

            $payment->update(['status' => 'expired']);
            $payment->reservation()->update([
                'reservation_status' => 'canceled',
                'canceled_at'        => Carbon::now(),
            ]);
        });
    }

    private function handlePaymentFailed(array $obj): void
    {
        $intentId = $obj['id'] ?? null;
        if (!$intentId) return;

        DB::transaction(function () use ($intentId) {
            $payment = Payment::where('payment_intent_id', $intentId)
                ->lockForUpdate()
                ->first();

            if (!$payment || $payment->status !== 'pending') return;

            // Reservation stays pending — user can retry with a new session
            $payment->update(['status' => 'failed']);
        });
    }

    /**
     * Fallback: Stripe confirms refund completed via webhook.
     * Handles the case where the RefundService call succeeded but the local
     * DB update to 'refunded' failed (e.g. process crash after Stripe call).
     */
    private function handleChargeRefunded(array $obj): void
    {
        $intentId = $obj['payment_intent'] ?? null;
        if (!$intentId) return;

        DB::transaction(function () use ($intentId) {
            $payment = Payment::where('payment_intent_id', $intentId)
                ->lockForUpdate()
                ->first();

            if (!$payment || $payment->status === 'refunded') return;

            $payment->update(['status' => 'refunded']);
        });
    }
}
