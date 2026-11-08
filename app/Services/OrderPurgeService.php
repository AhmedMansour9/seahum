<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Permanently deletes orders together with the rows that belong to them.
 */
class OrderPurgeService
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

    /**
     * @param Collection<int, int|string> $orderIds
     */
    public function purge(Collection $orderIds): void
    {
        DB::transaction(function () use ($orderIds): void {
            foreach ($orderIds->chunk(500) as $chunk) {
                foreach (self::ORDER_RELATED_TABLES as $table) {
                    DB::table($table)->whereIn('order_id', $chunk)->delete();
                }
                Order::whereIn('id', $chunk)->delete();
            }
        });
    }
}
