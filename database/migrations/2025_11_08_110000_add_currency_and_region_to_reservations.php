<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'currency')) {
                $table->char('currency', 3)->default('JPY')->after('amount_paid');
            }

            if (!Schema::hasColumn('reservations', 'payment_region')) {
                $table->string('payment_region', 10)->default('JP')->after('currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'payment_region')) {
                $table->dropColumn('payment_region');
            }
            if (Schema::hasColumn('reservations', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};
