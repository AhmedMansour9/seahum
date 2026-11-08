<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Utils\OrderManager;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EasyOrdersWebhookService
{
    private const IN_HOUSE_SELLER_ID = 1;
    private const IN_HOUSE_SELLER_IS = 'admin';

    /**
     * Orders placed longer ago than this (re-sent or imported old orders) do not push a new order notification.
     */
    private const NEW_ORDER_NOTIFICATION_WINDOW_MINUTES = 60;

    public function __construct(private readonly StaffNotificationService $staffNotificationService)
    {
    }

    public function isValidSecret(?string $secret): bool
    {
        $configuredSecret = (string)config('easy_orders.webhook_secret');

        return $configuredSecret !== '' && is_string($secret) && hash_equals($configuredSecret, $secret);
    }

    /**
     * Stores an EasyOrders order once. A repeated delivery of the same order returns the stored one,
     * taking the EasyOrders status when the payload is newer than the last change made in SEA HUM
     * (re-sent orders), so a late retry of the original delivery never reverts a newer status.
     *
     * @param array{id: string, status?: string, payment_method?: string, cost?: float, shipping_cost?: float, total_cost: float, full_name: string, phone: string, government?: string, address?: string, created_at?: string, cart_items: array<int, array<string, mixed>>} $payload
     */
    public function storeOrder(array $payload, bool $notifyStaff = true): Order
    {
        $existingOrder = $this->findOrder(externalOrderId: (string)$payload['id']);
        if ($existingOrder) {
            return $this->syncResentOrderStatus(order: $existingOrder, payload: $payload);
        }

        try {
            $order = DB::transaction(fn () => $this->createOrder(payload: $payload));
            if ($notifyStaff && $order['created_at']->greaterThan(now()->subMinutes(self::NEW_ORDER_NOTIFICATION_WINDOW_MINUTES))) {
                $this->staffNotificationService->notifyNewOrder(order: $order);
            }

            return $order;
        } catch (UniqueConstraintViolationException) {
            return $this->findOrder(externalOrderId: (string)$payload['id']);
        }
    }

    /**
     * @param array{order_id: string, new_status: string, payment_ref_id?: string|null} $payload
     */
    public function updateOrderStatus(array $payload): ?Order
    {
        return DB::transaction(function () use ($payload): ?Order {
            $order = Order::where([
                'order_source' => OrderSource::EasyOrders->value,
                'external_order_id' => (string)$payload['order_id'],
            ])->lockForUpdate()->first();
            if (!$order) {
                return null;
            }

            $newStatus = strtolower((string)$payload['new_status']);
            $orderData = [];
            $orderDetailData = [];

            if (in_array($newStatus, config('easy_orders.paid_statuses'), true)) {
                $orderData['payment_status'] = 'paid';
                $orderData['transaction_ref'] = $payload['payment_ref_id'] ?? $order['transaction_ref'];
                $orderDetailData['payment_status'] = 'paid';
            }

            $mappedOrderStatus = $this->getMappedOrderStatus(easyOrdersStatus: $newStatus);
            $orderStatusChanged = $mappedOrderStatus !== null && $mappedOrderStatus !== $order['order_status'];

            if ($orderStatusChanged) {
                $this->applyStatusUpdate(
                    order: $order,
                    status: $mappedOrderStatus,
                    userType: 'easy_orders',
                    userId: 0,
                );
            }

            if (!empty($orderData)) {
                $order->update($orderData);
                if (!empty($orderDetailData)) {
                    OrderDetail::where('order_id', $order['id'])
                        ->update($orderDetailData);
                }
            }

            return $order->refresh();
        });
    }

    public function updateAdminOrderStatus(string|int $orderId, string $status, string|int $adminId, string $userType = 'admin'): ?Order
    {
        return DB::transaction(function () use ($orderId, $status, $adminId, $userType): ?Order {
            $order = Order::where([
                'id' => $orderId,
                'order_source' => OrderSource::EasyOrders->value,
            ])->lockForUpdate()->first();

            if (!$order) {
                return null;
            }

            if ($status !== $order['order_status']) {
                $this->applyStatusUpdate(
                    order: $order,
                    status: $status,
                    userType: $userType,
                    userId: $adminId,
                );
            }

            return $order->refresh();
        });
    }

    public function updateAdminOrderPaymentStatus(string|int $orderId, string $paymentStatus): ?Order
    {
        return DB::transaction(function () use ($orderId, $paymentStatus): ?Order {
            $order = Order::where([
                'id' => $orderId,
                'order_source' => OrderSource::EasyOrders->value,
            ])->lockForUpdate()->first();

            if (!$order) {
                return null;
            }

            $order->update(['payment_status' => $paymentStatus]);
            OrderDetail::where('order_id', $order['id'])->update(['payment_status' => $paymentStatus]);

            return $order->refresh();
        });
    }

    private function applyStatusUpdate(Order $order, string $status, string $userType, string|int $userId): void
    {
        $orderData = ['order_status' => $status];
        $orderDetailData = ['delivery_status' => $status];

        if ($status === 'delivered') {
            $orderData['payment_status'] = 'paid';
            $orderData['is_pause'] = 0;
            $orderDetailData['payment_status'] = 'paid';
        }

        $order->update($orderData);
        OrderDetail::where('order_id', $order['id'])->update($orderDetailData);
        OrderStatusHistory::create([
            'order_id' => $order['id'],
            'user_id' => $userId,
            'user_type' => $userType,
            'status' => $status,
        ]);
    }

    private function syncResentOrderStatus(Order $order, array $payload): Order
    {
        $status = strtolower((string)($payload['status'] ?? ''));
        if ($status === '' || empty($payload['updated_at']) || !Carbon::parse($payload['updated_at'])->greaterThan($order['updated_at'])) {
            return $order;
        }

        return $this->updateOrderStatus(payload: ['order_id' => (string)$payload['id'], 'new_status' => $status]) ?? $order;
    }

    public function findOrder(string $externalOrderId): ?Order
    {
        return Order::where([
            'order_source' => OrderSource::EasyOrders->value,
            'external_order_id' => $externalOrderId,
        ])->first();
    }

    private function createOrder(array $payload): Order
    {
        $orderStatus = $this->getMappedOrderStatus(easyOrdersStatus: (string)($payload['status'] ?? '')) ?? 'pending';
        $paymentStatus = in_array(strtolower((string)($payload['status'] ?? '')), config('easy_orders.paid_statuses'), true) ? 'paid' : 'unpaid';
        $paymentMethod = (string)($payload['payment_method'] ?? '');
        $shippingCost = (float)($payload['shipping_cost'] ?? 0);
        $orderCost = isset($payload['cost'])
            ? (float)$payload['cost']
            : max(0, (float)$payload['total_cost'] - $shippingCost);

        $order = new Order([
            'id' => OrderManager::generateNewOrderID(),
            'order_source' => OrderSource::EasyOrders,
            'external_order_id' => (string)$payload['id'],
            'order_type' => 'default_type',
            'customer_id' => null,
            'is_guest' => 1,
            'customer_type' => 'customer',
            'seller_id' => self::IN_HOUSE_SELLER_ID,
            'seller_is' => self::IN_HOUSE_SELLER_IS,
            'order_status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'payment_method' => config('easy_orders.payment_method_map.' . $paymentMethod, $paymentMethod ?: 'cash_on_delivery'),
            'discount_amount' => 0,
            'coupon_code' => '0',
            'refer_and_earn_discount' => 0,
            'admin_commission' => 0,
            'total_tax_amount' => 0,
            'shipping_responsibility' => 'inhouse_shipping',
            'is_shipping_free' => 0,
            'extra_discount' => 0,
            'order_amount' => currencyConverter(amount: $orderCost),
            'init_order_amount' => currencyConverter(amount: $orderCost),
            'shipping_cost' => currencyConverter(amount: $shippingCost),
            'verification_code' => rand(100000, 999999),
            'shipping_address_data' => $this->getShippingAddressData(payload: $payload),
        ]);
        $order->created_at = $this->getPlacedAt(payload: $payload);
        $order->save();

        foreach ($payload['cart_items'] as $cartItem) {
            OrderDetail::create($this->getOrderDetailData(
                orderId: $order['id'],
                cartItem: $cartItem,
                deliveryStatus: $orderStatus,
                paymentStatus: $paymentStatus,
            ));
        }

        return $order;
    }

    public function getMappedOrderStatus(string $easyOrdersStatus): ?string
    {
        return config('easy_orders.status_map.' . strtolower($easyOrdersStatus));
    }

    /**
     * Keeps the time the customer placed the order on EasyOrders, so imported past orders land on their real day.
     */
    private function getPlacedAt(array $payload): Carbon
    {
        if (empty($payload['created_at'])) {
            return now();
        }

        return Carbon::parse($payload['created_at'])->setTimezone(config('app.timezone'));
    }

    /**
     * @return array{contact_person_name: string, phone: string, email: null, address: string|null, city: string|null, state: string|null, zip: null, country: string, address_type: string}
     */
    private function getShippingAddressData(array $payload): array
    {
        return [
            'contact_person_name' => (string)$payload['full_name'],
            'phone' => (string)$payload['phone'],
            'email' => null,
            'address' => $payload['address'] ?? null,
            'city' => $payload['government'] ?? null,
            'state' => $payload['government'] ?? null,
            'zip' => null,
            'country' => 'Egypt',
            'address_type' => 'home',
        ];
    }

    /**
     * Keeps the EasyOrders product inside the order line only; it is never added to the SEA HUM catalog.
     */
    private function getOrderDetailData(int $orderId, array $cartItem, string $deliveryStatus, string $paymentStatus): array
    {
        $product = $cartItem['product'] ?? [];
        $variant = $cartItem['variant'] ?? null;
        $variationProps = $variant['variation_props'] ?? [];

        $variantLabel = collect($variationProps)
            ->map(fn (array $variationProp) => trim(($variationProp['variation'] ?? '') . ': ' . ($variationProp['variation_prop'] ?? ''), ': '))
            ->filter()
            ->implode(', ');

        return [
            'order_id' => $orderId,
            'product_id' => null,
            'seller_id' => self::IN_HOUSE_SELLER_ID,
            'product_details' => json_encode([
                'source' => OrderSource::EasyOrders->value,
                'id' => $product['id'] ?? $cartItem['product_id'] ?? null,
                'name' => $product['name'] ?? 'EasyOrders product',
                'sku' => $product['sku'] ?? null,
                'taager_code' => $variant['taager_code'] ?? $product['taager_code'] ?? null,
                'variant_id' => $cartItem['variant_id'] ?? null,
                'variation_props' => $variationProps,
                'added_by' => self::IN_HOUSE_SELLER_IS,
            ], JSON_UNESCAPED_UNICODE),
            'qty' => (int)$cartItem['quantity'],
            'price' => currencyConverter(amount: (float)$cartItem['price']),
            'discount' => 0,
            'tax' => 0,
            'variant' => $variantLabel !== '' ? $variantLabel : null,
            'variation' => json_encode($variationProps, JSON_UNESCAPED_UNICODE),
            'delivery_status' => $deliveryStatus,
            'payment_status' => $paymentStatus,
            'is_stock_decreased' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
