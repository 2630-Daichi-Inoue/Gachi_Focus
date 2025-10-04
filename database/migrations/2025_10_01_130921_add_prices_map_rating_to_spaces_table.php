<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->decimal('weekday_price', 8, 2)->default(0)->after('capacity_max');
            $table->decimal('weekend_price', 8, 2)->default(0)->after('weekday_price');
            $table->string('map_embed')->nullable()->after('address');
            $table->float('rating', 2, 1)->default(0)->after('map_embed');
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn(['weekday_price', 'weekend_price', 'map_embed', 'rating']);
        });
    }
};
