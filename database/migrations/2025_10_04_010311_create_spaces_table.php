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


        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location_for_overview');
            $table->string('location_for_details');
            $table->unsignedInteger('min_capacity');
            $table->unsignedInteger('max_capacity');
            $table->decimal('weekday_price', 8, 2);
            $table->decimal('weekend_price', 8, 2);
            $table->decimal('area', 10, 2);
            $table->text('description');
            $table->longText('image');
            $table->text('map_embed')->nullable();
            $table->float('rating', 2, 1)->default(0);
            $table->timestamps();
            $table->softDeletes();
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
