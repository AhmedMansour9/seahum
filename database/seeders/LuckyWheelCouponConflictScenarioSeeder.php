<?php

namespace Database\Seeders;

use App\Enums\LuckyWheel\LuckyWheelRewardType;
use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Models\Cart;
use App\Models\CartShipping;
use App\Models\Coupon;
use App\Models\LuckyWheelReward;
use App\Models\LuckyWheelSpin;
use App\Models\LuckyWheelWonGift;
use App\Models\OfflinePaymentMethod;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ReferralCustomer;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\LuckyWheelService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds one customer per branch of the discount coupon / lucky wheel gift rule (order refused, coupon allowed,
 * stale web session coupon) and prints every row, login and ready-to-send request. Safe to re-run: each run puts
 * the scenario customers' gifts, carts and orders back into their scenario state.
 */
class LuckyWheelCouponConflictScenarioSeeder extends Seeder
{
    private const PASSWORD = '12345678';
    private const EMAIL_PREFIX = 'lw-cc-';
    private const EMAIL_DOMAIN = '@seahum.test';
    private const QA_PRODUCT_SLUG_PREFIX = 'qa-lucky-wheel-item-';
    private const DISCOUNT_COUPON = 'LW-QA-DISCOUNT';
    private const FREE_DELIVERY_COUPON = 'LW-QA-FREESHIP';
    private const OWN_MINIMUM_COUPON = 'LW-QA-MIN5000';
    private const CONFLICT_MESSAGE_EN = "This discount coupon can't be combined with your Lucky Wheel gift. Please remove the coupon and try again.";
    private const CONFLICT_MESSAGE_EG = 'لا يمكن الجمع بين كوبون الخصم وهدية عجلة الحظ. يرجى إزالة الكوبون والمحاولة مرة أخرى.';

    private Carbon $businessToday;
    private Product $orderProduct;
    private LuckyWheelReward $giftReward;
    private ?ShippingMethod $shippingMethod;
    private array $rows = [];
    private array $requests = [];

    public function run(): void
    {
        $this->call(LuckyWheelSeeder::class);

        $luckyWheelService = app(LuckyWheelService::class);
        $this->businessToday = $luckyWheelService->getBusinessNow()->startOfDay();
        $this->orderProduct = Product::where(['added_by' => 'admin', 'status' => 1])->orderBy('id')->firstOrFail();
        $this->giftReward = LuckyWheelReward::where('type', LuckyWheelRewardType::Gift)->orderBy('display_order')->orderBy('id')->firstOrFail();
        $this->shippingMethod = ShippingMethod::where('status', 1)->where('cost', '>', 0)->orderBy('id')->first();
        if (!$this->shippingMethod) {
            $this->command->warn('No active shipping method with a cost: checkout validation and the free delivery scenario will not work.');
        }

        $this->seedCoupon(code: self::DISCOUNT_COUPON, title: '[QA] Lucky wheel 100 off', couponType: 'discount_on_purchase', discount: 100, minimumPurchase: 0);
        $this->seedCoupon(code: self::FREE_DELIVERY_COUPON, title: '[QA] Lucky wheel free delivery', couponType: 'free_delivery', discount: 0, minimumPurchase: 0);
        $this->seedCoupon(code: self::OWN_MINIMUM_COUPON, title: '[QA] 100 off over 5000', couponType: 'discount_on_purchase', discount: 100, minimumPurchase: 5000);

        $this->seedConflictScenarios();
        $this->seedAllowedCouponScenarios();
        $this->seedStaleWebCouponScenarios();

        $this->printReport(minimumOrderAmount: $luckyWheelService->getMinimumOrderAmount());
    }

    /**
     * One customer whose cart takes the gift: every order entry point refuses the discount coupon, nothing is saved,
     * so the same customer serves all five requests and can repeat them.
     */
    private function seedConflictScenarios(): void
    {
        $customer = $this->scenarioCustomer(key: 'conflict', firstName: 'Coupon Conflict', walletBalance: 5000);
        $gift = $this->seedPendingGift(customer: $customer);
        $address = $this->seedAddress(customer: $customer);
        $cartItem = $this->seedCart(customer: $customer, price: '1200');
        $orderQuery = $this->orderQuery(couponCode: self::DISCOUNT_COUPON, address: $address);
        $ids = "user={$customer->id} gift={$gift->id} cart={$cartItem->id} address={$address->id}";
        $keyFields = 'gift=pending, cart=1200, coupon='.self::DISCOUNT_COUPON;
        $expected = '403 errors[0].code='.LuckyWheelService::DISCOUNT_COUPON_CONFLICT.', no order saved';

        $this->addRow('API cash on delivery refused', $ids, $keyFields, $customer, 'GET /customer/order/place',
            ['GET', '/customer/order/place?payment_method=cash_on_delivery&'.$orderQuery, 'en', null, $expected]);
        $this->addRow('API wallet order refused', $ids, $keyFields.', wallet=5000', $customer, 'GET /customer/order/place-by-wallet',
            ['GET', '/customer/order/place-by-wallet?'.$orderQuery, 'en', null, $expected.', wallet unchanged']);
        $this->addRow('API offline payment refused', $ids, $keyFields, $customer, 'POST /customer/order/place-by-offline-payment',
            ['POST', '/customer/order/place-by-offline-payment', 'en', $this->offlinePaymentBody(address: $address), $expected]);
        $this->addRow('App digital payment refused', $ids, $keyFields, $customer, 'POST /digital-payment',
            ['POST', '/digital-payment', 'en', $this->digitalPaymentBody(customer: $customer, address: $address), $expected.', no payment request']);
        $this->addRow('Refusal message in Arabic', $ids, $keyFields.', lang=eg', $customer, 'GET /customer/order/place (lang: eg)',
            ['GET', '/customer/order/place?payment_method=cash_on_delivery&'.$orderQuery, 'eg', null, '403 errors[0].message="'.self::CONFLICT_MESSAGE_EG.'"']);
    }

    /**
     * Branches where the gift rule does not refuse the coupon, so the existing coupon rules decide.
     */
    private function seedAllowedCouponScenarios(): void
    {
        $freeDelivery = $this->scenarioCustomer(key: 'freeship', firstName: 'Free Delivery');
        $freeDeliveryGift = $this->seedPendingGift(customer: $freeDelivery);
        $freeDeliveryAddress = $this->seedAddress(customer: $freeDelivery);
        $freeDeliveryCart = $this->seedCart(customer: $freeDelivery, price: '1200');
        $this->addRow('Free delivery coupon + gift allowed',
            "user={$freeDelivery->id} gift={$freeDeliveryGift->id} cart={$freeDeliveryCart->id}",
            'gift=pending, cart=1200, shipping='.($this->shippingMethod?->cost ?? 0).', coupon='.self::FREE_DELIVERY_COUPON,
            $freeDelivery, 'GET /coupon/apply, then GET /customer/order/place',
            ['GET', '/coupon/apply?code='.self::FREE_DELIVERY_COUPON, 'en', null,
                '200 coupon_type=free_delivery, coupon_discount='.($this->shippingMethod?->cost ?? 0).'; then placing GET /customer/order/place?payment_method=cash_on_delivery&'.$this->orderQuery(couponCode: self::FREE_DELIVERY_COUPON, address: $freeDeliveryAddress).' → 200 order_ids, gift attached (consumes the gift)']);

        $belowMinimum = $this->scenarioCustomer(key: 'below-min', firstName: 'Below Minimum');
        $belowMinimumGift = $this->seedPendingGift(customer: $belowMinimum);
        $this->seedAddress(customer: $belowMinimum);
        $belowMinimumCart = $this->seedCart(customer: $belowMinimum, price: '600');
        $this->addRow('Discount coupon, cart below gift min',
            "user={$belowMinimum->id} gift={$belowMinimumGift->id} cart={$belowMinimumCart->id}",
            'gift=pending, cart=600, coupon='.self::DISCOUNT_COUPON,
            $belowMinimum, 'GET /coupon/apply',
            ['GET', '/coupon/apply?code='.self::DISCOUNT_COUPON, 'en', null, '200 coupon_discount=100, coupon_type=discount_on_purchase (gift stays pending)']);

        $withoutGift = $this->scenarioCustomer(key: 'no-gift', firstName: 'No Gift');
        $this->seedAddress(customer: $withoutGift);
        $withoutGiftCart = $this->seedCart(customer: $withoutGift, price: '1200');
        $this->addRow('Discount coupon, no pending gift',
            "user={$withoutGift->id} cart={$withoutGiftCart->id}",
            'gift=none, cart=1200, coupon='.self::DISCOUNT_COUPON,
            $withoutGift, 'GET /coupon/apply',
            ['GET', '/coupon/apply?code='.self::DISCOUNT_COUPON, 'en', null, '200 coupon_discount=100, coupon_type=discount_on_purchase']);

        $ownRule = $this->scenarioCustomer(key: 'own-rule', firstName: 'Own Rule');
        $ownRuleGift = $this->seedPendingGift(customer: $ownRule);
        $ownRuleAddress = $this->seedAddress(customer: $ownRule);
        $ownRuleCart = $this->seedCart(customer: $ownRule, price: '1200');
        $this->addRow('Coupon fails its own minimum: no conflict',
            "user={$ownRule->id} gift={$ownRuleGift->id} cart={$ownRuleCart->id}",
            'gift=pending, cart=1200, coupon='.self::OWN_MINIMUM_COUPON.' (min_purchase=5000)',
            $ownRule, 'GET /coupon/apply',
            ['GET', '/coupon/apply?code='.self::OWN_MINIMUM_COUPON, 'en', null,
                '202 "Minimum purchase amount not reached for this coupon."; placing GET /customer/order/place?payment_method=cash_on_delivery&'.$this->orderQuery(couponCode: self::OWN_MINIMUM_COUPON, address: $ownRuleAddress).' → 200, no discount, gift attached (existing rule)']);

        $referral = $this->scenarioCustomer(key: 'referral', firstName: 'Referral');
        $referralGift = $this->seedPendingGift(customer: $referral);
        $this->seedAddress(customer: $referral);
        $referralCart = $this->seedCart(customer: $referral, price: '1200');
        $referralDiscount = $this->seedReferralDiscount(customer: $referral, amount: 300);
        $this->addRow('Referral pushes cart below gift min',
            "user={$referral->id} gift={$referralGift->id} cart={$referralCart->id} referral={$referralDiscount->id}",
            'gift=pending, cart=1200, referral=300 flat → 900, coupon='.self::DISCOUNT_COUPON,
            $referral, 'GET /coupon/apply',
            ['GET', '/coupon/apply?code='.self::DISCOUNT_COUPON, 'en', null, '200 coupon_discount=100 (900 after referral < 1000, so no gift on this order)']);
    }

    /**
     * Web customers whose gift is attached to a pending cash on delivery order. They apply the coupon while no gift
     * is pending, then cancel that order, which gives the gift back and makes the session coupon stale.
     */
    private function seedStaleWebCouponScenarios(): void
    {
        $scenarios = [
            ['web-cart', 'Web: stale coupon removed on page load', 'shop-cart', '1. Log in on the web  2. /shop-cart → apply '.self::DISCOUNT_COUPON.' (100 off)  3. /order-cancel/{order}  4. reload /shop-cart → warning "Your discount coupon was removed…", coupon gone, total without discount'],
            ['web-checkout', 'Web: stale coupon refused at order', 'checkout-payment', '1. Log in, apply '.self::DISCOUNT_COUPON.' on /shop-cart  2. proceed to /checkout-payment (pick the seeded address)  3. in another tab /order-cancel/{order}  4. submit cash on delivery → back on /checkout-payment with the warning, no new order'],
        ];

        foreach ($scenarios as [$key, $scenario, $routeName, $steps]) {
            $customer = $this->scenarioCustomer(key: $key, firstName: ucwords(str_replace('-', ' ', $key)));
            $this->seedAddress(customer: $customer);
            $order = $this->seedPendingOrder(customer: $customer, key: $key);
            $gift = $this->seedPendingGift(customer: $customer);
            $gift->update(['order_id' => $order->id]);
            $cartItem = $this->seedCart(customer: $customer, price: '1200');
            $steps = str_replace('{order}', (string)$order->id, $steps);

            $this->addRow($scenario,
                "user={$customer->id} gift={$gift->id} order={$order->id} cart={$cartItem->id}",
                "gift=attached to order {$order->id} (pending, COD), cart=1200",
                $customer, 'web /'.$routeName.' (see steps)',
                ['WEB', route($routeName, absolute: false), 'en', null, $steps]);
        }
    }

    private function scenarioCustomer(string $key, string $firstName, int $walletBalance = 0): User
    {
        return User::updateOrCreate(
            ['email' => self::EMAIL_PREFIX.$key.self::EMAIL_DOMAIN],
            [
                'name' => $firstName.' Customer',
                'f_name' => $firstName,
                'l_name' => 'Customer',
                'phone' => '+97151'.str_pad((string)(crc32($key) % 10000000), 7, '0', STR_PAD_LEFT),
                'password' => Hash::make(self::PASSWORD),
                'is_active' => 1,
                'is_phone_verified' => 1,
                'is_email_verified' => 1,
                'email_verified_at' => now(),
                'login_medium' => 'general',
                'wallet_balance' => $walletBalance,
            ],
        );
    }

    /**
     * Yesterday's win, put back to pending on every run so the scenario survives earlier test orders.
     */
    private function seedPendingGift(User $customer): LuckyWheelWonGift
    {
        $spinDay = $this->businessToday->copy()->subDay();
        $spin = LuckyWheelSpin::firstOrCreate(
            ['customer_id' => $customer->id, 'spin_date' => $spinDay->toDateString()],
            [
                'reward_id' => $this->giftReward->id,
                'outcome_type' => LuckyWheelRewardType::Gift,
                'spin_frequency' => LuckyWheelSpinFrequency::Daily,
                'reward_snapshot' => [
                    'id' => $this->giftReward->id,
                    'name' => $this->giftReward->name,
                    'name_translations' => $this->giftReward->getNameTranslations(),
                    'type' => LuckyWheelRewardType::Gift->value,
                    'image' => $this->giftReward->image,
                    'image_storage_type' => $this->giftReward->image_storage_type,
                ],
            ],
        );

        $gift = LuckyWheelWonGift::firstOrCreate(
            ['spin_id' => $spin->id],
            [
                'customer_id' => $customer->id,
                'reward_id' => $this->giftReward->id,
                'name' => $this->giftReward->name,
                'name_translations' => $this->giftReward->getNameTranslations(),
                'image' => $this->giftReward->image,
                'image_storage_type' => $this->giftReward->image_storage_type,
                'awarded_at' => $spinDay->copy()->setTime(12, 0),
            ],
        );
        $gift->update(['order_id' => null]);
        return $gift;
    }

    private function seedAddress(User $customer): ShippingAddress
    {
        return ShippingAddress::updateOrCreate(
            ['customer_id' => $customer->id, 'is_guest' => 0, 'address_type' => 'home'],
            [
                'contact_person_name' => $customer->f_name.' Customer',
                'email' => $customer->email,
                'address' => 'Sheikh Zayed Road, Dubai',
                'city' => 'Dubai',
                'zip' => '00000',
                'phone' => $customer->phone,
                'state' => 'Dubai',
                'country' => 'United Arab Emirates',
                'latitude' => '25.2048',
                'longitude' => '55.2708',
                'is_billing' => 0,
            ],
        );
    }

    /**
     * One checked cart item at a fixed price (no discount or tax) with the order-wise shipping method chosen.
     */
    private function seedCart(User $customer, string $price): Cart
    {
        $product = $this->qaProduct(price: $price);
        $cartGroupId = $customer->id.'-lucky-wheel-qa';
        Cart::where(['customer_id' => $customer->id, 'is_guest' => 0])->where('product_id', '!=', $product->id)->delete();

        $cartItem = Cart::updateOrCreate(
            ['customer_id' => $customer->id, 'is_guest' => 0, 'product_id' => $product->id],
            [
                'cart_group_id' => $cartGroupId,
                'product_type' => 'physical',
                'choices' => json_encode([]),
                'variations' => json_encode([]),
                'variant' => '',
                'quantity' => 1,
                'price' => $product->unit_price,
                'tax' => 0,
                'discount' => 0,
                'tax_model' => 'exclude',
                'is_checked' => 1,
                'slug' => $product->slug,
                'name' => $product->name,
                'thumbnail' => $product->thumbnail,
                'seller_id' => $product->user_id,
                'seller_is' => 'admin',
                'shipping_cost' => 0,
                'shipping_type' => 'order_wise',
            ],
        );

        if ($this->shippingMethod) {
            CartShipping::updateOrCreate(
                ['cart_group_id' => $cartGroupId],
                ['shipping_method_id' => $this->shippingMethod->id, 'shipping_cost' => $this->shippingMethod->cost],
            );
        }
        Cache::forget('cache_cart_list_for_customer_'.$customer->id);
        return $cartItem;
    }

    private function qaProduct(string $price): Product
    {
        $slug = self::QA_PRODUCT_SLUG_PREFIX.$price;
        $product = Product::where('slug', $slug)->first() ?? $this->orderProduct->replicate();
        $product->forceFill([
            'name' => '[QA] Lucky Wheel Item '.$price.'.00',
            'slug' => $slug,
            'unit_price' => $price,
            'purchase_price' => $price,
            'discount' => 0,
            'discount_type' => 'flat',
            'tax' => 0,
            'variation' => json_encode([]),
            'choice_options' => json_encode([]),
            'colors' => json_encode([]),
            'attributes' => json_encode([]),
            'current_stock' => 1000,
            'minimum_order_qty' => 1,
            'status' => 1,
            'request_status' => 1,
        ])->save();
        return $product;
    }

    private function seedPendingOrder(User $customer, string $key): Order
    {
        $order = Order::updateOrCreate(
            ['order_group_id' => 'lw-cc-'.$key],
            [
                'customer_id' => $customer->id,
                'is_guest' => 0,
                'customer_type' => 'customer',
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'payment_method' => 'cash_on_delivery',
                'order_amount' => 1200,
                'init_order_amount' => 1200,
                'admin_commission' => 0,
                'total_tax_amount' => 0,
                'discount_amount' => 0,
                'shipping_cost' => 0,
                'is_shipping_free' => 0,
                'shipping_method_id' => 0,
                'shipping_type' => 'order_wise',
                'shipping_responsibility' => 'inhouse_shipping',
                'seller_id' => 1,
                'seller_is' => 'admin',
                'verification_code' => '123456',
                'order_type' => 'default_type',
                'checked' => 1,
                'order_note' => 'Lucky wheel coupon conflict scenario: '.$key,
            ],
        );

        OrderDetail::updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $this->orderProduct->id],
            [
                'seller_id' => 1,
                'product_details' => json_encode($this->orderProduct->toArray()),
                'qty' => 1,
                'price' => 1200,
                'tax' => 0,
                'discount' => 0,
                'tax_model' => 'exclude',
                'delivery_status' => 'pending',
                'payment_status' => 'unpaid',
                'variant' => '',
                'variation' => json_encode([]),
                'discount_type' => 'discount_on_product',
                'is_stock_decreased' => 0,
                'refund_request' => 0,
            ],
        );
        return $order;
    }

    private function seedReferralDiscount(User $customer, int $amount): ReferralCustomer
    {
        $referralCustomer = ReferralCustomer::firstOrNew(['user_id' => $customer->id]);
        $referralCustomer->forceFill([
            'refer_by' => User::where('email', 'buyer@gmail.com')->value('id') ?? $customer->id,
            'ref_by_earning_amount' => 0,
            'customer_discount_amount' => $amount,
            'customer_discount_amount_type' => 'flat',
            'customer_discount_validity' => 1,
            'customer_discount_validity_type' => 'month',
            'is_used' => 0,
            'created_at' => now(),
        ])->save();
        return $referralCustomer;
    }

    private function seedCoupon(string $code, string $title, string $couponType, int $discount, int $minimumPurchase): void
    {
        Coupon::firstOrNew(['code' => $code])->forceFill([
            'added_by' => 'admin',
            'coupon_type' => $couponType,
            'coupon_bearer' => 'inhouse',
            'seller_id' => 0,
            'customer_id' => 0,
            'title' => $title,
            'start_date' => $this->businessToday->copy()->subDay()->toDateString(),
            'expire_date' => $this->businessToday->copy()->addYear()->toDateString(),
            'min_purchase' => $minimumPurchase,
            'max_discount' => 0,
            'discount' => $discount,
            'discount_type' => 'amount',
            'status' => 1,
            'limit' => 1000,
        ])->save();
    }

    private function orderQuery(string $couponCode, ShippingAddress $address): string
    {
        return http_build_query(['coupon_code' => $couponCode, 'address_id' => $address->id, 'billing_address_id' => $address->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function offlinePaymentBody(ShippingAddress $address): array
    {
        $method = OfflinePaymentMethod::where('status', 1)->orderBy('id')->first();
        $customerInputs = [];
        foreach ($method?->method_informations ?? [] as $information) {
            $customerInputs[$information['customer_input']] = 'QA Customer';
        }

        return [
            'coupon_code' => self::DISCOUNT_COUPON,
            'method_id' => $method?->id,
            'method_informations' => base64_encode(json_encode($customerInputs, JSON_UNESCAPED_UNICODE)),
            'address_id' => $address->id,
            'billing_address_id' => $address->id,
            'payment_note' => 'QA lucky wheel coupon conflict',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function digitalPaymentBody(User $customer, ShippingAddress $address): array
    {
        return [
            'payment_method' => 'stripe',
            'payment_platform' => 'app',
            'payment_request_from' => 'app',
            'customer_id' => $customer->id,
            'is_guest' => 0,
            'coupon_code' => self::DISCOUNT_COUPON,
            'address_id' => $address->id,
            'billing_address_id' => $address->id,
        ];
    }

    /**
     * @param array{0: string, 1: string, 2: string, 3: array<string, mixed>|null, 4: string} $request
     */
    private function addRow(string $scenario, string $ids, string $keyFields, User $customer, string $reach, array $request): void
    {
        $this->rows[] = [count($this->rows) + 1, $scenario, $ids, $keyFields, $customer->email, $reach];
        $this->requests[] = $request;
    }

    private function printReport(string $minimumOrderAmount): void
    {
        $this->command->newLine();
        $this->command->info('Lucky wheel gift vs discount coupon scenarios (gift minimum '.$minimumOrderAmount.', business date '.$this->businessToday->toDateString().')');
        $this->command->table(['#', 'Scenario', 'ID', 'Key fields', 'Login', 'How to reach it'], $this->rows);
        $this->command->comment('Password for every login above: '.self::PASSWORD);
        $this->command->comment('Coupons: '.self::DISCOUNT_COUPON.' (100 off) · '.self::FREE_DELIVERY_COUPON.' (free delivery) · '.self::OWN_MINIMUM_COUPON.' (100 off, min purchase 5000)');

        $this->command->newLine();
        $this->command->info('Requests (API: Authorization: Bearer {{token}} from POST {{base_url}}/auth/login {"email_or_phone": "<login>", "password": "'.self::PASSWORD.'", "type": "email"})');
        foreach ($this->requests as $index => [$method, $path, $language, $body, $expected]) {
            $target = $method === 'WEB' ? 'WEB '.$path : $method.' {{base_url}}'.$path.'  (lang: '.$language.')';
            $this->command->line(sprintf('%d. %s', $index + 1, $target));
            if ($body !== null) {
                $this->command->line(json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }
            $this->command->line('   → '.$expected);
        }

        $customerIds = User::where('email', 'like', self::EMAIL_PREFIX.'%'.self::EMAIL_DOMAIN)->pluck('id');
        $this->command->newLine();
        $this->command->info('Counts for the '.$customerIds->count().' scenario customers');
        $this->command->table(['Gifts pending', 'Gifts attached', 'Checked cart items', 'Pending scenario orders'], [[
            LuckyWheelWonGift::whereIn('customer_id', $customerIds)->whereNull('order_id')->count(),
            LuckyWheelWonGift::whereIn('customer_id', $customerIds)->whereNotNull('order_id')->count(),
            Cart::whereIn('customer_id', $customerIds)->where(['is_guest' => 0, 'is_checked' => 1])->count(),
            Order::where('order_group_id', 'like', 'lw-cc-%')->where('order_status', 'pending')->count(),
        ]]);
        $this->command->comment('Expected refusal (en): '.self::CONFLICT_MESSAGE_EN);
        $this->command->comment('Re-run: php artisan db:seed --class=LuckyWheelCouponConflictScenarioSeeder');
    }
}
