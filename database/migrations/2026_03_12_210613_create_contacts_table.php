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
        Schema::create('contacts', function (Blueprint $table) {
            $table->ulid('id')
                    ->primary();
            $table->foreignUlId('user_id')
                    ->constrained();
            $table->foreignUlId('reservation_id')
                    ->nullable()
                    ->constrained();
            $table->string('title', 50);
            $table->text('message');
            $table->string('contact_status', 20)
                    ->default('open'); // 'open', 'closed'
            $table->timestamp('read_at')
                    ->nullable();
            $table->timestamp('canceled_at')
                    ->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['reservation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
