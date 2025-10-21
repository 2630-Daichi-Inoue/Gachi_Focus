<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->tinyInteger('cleanliness')->nullable()->after('space_id');
            $table->tinyInteger('conditions')->nullable()->after('cleanliness');
            $table->tinyInteger('facilities')->nullable()->after('conditions');
            $table->string('photo')->nullable()->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['cleanliness', 'conditions', 'facilities', 'photo']);
        });
    }
};
