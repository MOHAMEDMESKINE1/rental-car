<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUuid('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('1-5 stars');
            $table->unsignedTinyInteger('vehicle_rating')->nullable();
            $table->unsignedTinyInteger('service_rating')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique('rental_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
