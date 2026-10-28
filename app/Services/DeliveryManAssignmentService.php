<?php

namespace App\Services;

use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\DeliveryMan;
use App\Models\Order;
use Illuminate\Support\Carbon;

class DeliveryManAssignmentService
{
    public function __construct(
        private readonly DeliveryManRepositoryInterface $deliveryManRepo,
        private readonly OrderRepositoryInterface       $orderRepo,
        private readonly OrderService                   $orderService,
    )
    {
    }

    /**
     * Assign a newly placed order to the active delivery man of its owner with the fewest open orders.
     * Digital-only orders, already assigned orders and owners without active delivery men are left untouched.
     */
    public function assignFirstAvailableDeliveryMan(Order $order): ?DeliveryMan
    {
        if ($order['delivery_man_id'] || $this->orderService->getCheckIsOrderOnlyDigital(order: $order)) {
            return null;
        }

        $deliveryMan = $this->deliveryManRepo->getActiveWithFewestOpenOrders(
            sellerId: $this->orderService->getDeliveryManOwnerId(order: $order)
        );
        if (!$deliveryMan) {
            return null;
        }

        $this->orderRepo->update(id: $order['id'], data: [
            'delivery_man_id' => $deliveryMan['id'],
            'delivery_type' => 'self_delivery',
            'delivery_service_name' => null,
            'third_party_delivery_tracking_id' => null,
            'deliveryman_assigned_at' => Carbon::now(),
        ]);

        return $deliveryMan;
    }
}
