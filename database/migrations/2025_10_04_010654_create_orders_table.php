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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            // $table->unsignedBigInteger('reservation_id')->constrained()->cascadeOnDelete();;
            // $table->unsignedBigInteger('facility_id')->constrained()->cascadeOnDelete();;
            $table->unsignedTinyInteger('quantity');
            $table->decimal('subtotal_price', 8, 2);
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('reservation_id')->references('id')->on('reservations');
            // $table->foreign('facility_id')->references('id')->on('facilities');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};