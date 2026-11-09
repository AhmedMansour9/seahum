<?php

namespace App\Services;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Events\OrderStatusEvent;
use App\Models\Order;
use Illuminate\Support\Carbon;

class DeliveryManAssignmentService
{
    public function __construct(private readonly OrderRepositoryInterface $orderRepo)
    {
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
