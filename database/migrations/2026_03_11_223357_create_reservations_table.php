<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlId('space_id')->constrained()->cascadeOnDelete();
            $table->string('reservation_status')
                    ->default('booked'); // 'booked', 'canceled'
            $table->datetime('start_at');
            $table->datetime('end_at'); // end_at = start_at + 30 minutes * slot_count, end_at <= spaces.close_time
            $table->unsignedTinyInteger('quantity'); // quantity >= 1
            $table->unsignedTinyInteger('slot_count'); // 1 <= slot_count <= 16(eight hours at most)
            $table->unsignedInteger('unit_price_yen');
            $table->unsignedInteger('total_price_yen');
            $table->datetime('canceled_at')->nullable();
            $table->timestamps();

            $table->index(['space_id', 'start_at']);
            $table->index(['space_id', 'end_at']);
            $table->index(['user_id', 'start_at']);
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
