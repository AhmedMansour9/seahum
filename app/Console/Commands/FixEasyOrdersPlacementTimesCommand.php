<?php

namespace App\Console\Commands;

use App\Enums\OrderSource;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * One-off repair: EasyOrders orders stored while placement times were written in the config timezone
 * (Asia/Dhaka) instead of the store timezone. The order lines were inserted at the same moment as the
 * order, in the store timezone, so they tell which orders were stored during that period.
 * Run it once: running it again would shift the same orders twice.
 */
class FixEasyOrdersPlacementTimesCommand extends Command
{
    private const WRONG_TIMEZONE = 'Asia/Dhaka';

    protected $signature = 'easy-orders:fix-placement-times
                            {--stored-from= : Start of the period the affected orders were stored in (store timezone)}
                            {--stored-to= : End of that period (store timezone)}
                            {--dry-run : Only list the changes, without saving}';

    protected $description = 'Convert EasyOrders placement times that were saved in Asia/Dhaka to the store timezone (run once)';

    public function handle(): int
    {
        $storedFrom = $this->option('stored-from');
        $storedTo = $this->option('stored-to');
        if (!$storedFrom || !$storedTo) {
            $this->error('Both --stored-from and --stored-to are required.');

            return self::FAILURE;
        }

        $orders = DB::table('orders')
            ->where('order_source', OrderSource::EasyOrders->value)
            ->whereExists(fn ($query) => $query->select(DB::raw(1))
                ->from('order_details')
                ->whereColumn('order_details.order_id', 'orders.id')
                ->whereBetween('order_details.created_at', [$storedFrom, $storedTo]))
            ->get(['id', 'created_at']);

        $storeTimezone = date_default_timezone_get();
        foreach ($orders as $order) {
            $fixedTime = Carbon::parse($order->created_at, self::WRONG_TIMEZONE)->setTimezone($storeTimezone)->format('Y-m-d H:i:s');
            $this->line("#{$order->id}: {$order->created_at} → {$fixedTime}");
            if (!$this->option('dry-run')) {
                DB::table('orders')->where('id', $order->id)->update(['created_at' => $fixedTime]);
            }
        }

        $this->info(($this->option('dry-run') ? '[DRY RUN] ' : '') . $orders->count() . ' orders ' . ($this->option('dry-run') ? 'would be ' : '') . 'fixed (store timezone ' . $storeTimezone . ').');

        return self::SUCCESS;
    }
}
