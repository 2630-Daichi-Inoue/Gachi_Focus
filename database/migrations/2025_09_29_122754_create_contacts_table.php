<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // ⑥ここでnameとemailとphoneを持つ理由はあるか？usersテーブルから持ってくればいいのではないか？
            $table->string('name',100);
            $table->string('email',200);
            $table->string('phone',50)->nullable();
            
            $table->text('message');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists('contacts');
    }
};
