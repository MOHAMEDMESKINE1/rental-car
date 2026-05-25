<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('rental_number')->unique();
            $table->foreignUuid('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignUuid('pickup_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('dropoff_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('insurance_plan_id')->nullable()->constrained('insurance_plans')->nullOnDelete();
            $table->foreignUuid('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            $table->datetime('planned_pickup_at');
            $table->datetime('planned_dropoff_at');
            $table->datetime('actual_pickup_at')->nullable();
            $table->datetime('actual_dropoff_at')->nullable();
            $table->integer('pickup_mileage')->default(0);
            $table->integer('dropoff_mileage')->nullable();
            $table->integer('pickup_fuel_level')->default(100);
            $table->integer('dropoff_fuel_level')->nullable();
            $table->decimal('base_amount', 10, 2);
            $table->decimal('insurance_amount', 10, 2)->default(0);
            $table->decimal('extras_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('extra_km_charges', 10, 2)->default(0);
            $table->decimal('late_return_charges', 10, 2)->default(0);
            $table->decimal('damage_charges', 10, 2)->default(0);
            $table->decimal('fuel_charges', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['active', 'overdue', 'completed', 'cancelled'])->default('active');
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->boolean('deposit_returned')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
