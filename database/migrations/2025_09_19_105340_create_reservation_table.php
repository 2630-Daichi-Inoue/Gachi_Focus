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
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('room', 10)->nullable();         // 'B'
            $table->string('type', 50);                      // focus_booth / meeting / phone_call
            $table->date('date');
            $table->time('start_time');                      // ← time_from
            $table->time('end_time');                        // ← time_to  
            $table->unsignedTinyInteger('adults');
            $table->json('facilities')->nullable();
            $table->decimal('total_price', 8, 2)->default(0); // ← price
            $table->timestamps();
            $table->softDeletes(); 
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation');
    }
};
