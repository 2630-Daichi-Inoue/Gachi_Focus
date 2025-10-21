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

            // ③nullOnDeleteにする理由は？「ユーザ削除→予約削除」とするとcascadeOnDelete()の方がいいのではないか？
            //　 nullableでいいの？ユーザがいない予約はありということ？
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->string('status')->after('user_id');

            // ④roomとtypeは何に使う？spaceに値を持たせてそこから持ってくるようにできるのではないか？
            //　 roomがもし名前のことならspacesにすでにnameがあるのでそこから持ってこられる。
            //　 space_idを紐づけることで実現できる。
            $table->string('room', 10)->nullable();         // 'B'
            $table->string('type', 50);                      // focus_booth / meeting / phone_call
            
            $table->date('date');
            $table->time('start_time');                      // ← time_from
            $table->time('end_time');                        // ← time_to  

            // ⑤adultsは何を意味して何に使う？利用人数のこと？もしそうなら、adultという言葉の理由は？
            $table->unsignedTinyInteger('adults');

            // ⑥facilitiesがreservationにある理由は？orderテーブルを仲介して持ってくるのではないか？
            $table->json('facilities')->nullable();

            $table->decimal('total_price', 8, 2)->default(0); // ← price
            $table->timestamps();
            $table->softDeletes();
        });

    }

// INSERT INTO `reservations`(`id`, `user_id`, `space_id`, `status`, `room`, `type`, `date`, `start_time`, `end_time`, `adults`, `facilities`, `total_price`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 1, 1, 'Completed', 'A', 'Focus Booth', '2025-04-01', '08:00:00', '12:00:00', 10, null, 2400, '2025-01-01 00:00:00', null, null);

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation');
    }
};
