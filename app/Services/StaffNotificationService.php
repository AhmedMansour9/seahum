<?php

namespace App\Services;

use App\Enums\DeliveryManAppRole;
use App\Enums\OrderSource;
use App\Models\DeliveryMan;
use App\Models\Order;
use App\Traits\PushNotificationTrait;
use App\Utils\OrderManager;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Push notifications for the admin and customer service accounts of the delivery man app.
 */
class StaffNotificationService
{
    use PushNotificationTrait;

    public const NEW_ORDER_TYPE = 'staff_new_order';

    /**
     * Notifies every active staff account about a newly placed order.
     * A failed push must never fail the order it reports, so errors are only logged.
     */
    public function notifyNewOrder(Order $order): void
    {
        try {
            if ($order['order_type'] && $order['order_type'] !== 'default_type') {
                return;
            }

            $data = $this->getNewOrderNotificationData(order: $order);
            foreach ($this->getStaffAccounts() as $staffAccount) {
                $this->sendPushNotificationToDeliveryManDevices(deliveryMan: $staffAccount, data: $data);
            }
        } catch (Throwable $exception) {
            Log::warning('Staff new order notification failed', ['order_id' => $order['id'], 'error' => $exception->getMessage()]);
        }
    }

    /**
     * @return array{title: string, description: string, image: string, order_id: int, type: string}
     */
    public function getNewOrderNotificationData(Order $order): array
    {
        $order->loadMissing('details');
        $shippingAddress = $order['shipping_address_data'];
        if (is_string($shippingAddress)) {
            $shippingAddress = json_decode($shippingAddress);
        }

        $sourceLabel = $order['order_source'] === OrderSource::EasyOrders ? 'إيزي أوردرز' : 'SEA HUM';
        $totalAmount = OrderManager::getOrderTotalPriceSummary(order: $order)['totalAmount'];
        $total = number_format(usdToDefaultCurrency(amount: $totalAmount), 2) . ' ' . config('order_whatsapp.currency_label');
        $customerName = $shippingAddress->contact_person_name ?? null;

        return [
            'title' => 'طلب جديد #' . $order['id'],
            'description' => implode(' • ', array_filter([$sourceLabel, $total, $customerName])),
            'image' => '',
            'order_id' => $order['id'],
            'type' => self::NEW_ORDER_TYPE,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, DeliveryMan>
     */
    private function getStaffAccounts(): \Illuminate\Support\Collection
    {
        return DeliveryMan::where(['seller_id' => 0, 'is_active' => 1])
            ->whereIn('app_role', [DeliveryManAppRole::Admin->value, DeliveryManAppRole::CustomerService->value])
            ->get();
    }
}
