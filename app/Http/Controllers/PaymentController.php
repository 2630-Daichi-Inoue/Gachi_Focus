<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Stripe\Webhook;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private StripePaymentService $stripePaymentService) {}

    public function checkout(Reservation $reservation)
    {
        $this->authorize('pay', $reservation);

        if ($reservation->reservation_status !== 'pending') {
            $message = $reservation->reservation_status === 'booked'
                ? 'This reservation has already been paid.'
                : 'This reservation is no longer available for payment.';

            return redirect()->route('reservations.index')->with('error', $message);
        }

        $existingPayment = $reservation->payments()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($existingPayment?->stripe_session_url) {
            return Inertia::location($existingPayment->stripe_session_url);
        }

        try {
            $this->stripePaymentService->revalidateAvailability($reservation);
        } catch (ValidationException $e) {
            return redirect()->route('reservations.index')
                ->with('error', 'Sorry, the time slot is no longer available. Your reservation has been canceled.')
                ->withErrors($e->errors());
        }

        $sessionUrl = $this->stripePaymentService->createCheckoutSession($reservation, Auth::user()->email);

        return Inertia::location($sessionUrl);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');
        $secret = Config::get('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        $obj = (array) $event->data->object;

        match ($event->type) {
            'checkout.session.completed' => $this->stripePaymentService->markSessionPaid(
                $obj['id'],
                $obj['payment_intent'] ?? null
            ),
            'checkout.session.expired' => $this->stripePaymentService->markSessionExpired($obj['id']),
            'payment_intent.payment_failed' => $this->stripePaymentService->markPaymentFailed($obj['id']),
            'charge.refunded' => $this->stripePaymentService->markChargeRefunded($obj['payment_intent'] ?? ''),
            default => null,
        };

        return response()->noContent();
    }

    public function success(Request $request, Reservation $reservation)
    {
        $this->authorize('pay', $reservation);

        $sessionId = $request->query('session_id');

        if ($sessionId) {
            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    $this->stripePaymentService->markSessionPaid($sessionId, $session->payment_intent ?? null);
                }
            } catch (\Throwable $e) {
                Log::warning('Stripe session retrieval failed on success redirect', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('reservations.index')
            ->with('ok', 'Payment completed! Your reservation is confirmed.');
    }

    public function cancel(Reservation $reservation)
    {
        $this->authorize('pay', $reservation);

        $this->stripePaymentService->cancelPendingPayment($reservation);

        return redirect()->route('reservations.index')
            ->with('warning', 'Payment was canceled. Your reservation slot is held for 30 minutes — you can retry payment from your reservations list.');
    }
}
