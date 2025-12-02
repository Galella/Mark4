<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IncomeTarget;

class ClearIncomeTargetsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Clearing all income target records...');

        // Hapus semua data dari tabel income_targets
        $count = IncomeTarget::count();
        IncomeTarget::truncate();

        $this->command->info("Successfully deleted {$count} income target records.");
        $this->command->info('Income targets table has been cleared and reset.');
    }
}