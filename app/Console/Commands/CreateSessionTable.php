<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSessionTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-session-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sessions table directly in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Check if table exists first
            if (!Schema::hasTable('sessions')) {
                DB::statement('CREATE TABLE sessions (
                    id VARCHAR(255) PRIMARY KEY,
                    user_id BIGINT UNSIGNED,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    payload TEXT NOT NULL,
                    last_activity INT NOT NULL
                )');

                // Also create index for performance
                DB::statement('CREATE INDEX idx_sessions_last_activity ON sessions(last_activity)');
                DB::statement('CREATE INDEX idx_sessions_user_id ON sessions(user_id)');

                $this->info('Sessions table created successfully!');
            } else {
                $this->info('Sessions table already exists!');
            }
        } catch (\Exception $e) {
            $this->error('Error creating sessions table: ' . $e->getMessage());
        }
    }
}
