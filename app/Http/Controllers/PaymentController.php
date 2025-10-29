<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Config, Log, DB};
use App\Models\Reservation;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Webhook;

class PaymentController extends Controller
{
    /**
     * Create a Stripe Checkout session on the server
     * and redirect the user to Stripe-hosted checkout page.
     */
    public function checkout(Request $request, Reservation $reservation)
{
    // 1) Ensure the reservation belongs to the current user
    if (Auth::check() && $reservation->user_id && $reservation->user_id !== Auth::id()) {
        abort(403);
    }

    // 2) Prevent double payment
    $status = $reservation->payment_status ?? 'unpaid';
    if ($status === 'paid') {
        return back()->with('success', 'This reservation is already paid.');
    }

    // 3) Prepare amount and currency (server-side only)
    $currency = strtoupper($reservation->currency ?? 'JPY');
    $rawTotal = (float) ($reservation->total_price ?? 0.0);

    // Determine zero-decimal currencies
    $zeroDecimal = in_array($currency, [
        'BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG',
        'RWF','UGX','VND','VUV','XAF','XOF','XPF'
    ], true);

    $amount = (int) round($rawTotal * ($zeroDecimal ? 1 : 100));
    if ($amount <= 0) {
        return back()->with('error', 'Invalid payment amount.');
    }

    // 4) Initialize Stripe (hard guard & sanitize)
    // Ensure Stripe secret is present and clean before using it
    $secret = (string) config('services.stripe.secret');
    // strip BOM / stray unicode spaces just in case
    $secret = preg_replace('/^\xEF\xBB\xBF/', '', $secret ?? '');
    $secret = preg_replace('/^\s+/u', '', $secret ?? '');
    $secret = preg_replace('/\s+$/u', '', $secret ?? '');

    // Log basic diagnostics (safe: only prefix/length/last4)
    \Log::info('Stripe key diag', [
        'starts_with_sk' => str_starts_with($secret, 'sk_'),
        'length'         => strlen($secret),
        'first7'         => substr($secret, 0, 7),
        'last4'          => substr($secret, -4),
    ]);

    if (!$secret || !str_starts_with($secret, 'sk_') || strlen($secret) < 20) {
        abort(500, 'Stripe secret key is missing or invalid. Check .env and config cache.');
    }
    \Stripe\Stripe::setApiKey($secret);

    // Success / Cancel URLs
    $successUrl = route('payments.success', ['reservation' => $reservation->id]) . '?session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl  = route('payments.cancel',  ['reservation' => $reservation->id]);

    // Product name (optional)
    $spaceName   = optional($reservation->space)->name ?? "Space #{$reservation->space_id}";
    $day         = optional($reservation->date)->format('Y-m-d'); // make date display consistent
    $productName = "{$spaceName} / {$day} {$reservation->start_time}-{$reservation->end_time}";

    // Idempotency key (to prevent duplicate sessions)
    $idempotencyKey = "reserve_srv_{$reservation->id}_{$reservation->updated_at?->timestamp}";

    // 5) Create checkout session
    $session = \Stripe\Checkout\Session::create([
        'mode' => 'payment',
        'payment_method_types' => ['card'],
        'customer_email' => Auth::user()->email ?? null,
        'line_items' => [[
            'price_data' => [
                'currency' => strtolower($currency),
                'unit_amount' => $amount,
                'product_data' => [
                    'name' => $productName,
                    'metadata' => [
                        'reservation_id' => (string) $reservation->id,
                        'user_id'        => (string) Auth::id(),
                    ],
                ],
            ],
            'quantity' => 1,
        ]],
        'success_url' => $successUrl,
        'cancel_url'  => $cancelUrl,
        'metadata' => [
            'reservation_id' => (string) $reservation->id,
            'user_id'        => (string) Auth::id(),
        ],
    ], [
        'idempotency_key' => $idempotencyKey,
    ]);

    // 6) Mark as pending until Stripe confirms via webhook
    $reservation->payment_status = 'pending';
    $reservation->payment_intent_id = $session->payment_intent ?? null;
    $reservation->save();

    // 7) Redirect to Stripe checkout page
    return redirect()->away($session->url);
    }


    /**
     * Handle Stripe webhook events.
     * This is the source of truth for marking a payment as "paid".
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');
        $secret  = Config::get('services.stripe.webhook_secret');

        try {
            // Verify Stripe signature
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        // Listen for relevant events
        if (in_array($event->type, ['checkout.session.completed', 'payment_intent.succeeded'], true)) {
            $obj = $event->data['object'];

            $reservationId   = $obj['metadata']['reservation_id'] ?? null;
            $paymentIntentId = $obj['payment_intent'] ?? $obj['id'] ?? null;
            $amount          = (int) ($obj['amount_total'] ?? $obj['amount'] ?? 0);
            $currency        = strtoupper($obj['currency'] ?? 'JPY');
            $region          = strtoupper($obj['payment_method_details']['card']['country'] ?? '');

            if ($reservationId) {
                DB::transaction(function () use ($reservationId, $paymentIntentId, $amount, $currency, $region) {
                    $res = Reservation::lockForUpdate()->find($reservationId);
                    if (!$res) return;

                    // Skip if already paid
                    if ($res->payment_status === 'paid') return;

                    // Update payment details
                    $res->payment_status    = 'paid';
                    $res->payment_intent_id = $paymentIntentId;
                    $res->amount_paid       = $amount ?: (int) round($res->total_price ?? 0);
                    $res->currency          = $currency;
                    $res->payment_region    = $region ?: ($res->payment_region ?? null);
                    $res->paid_at           = Carbon::now();
                    $res->save();
                });
            }
        }

        return response()->noContent();
    }

    /**
     * Show success page after Stripe redirects back.
     * Note: The actual "paid" state should be confirmed by webhook.
     */
    public function success(Request $request, Reservation $reservation)
    {
        return view('payments.success', [
            'reservation' => $reservation,
            'session_id'  => $request->query('session_id'),
        ]);
    }

    /**
     * Show cancel page when user aborts payment.
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        return view('payments.cancel', [
            'reservation' => $reservation,
        ]);
    }
    
}
