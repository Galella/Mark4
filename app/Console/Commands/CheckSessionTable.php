<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckSessionTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-session-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if sessions table exists and has records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Check if table exists
            if (Schema::hasTable('sessions')) {
                $this->info('✅ Sessions table exists!');

                // Count records
                $count = DB::table('sessions')->count();
                $this->info("📊 Sessions table has {$count} records");

                // Show some session info if any exist
                if ($count > 0) {
                    $sessions = DB::table('sessions')->limit(5)->get();
                    foreach ($sessions as $session) {
                        $this->line("- Session ID: {$session->id}, Last Activity: {$session->last_activity}");
                    }
                }
            } else {
                $this->error('❌ Sessions table does NOT exist!');

                // List all tables to see what exists
                $tables = DB::select('SELECT name FROM sqlite_master WHERE type = "table"');
                $this->info('📋 Available tables in database:');
                foreach ($tables as $table) {
                    $this->line("- {$table->name}");
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Error checking sessions table: ' . $e->getMessage());
        }
    }
}
