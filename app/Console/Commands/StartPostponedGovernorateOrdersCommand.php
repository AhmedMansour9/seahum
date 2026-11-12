<?php

namespace App\Console\Commands;

use App\Services\ScheduledOrderStatusService;
use Illuminate\Console\Command;

class StartPostponedGovernorateOrdersCommand extends Command
{
    protected $signature = 'orders:start-postponed-governorate';

    protected $description = 'Move the governorate orders postponed to today (pending or confirmed) to processing';

    public function handle(ScheduledOrderStatusService $scheduledOrderStatusService): int
    {
        $this->info($scheduledOrderStatusService->startOrdersDueToday() . ' governorate orders moved to processing.');

        return self::SUCCESS;
    }
}
