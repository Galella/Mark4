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
        Schema::table('income_targets', function (Blueprint $table) {
            // Add moda_id column if it doesn't exist
            if (!Schema::hasColumn('income_targets', 'moda_id')) {
                $table->unsignedBigInteger('moda_id')->nullable();
                $table->foreign('moda_id')->references('id')->on('modas')->onDelete('cascade');
            }
        });

        // Update any null moda_id values to a default, but only if the column exists and has null values
        $hasModaId = Schema::hasColumn('income_targets', 'moda_id');
        if ($hasModaId) {
            \DB::statement('UPDATE income_targets SET moda_id = (SELECT id FROM modas LIMIT 1) WHERE moda_id IS NULL OR moda_id = 0');
        }

        // Temporarily drop the foreign key constraint on outlet_id to allow changing the unique constraint
        Schema::table('income_targets', function (Blueprint $table) {
            try {
                $table->dropForeign(['outlet_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }

            try {
                $table->dropUnique(['outlet_id', 'target_year', 'target_month']);
            } catch (\Exception $e) {
                // Unique constraint might not exist
            }

            // Add the new unique constraint with moda_id
            $table->unique(['outlet_id', 'moda_id', 'target_year', 'target_month']);

            // Recreate the foreign key constraint
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('income_targets', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique(['outlet_id', 'moda_id', 'target_year', 'target_month']);

            // Add back the old unique constraint
            $table->unique(['outlet_id', 'target_year', 'target_month']);

            // Drop the moda_id foreign key and column
            $table->dropForeign(['moda_id']);
            $table->dropColumn('moda_id');
        });
    }
};