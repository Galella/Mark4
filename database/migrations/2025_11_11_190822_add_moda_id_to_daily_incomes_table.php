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
            // Add foreign key constraint for moda_id if the column exists
            if (Schema::hasColumn('daily_incomes', 'moda_id')) {
                // Check if the foreign key constraint already exists before adding it
                $table->foreign('moda_id')->references('id')->on('modas')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->dropForeign(['moda_id']);
            $table->dropColumn('moda_id');
        });
    }
};
