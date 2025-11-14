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
        Schema::create('outlet_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama tipe outlet (Cabang, Agen, Gerai, dll)
            $table->text('description')->nullable(); // Deskripsi tipe outlet
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlet_types');
    }
};
