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
        Schema::table('spaces', function (Blueprint $table) {
            $table->string('country_code', 2)
            ->default('JP')
            ->after('location_for_details');

            $table->string('currency_code', 3)
            ->default('USD')
            ->after('country_code');

            $table->string('timezone', 50)
            ->default('Asia/Tokyo')
            ->after('currency_code');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'currency_code', 'timezone']);
        });
    }
};
