<?php

namespace App\Console\Commands;

use App\Services\EasyOrdersImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ImportEasyOrdersCommand extends Command
{
    protected $signature = 'easy-orders:import
                            {--from=2026-10-01 : First day to copy (YYYY-MM-DD)}
                            {--dry-run : Only show what would change, without saving}';

    protected $description = 'Copy EasyOrders orders since a date and sync the status of orders already in SEA HUM';

    public function handle(EasyOrdersImportService $easyOrdersImportService): int
    {
        $createdFrom = Carbon::parse($this->option('from'), config('app.timezone'))->startOfDay();
        $isDryRun = (bool)$this->option('dry-run');

        $this->info(($isDryRun ? '[DRY RUN] ' : '') . 'Importing EasyOrders orders since ' . $createdFrom->toDateTimeString());

        $summary = $easyOrdersImportService->importSince(
            createdFrom: $createdFrom,
            isDryRun: $isDryRun,
            onProgress: fn (string $line) => $this->line($line),
        );

        $this->newLine();
        $this->table(['created', 'updated', 'unchanged', 'failed'], [[$summary['created'], $summary['updated'], $summary['unchanged'], $summary['failed']]]);
        if (!empty($summary['unknown_statuses'])) {
            $this->warn('Statuses with no SEA HUM match (orders kept as pending / unchanged):');
            foreach ($summary['unknown_statuses'] as $status => $count) {
                $this->line('  ' . ($status ?: '(empty)') . ': ' . $count);
            }
        }

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
