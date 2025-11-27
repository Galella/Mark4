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
        Schema::create('daily_income_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('outlet_id')->nullable();
            $table->unsignedBigInteger('moda_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // For tracking

            // Summary data
            $table->unsignedInteger('total_colly')->default(0);
            $table->decimal('total_weight', 15, 2)->default(0);
            $table->decimal('total_income', 15, 2)->default(0);
            $table->unsignedInteger('record_count')->default(0); // Number of detailed records

            $table->timestamps();

            // Indexes for performance
            $table->index(['date'], 'idx_daily_summaries_date');
            $table->index(['outlet_id'], 'idx_daily_summaries_outlet_id');
            $table->index(['moda_id'], 'idx_daily_summaries_moda_id');
            $table->index(['date', 'outlet_id'], 'idx_daily_summaries_date_outlet');
            $table->index(['date', 'moda_id'], 'idx_daily_summaries_date_moda');
            $table->index(['outlet_id', 'moda_id'], 'idx_daily_summaries_outlet_moda');
            $table->index(['date', 'outlet_id', 'moda_id'], 'idx_daily_summaries_compound');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_income_summaries');
    }
};
