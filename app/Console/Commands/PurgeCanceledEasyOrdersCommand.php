<?php

namespace App\Console\Commands;

use App\Enums\OrderSource;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeCanceledEasyOrdersCommand extends Command
{
    private const ORDER_RELATED_TABLES = [
        'order_details',
        'order_details_rewards',
        'order_status_histories',
        'order_edit_histories',
        'order_transactions',
        'order_expected_delivery_histories',
        'delivery_histories',
    ];

    protected $signature = 'easy-orders:purge-canceled
                            {--dry-run : Only count the orders, without deleting}
                            {--force : Delete without asking for confirmation}';

    protected $description = 'Permanently delete canceled EasyOrders orders (SEA HUM app orders are never touched)';

    public function handle(): int
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

        DB::transaction(function () use ($orderIds): void {
            foreach ($orderIds->chunk(500) as $chunk) {
                foreach (self::ORDER_RELATED_TABLES as $table) {
                    DB::table($table)->whereIn('order_id', $chunk)->delete();
                }
                Order::whereIn('id', $chunk)->delete();
            }
        });

        $this->info('Deleted ' . $orderIds->count() . ' orders.');

        return self::SUCCESS;
    }
}
