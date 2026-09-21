<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Cart;
use App\Models\CartShipping;
use App\Models\Coupon;
use App\Models\DeliveryMan;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\PasswordReset;
use App\Models\PhoneOrEmailVerification;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\Review;
use App\Models\ShippingAddress;
use App\Models\Shop;
use App\Models\StockClearanceProduct;
use App\Models\StockClearanceSetup;
use App\Models\SupportTicket;
use App\Models\SupportTicketConv;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Blog\app\Models\Blog;
use Modules\Blog\app\Models\BlogCategory;

/**
 * Seeds data so every storefront page still on the legacy layout renders with
 * real content for buyer@gmail.com. Single-vendor mode: every seeded product
 * belongs to the admin in-house shop.
 *
 * Idempotent: every row is matched on a deterministic key (email, slug, code,
 * order_group_id, transaction_id). Nothing is truncated or deleted.
 *
 * Run: php artisan db:seed --class="Database\Seeders\StorefrontPagesScenarioSeeder"
 */
class StorefrontPagesScenarioSeeder extends Seeder
{
    private const BASE_URL = 'http://delta2.test';

    private const PASSWORD = '12345678';

    private const BUYER_PHONE = '+971500000001';

    private const OTP = '123456';

    private const PRODUCT_PRICE = 1000;

    private const SHIPPING_COST = 20;

    /**
     * @var array<string, string|int>
     */
    private const SETTINGS = [
        'business_mode' => 'single',
        'wallet_status' => 1,
        'loyalty_point_status' => 1,
        'loyalty_point_exchange_rate' => 10,
        'loyalty_point_minimum_point' => 100,
        'ref_earning_status' => 1,
        'ref_earning_exchange_rate' => 50,
        'refund_day_limit' => 30,
        'blog_feature_active_status' => 1,
    ];

    /**
     * @var array<int, array<int, string>>
     */
    private array $rows = [];

    public function run(): void
    {
        $previousSettings = $this->enableSettings();

        $buyer = $this->seedBuyer();
        $adminId = Admin::orderBy('id')->value('id');
        $shop = Shop::findOrFail(getInHouseShopConfig(key: 'id'));
        $brands = $this->seedBrands();
        $products = $this->seedProducts(adminId: $adminId, shop: $shop, brands: $brands);
        $flashDealId = $this->seedFlashDeal(products: $products);
        $this->seedClearanceSale(adminId: $adminId, shop: $shop, products: $products);
        $deliveryMan = $this->seedDeliveryMan();
        $address = $this->seedAddress(buyer: $buyer);
        $orders = $this->seedOrders(buyer: $buyer, adminId: $adminId, products: $products, address: $address, deliveryMan: $deliveryMan);
        $this->seedReviewsAndRefunds(buyer: $buyer, orders: $orders, deliveryMan: $deliveryMan);
        $this->seedWishlist(buyer: $buyer, products: $products);
        $this->seedCart(buyer: $buyer, adminId: $adminId, shop: $shop, products: $products);
        $this->seedCoupons();
        $ticketId = $this->seedSupportTickets(buyer: $buyer);
        $this->seedWalletAndLoyalty(buyer: $buyer);
        $this->seedReferral(buyer: $buyer);
        $this->seedAuthTokens(buyer: $buyer);
        $blog = $this->seedBlog();

        cacheRemoveByType(type: 'business_settings');
        cacheRemoveByType(type: 'products');

        $this->collectRows(
            buyer: $buyer,
            shop: $shop,
            brands: $brands,
            products: $products,
            flashDealId: $flashDealId,
            orders: $orders,
            address: $address,
            ticketId: $ticketId,
            blog: $blog,
        );

        $this->printReport(previousSettings: $previousSettings, buyer: $buyer, orders: $orders);
    }

    /**
     * @return array<string, mixed>
     */
    private function enableSettings(): array
    {
        $previous = [];

        foreach (self::SETTINGS as $type => $value) {
            $previous[$type] = BusinessSetting::where('type', $type)->value('value');
            BusinessSetting::updateOrInsert(['type' => $type], ['value' => $value, 'updated_at' => now()]);
        }

        return $previous;
    }

    private function seedBuyer(): User
    {
        return User::updateOrCreate(
            ['email' => 'buyer@gmail.com'],
            [
                'name' => 'Seafood Buyer',
                'f_name' => 'Seafood',
                'l_name' => 'Buyer',
                'phone' => self::BUYER_PHONE,
                'password' => Hash::make(self::PASSWORD),
                'is_active' => 1,
                'is_phone_verified' => 1,
                'is_email_verified' => 1,
                'email_verified_at' => now(),
                'wallet_balance' => 1500,
                'loyalty_point' => 5000,
                'referral_code' => 'SEAFOODBUYER',
                'login_medium' => 'general',
            ],
        );
    }

    /**
     * @return array<string, Brand>
     */
    private function seedBrands(): array
    {
        $brands = [];

        foreach (['Gulf Catch', 'Arabian Sea Farms', 'Oman Ocean'] as $name) {
            $brands[$name] = Brand::updateOrCreate(
                ['slug' => str($name)->slug()->toString()],
                ['name' => $name, 'status' => 1, 'image' => 'def.png'],
            );
        }

        return $brands;
    }

    /**
     * @param  array<string, Brand>  $brands
     * @return array<string, Product>
     */
    private function seedProducts(int $adminId, Shop $shop, array $brands): array
    {
        $brandIds = array_values(array_map(static fn (Brand $brand): int => $brand->id, $brands));
        $category = Product::withoutGlobalScopes()->where('added_by', 'admin')->whereNotNull('category_id')->first();

        $definitions = [
            'hamour' => ['name' => 'Fresh Hamour Fillet', 'brand' => $brandIds[0], 'featured' => 1, 'discount' => 100, 'image' => 'sea-bass-whole.webp'],
            'kingfish' => ['name' => 'Kingfish Steaks', 'brand' => $brandIds[1], 'featured' => 1, 'discount' => 0, 'image' => 'sea-bream-whole.webp'],
            'shrimp' => ['name' => 'Jumbo Gulf Shrimp', 'brand' => $brandIds[2], 'featured' => 0, 'discount' => 150, 'image' => 'whole-fish-on-ice.webp'],
            'lobster' => ['name' => 'Omani Lobster', 'brand' => $brandIds[0], 'featured' => 0, 'discount' => 0, 'image' => 'whole-fish-on-ice.webp'],
        ];

        $products = [];

        foreach ($definitions as $key => $definition) {
            $products[$key] = $this->upsertProduct(
                code: 'SEED-ADMIN-'.strtoupper($key),
                adminId: $adminId,
                shop: $shop,
                category: $category,
                attributes: [
                    'name' => $definition['name'],
                    'slug' => str($definition['name'])->slug()->toString(),
                    'product_type' => 'physical',
                    'digital_product_type' => null,
                    'digital_file_ready' => null,
                    'brand_id' => $definition['brand'],
                    'featured' => $definition['featured'],
                    'discount' => $definition['discount'],
                    'current_stock' => 50,
                    'thumbnail' => $definition['image'],
                    'images' => json_encode([['image_name' => $definition['image'], 'storage' => 'public']]),
                ],
            );
        }

        $digitalFile = 'seafood-recipe-guide.txt';
        Storage::disk('public')->put('product/digital-product/'.$digitalFile, 'Seafood recipe guide — seeded demo download.');

        $products['guide'] = $this->upsertProduct(
            code: 'SEED-ADMIN-GUIDE',
            adminId: $adminId,
            shop: $shop,
            category: $category,
            attributes: [
                'name' => 'Seafood Recipe Guide (Digital)',
                'slug' => 'seafood-recipe-guide-digital',
                'product_type' => 'digital',
                'digital_product_type' => 'ready_product',
                'digital_file_ready' => $digitalFile,
                'digital_file_ready_storage_type' => 'public',
                'brand_id' => null,
                'featured' => 0,
                'discount' => 0,
                'current_stock' => 0,
                'thumbnail' => 'whole-fish-on-ice.webp',
                'images' => json_encode([['image_name' => 'whole-fish-on-ice.webp', 'storage' => 'public']]),
            ],
        );

        return $products;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsertProduct(string $code, int $adminId, Shop $shop, ?Product $category, array $attributes): Product
    {
        return Product::withoutGlobalScopes()->updateOrCreate(
            ['code' => $code],
            array_merge([
                'added_by' => 'admin',
                'user_id' => $adminId,
                'shop_id' => $shop->id,
                'category_ids' => $category?->category_ids ?? json_encode([]),
                'category_id' => $category?->category_id,
                'sub_category_id' => $category?->sub_category_id,
                'unit' => 'kg',
                'min_qty' => 1,
                'refundable' => 1,
                'details' => '<p>Seeded in-house product for storefront previews.</p>',
                'colors' => json_encode([]),
                'choice_options' => json_encode([]),
                'attributes' => json_encode([]),
                'variation' => json_encode([]),
                'color_image' => json_encode([]),
                'unit_price' => self::PRODUCT_PRICE,
                'purchase_price' => 0,
                'tax' => 0,
                'tax_type' => 'percent',
                'tax_model' => 'exclude',
                'discount_type' => 'flat',
                'minimum_order_qty' => 1,
                'free_shipping' => 0,
                'status' => 1,
                'request_status' => 1,
                'featured_status' => 1,
                'published' => 1,
                'shipping_cost' => 0,
                'multiply_qty' => 0,
                'thumbnail_storage_type' => 'public',
                'video_provider' => 'youtube',
            ], $attributes),
        );
    }

    /**
     * Existing demo deals ended on 2026-08-25, so their window is moved to
     * today + 30 days and the seeded products are attached to the flash deal.
     *
     * @param  array<string, Product>  $products
     */
    private function seedFlashDeal(array $products): int
    {
        FlashDeal::whereIn('deal_type', ['flash_deal', 'feature_deal'])
            ->where('end_date', '<', now()->toDateString())
            ->update(['start_date' => now()->subDay()->toDateString(), 'end_date' => now()->addDays(30)->toDateString(), 'status' => 1]);

        $flashDeal = FlashDeal::firstOrCreate(
            ['slug' => 'seafood-flash-deal'],
            [
                'title' => 'Seafood Flash Deal',
                'start_date' => now()->subDay()->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'status' => 1,
                'featured' => 0,
                'deal_type' => 'flash_deal',
                'background_color' => '#0b3b5c',
                'text_color' => '#ffffff',
                'banner' => 'def.png',
            ],
        );

        foreach (['hamour', 'shrimp'] as $key) {
            FlashDealProduct::updateOrCreate(
                ['flash_deal_id' => $flashDeal->id, 'product_id' => $products[$key]->id],
                ['discount' => 10, 'discount_type' => 'percentage'],
            );
        }

        return $flashDeal->id;
    }

    /**
     * @param  array<string, Product>  $products
     */
    private function seedClearanceSale(int $adminId, Shop $shop, array $products): void
    {
        $setup = StockClearanceSetup::updateOrCreate(
            ['setup_by' => 'admin'],
            [
                'shop_id' => $shop->id,
                'user_id' => $adminId,
                'is_active' => 1,
                'discount_type' => 'product_wise',
                'discount_amount' => 0,
                'offer_active_time' => 'always',
                'offer_active_range_start' => null,
                'offer_active_range_end' => null,
                'show_in_homepage' => 1,
                'show_in_shop' => 1,
                'duration_start_date' => now()->subDay(),
                'duration_end_date' => now()->addDays(30),
            ],
        );

        foreach (['kingfish', 'lobster'] as $key) {
            StockClearanceProduct::updateOrCreate(
                ['setup_id' => $setup->id, 'product_id' => $products[$key]->id],
                [
                    'added_by' => 'admin',
                    'user_id' => $adminId,
                    'shop_id' => $shop->id,
                    'is_active' => 1,
                    'discount_type' => 'flat',
                    'discount_amount' => 200,
                ],
            );
        }
    }

    private function seedDeliveryMan(): DeliveryMan
    {
        return DeliveryMan::updateOrCreate(
            ['email' => 'deliveryman@gmail.com'],
            [
                'seller_id' => 0,
                'f_name' => 'Omar',
                'l_name' => 'Rider',
                'country_code' => '971',
                'phone' => '500000003',
                'full_phone' => '+971500000003',
                'password' => Hash::make(self::PASSWORD),
                'address' => 'Al Qusais, Dubai',
                'is_active' => 1,
                'image' => 'def.png',
            ],
        );
    }

    private function seedAddress(User $buyer): ShippingAddress
    {
        return ShippingAddress::updateOrCreate(
            ['customer_id' => $buyer->id, 'is_guest' => 0, 'address_type' => 'home'],
            [
                'contact_person_name' => 'Seafood Buyer',
                'email' => 'buyer@gmail.com',
                'address' => 'Villa 12, Jumeirah Beach Road',
                'city' => 'Dubai',
                'zip' => '00000',
                'phone' => self::BUYER_PHONE,
                'country' => 'United Arab Emirates',
                'latitude' => '25.2048',
                'longitude' => '55.2708',
                'is_billing' => 0,
            ],
        );
    }

    /**
     * @param  array<string, Product>  $products
     * @return array<string, Order>
     */
    private function seedOrders(User $buyer, int $adminId, array $products, ShippingAddress $address, DeliveryMan $deliveryMan): array
    {
        $scenarios = [
            'delivered' => ['status' => 'delivered', 'payment' => 'paid', 'items' => ['hamour', 'kingfish'], 'delivery_man' => true, 'days_ago' => 3],
            'pending' => ['status' => 'pending', 'payment' => 'unpaid', 'items' => ['shrimp'], 'delivery_man' => false, 'days_ago' => 0],
            'out_for_delivery' => ['status' => 'out_for_delivery', 'payment' => 'unpaid', 'items' => ['lobster'], 'delivery_man' => true, 'days_ago' => 1],
            'digital' => ['status' => 'delivered', 'payment' => 'paid', 'items' => ['guide'], 'delivery_man' => false, 'days_ago' => 2],
            'canceled' => ['status' => 'canceled', 'payment' => 'unpaid', 'items' => ['kingfish'], 'delivery_man' => false, 'days_ago' => 5],
        ];

        $orders = [];

        foreach ($scenarios as $key => $scenario) {
            $isDigital = $key === 'digital';
            $itemTotal = count($scenario['items']) * self::PRODUCT_PRICE;
            $shippingCost = $isDigital ? 0 : self::SHIPPING_COST;
            $createdAt = now()->subDays($scenario['days_ago']);

            $order = Order::updateOrCreate(
                ['order_group_id' => 'seed-buyer-'.$key],
                [
                    'customer_id' => $buyer->id,
                    'is_guest' => 0,
                    'customer_type' => 'customer',
                    'payment_status' => $scenario['payment'],
                    'order_status' => $scenario['status'],
                    'payment_method' => $isDigital ? 'wallet' : 'cash_on_delivery',
                    'order_amount' => $itemTotal + $shippingCost,
                    'init_order_amount' => $itemTotal + $shippingCost,
                    'paid_amount' => $scenario['payment'] === 'paid' ? $itemTotal + $shippingCost : 0,
                    'admin_commission' => 0,
                    'total_tax_amount' => 0,
                    'discount_amount' => 0,
                    'shipping_cost' => $shippingCost,
                    'is_shipping_free' => 0,
                    'shipping_method_id' => 0,
                    'shipping_type' => 'order_wise',
                    'shipping_responsibility' => 'inhouse_shipping',
                    'shipping_address' => $isDigital ? null : $address->id,
                    'shipping_address_data' => $isDigital ? null : $address->toArray(),
                    'billing_address' => $address->id,
                    'billing_address_data' => $address->toArray(),
                    'seller_id' => $adminId,
                    'seller_is' => 'admin',
                    'delivery_man_id' => $scenario['delivery_man'] ? $deliveryMan->id : null,
                    'deliveryman_assigned_at' => $scenario['delivery_man'] ? $createdAt : null,
                    'delivery_type' => $scenario['delivery_man'] ? 'self_delivery' : null,
                    'verification_code' => '123456',
                    'order_type' => 'default_type',
                    'checked' => 1,
                    'order_note' => 'Seeded '.$key.' order',
                    'cause' => $key === 'canceled' ? 'Changed my mind' : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ],
            );

            foreach ($scenario['items'] as $productKey) {
                $product = $products[$productKey];

                OrderDetail::updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $product->id],
                    [
                        'seller_id' => $adminId,
                        'product_details' => json_encode($product->toArray()),
                        'qty' => 1,
                        'price' => self::PRODUCT_PRICE,
                        'tax' => 0,
                        'discount' => 0,
                        'tax_model' => 'exclude',
                        'delivery_status' => $scenario['status'],
                        'payment_status' => $scenario['payment'],
                        'variant' => '',
                        'variation' => json_encode([]),
                        'discount_type' => 'discount_on_product',
                        'is_stock_decreased' => 1,
                        'refund_request' => 0,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ],
                );
            }

            foreach (array_unique(['pending', 'confirmed', $scenario['status']]) as $status) {
                if ($status === 'confirmed' && in_array($scenario['status'], ['pending', 'canceled'], true)) {
                    continue;
                }

                OrderStatusHistory::firstOrCreate(
                    ['order_id' => $order->id, 'status' => $status],
                    ['user_id' => $buyer->id, 'user_type' => 'customer'],
                );
            }

            $orders[$key] = $order->fresh(['details']);
        }

        return $orders;
    }

    /**
     * @param  array<string, Order>  $orders
     */
    private function seedReviewsAndRefunds(User $buyer, array $orders, DeliveryMan $deliveryMan): void
    {
        $delivered = $orders['delivered'];
        $reviewedDetail = $delivered->details->first();
        $refundedDetail = $delivered->details->last();

        Review::updateOrCreate(
            ['product_id' => $reviewedDetail->product_id, 'customer_id' => $buyer->id, 'order_id' => $delivered->id, 'delivery_man_id' => null],
            ['comment' => 'Very fresh, arrived on ice. Will order again.', 'rating' => 5, 'status' => 1, 'attachment' => []],
        );

        Review::updateOrCreate(
            ['product_id' => $reviewedDetail->product_id, 'customer_id' => $buyer->id, 'order_id' => $delivered->id, 'delivery_man_id' => $deliveryMan->id],
            ['comment' => 'Polite rider, delivered on time.', 'rating' => 4, 'status' => 1],
        );

        RefundRequest::updateOrCreate(
            ['order_details_id' => $refundedDetail->id],
            [
                'customer_id' => $buyer->id,
                'status' => 'pending',
                'amount' => self::PRODUCT_PRICE,
                'product_id' => $refundedDetail->product_id,
                'order_id' => $delivered->id,
                'refund_reason' => 'Package arrived partially thawed.',
                'images' => [],
            ],
        );

        $refundedDetail->update(['refund_request' => 1, 'refund_started_at' => now()]);
    }

    /**
     * @param  array<string, Product>  $products
     */
    private function seedWishlist(User $buyer, array $products): void
    {
        foreach (['hamour', 'kingfish', 'shrimp'] as $key) {
            Wishlist::firstOrCreate(['customer_id' => $buyer->id, 'product_id' => $products[$key]->id]);
        }
    }

    /**
     * @param  array<string, Product>  $products
     */
    private function seedCart(User $buyer, int $adminId, Shop $shop, array $products): void
    {
        $cartGroupId = Cart::where(['customer_id' => $buyer->id, 'is_guest' => 0, 'seller_id' => $adminId, 'seller_is' => 'admin'])
            ->value('cart_group_id') ?? $buyer->id.'-seed1-'.time();

        foreach (['hamour' => 2, 'lobster' => 1] as $key => $quantity) {
            $product = $products[$key];

            Cart::updateOrCreate(
                ['customer_id' => $buyer->id, 'is_guest' => 0, 'product_id' => $product->id],
                [
                    'cart_group_id' => $cartGroupId,
                    'product_type' => 'physical',
                    'color' => null,
                    'choices' => json_encode([]),
                    'variations' => json_encode([]),
                    'variant' => '',
                    'quantity' => $quantity,
                    'price' => self::PRODUCT_PRICE,
                    'tax' => 0,
                    'discount' => $product->discount,
                    'tax_model' => 'exclude',
                    'is_checked' => 1,
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'thumbnail' => $product->thumbnail,
                    'seller_id' => $adminId,
                    'seller_is' => 'admin',
                    'shop_info' => $shop->name,
                    'shipping_cost' => 0,
                    'shipping_type' => 'order_wise',
                ],
            );
        }

        CartShipping::updateOrCreate(
            ['cart_group_id' => $cartGroupId],
            ['shipping_method_id' => 0, 'shipping_cost' => self::SHIPPING_COST],
        );
    }

    private function seedCoupons(): void
    {
        $coupons = [
            'SEAFOOD100' => ['title' => 'AED 100 off seafood', 'type' => 'discount_on_purchase', 'discount' => 100, 'discount_type' => 'amount'],
            'FIRSTCATCH' => ['title' => 'First order 15% off', 'type' => 'first_order', 'discount' => 15, 'discount_type' => 'percentage'],
            'GULFFREE' => ['title' => 'Free delivery on seafood', 'type' => 'free_delivery', 'discount' => 0, 'discount_type' => 'amount'],
        ];

        foreach ($coupons as $code => $coupon) {
            Coupon::updateOrCreate(
                ['code' => $code],
                [
                    'added_by' => 'admin',
                    'coupon_type' => $coupon['type'],
                    'coupon_bearer' => 'inhouse',
                    'seller_id' => null,
                    'customer_id' => 0,
                    'title' => $coupon['title'],
                    'start_date' => now()->subDay()->toDateString(),
                    'expire_date' => now()->addDays(30)->toDateString(),
                    'min_purchase' => 500,
                    'max_discount' => 300,
                    'discount' => $coupon['discount'],
                    'discount_type' => $coupon['discount_type'],
                    'status' => 1,
                    'limit' => 5,
                ],
            );
        }
    }

    private function seedSupportTickets(User $buyer): int
    {
        $tickets = [
            'Late delivery on my hamour order' => ['type' => 'Delivery problem', 'priority' => 'High', 'status' => 'open'],
            'Wrong item received' => ['type' => 'Partner request', 'priority' => 'Medium', 'status' => 'close'],
        ];

        $firstTicketId = 0;

        foreach ($tickets as $subject => $ticket) {
            $model = SupportTicket::updateOrCreate(
                ['customer_id' => $buyer->id, 'subject' => $subject],
                ['type' => $ticket['type'], 'priority' => $ticket['priority'], 'description' => $subject.' — seeded ticket.', 'status' => $ticket['status']],
            );

            SupportTicketConv::firstOrCreate(
                ['support_ticket_id' => $model->id, 'customer_message' => 'Hello, any update on this?'],
                ['position' => 0],
            );
            SupportTicketConv::firstOrCreate(
                ['support_ticket_id' => $model->id, 'admin_message' => 'We are checking with the rider and will reply shortly.'],
                ['admin_id' => 1, 'position' => 1],
            );

            $firstTicketId = $firstTicketId ?: $model->id;
        }

        return $firstTicketId;
    }

    private function seedWalletAndLoyalty(User $buyer): void
    {
        $walletEntries = [
            'seed-wallet-1' => ['credit' => 1000, 'debit' => 0, 'balance' => 1000, 'type' => 'add_fund', 'reference' => 'add_funds_to_wallet'],
            'seed-wallet-2' => ['credit' => 1500, 'debit' => 0, 'balance' => 2500, 'type' => 'refund_order', 'reference' => 'order_refund'],
            'seed-wallet-3' => ['credit' => 0, 'debit' => 1000, 'balance' => 1500, 'type' => 'order_place', 'reference' => 'order_payment'],
        ];

        foreach ($walletEntries as $transactionId => $entry) {
            WalletTransaction::updateOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'user_id' => $buyer->id,
                    'credit' => $entry['credit'],
                    'debit' => $entry['debit'],
                    'admin_bonus' => 0,
                    'balance' => $entry['balance'],
                    'transaction_type' => $entry['type'],
                    'reference' => $entry['reference'],
                ],
            );
        }

        $loyaltyEntries = [
            'seed-loyalty-1' => ['credit' => 5500, 'debit' => 0, 'balance' => 5500, 'type' => 'order_place'],
            'seed-loyalty-2' => ['credit' => 0, 'debit' => 500, 'balance' => 5000, 'type' => 'point_to_wallet'],
        ];

        foreach ($loyaltyEntries as $transactionId => $entry) {
            LoyaltyPointTransaction::updateOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'user_id' => $buyer->id,
                    'credit' => $entry['credit'],
                    'debit' => $entry['debit'],
                    'balance' => $entry['balance'],
                    'transaction_type' => $entry['type'],
                    'reference' => 'seeded',
                ],
            );
        }
    }

    private function seedReferral(User $buyer): void
    {
        User::updateOrCreate(
            ['email' => 'referred.friend@gmail.com'],
            [
                'name' => 'Referred Friend',
                'f_name' => 'Referred',
                'l_name' => 'Friend',
                'phone' => '+971500000004',
                'password' => Hash::make(self::PASSWORD),
                'is_active' => 1,
                'referred_by' => $buyer->id,
                'referral_code' => 'SEAFOODFRIEND',
            ],
        );
    }

    private function seedAuthTokens(User $buyer): void
    {
        PasswordReset::updateOrCreate(
            ['identity' => $buyer->email, 'user_type' => 'customer'],
            ['token' => self::OTP, 'otp_hit_count' => 0, 'is_temp_blocked' => 0, 'expires_at' => now()->addDay(), 'created_at' => now()],
        );

        foreach ([$buyer->email, $buyer->phone] as $identity) {
            PhoneOrEmailVerification::updateOrCreate(
                ['phone_or_email' => $identity],
                ['token' => self::OTP, 'otp_hit_count' => 0, 'is_temp_blocked' => 0, 'expires_at' => now()->addDay()],
            );
        }
    }

    private function seedBlog(): Blog
    {
        $category = BlogCategory::updateOrCreate(
            ['slug' => 'seafood-recipes'],
            ['name' => 'Seafood Recipes', 'status' => 1],
        );

        $posts = [
            'how-to-choose-fresh-fish' => ['title' => 'How to Choose Fresh Fish', 'clicks' => 120],
            'grilled-hamour-with-lemon' => ['title' => 'Grilled Hamour with Lemon and Sumac', 'clicks' => 80],
            'storing-shrimp-safely' => ['title' => 'Storing Shrimp Safely at Home', 'clicks' => 40],
        ];

        $first = null;
        $readableId = 900001;

        foreach ($posts as $slug => $post) {
            $blog = Blog::withoutGlobalScopes()->updateOrCreate(
                ['slug' => $slug],
                [
                    'readable_id' => $readableId++,
                    'category_id' => $category->id,
                    'writer' => 'Seafood Kitchen',
                    'title' => $post['title'],
                    'description' => $this->blogArticleBody(title: $post['title']),
                    'image' => 'def.png',
                    'image_storage_type' => 'public',
                    'publish_date' => now()->subDays(2),
                    'is_published' => 1,
                    'status' => 1,
                    'is_draft' => 0,
                    'click_count' => $post['clicks'],
                ],
            );

            $first ??= $blog;
        }

        return $first;
    }

    /**
     * Several <h2> sections so the blog details page renders its "In this article" navigation.
     */
    private function blogArticleBody(string $title): string
    {
        $sections = [
            'Look at the eyes and gills' => 'Fresh fish has clear, bright eyes and red gills. Cloudy eyes or brown gills mean it has been out of the water too long.',
            'Press the flesh' => 'The flesh should spring back when pressed. A dent that stays is a sign the fish is past its best.',
            'Trust your nose' => 'Fresh seafood smells of the sea, never sour or strongly fishy.',
            'Store it cold, cook it soon' => 'Keep seafood on ice in the coldest part of the fridge and cook it within one or two days.',
        ];

        $body = '<p>'.$title.' — seeded article for storefront previews.</p>';
        foreach ($sections as $heading => $paragraph) {
            $body .= '<h2>'.$heading.'</h2>'.str_repeat('<p>'.$paragraph.'</p>', 3);
        }

        return $body;
    }

    /**
     * @param  array<string, Brand>  $brands
     * @param  array<string, Product>  $products
     * @param  array<string, Order>  $orders
     */
    private function collectRows(User $buyer, Shop $shop, array $brands, array $products, int $flashDealId, array $orders, ShippingAddress $address, int $ticketId, Blog $blog): void
    {
        $guest = '-';
        $login = 'buyer@gmail.com / '.self::PASSWORD;
        $delivered = $orders['delivered'];
        $reviewedDetail = $delivered->details->first();
        $refundedDetail = $delivered->details->last();
        $brand = reset($brands);

        $pages = [
            ['All products', 'seeded products=5', $guest, '/products'],
            ['Featured products', 'featured=hamour,kingfish', $guest, '/featured-products'],
            ['Latest products', 'seeded products newest', $guest, '/latest-products'],
            ['Best-selling products', 'order_details rows', $guest, '/best-selling-products'],
            ['Top-rated products', 'review rating=5', $guest, '/top-rated-products'],
            ['Most-favorite products', 'wishlist=3', $guest, '/most-favorite-products'],
            ['Discounted products', 'hamour -100, shrimp -150', $guest, '/discounted-products'],
            ['Clearance-sale products', 'kingfish, lobster -200', $guest, '/clearance-sale-products'],
            ['Brand products', "brand={$brand->id}", $guest, "/brand/{$brand->slug}"],
            ['All brands', 'brands=3', $guest, '/brands'],
            ['Flash deal', "deal={$flashDealId}", $guest, "/flash-deals/{$flashDealId}"],
            ['In-house shop', "shop={$shop->id}", $guest, "/vendor-shop/{$shop->slug}"],
            ['Blog list', 'blogs=3', $guest, '/blog'],
            ['Popular blogs', 'click_count 120/80/40', $guest, '/popular-blog'],
            ['Blog details', "blog={$blog->id}", $guest, "/blog/{$blog->slug}"],
            ['Cart', 'hamour x2, lobster x1', $login, '/shop-cart'],
            ['Checkout details', "address={$address->id}", $login, '/checkout-details'],
            ['Track order form', $guest, $guest, '/track-order'],
            ['Track order result', "order={$delivered->id}", $guest, "/track-order/result?order_id={$delivered->id}&phone_number=".urlencode(self::BUYER_PHONE)],
            ['Digital download', "order={$orders['digital']->id} paid", $guest, "/digital-product-download-pos?order_id={$orders['digital']->id}&email=buyer@gmail.com"],
            ['OTP verification', 'otp='.self::OTP, $guest, '/customer/auth/otp-verification?identity=buyer@gmail.com'],
            ['Reset password', 'token='.self::OTP, $guest, '/customer/auth/reset-password?token='.self::OTP.'&identity='.base64_encode($buyer->email)],
            ['Login verify phone', 'otp='.self::OTP, $guest, '/customer/auth/login/verify-account?identity='.base64_encode($buyer->phone)],
            ['Login update info', $guest, $guest, '/customer/auth/login/update-info?identity='.base64_encode($buyer->phone)],
            ['Order details (delivered)', "order={$delivered->id}", $login, "/account-order-details?id={$delivered->id}"],
            ['Order details (pending)', "order={$orders['pending']->id}", $login, "/account-order-details?id={$orders['pending']->id}"],
            ['Order details (on the way)', "order={$orders['out_for_delivery']->id}", $login, "/account-order-details?id={$orders['out_for_delivery']->id}"],
            ['Order details (canceled)', "order={$orders['canceled']->id}", $login, "/account-order-details?id={$orders['canceled']->id}"],
            ['Order details (digital)', "order={$orders['digital']->id}", $login, "/account-order-details?id={$orders['digital']->id}"],
            ['Order shop info', "order={$delivered->id}", $login, "/account-order-details-vendor-info?id={$delivered->id}"],
            ['Order delivery-man info', "order={$delivered->id}", $login, "/account-order-details-delivery-man-info?id={$delivered->id}"],
            ['Order tracking tab', "order={$delivered->id}", $login, "/track-order/order-wise-result-view?order_id={$delivered->id}"],
            ['Order reviews', "detail={$reviewedDetail->id} rated", $login, "/account-order-details-reviews?id={$delivered->id}"],
            ['Edit address', "address={$address->id}", $login, "/account-address-edit/{$address->id}"],
            ['Refund request form', "detail={$reviewedDetail->id}", $login, "/refund-request/{$reviewedDetail->id}"],
            ['Refund details', "detail={$refundedDetail->id} pending", $login, "/refund-details/{$refundedDetail->id}"],
            ['Support tickets', 'tickets=2 open/close', $login, '/account-tickets'],
            ['Single ticket', "ticket={$ticketId}", $login, "/support-ticket/{$ticketId}"],
            ['Coupons', 'SEAFOOD100, FIRSTCATCH, GULFFREE', $login, '/user-coupons'],
            ['Refer & earn', 'code=SEAFOODBUYER', $login, '/refer-earn'],
            ['Wallet', 'balance=1500 tx=3', $login, '/wallet'],
            ['Loyalty', 'points=5000 tx=2', $login, '/loyalty'],
        ];

        foreach ($pages as $index => $page) {
            $this->rows[] = [$index + 1, $page[0], $page[1], $page[2], self::BASE_URL.$page[3]];
        }
    }

    /**
     * @param  array<string, mixed>  $previousSettings
     * @param  array<string, Order>  $orders
     */
    private function printReport(array $previousSettings, User $buyer, array $orders): void
    {
        $this->command->newLine();
        $this->command->info('Storefront pages (legacy layout) — seeded scenarios');
        $this->command->table(['#', 'Page', 'Key fields', 'Login', 'URL'], $this->rows);

        $this->command->newLine();
        $this->command->info('Accounts (password: '.self::PASSWORD.')');
        $this->command->line("  buyer:        buyer@gmail.com  id={$buyer->id} phone=".self::BUYER_PHONE);
        $this->command->line('  delivery man: deliveryman@gmail.com');
        $this->command->line('  OTP / reset token: '.self::OTP);

        $this->command->newLine();
        $this->command->info('Orders by status');
        foreach ($orders as $key => $order) {
            $this->command->line("  {$key}: id={$order->id} status={$order->order_status} payment={$order->payment_status} items={$order->details->count()}");
        }

        $this->command->newLine();
        $this->command->warn('Flow-only pages (need session, cannot be opened by URL):');
        $this->command->line('  /checkout-payment      → /shop-cart → Proceed → pick address on /checkout-details → Continue');
        $this->command->line('  /checkout-complete     → choose Cash on delivery on /checkout-payment → Place order');
        $this->command->line('  /order-placed-success  → shown after placing an order');
        $this->command->line('  submit review forms    → Order reviews page → "Review" button on an unreviewed item');

        $this->command->newLine();
        $this->command->warn('Settings changed (revert values shown):');
        foreach (self::SETTINGS as $type => $value) {
            $previous = $previousSettings[$type] ?? null;
            $revert = $previous === null ? 'row did not exist — delete it to revert' : "was: {$previous}";
            $this->command->line("  {$type}: {$value}  ({$revert})");
        }

        $this->command->newLine();
        $this->command->comment('Then run: php artisan optimize:clear');
        $this->command->comment('Re-run:   php artisan db:seed --class="Database\Seeders\StorefrontPagesScenarioSeeder"');
    }
}
