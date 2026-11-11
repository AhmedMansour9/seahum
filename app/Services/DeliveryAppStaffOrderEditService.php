<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Models\Color;
use App\Models\DeliveryMan;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\Product;
use App\Models\Translation;
use App\Traits\OrderEditManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Edits SEA HUM app orders from the admin and customer service screens of the delivery man app.
 * Item changes go through the same order edit engine as the admin panel (stock, tax, coupon,
 * due and return amounts, edit history), so both places always produce the same order.
 */
class DeliveryAppStaffOrderEditService
{
    use OrderEditManager;

    private const ORDER_RELATIONS = ['details', 'customer'];

    public function __construct(private readonly OrderEditService $orderEditService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public static function getValidationRules(): array
    {
        return [
            'order_id' => 'required|integer',
            'contact_person_name' => 'required|string|max:191',
            'phone' => 'required|string|max:30',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'location_link' => 'nullable|url|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'order_note' => 'nullable|string|max:1000',
            ...self::getProductsValidationRules(isRequired: false),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getProductsValidationRules(bool $isRequired = true): array
    {
        return [
            'products' => ($isRequired ? 'required' : 'nullable') . '|array|min:1',
            'products.*.id' => 'required|integer',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.variant' => 'nullable|string|max:191',
            'products.*.price' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Same rules as the admin panel, except app orders stay editable until they are delivered, canceled, returned or failed, like EasyOrders orders, and unconfirmed offline payments do not lock them.
     *
     * @return array{status: bool, message: string}
     */
    public function getEditableState(Order $order): array
    {
        if ($order['order_source'] === OrderSource::EasyOrders) {
            return ['status' => false, 'message' => translate('Order_not_found')];
        }

        $editableState = $this->orderEditService->checkIsOrderEditable(order: $order, type: 'admin', lockedStatuses: EasyOrdersOrderEditService::LOCKED_STATUSES, isOfflinePaymentConfirmationRequired: false);
        if (!$editableState['status'] && in_array($order['order_status'], EasyOrdersOrderEditService::LOCKED_STATUSES, true)) {
            $editableState['message'] = translate('This_order_can_no_longer_be_edited');
        }

        return $editableState;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function searchProducts(?string $search): Collection
    {
        $products = $this->getInHouseProductsQuery()
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('translations', fn ($query) => $query->where('key', 'name')->where('value', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get();
        $localizedNames = Translation::where([
            'translationable_type' => Product::class,
            'key' => 'name',
            'locale' => config('app.invoice_product_name_locale'),
        ])->whereIn('translationable_id', $products->pluck('id'))->pluck('value', 'translationable_id');

        return $products->map(function (Product $product) use ($localizedNames) {
            $variations = collect(json_decode($product->getRawOriginal('variation') ?? '[]', true) ?: []);

            return [
                'id' => $product['id'],
                'name' => $localizedNames[$product['id']] ?? $product->getRawOriginal('name'),
                'thumbnail_full_url' => $product['thumbnail_full_url'],
                'unit_price' => (float)$product['unit_price'],
                'current_stock' => (int)$product['current_stock'],
                'minimum_order_qty' => (int)($product['minimum_order_qty'] ?? 1),
                'variations' => $variations->map(fn (array $variation) => [
                    'type' => $variation['type'],
                    'price' => (float)($variation['price'] ?? 0),
                    'qty' => (int)($variation['qty'] ?? 0),
                ])->values(),
            ];
        });
}

    /**
     * @return Builder<Product>
     */
    private function getInHouseProductsQuery(): Builder
    {
        return Product::where(['added_by' => 'admin', 'product_type' => 'physical', 'status' => 1]);
    }

    /**
     * @param array<int, array{id: int|string, quantity: int|string, variant?: string|null, price?: float|string|null}> $products
     * @return array{status: bool, message: string, order_amount?: float, current_order_amount?: float, edit_due_amount?: float, order_return_amount?: float}
     */
    public function previewProducts(string|int $orderId, array $products, DeliveryMan $staff, float|string|null $shippingCost = null): array
    {
        $order = $this->findEditableOrder(orderId: $orderId);
        if (!$order['status']) {
            return $order;
        }
        if ($shippingCost !== null) {
            $order['order']['shipping_cost'] = (float)$shippingCost;
        }

        $editedOrder = $this->getEditedOrderProducts(order: $order['order'], products: $products);
        if (!$editedOrder['status']) {
            return $editedOrder;
        }

        $summary = $this->generateEditOrderSummary(order: $order['order'], editedOrder: $editedOrder['product_list'], data: $this->getEditorData(staff: $staff));
        if ($summary['status'] !== 'success') {
            return ['status' => false, 'message' => $summary['message']];
        }

        return [
            'status' => true,
            'message' => $summary['message'],
            'current_order_amount' => (float)$order['order']['order_amount'],
            'order_amount' => (float)$summary['order_amount'],
            'edit_due_amount' => (float)$summary['edit_due_amount'],
            'order_return_amount' => (float)$summary['order_return_amount'],
        ];
    }

    /**
     * Saves customer data and note, and when products are sent, rebuilds the order lines.
     *
     * @param array{contact_person_name: string, phone: string, address?: string|null, city?: string|null, order_note?: string|null, products?: array<int, array<string, mixed>>|null} $data
     * @return array{status: bool, message: string}
     */
    public function updateOrder(string|int $orderId, array $data, DeliveryMan $staff): array
    {
        return DB::transaction(function () use ($orderId, $data, $staff): array {
            $order = $this->findEditableOrder(orderId: $orderId);
            if (!$order['status']) {
                return $order;
            }

            $shippingCost = isset($data['shipping_cost']) ? (float)$data['shipping_cost'] : null;
            if ($shippingCost !== null) {
                $order['order']['shipping_cost'] = $shippingCost;
            }

            if (!empty($data['products'])) {
                $editedOrder = $this->getEditedOrderProducts(order: $order['order'], products: $data['products']);
                if (!$editedOrder['status']) {
                    return $editedOrder;
                }

                $result = $this->generateEditOrder(order: $order['order'], editedOrder: $editedOrder['product_list'], data: $this->getEditorData(staff: $staff));
                if ($result['status'] !== 'success') {
                    return ['status' => false, 'message' => $result['message']];
                }
            }

            $freshOrder = Order::findOrFail($order['order']['id']);
            if ($shippingCost !== null && empty($data['products'])) {
                $this->updateShippingCost(order: $freshOrder, shippingCost: $shippingCost);
            }
            $freshOrder->update([
                'shipping_address_data' => EasyOrdersOrderEditService::mergeCustomerData(
                    shippingAddress: (array)($freshOrder['shipping_address_data'] ?? []),
                    data: $data,
                ),
                'order_note' => $data['order_note'] ?? null,
            ]);

            return ['status' => true, 'message' => translate('Order_updated_successfully')];
        });
    }

    /**
     * Shipping only edit: the amount the customer pays moves by the same difference.
     */
    private function updateShippingCost(Order $order, float $shippingCost): void
    {
        $difference = $shippingCost - (float)$order['shipping_cost'];
        $order->update([
            'shipping_cost' => $shippingCost,
            'order_amount' => (float)$order['order_amount'] + $difference,
        ]);
        OrderTransaction::where('order_id', $order['id'])->update(['delivery_charge' => $shippingCost - (float)$order['extra_discount']]);
    }

    /**
     * @return array{status: bool, message: string, order?: Order}
     */
    private function findEditableOrder(string|int $orderId): array
    {
        $order = Order::with(self::ORDER_RELATIONS)
            ->where(['id' => $orderId, 'order_type' => 'default_type'])
            ->where(fn ($query) => $query->whereNull('order_source')->orWhere('order_source', '!=', OrderSource::EasyOrders->value))
            ->first();
        if (!$order) {
            return ['status' => false, 'message' => translate('Order_not_found')];
        }

        $editableState = $this->getEditableState(order: $order);
        if (!$editableState['status']) {
            return $editableState;
        }

        return ['status' => true, 'message' => '', 'order' => $order];
    }

    /**
     * Converts the app's final item list into the format of the admin panel edit engine and checks stock.
     *
     * @param array<int, array{id: int|string, quantity: int|string, variant?: string|null, price?: float|string|null}> $products
     * @return array{status: bool, message: string, product_list?: array<string, array<string, mixed>>}
     */
    private function getEditedOrderProducts(Order $order, array $products): array
    {
        $catalogProducts = $this->getInHouseProductsQuery()->whereIn('id', collect($products)->pluck('id'))->get()->keyBy('id');
        $requestProducts = [];

        foreach ($products as $item) {
            $product = $catalogProducts->get((int)$item['id']);
            if (!$product) {
                return ['status' => false, 'message' => translate('Some_products_are_no_longer_available')];
            }
            $requestProducts[] = [
                'id' => $product['id'],
                'quantity' => (int)$item['quantity'],
                'variant' => !empty($item['variant']) ? $item['variant'] : null,
                'price' => $item['price'] ?? null,
                ...$this->getVariationChoices(product: $product, variant: $item['variant'] ?? null),
            ];
        }

        $colors = Color::whereIn('code', collect($requestProducts)->pluck('color')->filter())->get();
        $result = $this->orderEditService->getFormatAPIProductsForEditOrder(request: ['products' => $requestProducts], order: $order, colors: $colors);
        if ($result['status'] !== 'success') {
            return ['status' => false, 'message' => translate('Out of stock')];
        }

        foreach ($result['product_list'] as $productItem) {
            if ($productItem['qty'] > $productItem['current_stock']) {
                return [
                    'status' => false,
                    'message' => translate('Out of stock') . ': ' . $productItem['name'] . ' (' . $productItem['current_stock'] . ')',
                ];
            }
        }

        return ['status' => true, 'message' => '', 'product_list' => $result['product_list']];
    }

    /**
     * A variant such as "Red-1Kg" is the color name followed by each choice value, as built at checkout.
     *
     * @return array{color: string|null, choice: array<int, array{key: string, value: string}>}
     */
    private function getVariationChoices(Product $product, ?string $variant): array
    {
        if (empty($variant)) {
            return ['color' => null, 'choice' => []];
        }

        $parts = explode('-', $variant);
        $colorCodes = json_decode($product->getRawOriginal('colors') ?? '[]', true) ?: [];
        $color = null;
        if (!empty($colorCodes)) {
            $colorName = array_shift($parts);
            $color = Color::whereIn('code', $colorCodes)->where('name', $colorName)->value('code');
        }

        $choices = [];
        foreach (json_decode($product->getRawOriginal('choice_options') ?? '[]') ?: [] as $index => $choiceOption) {
            $part = $parts[$index] ?? null;
            if ($part === null) {
                continue;
            }
            $value = collect($choiceOption->options ?? [])->first(fn ($option) => str_replace(' ', '', $option) === $part) ?? $part;
            $choices[] = ['key' => $choiceOption->name, 'value' => $value];
        }

        return ['color' => $color, 'choice' => $choices];
    }

    /**
     * @return array{edit_by: string, edited_user_id: int, edited_user_name: string, order_request_from: string}
     */
    private function getEditorData(DeliveryMan $staff): array
    {
        return [
            'edit_by' => 'admin',
            'edited_user_id' => $staff['id'],
            'edited_user_name' => trim($staff['f_name'] . ' ' . $staff['l_name']) . ' (' . translate('delivery_app') . ')',
            'order_request_from' => 'app',
        ];
    }
}
