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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            // $table->unsignedBigInteger('user_id');
            // $table->unsignedBigInteger('space_id');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('space_id')->references('id')->on('spaces');
        });
    }
// INSERT INTO `reviews`(`id`, `user_id`, `space_id`, `rating`, `comment`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 1, 1, 5, 'Great!', '2025-01-01 00:00:00', null, null), (2, 2, 1, 4, 'Nice!', '2025-01-01 00:00:00', null, null), (3, 2, 1, 4, 'Good!', '2025-01-01 00:00:00', null, null), (4, 1, 1, 5, 'Wonderful', '2025-01-01 00:00:00', null, null);

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};