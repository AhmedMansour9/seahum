<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Utils\OrderManager;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class EasyOrdersWebhookService
{
    private const IN_HOUSE_SELLER_ID = 1;
    private const IN_HOUSE_SELLER_IS = 'admin';

    public function isValidSecret(?string $secret): bool
    {
        $configuredSecret = (string)config('easy_orders.webhook_secret');

        return $configuredSecret !== '' && is_string($secret) && hash_equals($configuredSecret, $secret);
    }

    /**
     * Stores an EasyOrders order once; a repeated delivery of the same order returns the stored one.
     *
     * @param array{id: string, status?: string, payment_method?: string, cost?: float, shipping_cost?: float, total_cost: float, full_name: string, phone: string, government?: string, address?: string, created_at?: string, cart_items: array<int, array<string, mixed>>} $payload
     */
    public function storeOrder(array $payload): Order
    {
        $existingOrder = $this->findOrder(externalOrderId: (string)$payload['id']);
        if ($existingOrder) {
            return $existingOrder;
        }

        try {
            return DB::transaction(fn () => $this->createOrder(payload: $payload));
        } catch (UniqueConstraintViolationException) {
            return $this->findOrder(externalOrderId: (string)$payload['id']);
        }
    }

    /**
     * @param array{order_id: string, new_status: string, payment_ref_id?: string|null} $payload
     */
    public function updateOrderStatus(array $payload): ?Order
    {
        $order = $this->findOrder(externalOrderId: (string)$payload['order_id']);
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
        if ($mappedOrderStatus) {
            $orderData['order_status'] = $mappedOrderStatus;
            $orderDetailData['delivery_status'] = $mappedOrderStatus;
        }

        if (!empty($orderData)) {
            DB::transaction(function () use ($order, $orderData, $orderDetailData) {
                $order->update($orderData);
                OrderDetail::where('order_id', $order['id'])->update($orderDetailData);
            });
        }

        return $order->refresh();
    }

    private function findOrder(string $externalOrderId): ?Order
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

        $order = Order::create([
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
            'order_amount' => currencyConverter(amount: (float)$payload['total_cost']),
            'init_order_amount' => currencyConverter(amount: (float)$payload['total_cost']),
            'shipping_cost' => currencyConverter(amount: (float)($payload['shipping_cost'] ?? 0)),
            'verification_code' => rand(100000, 999999),
            'shipping_address_data' => $this->getShippingAddressData(payload: $payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

    private function getMappedOrderStatus(string $easyOrdersStatus): ?string
    {
        return config('easy_orders.status_map.' . strtolower($easyOrdersStatus));
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
