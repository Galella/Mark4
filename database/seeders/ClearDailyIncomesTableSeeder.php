<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyIncome;

class ClearDailyIncomesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Clearing all daily income records...');
        
        // Hapus semua data dari tabel daily_incomes
        $count = DailyIncome::count();
        DailyIncome::truncate();
        
        $this->command->info("Successfully deleted {$count} daily income records.");
        $this->command->info('Daily incomes table has been cleared and reset.');
    }
}