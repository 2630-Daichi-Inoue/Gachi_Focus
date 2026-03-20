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
        Schema::create('spaces', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 50);
            $table->string('prefecture', 20);
            $table->string('city', 50);
            $table->string('address_line', 255);
            $table->unsignedTinyInteger('capacity');
            $table->time('open_time');
            $table->time('close_time');
            $table->unsignedInteger('weekday_price_yen');
            $table->unsignedInteger('weekend_price_yen');
            $table->text('description');
            $table->string('image_path', 255);
            $table->boolean('is_public')
                    ->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['prefecture', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }

};
