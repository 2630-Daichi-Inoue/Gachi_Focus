<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // ISO currency code (ex: JPY, USD, EUR)
            $table->char('currency', 3)->default('JPY')->after('amount_paid');

            // Payment region or market (ex: JP, US, EU)
            $table->string('payment_region', 10)->nullable()->after('currency');

            // index for analytics
            $table->index(['currency']);
            $table->index(['payment_region']);
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['currency']);
            $table->dropIndex(['payment_region']);
            $table->dropColumn(['currency', 'payment_region']);
        });
    }
};
