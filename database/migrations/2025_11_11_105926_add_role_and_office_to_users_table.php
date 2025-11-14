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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin_wilayah', 'admin_area', 'admin_outlet'])->default('admin_outlet');
            $table->unsignedBigInteger('office_id')->nullable(); // Relasi ke kantor (wilayah/area) untuk admin wilayah/area
            $table->unsignedBigInteger('outlet_id')->nullable(); // Relasi ke outlet untuk admin outlet
            
            // Foreign key constraints
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('set null');
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropForeign(['outlet_id']);
            $table->dropColumn(['role', 'office_id', 'outlet_id']);
        });
    }
};
