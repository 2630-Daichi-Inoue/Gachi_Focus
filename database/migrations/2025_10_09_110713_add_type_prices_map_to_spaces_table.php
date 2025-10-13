<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            // $table->integer('type')->after('location_for_details');
            $table->decimal('weekday_price', 8, 2)->default(0)->after('max_capacity');
            $table->decimal('weekend_price', 8, 2)->default(0)->after('weekday_price');
            $table->text('map_embed')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn(['weekday_price', 'weekend_price', 'map_embed', 'rating']);
        });
    }
};
