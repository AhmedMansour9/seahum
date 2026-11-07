<?php

namespace App\Services;

use App\Enums\DeliveryManAppRole;
use App\Enums\OrderSource;
use App\Models\DeliveryMan;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Read models for the admin and customer service screens of the delivery man app.
 */
class DeliveryAppStaffService
{
    private const OPEN_ORDER_STATUSES = ['pending', 'confirmed', 'processing', 'out_for_delivery'];

    /**
     * @param array{status?: string|null, search?: string|null, order_source?: string|null, date_type?: string|null} $filters
     */
    public function getOrders(array $filters, int $limit, int $offset): LengthAwarePaginator
    {
        return Order::with(['customer:id,f_name,l_name,phone', 'deliveryMan:id,f_name,l_name,phone', 'shippingAddress'])
            ->where('order_type', 'default_type')
            ->when(!empty($filters['status']), fn ($query) => $query->where('order_status', $filters['status']))
            ->when(!empty($filters['order_source']), fn ($query) => $query->where('order_source', $filters['order_source']))
            ->when(($filters['date_type'] ?? null) === 'today', fn ($query) => $query->whereDate('created_at', Carbon::today()))
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhere('external_order_id', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($query) => $query->where('phone', 'like', "%{$search}%"))
                        ->orWhere('shipping_address_data', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(perPage: $limit, page: $offset);
    }

    /**
     * @return array<string, mixed>
     */
    public function formatOrderListItem(Order $order): array
    {
        return [
            'id' => $order['id'],
            'order_source' => $order['order_source']?->value ?? OrderSource::App->value,
            'external_order_id' => $order['external_order_id'],
            'order_status' => $order['order_status'],
            'payment_status' => $order['payment_status'],
            'payment_method' => $order['payment_method'],
            'order_amount' => (float)$order['order_amount'],
            'shipping_cost' => (float)$order['shipping_cost'],
            'customer' => $this->getCustomerData(order: $order),
            'delivery_man' => $order->deliveryMan ? [
                'id' => $order->deliveryMan['id'],
                'name' => trim($order->deliveryMan['f_name'] . ' ' . $order->deliveryMan['l_name']),
                'phone' => $order->deliveryMan['phone'],
            ] : null,
            'created_at' => $order['created_at'],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getOrderDetails(string|int $orderId): ?array
    {
        $order = Order::with(['customer:id,f_name,l_name,phone', 'deliveryMan:id,f_name,l_name,phone', 'shippingAddress', 'details'])
            ->where(['id' => $orderId, 'order_type' => 'default_type'])
            ->first();

        if (!$order) {
            return null;
        }

        return array_merge($this->formatOrderListItem(order: $order), [
            'order_note' => $order['order_note'],
            'discount_amount' => (float)$order['discount_amount'],
            'items' => $order->details->map(fn (OrderDetail $detail) => $this->formatOrderItem(detail: $detail))->values(),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getDeliveryMen(): Collection
    {
        return DeliveryMan::where(['seller_id' => 0, 'app_role' => DeliveryManAppRole::DeliveryMan->value])
            ->withCount(['orders as open_orders_count' => fn ($query) => $query->whereIn('order_status', self::OPEN_ORDER_STATUSES)])
            ->orderByDesc('is_online')
            ->orderBy('f_name')
            ->get()
            ->map(fn (DeliveryMan $deliveryMan) => [
                'id' => $deliveryMan['id'],
                'name' => trim($deliveryMan['f_name'] . ' ' . $deliveryMan['l_name']),
                'phone' => $deliveryMan['phone'],
                'image_full_url' => $deliveryMan['image_full_url'],
                'is_active' => (int)$deliveryMan['is_active'],
                'is_online' => (int)$deliveryMan['is_online'],
                'open_orders_count' => (int)$deliveryMan['open_orders_count'],
            ]);
    }

    public function isAssignableDeliveryMan(string|int $deliveryManId): bool
    {
        return DeliveryMan::where([
            'id' => $deliveryManId,
            'seller_id' => 0,
            'is_active' => 1,
            'app_role' => DeliveryManAppRole::DeliveryMan->value,
        ])->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function getTodayStats(): array
    {
        $todayOrders = Order::where('order_type', 'default_type')->whereDate('created_at', Carbon::today());
        $statusCounts = (clone $todayOrders)
            ->selectRaw('order_status, count(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return [
            'total_orders' => (int)(clone $todayOrders)->count(),
            'total_amount' => (float)(clone $todayOrders)->whereNotIn('order_status', ['canceled', 'failed', 'returned'])->sum('order_amount'),
            'delivered_amount' => (float)(clone $todayOrders)->where('order_status', 'delivered')->sum('order_amount'),
            'easy_orders_count' => (int)(clone $todayOrders)->where('order_source', OrderSource::EasyOrders->value)->count(),
            'status_counts' => collect(getOrderStatusList())->mapWithKeys(fn (string $status) => [$status => (int)($statusCounts[$status] ?? 0)]),
            'open_orders' => (int)Order::where('order_type', 'default_type')->whereIn('order_status', self::OPEN_ORDER_STATUSES)->count(),
            'unassigned_open_orders' => (int)Order::where('order_type', 'default_type')->whereIn('order_status', self::OPEN_ORDER_STATUSES)->whereNull('delivery_man_id')->count(),
            'online_delivery_men' => (int)DeliveryMan::where(['seller_id' => 0, 'is_online' => 1, 'app_role' => DeliveryManAppRole::DeliveryMan->value])->count(),
        ];
    }

    /**
     * @return array{name: string|null, phone: string|null, address: string|null, city: string|null}
     */
    private function getCustomerData(Order $order): array
    {
        $shippingAddress = $order['shipping_address_data'];
        if (is_string($shippingAddress)) {
            $shippingAddress = json_decode($shippingAddress);
        }
        $shippingAddress = $shippingAddress ?: $order->shippingAddress;

        $customerName = $order->customer ? trim($order->customer['f_name'] . ' ' . $order->customer['l_name']) : null;

        return [
            'name' => $shippingAddress?->contact_person_name ?? $customerName,
            'phone' => $shippingAddress?->phone ?? $order->customer?->phone,
            'address' => $shippingAddress?->address ?? null,
            'city' => $shippingAddress?->city ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatOrderItem(OrderDetail $detail): array
    {
        $productDetails = is_array($detail['product_details']) ? $detail['product_details'] : (json_decode($detail['product_details'] ?? '', true) ?? []);

        return [
            'id' => $detail['id'],
            'product_id' => $detail['product_id'],
            'name' => $productDetails['name'] ?? null,
            'sku' => $productDetails['sku'] ?? $productDetails['code'] ?? null,
            'thumbnail_full_url' => $detail['product_id'] ? $detail?->productAllStatus?->thumbnail_full_url : null,
            'variant' => $detail['variant'],
            'qty' => (int)$detail['qty'],
            'price' => (float)$detail['price'],
            'discount' => (float)$detail['discount'],
            'delivery_status' => $detail['delivery_status'],
        ];
    }
}
