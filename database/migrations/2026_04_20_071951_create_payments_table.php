<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method');
            $table->string('status')->default('pending'); // pending / paid / failed /canceled / expired / refunded
            $table->string('stripe_session_id')->unique();
            $table->text('stripe_session_url')->nullable();
            $table->string('payment_intent_id')->nullable()->unique();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('JPY');
            $table->datetime('paid_at')->nullable();
            $table->timestamps();

            $table->index('reservation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
