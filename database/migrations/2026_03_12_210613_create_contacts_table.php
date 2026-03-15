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
        Schema::create('contacts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlId('reservation_id')->constrained()->cascadeOnDelete()->nullable();
            $table->string('title', 100);
            $table->text('message');
            $table->string('contact_status', 20)->default('open');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
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
