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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->constrained()->cascadeOnDelete();;
            $table->unsignedBigInteger('space_id')->constrained()->cascadeOnDelete();;
            $table->string('status');
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('space_id')->references('id')->on('spaces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
