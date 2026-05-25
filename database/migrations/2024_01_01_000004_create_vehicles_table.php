<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('vehicle_categories')->restrictOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('make');
            $table->string('model');
            $table->year('year');
            $table->string('color');
            $table->string('plate_number')->unique();
            $table->string('vin')->unique()->nullable();
            $table->enum('transmission', ['automatic', 'manual']);
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric', 'hybrid'])->default('gasoline');
            $table->integer('seat_count')->default(5);
            $table->enum('status', ['available', 'reserved', 'rented', 'maintenance', 'retired'])->default('available');
            $table->integer('mileage')->default(0);
            $table->integer('fuel_level')->default(100)->comment('0-100 percentage');
            $table->date('next_service_date')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
