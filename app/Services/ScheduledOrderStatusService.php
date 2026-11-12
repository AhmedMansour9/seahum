<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class ScheduledOrderStatusService
{
    public function __construct(
        private readonly OrderStatusUpdateService $orderStatusUpdateService,
        private readonly EasyOrdersWebhookService $easyOrdersWebhookService,
    ) {
    }

    /**
     * Governorate orders postponed to today that are still waiting (pending or confirmed).
     *
     * @return Collection<int, Order>
     */
    public function getOrdersToStartToday(): Collection
    {
        return Order::governorate()
            ->where('order_type', 'default_type')
            ->whereDate('scheduled_for', today())
            ->whereIn('order_status', ['pending', 'confirmed'])
            ->get();
    }

    /**
     * Moves every order returned by getOrdersToStartToday() to processing and returns how many were moved.
     */
    public function startOrdersDueToday(): int
    {
        $orders = $this->getOrdersToStartToday();

        foreach ($orders as $order) {
            if ($order['order_source'] === OrderSource::EasyOrders) {
                $this->easyOrdersWebhookService->updateAdminOrderStatus(orderId: $order['id'], status: 'processing', adminId: 0);
                continue;
            }
            $this->orderStatusUpdateService->updateStatus(orderId: $order['id'], status: 'processing');
        }

        return $orders->count();
    }
}
