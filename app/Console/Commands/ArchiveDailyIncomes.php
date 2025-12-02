<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\DailyIncome;
use App\Models\DailyIncomeArchive;

class ArchiveDailyIncomes extends Command
{
    protected $signature = 'daily-incomes:archive {--months=3 : Number of months to keep in main table} {--force : Force run without confirmation}';
    protected $description = 'Archive old daily income records to improve performance';

    public function handle()
    {
        $monthsToKeep = $this->option('months');
        $thresholdDate = now()->subMonths($monthsToKeep)->format('Y-m-d');

        $this->info("Archiving daily income records older than {$thresholdDate}...");

        // Hitung jumlah record yang akan diarsipkan
        $countToArchive = DailyIncome::where('date', '<', $thresholdDate)->count();

        if ($countToArchive === 0) {
            $this->info('No records to archive.');
            return;
        }

        $this->info("Found {$countToArchive} records to archive.");

        if ($this->option('force')) {
            $this->info("Force mode enabled. Proceeding with archiving {$countToArchive} records...");
        } else {
            if (!$this->confirm("Do you want to proceed with archiving {$countToArchive} records?")) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $totalArchived = 0;
        $processed = 0;

        // Ambil data yang akan diarsipkan (batch untuk efisiensi memory)
        DailyIncome::where('date', '<', $thresholdDate)
            ->orderBy('id')
            ->chunk(1000, function ($records) use (&$totalArchived, &$processed) {
                $processed += count($records);

                // Masukkan ke tabel arsip
                $archiveData = [];
                foreach ($records as $record) {
                    $archiveData[] = [
                        'date' => $record->date,
                        'moda_id' => $record->moda_id,
                        'colly' => $record->colly,
                        'weight' => $record->weight,
                        'income' => $record->income,
                        'outlet_id' => $record->outlet_id,
                        'user_id' => $record->user_id,
                        'created_at' => $record->created_at,
                        'updated_at' => $record->updated_at,
                    ];
                }

                // Masukkan ke tabel arsip
                if (!empty($archiveData)) {
                    DB::table('daily_income_archives')->insert($archiveData);
                    $totalArchived += count($archiveData);

                    // Hapus dari tabel utama
                    $ids = $records->pluck('id')->toArray();
                    DailyIncome::whereIn('id', $ids)->delete();
                }

                $this->info('Processed batch: ' . count($archiveData) . ' records archived. Total so far: ' . $totalArchived);
            });

        $this->info('Total records processed: ' . $processed . ', archived: ' . $totalArchived);

        $this->info('Archiving completed successfully!');
        $this->info('Remaining records in main table: ' . DailyIncome::count());
    }
}