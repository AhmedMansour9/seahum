<?php

namespace App\Console\Commands;

use App\Services\ScheduledOrderStatusService;
use App\Services\StaffNotificationService;
use Illuminate\Console\Command;

class NotifyPostponedOrdersDueTodayCommand extends Command
{
    protected $signature = 'orders:notify-postponed-due-today';

    protected $description = 'Start the governorate orders postponed to today (processing), then push the staff the number of postponed orders due today';

    public function handle(ScheduledOrderStatusService $scheduledOrderStatusService, StaffNotificationService $staffNotificationService): int
    {
        $startedCount = $scheduledOrderStatusService->startOrdersDueToday();
        $dueTodayCount = $staffNotificationService->notifyPostponedOrdersDueToday();
        $this->info($startedCount . ' governorate orders moved to processing; ' . $dueTodayCount . ' postponed orders due today.');

        return self::SUCCESS;
    }
}
