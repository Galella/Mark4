<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek apakah kolom moda_id sudah ada
        $hasModaId = Schema::hasColumn('daily_incomes', 'moda_id');
        $hasModaString = Schema::hasColumn('daily_incomes', 'moda');
        
        if ($hasModaString && !$hasModaId) {
            // Kolom moda string ada tapi moda_id belum ada - konversi
            Schema::table('daily_incomes', function (Blueprint $table) {
                $table->unsignedBigInteger('moda_id')->nullable()->after('date');
            });
            
            // Update data dari moda string ke moda_id
            $incomes = DB::table('daily_incomes')->select('id', 'moda')->get();
            foreach ($incomes as $income) {
                if (!empty($income->moda)) {
                    $moda = DB::table('modas')->where('name', $income->moda)->first();
                    if ($moda) {
                        DB::table('daily_incomes')
                            ->where('id', $income->id)
                            ->update(['moda_id' => $moda->id]);
                    }
                }
            }
            
            // Hapus foreign key constraint untuk sementara jika ada
            try {
                Schema::table('daily_incomes', function (Blueprint $table) {
                    $table->dropForeign(['moda_id']);
                });
            } catch (\Exception $e) {
                // Jika foreign key tidak ada, lewati
            }
            
            // Tambahkan foreign key constraint
            Schema::table('daily_incomes', function (Blueprint $table) {
                $table->foreign('moda_id')->references('id')->on('modas')->onDelete('cascade');
            });
            
            // Hapus kolom moda string
            Schema::table('daily_incomes', function (Blueprint $table) {
                $table->dropColumn('moda');
            });
        } elseif ($hasModaString && $hasModaId) {
            // Jika kedua kolom ada, hapus kolom string
            Schema::table('daily_incomes', function (Blueprint $table) {
                $table->dropColumn('moda');
            });
        } elseif (!$hasModaString && !$hasModaId) {
            // Jika tidak ada kolom moda sama sekali, tambahkan dengan foreign key
            Schema::table('daily_incomes', function (Blueprint $table) {
                $table->unsignedBigInteger('moda_id');
                $table->foreign('moda_id')->references('id')->on('modas')->onDelete('cascade');
            });
        }
        // Jika hanya moda_id yang ada, tidak perlu melakukan apa-apa
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tambahkan kembali kolom string moda
        $hasModaString = Schema::hasColumn('daily_incomes', 'moda');
        if (!$hasModaString) {
            Schema::table('daily_incomes', function (Blueprint $table) {
                $table->string('moda')->nullable()->after('date');
            });
        }
        
        // Update kembali datanya ke string
        $incomes = DB::table('daily_incomes')->select('id', 'moda_id')->get();
        foreach ($incomes as $income) {
            if (!empty($income->moda_id)) {
                $moda = DB::table('modas')->where('id', $income->moda_id)->first();
                if ($moda) {
                    DB::table('daily_incomes')
                        ->where('id', $income->id)
                        ->update(['moda' => $moda->name]);
                }
            }
        }
        
        // Hapus foreign key dan kolom moda_id
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->dropForeign(['moda_id']);
            $table->dropColumn('moda_id');
        });
    }
};