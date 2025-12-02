<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMissingTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-missing-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing tables required by the dashboard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Force SQLite connection
            $this->callSilent('config:clear');

            // Override database configuration temporarily
            config(['database.default' => 'sqlite']);
            config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

            // This will use the default connection which is now SQLite
            $connection = DB::connection('sqlite');

            // Create modas table
            if (!$connection->getSchemaBuilder()->hasTable('modas')) {
                $connection->statement('CREATE TABLE modas (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )');

                $this->info('✅ Modas table created');
            } else {
                $this->info('ℹ️  Modas table already exists');
            }

            // Create income_targets table
            if (!$connection->getSchemaBuilder()->hasTable('income_targets')) {
                $connection->statement('CREATE TABLE income_targets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    outlet_id BIGINT NOT NULL,
                    moda_id BIGINT NOT NULL,
                    target_year INTEGER NOT NULL,
                    target_month INTEGER NOT NULL,
                    target_amount DECIMAL(15, 2) NOT NULL,
                    description TEXT,
                    assigned_by BIGINT,
                    assigned_at TIMESTAMP,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP,
                    UNIQUE(outlet_id, moda_id, target_year, target_month)
                )');

                // Create indexes
                $connection->statement('CREATE INDEX idx_income_targets_outlet ON income_targets(outlet_id)');
                $connection->statement('CREATE INDEX idx_income_targets_moda ON income_targets(moda_id)');
                $connection->statement('CREATE INDEX idx_income_targets_year_month ON income_targets(target_year, target_month)');

                $this->info('✅ Income targets table created');
            } else {
                $this->info('ℹ️  Income targets table already exists');
            }

            // Create daily_incomes table
            if (!$connection->getSchemaBuilder()->hasTable('daily_incomes')) {
                $connection->statement('CREATE TABLE daily_incomes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    date DATE NOT NULL,
                    moda_id BIGINT NOT NULL,
                    colly INTEGER NOT NULL,
                    weight DECIMAL(10, 2) NOT NULL,
                    income DECIMAL(15, 2) NOT NULL,
                    outlet_id BIGINT NOT NULL,
                    user_id BIGINT NOT NULL,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )');

                // Create indexes
                $connection->statement('CREATE INDEX idx_daily_incomes_date ON daily_incomes(date)');
                $connection->statement('CREATE INDEX idx_daily_incomes_outlet ON daily_incomes(outlet_id)');
                $connection->statement('CREATE INDEX idx_daily_incomes_moda ON daily_incomes(moda_id)');

                $this->info('✅ Daily incomes table created');
            } else {
                $this->info('ℹ️  Daily incomes table already exists');
            }

            // Mark these migrations as run in the migrations table using SQLite connection
            $migrations = [
                '2025_11_11_190457_create_modas_table',
                '2025_11_12_095031_create_income_targets_table',
                '2025_11_11_184647_create_daily_incomes_table'
            ];

            foreach ($migrations as $migration) {
                $exists = $connection->table('migrations')
                    ->where('migration', $migration)
                    ->exists();

                if (!$exists) {
                    $connection->table('migrations')->insert([
                        'migration' => $migration,
                        'batch' => 2 // Use batch 2 to indicate these are manually created
                    ]);
                    $this->info("✅ Migration {$migration} marked as run");
                } else {
                    $this->info("ℹ️  Migration {$migration} already marked as run");
                }
            }

            $this->info('🎉 All required tables have been created successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Error creating tables: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
