<!-- ②custom_notificationsのcustom_とつける理由はある？ -->

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
        Schema::create('custom_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->cascadeonDelete();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->cascadeonDelete();
            $table->string('type'); // the notification's type: approved reservation, changed coworking space, and so on.
            $table->text('message');
            $table->foreignId('reservation_id')->nullable()->constrained()->cascadeOnDelete(); // It is related to reservation of coworking space.
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_notifications');
    }
};
