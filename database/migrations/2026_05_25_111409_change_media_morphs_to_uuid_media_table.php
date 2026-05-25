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
        Schema::table('media', function (Blueprint $table) {
            // Drop old BIGINT morph
            $table->dropColumn('model_id');
        });

        Schema::table('media', function (Blueprint $table) {
            // Add UUID morph
            $table->uuid('model_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('model_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->index();
        });
    }
};
