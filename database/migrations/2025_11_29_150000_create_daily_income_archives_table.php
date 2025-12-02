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
        Schema::create('daily_income_archives', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('moda_id')->nullable(); // Make nullable for archival
            $table->integer('colly');
            $table->decimal('weight', 10, 2);
            $table->decimal('income', 15, 2);
            $table->unsignedBigInteger('outlet_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            // Indexes for performance
            $table->index(['date'], 'idx_daily_archives_date');
            $table->index(['outlet_id'], 'idx_daily_archives_outlet_id');
            $table->index(['moda_id'], 'idx_daily_archives_moda_id');
            $table->index(['date', 'outlet_id'], 'idx_daily_archives_date_outlet');
            $table->index(['date', 'moda_id'], 'idx_daily_archives_date_moda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_income_archives');
    }
};