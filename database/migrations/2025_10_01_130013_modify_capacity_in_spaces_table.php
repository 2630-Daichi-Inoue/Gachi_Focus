<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('capacity');

            // add min / max 
            $table->integer('capacity_min')->nullable()->after('type');
            $table->integer('capacity_max')->nullable()->after('capacity_min');
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            // rollback 
            $table->dropColumn(['capacity_min', 'capacity_max']);
            $table->integer('capacity')->default(0)->after('type');
        });
    }
};
