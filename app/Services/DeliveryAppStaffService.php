<?php

namespace App\Services;

use App\Enums\DeliveryManAppRole;
use App\Enums\OrderSource;
use App\Models\DeliveryMan;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Utils\OrderManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Read models for the admin and customer service screens of the delivery man app.
 */
class DeliveryAppStaffService
{
    public function __construct(
        private readonly EasyOrdersOrderEditService       $easyOrdersOrderEditService,
        private readonly DeliveryAppStaffOrderEditService $deliveryAppStaffOrderEditService,
    )
    {
    }

    private const OPEN_ORDER_STATUSES = ['pending', 'confirmed', 'processing', 'out_for_delivery'];

    /**
     * @param array{status?: string|null, search?: string|null, order_source?: string|null, date_type?: string|null, delivery_man_id?: string|int|null, date_from?: string|null, date_to?: string|null} $filters
     */
    public function getOrders(array $filters, int $limit, int $offset): LengthAwarePaginator
    {
        return $this->getOrdersQuery(filters: $filters)
            ->with(['customer:id,f_name,l_name,phone', 'deliveryMan:id,f_name,l_name,phone', 'shippingAddress', 'details'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(perPage: $limit, page: $offset);
    }

    /**
     * Number of orders per status for the same filters, ignoring the status filter itself.
     *
     * @param array{search?: string|null, order_source?: string|null, date_type?: string|null, delivery_man_id?: string|int|null, date_from?: string|null, date_to?: string|null} $filters
     * @return Collection<string, int>
     */
    public function getStatusCounts(array $filters): Collection
    {
        return $this->getOrdersQuery(filters: Arr::except($filters, ['status']))
            ->selectRaw('order_status, count(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status')
            ->map(fn ($total) => (int)$total);
    }

    /**
     * @param array<string, mixed> $filters
     * @return Builder<Order>
     */
    private function getOrdersQuery(array $filters): Builder
    {
        return Order::query()
            ->where('order_type', 'default_type')
            ->when(!empty($filters['status']), fn ($query) => $query->where('order_status', $filters['status']))
            ->when(!empty($filters['order_source']), fn ($query) => $query->where('order_source', $filters['order_source']))
            ->when(!empty($filters['delivery_man_id']), fn ($query) => $query->where('delivery_man_id', $filters['delivery_man_id']))
            ->when(($filters['date_type'] ?? null) === 'today', fn ($query) => $query->whereDate('updated_at', Carbon::today()))
            ->when(!empty($filters['date_from']), fn ($query) => $query->whereDate('updated_at', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn ($query) => $query->whereDate('updated_at', '<=', $filters['date_to']))
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhere('external_order_id', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($query) => $query->where('phone', 'like', "%{$search}%"))
                        ->orWhere('shipping_address_data', 'like', "%{$search}%");
                });
            });
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
            'total_amount' => (float)OrderManager::getOrderTotalPriceSummary(order: $order)['totalAmount'],
            'customer' => $this->getCustomerData(order: $order),
            'delivery_man' => $order->deliveryMan ? [
                'id' => $order->deliveryMan['id'],
                'name' => trim($order->deliveryMan['f_name'] . ' ' . $order->deliveryMan['l_name']),
                'phone' => $order->deliveryMan['phone'],
            ] : null,
            'created_at' => $order['created_at'],
            'updated_at' => $order['updated_at'],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getOrderDetails(string|int $orderId, string|int|null $deliveryManId = null): ?array
    {
        $order = Order::with(['customer:id,f_name,l_name,phone', 'deliveryMan:id,f_name,l_name,phone', 'shippingAddress', 'details'])
            ->where(['id' => $orderId, 'order_type' => 'default_type'])
            ->when($deliveryManId, fn ($query) => $query->where('delivery_man_id', $deliveryManId))
            ->first();

        if (!$order) {
            return null;
        }

        return array_merge($this->formatOrderListItem(order: $order), [
            'order_note' => $order['order_note'],
            'discount_amount' => (float)$order['discount_amount'],
            ...$this->getEditState(order: $order),
            'whatsapp_message' => getOrderWhatsAppMessage(order: $order),
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
        $todayOrders = Order::where('order_type', 'default_type')->whereDate('updated_at', Carbon::today());
        $statusCounts = (clone $todayOrders)
            ->selectRaw('order_status, count(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return [
            'total_orders' => (int)(clone $todayOrders)->count(),
            'total_amount' => (float)Order::where('order_type', 'default_type')->whereDate('created_at', Carbon::today())->whereNotIn('order_status', ['canceled', 'failed', 'returned'])->sum('order_amount'),
            'delivered_amount' => (float)(clone $todayOrders)->where('order_status', 'delivered')->sum('order_amount'),
            'easy_orders_count' => (int)(clone $todayOrders)->where('order_source', OrderSource::EasyOrders->value)->count(),
            'status_counts' => collect(getOrderStatusList())->mapWithKeys(fn (string $status) => [$status => (int)($statusCounts[$status] ?? 0)]),
            'open_orders' => (int)Order::where('order_type', 'default_type')->whereIn('order_status', self::OPEN_ORDER_STATUSES)->count(),
            'unassigned_open_orders' => (int)Order::where('order_type', 'default_type')->whereIn('order_status', self::OPEN_ORDER_STATUSES)->whereNull('delivery_man_id')->count(),
            'online_delivery_men' => (int)DeliveryMan::where(['seller_id' => 0, 'is_online' => 1, 'app_role' => DeliveryManAppRole::DeliveryMan->value])->count(),
        ];
    }

    /**
     * EasyOrders orders use the simple line editor, app orders use the catalog editor of the admin panel.
     *
     * @return array{is_editable: bool, edit_type: string|null, edit_blocked_reason: string|null}
     */
    private function getEditState(Order $order): array
    {
        if ($order['order_source'] === OrderSource::EasyOrders) {
            $isEditable = $this->easyOrdersOrderEditService->isEditable(order: $order);

            return [
                'is_editable' => $isEditable,
                'edit_type' => $isEditable ? 'easy_orders' : null,
                'edit_blocked_reason' => $isEditable ? null : translate('This_order_can_no_longer_be_edited'),
            ];
        }

        $editableState = $this->deliveryAppStaffOrderEditService->getEditableState(order: $order);

        return [
            'is_editable' => $editableState['status'],
            'edit_type' => $editableState['status'] ? 'catalog' : null,
            'edit_blocked_reason' => $editableState['status'] ? null : $editableState['message'],
        ];
    }

    /**
     * @return array{name: string|null, phone: string|null, address: string|null, city: string|null, latitude: float|null, longitude: float|null, location_link: string|null}
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
            'latitude' => $this->getCoordinate(value: $shippingAddress?->latitude ?? null),
            'longitude' => $this->getCoordinate(value: $shippingAddress?->longitude ?? null),
            'location_link' => filled($shippingAddress?->location_link ?? null) ? trim($shippingAddress->location_link) : null,
        ];
    }

    private function getCoordinate(mixed $value): ?float
    {
        return is_numeric($value) && (float)$value != 0.0 ? (float)$value : null;
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
            'name' => $detail->getInvoiceProductName() ?? $productDetails['name'] ?? null,
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
