<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // 既に外部キーがある場合はいったん外す
            $table->dropForeign(['space_id']);

            // nullable → not null へ変更（要 doctrine/dbal）
            $table->foreignId('space_id')->nullable(false)->change();

            // 外部キーを再定義
            $table->foreign('space_id')
                  ->references('id')->on('spaces')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['space_id']);
            $table->foreignId('space_id')->nullable()->change();
            $table->foreign('space_id')
                  ->references('id')->on('spaces')
                  ->cascadeOnDelete();
        });
    }
};