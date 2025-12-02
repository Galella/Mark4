<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Outlet;

class ClearOutletsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Clearing all outlet records...');

        // Hapus semua data dari tabel outlets
        $count = Outlet::count();
        Outlet::truncate();

        $this->command->info("Successfully deleted {$count} outlet records.");
        $this->command->info('Outlets table has been cleared and reset.');
    }
}