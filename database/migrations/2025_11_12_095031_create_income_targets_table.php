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
        Schema::create('income_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outlet_id');
            $table->integer('target_year'); // Year of the target
            $table->integer('target_month'); // Month of the target (1-12)
            $table->decimal('target_amount', 15, 2); // Target income amount
            $table->text('description')->nullable(); // Optional description
            $table->unsignedBigInteger('assigned_by'); // User who assigned the target
            $table->timestamp('assigned_at')->useCurrent(); // When the target was assigned
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');

            // Ensure unique combination of outlet, year, and month
            $table->unique(['outlet_id', 'target_year', 'target_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_targets');
    }
};
