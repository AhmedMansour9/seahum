<?php

namespace App\Console\Commands;

use App\Enums\OrderSource;
use App\Models\Order;
use App\Services\OrderPurgeService;
use Illuminate\Console\Command;

class PurgeCanceledEasyOrdersCommand extends Command
{
    protected $signature = 'easy-orders:purge-canceled
                            {--dry-run : Only count the orders, without deleting}
                            {--force : Delete without asking for confirmation}';

    protected $description = 'Permanently delete canceled EasyOrders orders (SEA HUM app orders are never touched)';

    public function handle(OrderPurgeService $orderPurgeService): int
    {
        $orderIds = Order::where([
            'order_source' => OrderSource::EasyOrders->value,
            'order_status' => 'canceled',
        ])->pluck('id');

        $this->info($orderIds->count() . ' canceled EasyOrders orders found.');
        if ($orderIds->isEmpty() || $this->option('dry-run')) {
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Delete them permanently? This cannot be undone.')) {
            return self::FAILURE;
        }

        $orderPurgeService->purge(orderIds: $orderIds);
        $this->info('Deleted ' . $orderIds->count() . ' orders.');

        return self::SUCCESS;
    }
}
