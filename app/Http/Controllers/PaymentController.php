<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Config,DB,Log};
use App\Models\Reservation;
use Carbon\Carbon;
use Stripe\StripeClient;
use Stripe\Webhook;

class PaymentController extends Controller
{
    // create Stripe Checkout Session
    public function createCheckoutSession(Request $request)
    {
        // validate input
        $data = $request->validate([
            'reservation_id' => ['required','integer','exists:reservations,id'],
        ]);

        // ensure owner
        $reservation = Reservation::where('id', $data['reservation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // amount: use integer in smallest currency unit
        // JPY is zero-decimal, so use yen as integer
        $currency = strtoupper($reservation->currency ?? 'JPY'); // e.g., JPY / USD
        $amount = (int) ($reservation->amount_paid ?? round($reservation->total_price ?? 0));
        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid amount'], 422);
        }

        // Stripe client
        $stripe = new StripeClient(Config::get('services.stripe.secret'));

        // idempotency key to avoid duplicate sessions
        $idempotencyKey = "reserve_{$reservation->id}_{$reservation->updated_at?->timestamp}";

        // success/cancel URLs
        $successUrl = rtrim(config('app.url'),'/') . route('payments.success', [], false) . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = rtrim(config('app.url'),'/') . route('payments.cancel', [], false) . '?rid='.$reservation->id;

        // session create
        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => Auth::user()->email ?? null,
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($currency), // stripe expects lowercase
                    'unit_amount' => $amount,           // smallest unit
                    'product_data' => [
                        'name' => "Room {$reservation->room} / {$reservation->date} {$reservation->start_time}-{$reservation->end_time}",
                        'metadata' => [
                            'reservation_id' => (string)$reservation->id,
                            'user_id' => (string)Auth::id(),
                        ],
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl,
            'metadata' => [
                'reservation_id' => (string)$reservation->id,
                'user_id' => (string)Auth::id(),
            ],
        ], [
            'idempotency_key' => $idempotencyKey,
        ]);

        return response()->json(['id' => $session->id]);
    }

    // Stripe webhook (mark as paid)
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');
        $secret = Config::get('services.stripe.webhook_secret');

        try {
            // verify signature
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Throwable $e) {
            Log::warning('stripe webhook signature failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        // handle important events only
        if (in_array($event->type, ['checkout.session.completed','payment_intent.succeeded'], true)) {
            $obj = $event->data['object'];

            // read common fields
            $reservationId = null;
            $paymentIntentId = null;
            $amount = null;
            $currency = null;
            $region = null;

            if ($event->type === 'checkout.session.completed') {
                // from session
                $reservationId   = $obj['metadata']['reservation_id'] ?? null;
                $paymentIntentId = $obj['payment_intent'] ?? null;
                $amount          = (int)($obj['amount_total'] ?? 0);
                $currency        = strtoupper($obj['currency'] ?? 'JPY'); // e.g., jpy -> JPY
                // region from payment_details if available later (optional extra fetch)
            } else {
                // from payment_intent
                $reservationId   = $obj['metadata']['reservation_id'] ?? null;
                $paymentIntentId = $obj['id'] ?? null;
                $amount          = (int)($obj['amount'] ?? 0);
                $currency        = strtoupper($obj['currency'] ?? 'JPY');
                // try to read card country (if exists)
                $region = strtoupper($obj['payment_method_options']['card']['mandate_options']['start_date'] ?? '');
            }

            // try to resolve region from nested payment_method_details if provided
            // (Stripe may include country under payment_method_details->card->country in some events)
            if (!$region && isset($obj['payment_method_details']['card']['country'])) {
                $region = strtoupper($obj['payment_method_details']['card']['country']);
            }

            if ($reservationId) {
                DB::transaction(function () use ($reservationId, $paymentIntentId, $amount, $currency, $region) {
                    /** @var Reservation $res */
                    $res = Reservation::lockForUpdate()->find($reservationId);
                    if (!$res) return;

                    // idempotent: skip if already paid
                    if ($res->payment_status === 'paid') return;

                    $res->payment_status    = 'paid';
                    $res->payment_intent_id = $paymentIntentId;
                    $res->amount_paid       = $amount ?: ($res->amount_paid ?? (int) round($res->total_price ?? 0));
                    $res->currency          = $currency ?: ($res->currency ?? 'JPY');
                    $res->payment_region    = $region ?: ($res->payment_region ?? null);
                    $res->paid_at           = Carbon::now();
                    // optional: app-side reservation status
                    // $res->status = 'confirmed';
                    $res->save();
                });
            }
        }

        return response()->noContent();
    }

    // success/ cancel page
    public function success() { return view('payments.success'); }
    public function cancel()  { return view('payments.cancel');  }
}
