<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rental_id')->nullable()->constrained('rentals')->nullOnDelete();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->string('description');
            $table->enum('location', ['front', 'back', 'left', 'right', 'roof', 'interior', 'underbody']);
            $table->enum('severity', ['minor', 'moderate', 'major'])->default('minor');
            $table->decimal('repair_cost', 10, 2)->default(0);
            $table->boolean('customer_liable')->default(false);
            $table->boolean('is_repaired')->default(false);
            $table->date('repaired_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damages');
    }
};
