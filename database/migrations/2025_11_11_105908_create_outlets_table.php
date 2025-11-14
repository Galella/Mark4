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
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama outlet
            $table->string('code')->unique(); // Kode outlet
            $table->unsignedBigInteger('office_id'); // Relasi ke kantor area
            $table->unsignedBigInteger('outlet_type_id'); // Relasi ke tipe outlet
            $table->text('description')->nullable(); // Deskripsi outlet
            $table->string('address')->nullable(); // Alamat outlet
            $table->string('phone')->nullable(); // Nomor telepon outlet
            $table->string('email')->nullable(); // Email outlet
            $table->string('pic_name')->nullable(); // Nama PIC outlet
            $table->string('pic_phone')->nullable(); // Nomor PIC outlet
            $table->boolean('is_active')->default(true); // Status aktif/non-aktif
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
            $table->foreign('outlet_type_id')->references('id')->on('outlet_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
