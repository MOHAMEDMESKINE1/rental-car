<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('coverage_type', ['basic', 'collision', 'comprehensive', 'full'])->default('basic');
            $table->decimal('price_per_day', 10, 2);
            $table->decimal('deductible', 10, 2)->default(0);
            $table->boolean('covers_theft')->default(false);
            $table->boolean('covers_third_party')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_plans');
    }
};
