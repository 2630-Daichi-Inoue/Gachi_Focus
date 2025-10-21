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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->unique()->constrained()->cascadeOnDelete();;
            $table->string('method');
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }
// INSERT INTO `payments`(`id`, `reservation_id`, `method`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 1, 'Card', 'Paid', '2025-04-01 00:00:00', '2025-04-01 00:00:00', null);



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};