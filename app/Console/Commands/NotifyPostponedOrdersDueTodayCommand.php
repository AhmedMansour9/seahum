<?php

namespace App\Console\Commands;

use App\Services\StaffNotificationService;
use Illuminate\Console\Command;

class NotifyPostponedOrdersDueTodayCommand extends Command
{
    protected $signature = 'orders:notify-postponed-due-today';

    protected $description = 'Push the admin and customer service accounts the number of postponed orders due today';

    public function handle(StaffNotificationService $staffNotificationService): int
    {
        $this->info($staffNotificationService->notifyPostponedOrdersDueToday() . ' postponed orders due today.');

        return self::SUCCESS;
    }
}
