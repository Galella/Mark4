<?php

namespace App\Console\Commands;

use App\Models\DailyIncome;
use App\Models\DailyIncomeSummary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDailyIncomeSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily-income:generate-summaries {--date=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily income summaries for faster reporting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily income summaries generation...');

        // Get the date parameter or use yesterday's date as default
        $date = $this->option('date');
        if (!$date) {
            $date = now()->subDay()->format('Y-m-d');
        }

        $this->info("Processing summaries for date: {$date}");

        // Check if summaries already exist for this date
        $existingSummaries = DailyIncomeSummary::where('date', $date)->count();

        if ($existingSummaries > 0 && !$this->option('force')) {
            $this->error("Summaries already exist for {$date}. Use --force to overwrite.");
            return 1;
        }

        // Delete existing summaries for this date if force option is used
        if ($existingSummaries > 0 && $this->option('force')) {
            DailyIncomeSummary::where('date', $date)->delete();
            $this->info("Existing summaries for {$date} have been deleted.");
        }

        // Get raw data from daily_incomes for the specified date
        $summaryData = DB::table('daily_incomes')
            ->select([
                'date',
                'outlet_id',
                'moda_id',
                'user_id',
                DB::raw('SUM(colly) as total_colly'),
                DB::raw('SUM(weight) as total_weight'),
                DB::raw('SUM(income) as total_income'),
                DB::raw('COUNT(*) as record_count')
            ])
            ->where('date', $date)
            ->groupBy(['date', 'outlet_id', 'moda_id', 'user_id'])
            ->get();

        $count = 0;
        foreach ($summaryData as $data) {
            DailyIncomeSummary::create([
                'date' => $data->date,
                'outlet_id' => $data->outlet_id,
                'moda_id' => $data->moda_id,
                'user_id' => $data->user_id,
                'total_colly' => $data->total_colly,
                'total_weight' => $data->total_weight,
                'total_income' => $data->total_income,
                'record_count' => $data->record_count,
            ]);
            $count++;
        }

        $this->info("Successfully generated {$count} summary records for {$date}");
        $this->info('Daily income summaries generation completed!');
    }
}