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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID pengguna yang melakukan aktivitas
            $table->string('action'); // Aksi yang dilakukan (login, create, update, delete, export, dll)
            $table->string('module')->nullable(); // Modul yang diakses
            $table->text('description')->nullable(); // Deskripsi aktivitas
            $table->json('old_values')->nullable(); // Nilai lama sebelum perubahan (untuk update)
            $table->json('new_values')->nullable(); // Nilai baru setelah perubahan (untuk update)
            $table->string('ip_address')->nullable(); // Alamat IP pengguna
            $table->text('user_agent')->nullable(); // User agent pengguna
            $table->timestamp('logged_at')->useCurrent(); // Waktu aktivitas dicatat
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
