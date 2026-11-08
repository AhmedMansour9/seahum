<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderPurgeService;
use Illuminate\Console\Command;

class PurgeOrdersCommand extends Command
{
    protected $signature = 'orders:purge
                            {ids* : Order ids to delete permanently}
                            {--dry-run : Only list the orders, without deleting}
                            {--force : Delete without asking for confirmation}';

    protected $description = 'Permanently delete the given orders with their details and histories';

    public function handle(OrderPurgeService $orderPurgeService): int
    {
        $orders = Order::whereIn('id', $this->argument('ids'))->get(['id', 'order_source', 'order_status']);
        if ($orders->isEmpty()) {
            $this->warn('No matching orders found.');

            return self::FAILURE;
        }

        $this->table(['id', 'source', 'status'], $orders->map(fn (Order $order) => [$order['id'], $order['order_source']?->value ?? 'app', $order['order_status']]));
        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Delete them permanently? This cannot be undone.')) {
            return self::FAILURE;
        }

        $orderPurgeService->purge(orderIds: $orders->pluck('id'));
        $this->info('Deleted ' . $orders->count() . ' orders.');

        return self::SUCCESS;
    }
}
