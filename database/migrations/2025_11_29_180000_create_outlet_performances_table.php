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
        Schema::create('outlet_performances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outlet_id');
            $table->date('date');
            
            // Actual performance metrics
            $table->decimal('income', 15, 2)->default(0);
            $table->integer('colly')->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            
            // Target metrics
            $table->decimal('target_income', 15, 2)->default(0);
            $table->integer('target_colly')->default(0);
            
            // Calculated performance metrics
            $table->decimal('achievement_rate', 8, 2)->default(0); // Percentage
            $table->decimal('performance_score', 8, 2)->default(0); // Overall score
            
            // Additional information
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['outlet_id'], 'idx_outlet_performance_outlet_id');
            $table->index(['date'], 'idx_outlet_performance_date');
            $table->index(['outlet_id', 'date'], 'idx_outlet_performance_outlet_date');
            $table->index(['achievement_rate'], 'idx_outlet_performance_achievement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlet_performances');
    }
};