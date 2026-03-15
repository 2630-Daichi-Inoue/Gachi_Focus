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
        Schema::create('favorites', function (Blueprint $table) {
            $table->foreignUlId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlId('space_id')->constrained()->cascadeOnDelete();

            $table->primary(['user_id','space_id']);
            $table->index(['user_id', 'space_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
