<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    // Minimal handler to unblock route:list
    public function handle(Request $request)
    {
        // You can log and return 200 to acknowledge Stripe
        // \Log::info('stripe webhook', ['event' => $request->all()]);
        return response()->json(['status' => 'ok']);
    }
}