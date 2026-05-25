<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->enum('type', ['oil_change', 'tire_rotation', 'brake_service', 'inspection', 'repair', 'other']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->integer('mileage_at_service')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->string('performed_by')->nullable();
            $table->string('garage_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance');
    }
};
