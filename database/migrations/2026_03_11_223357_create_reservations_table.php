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
            $table->ulid('id')
                    ->primary();
            $table->foreignUlId('user_id')
                    ->constrained();
            $table->foreignUlId('space_id')
                    ->constrained();
            $table->string('reservation_status')
                    ->default('booked'); // booked, canceled, pending
            $table->datetime('started_at');
            $table->datetime('ended_at'); // ended_at = started_at + 30 minutes * slot_count, ended_at <= spaces.close_time
            $table->unsignedTinyInteger('quantity'); // quantity >= 1
            $table->unsignedTinyInteger('slot_count'); // 1 <= slot_count <= 16(eight hours at most)
            $table->unsignedInteger('unit_price_yen');
            $table->unsignedInteger('total_price_yen');
            $table->datetime('canceled_at')
                    ->nullable();
            $table->timestamps();

            $table->index(['space_id', 'started_at']);
            $table->index(['space_id', 'ended_at']);
            $table->index(['user_id', 'started_at']);
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
