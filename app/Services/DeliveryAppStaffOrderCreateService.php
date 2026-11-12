<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Models\DeliveryMan;
use App\Models\Order;
use App\Models\Product;
use App\Traits\CommonTrait;
use App\Utils\OrderManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Creates an in-house guest order from the admin and customer service screens of the delivery man app.
 */
class DeliveryAppStaffOrderCreateService
{
    use CommonTrait;

    private const IN_HOUSE_SELLER_ID = 1;
    private const IN_HOUSE_SELLER_IS = 'admin';

    /**
     * @return array<string, array<int|string, string>>
     */
    public static function getValidationRules(): array
    {
        return [
            'contact_person_name' => ['required', 'string', 'max:191'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'location_link' => ['nullable', 'url', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'order_note' => ['nullable', 'string', 'max:1000'],
            'scheduled_for' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:' . now(config('order_regions.timezone'))->toDateString()],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'integer'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.variant' => ['nullable', 'string', 'max:191'],
            'products.*.price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @param array{contact_person_name: string, phone: string, address?: string|null, city?: string|null, location_link?: string|null, latitude?: float|string|null, longitude?: float|string|null, order_note?: string|null, scheduled_for?: string|null, shipping_cost?: float|string|null, products: array<int, array{id: int|string, quantity: int|string, variant?: string|null, price?: float|string|null}>} $data
     * @return array{status: bool, message: string, order_id?: int}
     */
    public function createOrder(array $data, DeliveryMan $staff): array
    {
        return DB::transaction(function () use ($data, $staff): array {
            $lines = $this->getOrderLines(items: $data['products']);
            if (!$lines['status']) {
                return $lines;
            }

            $shippingCost = (float)($data['shipping_cost'] ?? 0);
            $orderAmount = collect($lines['lines'])->sum(fn (array $line) => ($line['price'] - $line['unit_discount']) * $line['quantity']) + $shippingCost;

            $order = Order::create([
                'id' => OrderManager::generateNewOrderID(),
                'order_source' => OrderSource::App,
                'order_type' => 'default_type',
                'customer_id' => null,
                'is_guest' => 1,
                'customer_type' => 'customer',
                'seller_id' => self::IN_HOUSE_SELLER_ID,
                'seller_is' => self::IN_HOUSE_SELLER_IS,
                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'cash_on_delivery',
                'discount_amount' => 0,
                'coupon_code' => '0',
                'refer_and_earn_discount' => 0,
                'admin_commission' => 0,
                'total_tax_amount' => 0,
                'shipping_responsibility' => 'inhouse_shipping',
                'is_shipping_free' => 0,
                'extra_discount' => 0,
                'order_amount' => $orderAmount,
                'init_order_amount' => $orderAmount,
                'shipping_cost' => $shippingCost,
                'verification_code' => rand(100000, 999999),
                'order_note' => $data['order_note'] ?? null,
                'scheduled_for' => $data['scheduled_for'] ?? null,
                'shipping_address_data' => $this->getShippingAddressData(data: $data),
            ]);

            foreach ($lines['lines'] as $line) {
                $this->addOrderLine(order: $order, line: $line);
            }

            self::add_order_status_history($order['id'], $staff['id'], 'pending', 'delivery_man');

            return ['status' => true, 'message' => translate('Order_created_successfully'), 'order_id' => $order['id']];
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function getShippingAddressData(array $data): array
    {
        return [
            'contact_person_name' => $data['contact_person_name'],
            'phone' => $data['phone'],
            'email' => null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['city'] ?? null,
            'zip' => null,
            'country' => 'Egypt',
            'address_type' => 'home',
            'location_link' => $data['location_link'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ];
    }

    /**
     * Resolves each requested item to a catalog product with its price, discount and stock check.
     *
     * @param array<int, array{id: int|string, quantity: int|string, variant?: string|null, price?: float|string|null}> $items
     * @return array{status: bool, message: string, lines?: array<int, array<string, mixed>>}
     */
    private function getOrderLines(array $items): array
    {
        $products = $this->getInHouseProductsQuery()
            ->with(['clearanceSale' => fn ($query) => $query->active()])
            ->whereIn('id', collect($items)->pluck('id'))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $lines = [];
        foreach ($items as $item) {
            $product = $products->get((int)$item['id']);
            if (!$product) {
                return ['status' => false, 'message' => translate('Some_products_are_no_longer_available')];
            }

            $variant = !empty($item['variant']) ? $item['variant'] : null;
            $variation = $variant ? $this->getVariation(product: $product, variant: $variant) : null;
            if ($variant && !$variation) {
                return ['status' => false, 'message' => translate('Some_products_are_no_longer_available') . ': ' . $product['name']];
            }

            $quantity = (int)$item['quantity'];
            $availableStock = $variation ? (int)$variation['qty'] : (int)$product['current_stock'];
            if ($quantity > $availableStock) {
                return ['status' => false, 'message' => translate('Out of stock') . ': ' . $product['name'] . ' (' . $availableStock . ')'];
            }

            $hasCustomPrice = isset($item['price']) && $item['price'] !== '';
            $price = $hasCustomPrice ? (float)$item['price'] : (float)($variation['price'] ?? $product['unit_price']);
            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'variant' => $variant,
                'price' => $price,
                'unit_discount' => $hasCustomPrice ? 0.0 : (float)getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $price),
            ];
        }

        return ['status' => true, 'message' => '', 'lines' => $lines];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getVariation(Product $product, string $variant): ?array
    {
        return collect(json_decode($product->getRawOriginal('variation') ?? '[]', true) ?: [])
            ->first(fn (array $variation) => $variation['type'] === $variant);
    }

    /**
     * @param array<string, mixed> $line
     */
    private function addOrderLine(Order $order, array $line): void
    {
        /** @var Product $product */
        $product = $line['product'];
        $productDetails = Arr::except($product->toArray(), ['is_shop_temporary_close', 'meta_image_full_url', 'reviews', 'order_details', 'order_delivered', 'seo_info']);

        DB::table('order_details')->insert([
            'order_id' => $order['id'],
            'product_id' => $product['id'],
            'seller_id' => self::IN_HOUSE_SELLER_ID,
            'product_details' => json_encode($productDetails),
            'qty' => $line['quantity'],
            'price' => $line['price'],
            'discount' => $line['unit_discount'] * $line['quantity'],
            'discount_type' => 'discount_on_product',
            'tax' => 0,
            'tax_model' => 'exclude',
            'variant' => $line['variant'],
            'variation' => json_encode([]),
            'delivery_status' => 'pending',
            'payment_status' => 'unpaid',
            'shipping_method_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->decreaseStock(product: $product, quantity: $line['quantity'], variant: $line['variant']);
    }

    private function decreaseStock(Product $product, int $quantity, ?string $variant): void
    {
        $variations = collect(json_decode($product->getRawOriginal('variation') ?? '[]', true) ?: [])
            ->map(function (array $variation) use ($variant, $quantity) {
                if ($variant && $variation['type'] === $variant) {
                    $variation['qty'] -= $quantity;
                }

                return $variation;
            })->all();

        $product->update([
            'variation' => json_encode($variations),
            'current_stock' => $product['current_stock'] - $quantity,
        ]);
    }

    /**
     * @return Builder<Product>
     */
    private function getInHouseProductsQuery(): Builder
    {
        return Product::where(['added_by' => 'admin', 'product_type' => 'physical', 'status' => 1]);
    }
}
