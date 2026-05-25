<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->enum('type', ['pre_rental', 'post_rental']);
            $table->enum('condition_front', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->enum('condition_back', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->enum('condition_left', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->enum('condition_right', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->enum('condition_interior', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->integer('fuel_level')->default(100);
            $table->integer('mileage');
            $table->boolean('spare_tire')->default(true);
            $table->boolean('jack_tool')->default(true);
            $table->boolean('first_aid_kit')->default(true);
            $table->boolean('fire_extinguisher')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('inspected_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('inspected_at');
            $table->string('customer_signature')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspections');
    }
};
