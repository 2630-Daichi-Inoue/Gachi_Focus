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
        Schema::create('amenity_space', function (Blueprint $table) {
            $table->foreignUlId('space_id')->constrained()->cascadeOnDelete();
            $table->foreignUlId('amenity_id')->constrained()->cascadeOnDelete();

            $table->primary(['space_id','amenity_id']);
            $table->index(['space_id', 'amenity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenity_space');
    }
};
