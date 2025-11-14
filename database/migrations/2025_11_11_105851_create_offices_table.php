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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama kantor
            $table->string('code')->unique(); // Kode kantor
            $table->enum('type', ['pusat', 'wilayah', 'area']); // Jenis kantor: pusat, wilayah, area
            $table->unsignedBigInteger('parent_id')->nullable(); // Relasi ke kantor induk
            $table->text('description')->nullable(); // Deskripsi kantor
            $table->string('address')->nullable(); // Alamat kantor
            $table->string('phone')->nullable(); // Nomor telepon kantor
            $table->string('email')->nullable(); // Email kantor
            $table->string('pic_name')->nullable(); // Nama PIC kantor
            $table->string('pic_phone')->nullable(); // Nomor PIC kantor
            $table->boolean('is_active')->default(true); // Status aktif/non-aktif
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('parent_id')->references('id')->on('offices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
