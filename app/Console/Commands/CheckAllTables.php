<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAllTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-all-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all tables in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Force SQLite connection
            config(['database.default' => 'sqlite']);
            config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

            // Use explicit SQLite connection
            $connection = DB::connection('sqlite');

            // Get all tables in the database
            $tables = $connection->select('SELECT name FROM sqlite_master WHERE type = "table"');

            $this->info('📋 Available tables in database:');
            foreach ($tables as $table) {
                $tableName = $table->name;
                $count = $connection->table($tableName)->count();
                $this->line("- {$tableName} ({$count} records)");

                // Show some details for important tables
                if (in_array($tableName, ['users', 'modas', 'income_targets', 'daily_incomes', 'sessions'])) {
                    $this->info("  └─ {$tableName} ✓ (critical table for system)");
                }
            }

            // Check specifically for critical tables
            $criticalTables = [
                'users' => 'User management',
                'modas' => 'Transportation modes for dashboard',
                'income_targets' => 'Target income data',
                'daily_incomes' => 'Daily income records',
                'sessions' => 'User sessions',
                'outlets' => 'Outlets management',
                'offices' => 'Office management'
            ];

            $this->newLine();
            $this->info('🔍 Critical tables status:');

            $missing = [];
            foreach ($criticalTables as $table => $description) {
                $exists = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$table]);
                if ($exists) {
                    $count = $connection->table($table)->count();
                    $this->info("  ✅ {$table} - {$description} ({$count} records)");
                } else {
                    $this->error("  ❌ {$table} - {$description} (MISSING)");
                    $missing[] = $table;
                }
            }

            if (empty($missing)) {
                $this->newLine();
                $this->info('🎉 All critical tables are present!');
                $this->info('🚀 System should now be fully functional!');
            } else {
                $this->newLine();
                $this->warn('⚠️  Missing tables: ' . implode(', ', $missing));
            }

        } catch (\Exception $e) {
            $this->error('❌ Error checking tables: ' . $e->getMessage());
        }
    }
}
