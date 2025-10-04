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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });
    }
    
// INSERT INTO `categories`(`id`, `name`, `created_at`, `updated_at`) VALUES
// (1, 'Calls Allowed', '2025-01-01 00:00:00', null),
// (2, 'Power Outlets', '2025-01-01 00:00:00', null),
// (3, 'Complimentary Drinks', '2025-01-01 00:00:00', null),
// (4, 'Ergonomic Seats', '2025-01-01 00:00:00', null),
// (5, 'Printers', '2025-01-01 00:00:00', null),
// (6, 'Projectors', '2025-01-01 00:00:00', null),
// (7, 'Whiteboards', '2025-01-01 00:00:00', null),
// (8, 'Lockers', '2025-01-01 00:00:00', null),
// (9, 'Natural Light', '2025-01-01 00:00:00', null),
// (10, 'Quiet Zone', '2025-01-01 00:00:00', null),
// (11, '24/7 Access', '2025-01-01 00:00:00', null),
// (12, 'Phone Booths', '2025-01-01 00:00:00', null),
// (13, 'Standing Desks', '2025-01-01 00:00:00', null),
// (14, 'Wheelchair Accessible', '2025-01-01 00:00:00', null);

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
