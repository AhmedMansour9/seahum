<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Models\Order;
use App\Models\ShippingAddress;
use Illuminate\Support\Collection;

class DeliveryManOrderDisplayService
{
    /**
     * @param Collection<int, Order> $orders
     * @return Collection<int, Order>
     */
    public function prepareOrders(Collection $orders): Collection
    {
        return $orders->each(fn (Order $order) => $this->prepareOrder(order: $order));
    }

    public function prepareOrder(?Order $order): ?Order
    {
        if (!$order) {
            return $order;
        }

        return $this->fillEasyOrdersTotal(order: $this->fillGuestAddress(order: $order));
    }

    public function fillGuestAddress(Order $order): Order
    {
        if ($order->shippingAddress || !$order['shipping_address_data']) {
            return $order;
        }

        $addressData = $order['shipping_address_data'];
        $shippingAddress = (new ShippingAddress())->forceFill([
            'id' => 0,
            'customer_id' => $order['customer_id'],
            'contact_person_name' => $addressData->contact_person_name ?? null,
            'address_type' => $addressData->address_type ?? 'home',
            'address' => $addressData->address ?? null,
            'city' => $addressData->city ?? null,
            'zip' => $addressData->zip ?? null,
            'phone' => $addressData->phone ?? null,
            'state' => $addressData->state ?? null,
            'country' => $addressData->country ?? null,
            'latitude' => $this->getCoordinateText(value: $addressData->latitude ?? null),
            'longitude' => $this->getCoordinateText(value: $addressData->longitude ?? null),
            'location_link' => filled($addressData->location_link ?? null) ? trim($addressData->location_link) : null,
        ]);

        return $order->setRelation('shippingAddress', $shippingAddress);
    }

    private function fillEasyOrdersTotal(Order $order): Order
    {
        if ($order['order_source'] === OrderSource::EasyOrders) {
            $order->setAttribute('order_amount', (float)$order['order_amount'] + (float)$order['shipping_cost']);
        }

        return $order;
    }

    private function getCoordinateText(mixed $value): ?string
    {
        return is_numeric($value) && (float)$value != 0.0 ? (string)$value : null;
    }
}
