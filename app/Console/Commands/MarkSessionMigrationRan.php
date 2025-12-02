<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkSessionMigrationRan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-session-migration-ran';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark create_sessions_table migration as ran';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Check if entry already exists
            $migrationExists = DB::table('migrations')
                ->where('migration', '2025_11_29_002411_create_sessions_table')
                ->exists();

            if (!$migrationExists) {
                DB::table('migrations')->insert([
                    'migration' => '2025_11_29_002411_create_sessions_table',
                    'batch' => 1
                ]);

                $this->info('Migration marked as run successfully!');
            } else {
                $this->info('Migration is already marked as run!');
            }
        } catch (\Exception $e) {
            $this->error('Error marking migration as run: ' . $e->getMessage());
        }
    }
}
