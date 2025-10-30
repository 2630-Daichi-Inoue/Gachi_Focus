<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // add payment columns
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // status: unpaid|paid|canceled|refunded
            $table->string('payment_status', 20)->default('unpaid')->after('total_price');

            // Stripe payment_intent id (e.g. pi_xxx)
            $table->string('payment_intent_id', 100)->nullable()->after('payment_status');

            // paid amount in smallest currency unit (JPY=yens, USD=cents)
            $table->unsignedInteger('amount_paid')->nullable()->after('payment_intent_id');

            // paid datetime
            $table->timestamp('paid_at')->nullable()->after('amount_paid');

            // (optional) quick index
            $table->index(['payment_status']);
            $table->index(['payment_intent_id']);
        });
    }

    // remove payment columns
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);     // drop idx
            $table->dropIndex(['payment_intent_id']);  // drop idx
            $table->dropColumn(['payment_status','payment_intent_id','amount_paid','paid_at']);
        });
    }
};
