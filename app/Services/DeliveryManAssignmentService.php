<?php

namespace App\Services;

use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Events\OrderStatusEvent;
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

    /**
     * Assign a delivery man chosen by an admin to an order and notify the delivery man (and the vendor for vendor orders).
     */
    public function assignDeliveryMan(string|int $orderId, string|int $deliveryManId): Order
    {
        $order = $this->orderRepo->getFirstWhere(params: ['id' => $orderId]);
        $data = [
            'delivery_man_id' => $deliveryManId,
            'delivery_type' => 'self_delivery',
            'delivery_service_name' => null,
            'third_party_delivery_tracking_id' => null,
        ];

        if ($order['delivery_man_id'] != $deliveryManId) {
            $data['deliveryman_assigned_at'] = Carbon::now();
        }

        $this->orderRepo->update(id: $orderId, data: $data);

        $order = $this->orderRepo->getFirstWhere(params: ['id' => $orderId], relations: ['seller.shop', 'deliveryMan']);

        event(new OrderStatusEvent(key: 'new_order_assigned_message', type: 'delivery_man', order: $order));

        if ($order['seller_is'] == 'seller') {
            event(new OrderStatusEvent(key: 'delivery_man_assign_by_admin_message', type: 'seller', order: $order));
        }

        return $order;
    }
}
