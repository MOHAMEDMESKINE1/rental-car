<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reservation_number')->unique();
            $table->foreignUuid('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignUuid('pickup_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('dropoff_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('insurance_plan_id')->nullable()->constrained('insurance_plans')->nullOnDelete();
            $table->foreignUuid('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            $table->datetime('pickup_date');
            $table->datetime('dropoff_date');
            $table->decimal('base_amount', 10, 2);
            $table->decimal('insurance_amount', 10, 2)->default(0);
            $table->decimal('extras_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'converted', 'cancelled', 'expired'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
