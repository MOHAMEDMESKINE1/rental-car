<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('seat_count')->default(5);
            $table->integer('luggage_count')->default(2);
            $table->enum('transmission', ['automatic', 'manual', 'both'])->default('automatic');
            $table->decimal('base_price_per_day', 10, 2);
            $table->decimal('extra_km_price', 10, 2)->default(0);
            $table->integer('free_km_per_day')->default(300);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_categories');
    }
};
