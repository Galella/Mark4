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
        Schema::table('daily_incomes', function (Blueprint $table) {
            // Add indexes to improve query performance
            $table->index(['date'], 'idx_daily_incomes_date');
            $table->index(['outlet_id'], 'idx_daily_incomes_outlet_id');
            $table->index(['moda_id'], 'idx_daily_incomes_moda_id');
            $table->index(['user_id'], 'idx_daily_incomes_user_id');

            // Composite indexes for common queries
            $table->index(['outlet_id', 'date'], 'idx_daily_incomes_outlet_date');
            $table->index(['moda_id', 'date'], 'idx_daily_incomes_moda_date');
            $table->index(['date', 'outlet_id', 'moda_id'], 'idx_daily_incomes_date_outlet_moda');

            // Index for income amount for sorting/queries
            $table->index(['income'], 'idx_daily_incomes_income');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_incomes', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['idx_daily_incomes_date']); // drops 'daily_incomes_date_index'
            $table->dropIndex(['idx_daily_incomes_outlet_id']); // drops 'daily_incomes_outlet_id_index'
            $table->dropIndex(['idx_daily_incomes_moda_id']); // drops 'daily_incomes_moda_id_index'
            $table->dropIndex(['idx_daily_incomes_user_id']); // drops 'daily_incomes_user_id_index'

            $table->dropIndex(['idx_daily_incomes_outlet_date']); // drops 'daily_incomes_outlet_date_index'
            $table->dropIndex(['idx_daily_incomes_moda_date']); // drops 'daily_incomes_moda_date_index'
            $table->dropIndex(['idx_daily_incomes_date_outlet_moda']); // drops 'daily_incomes_date_outlet_moda_index'

            $table->dropIndex(['idx_daily_incomes_income']); // drops 'daily_incomes_income_index'
        });
    }
};
