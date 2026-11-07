<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;

/**
 * Edits an EasyOrders order inside SEA HUM: customer data, order lines and note.
 * EasyOrders lines are not catalog products, so no stock or wallet is touched.
 * Changes stay in SEA HUM and are not sent back to EasyOrders.
 */
class EasyOrdersOrderEditService
{
    public const LOCKED_STATUSES = ['delivered', 'canceled', 'returned', 'failed'];

    public function isEditable(Order $order): bool
    {
        return $order['order_source'] === OrderSource::EasyOrders
            && !in_array($order['order_status'], self::LOCKED_STATUSES, true);
    }

    /**
     * @param array{
     *     contact_person_name: string,
     *     phone: string,
     *     address?: string|null,
     *     city?: string|null,
     *     order_note?: string|null,
     *     items?: array<int, array{id: int|string, qty: int|string, price: float|string, remove?: bool|string|null}>,
     *     new_items?: array<int, array{name?: string|null, qty?: int|string|null, price?: float|string|null}>
     * } $data
     * @return array{status: bool, message: string}
     */
    public function updateOrder(string|int $orderId, array $data): array
    {
        return DB::transaction(function () use ($orderId, $data): array {
            $order = Order::where(['id' => $orderId, 'order_source' => OrderSource::EasyOrders->value])->lockForUpdate()->first();
            if (!$order) {
                return ['status' => false, 'message' => translate('Order_not_found')];
            }
            if (!$this->isEditable(order: $order)) {
                return ['status' => false, 'message' => translate('This_order_can_no_longer_be_edited')];
            }

            $existingItems = collect($data['items'] ?? [])->keyBy(fn (array $item) => (int)$item['id']);
            $newItems = collect($data['new_items'] ?? [])->filter(fn (array $item) => trim((string)($item['name'] ?? '')) !== '');
            $keptItemsCount = $order->details->filter(fn (OrderDetail $detail) => !$this->isMarkedForRemoval(item: $existingItems->get($detail['id'])))->count();

            if ($keptItemsCount + $newItems->count() === 0) {
                return ['status' => false, 'message' => translate('The_order_must_have_at_least_one_item')];
            }

            foreach ($order->details as $detail) {
                $item = $existingItems->get($detail['id']);
                if (!$item) {
                    continue;
                }
                if ($this->isMarkedForRemoval(item: $item)) {
                    $detail->delete();
                    continue;
                }
                $detail->update([
                    'qty' => (int)$item['qty'],
                    'price' => currencyConverter(amount: (float)$item['price']),
                ]);
            }

            foreach ($newItems as $newItem) {
                OrderDetail::create($this->getNewItemData(order: $order, item: $newItem));
            }

            $shippingAddress = (array)($order['shipping_address_data'] ?? []);
            $order->update([
                'shipping_address_data' => array_merge($shippingAddress, [
                    'contact_person_name' => $data['contact_person_name'],
                    'phone' => $data['phone'],
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['city'] ?? null,
                ]),
                'order_note' => $data['order_note'] ?? null,
                'order_amount' => OrderDetail::where('order_id', $order['id'])->get()->sum(fn (OrderDetail $detail) => $detail['price'] * $detail['qty']),
            ]);

            return ['status' => true, 'message' => translate('Order_updated_successfully')];
        });
    }

    /**
     * @param array<string, mixed>|null $item
     */
    private function isMarkedForRemoval(?array $item): bool
    {
        return $item !== null && filter_var($item['remove'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param array{name: string, qty?: int|string|null, price?: float|string|null} $item
     * @return array<string, mixed>
     */
    private function getNewItemData(Order $order, array $item): array
    {
        $deliveryStatus = $order['order_status'];
        $paymentStatus = $order['payment_status'];

        return [
            'order_id' => $order['id'],
            'product_id' => null,
            'seller_id' => $order['seller_id'],
            'product_details' => json_encode([
                'source' => OrderSource::EasyOrders->value,
                'id' => null,
                'name' => trim($item['name']),
                'sku' => null,
                'added_by' => $order['seller_is'],
            ], JSON_UNESCAPED_UNICODE),
            'qty' => max(1, (int)($item['qty'] ?? 1)),
            'price' => currencyConverter(amount: (float)($item['price'] ?? 0)),
            'discount' => 0,
            'tax' => 0,
            'variant' => null,
            'variation' => json_encode([]),
            'delivery_status' => $deliveryStatus,
            'payment_status' => $paymentStatus,
            'is_stock_decreased' => 0,
        ];
    }
}
