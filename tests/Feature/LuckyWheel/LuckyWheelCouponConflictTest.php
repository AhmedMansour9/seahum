<?php

namespace Tests\Feature\LuckyWheel;

use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Web\WebController;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\LuckyWheelWonGift;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\LuckyWheelService;
use App\Utils\OrderManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LuckyWheelCouponConflictTest extends TestCase
{
    use InteractsWithLuckyWheel;

    private const DISCOUNT_COUPON = 'SAVE100';
    private const FREE_DELIVERY_COUPON = 'FREESHIP';
    private const ORDER_ENDPOINTS = [
        '/api/v1/customer/order/place',
        '/api/v1/customer/order/place-by-wallet',
        '/api/v1/customer/order/place-by-offline-payment',
    ];

    private LuckyWheelService $luckyWheelService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createLuckyWheelTables();
        $this->createCheckoutTables();
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->setBusinessSetting(type: LuckyWheelService::MINIMUM_ORDER_AMOUNT_SETTING, value: '1000.00');
        $this->luckyWheelService = app(LuckyWheelService::class);
        $this->createReward(name: '50g Salmon');
        $this->createCoupon(code: self::DISCOUNT_COUPON, couponType: 'discount_on_purchase', discount: 100);
        $this->createCoupon(code: self::FREE_DELIVERY_COUPON, couponType: 'free_delivery', discount: 0);
    }

    public function test_every_order_endpoint_rejects_a_discount_coupon_on_an_order_that_takes_the_gift_and_saves_nothing(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $customer, price: '1200');

        foreach (self::ORDER_ENDPOINTS as $orderEndpoint) {
            $this->placeOrder(customer: $customer, endpoint: $orderEndpoint, couponCode: self::DISCOUNT_COUPON)
                ->assertForbidden()
                ->assertExactJson(['errors' => [[
                    'code' => LuckyWheelService::DISCOUNT_COUPON_CONFLICT,
                    'message' => "This discount coupon can't be combined with your Lucky Wheel gift. Please remove the coupon and try again.",
                ]]]);
        }

        $this->assertSame(0, Order::count());
        $this->assertSame(1, User::count());
        $this->assertNull(LuckyWheelWonGift::sole()->order_id);
        $this->assertSame(1, Cart::count());
    }

    public function test_the_rejection_message_follows_the_request_language(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $customer, price: '1200');

        $this->placeOrder(customer: $customer, endpoint: self::ORDER_ENDPOINTS[0], couponCode: self::DISCOUNT_COUPON, language: 'eg')
            ->assertForbidden()
            ->assertJsonPath('errors.0.code', LuckyWheelService::DISCOUNT_COUPON_CONFLICT)
            ->assertJsonPath('errors.0.message', 'لا يمكن الجمع بين كوبون الخصم وهدية عجلة الحظ. يرجى إزالة الكوبون والمحاولة مرة أخرى.');
    }

    public function test_checking_a_valid_coupon_keeps_later_api_messages_in_the_request_language(): void
    {
        $customer = $this->createCustomer(email: 'nogift@gmail.com');
        $this->addToCart(customer: $customer, price: '1200');
        $request = Request::create('/api/v1/customer/order/place', 'GET', ['coupon_code' => self::DISCOUNT_COUPON]);
        app()->instance('request', $request);
        $request->setUserResolver(fn () => $customer);
        app()->setLocale('eg');

        $this->assertNull(OrderManager::getLuckyWheelGiftCouponConflict(request: $request, couponCode: self::DISCOUNT_COUPON));

        $this->assertSame('eg', app()->getLocale());
        $this->assertSame('تحقق من متطلبات الحد الأدنى لمبلغ الطلب', translate('Check_minimum_order_amount_requirement'));
    }

    public function test_coupons_that_do_not_conflict_with_the_gift_are_left_to_the_existing_rules(): void
    {
        $customerWithGift = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $customerWithGift, price: '1200');
        $this->assertNull($this->getConflict(customer: $customerWithGift, couponCode: self::FREE_DELIVERY_COUPON));
        $this->assertNull($this->getConflict(customer: $customerWithGift, couponCode: null));

        $belowMinimum = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $belowMinimum, price: '600');
        $this->assertNull($this->getConflict(customer: $belowMinimum, couponCode: self::DISCOUNT_COUPON));

        $withoutGift = $this->createCustomer(email: 'nogift@gmail.com');
        $this->addToCart(customer: $withoutGift, price: '1200');
        $this->assertNull($this->getConflict(customer: $withoutGift, couponCode: self::DISCOUNT_COUPON));
    }

    public function test_a_coupon_failing_its_own_rules_is_not_reported_as_a_gift_conflict(): void
    {
        Coupon::where('code', self::DISCOUNT_COUPON)->update(['min_purchase' => 5000]);
        $customer = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $customer, price: '1200');

        $this->assertNull($this->getConflict(customer: $customer, couponCode: self::DISCOUNT_COUPON));
        $this->actingAs($customer, 'api')->getJson('/api/v1/coupon/apply?code='.self::DISCOUNT_COUPON)
            ->assertStatus(202)
            ->assertExactJson(['Minimum purchase amount not reached for this coupon.']);
    }

    public function test_coupon_apply_explains_the_gift_conflict_and_applies_otherwise(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $customer, price: '1200');
        $this->actingAs($customer, 'api')->getJson('/api/v1/coupon/apply?code='.self::DISCOUNT_COUPON)
            ->assertStatus(202)
            ->assertExactJson(["This discount coupon can't be combined with your Lucky Wheel gift. Please remove the coupon and try again."]);

        $belowMinimum = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $belowMinimum, price: '600');
        $this->actingAs($belowMinimum, 'api')->getJson('/api/v1/coupon/apply?code='.self::DISCOUNT_COUPON)
            ->assertOk()
            ->assertExactJson(['coupon_discount' => 100, 'coupon_type' => 'discount_on_purchase']);
    }

    public function test_cart_changes_and_referral_discounts_recalculate_the_conflict(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $cartItem = $this->addToCart(customer: $customer, price: '900');
        $this->assertNull($this->getConflict(customer: $customer, couponCode: self::DISCOUNT_COUPON));

        $cartItem->update(['quantity' => 2]);
        $this->forgetCartCache(customer: $customer);
        $this->assertSame(LuckyWheelService::DISCOUNT_COUPON_CONFLICT, $this->getConflict(customer: $customer, couponCode: self::DISCOUNT_COUPON)['code']);

        DB::table('referral_customers')->insert([
            'user_id' => $customer->id,
            'is_used' => 0,
            'customer_discount_amount' => 900,
            'customer_discount_amount_type' => 'flat',
            'customer_discount_validity' => 1,
            'customer_discount_validity_type' => 'month',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->assertNull($this->getConflict(customer: $customer, couponCode: self::DISCOUNT_COUPON));
    }

    public function test_a_web_session_coupon_applied_before_winning_the_gift_is_removed_on_the_next_page_load(): void
    {
        $customer = $this->createCustomer(email: 'web@gmail.com');
        $this->addToCart(customer: $customer, price: '1200');
        $this->actingAs($customer, 'customer');
        $this->assertTrue(OrderManager::getTotalCouponAmount(request: request(), couponCode: self::DISCOUNT_COUPON)['status']);
        $this->assertSame(self::DISCOUNT_COUPON, session('coupon_code'));
        $this->assertFalse(OrderManager::forgetSessionCouponConflictingWithLuckyWheelGift(request: request()));

        $this->luckyWheelService->spin(customerId: $customer->id);

        $this->assertTrue(OrderManager::forgetSessionCouponConflictingWithLuckyWheelGift(request: request()));
        foreach (['coupon_code', 'coupon_type', 'coupon_bearer', 'coupon_discount', 'coupon_seller_id'] as $sessionKey) {
            $this->assertFalse(session()->has($sessionKey), "{$sessionKey} should be removed");
        }
        $this->assertFalse(OrderManager::forgetSessionCouponConflictingWithLuckyWheelGift(request: request()));
    }

    public function test_a_web_free_delivery_session_coupon_is_kept_when_the_gift_is_won(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $customer, price: '1200');
        $this->actingAs($customer, 'customer');
        session()->put(['coupon_code' => self::FREE_DELIVERY_COUPON, 'coupon_type' => 'free_delivery', 'coupon_discount' => 5]);

        $this->assertFalse(OrderManager::forgetSessionCouponConflictingWithLuckyWheelGift(request: request()));
        $this->assertSame(self::FREE_DELIVERY_COUPON, session('coupon_code'));
    }

    public function test_web_checkout_completion_refuses_a_stale_conflicting_coupon_and_sends_the_customer_back_to_review_the_total(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $customer, price: '1200');
        $this->actingAs($customer, 'customer');
        $completions = [
            'cash_on_delivery' => fn (Request $request) => app(WebController::class)->getCashOnDeliveryCheckoutComplete($request),
            'offline_payment' => fn (Request $request) => app(WebController::class)->getOfflinePaymentCheckoutComplete($request),
            'pay_by_wallet' => fn (Request $request) => app(WebController::class)->checkout_complete_wallet($request),
            'stripe' => fn (Request $request) => app(PaymentController::class)->payment($request),
        ];

        foreach ($completions as $paymentName => $completeCheckout) {
            $this->putSessionCoupon();
            $response = $completeCheckout($this->webCheckoutRequest(customer: $customer, paymentMethod: $paymentName));

            $this->assertInstanceOf(RedirectResponse::class, $response, $paymentName);
            $this->assertSame(route('checkout-payment'), $response->getTargetUrl(), $paymentName);
            $this->assertFalse(session()->has('coupon_code'), "{$paymentName} should remove the coupon");
        }

        $this->assertSame(0, Order::count());
        $this->assertNull(LuckyWheelWonGift::sole()->order_id);
    }

    public function test_ajax_web_checkout_gets_a_machine_readable_conflict_code(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $customer, price: '1200');
        $this->actingAs($customer, 'customer');
        $this->putSessionCoupon();
        $request = $this->webCheckoutRequest(customer: $customer, paymentMethod: 'cash_on_delivery');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = app(WebController::class)->getCashOnDeliveryCheckoutComplete($request);

        $this->assertSame([
            'status' => 0,
            'code' => LuckyWheelService::DISCOUNT_COUPON_CONFLICT,
            'message' => 'Your discount coupon was removed because this order gets your free Lucky Wheel gift. Please review the updated total.',
            'redirect' => route('checkout-payment'),
        ], $response->getData(true));
        $this->assertSame(0, Order::count());
    }

    public function test_app_digital_payment_is_refused_before_a_payment_request_is_created(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $this->addToCart(customer: $customer, price: '1200');
        $request = Request::create('/api/v1/digital-payment', 'POST', [
            'payment_request_from' => 'app',
            'payment_method' => 'stripe',
            'payment_platform' => 'app',
            'customer_id' => $customer->id,
            'is_guest' => 0,
            'coupon_code' => self::DISCOUNT_COUPON,
        ]);
        $request->setUserResolver(fn () => $customer);

        $response = app(PaymentController::class)->payment($request);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(LuckyWheelService::DISCOUNT_COUPON_CONFLICT, $response->getData(true)['errors'][0]['code']);
        $this->assertSame(0, Order::count());
    }

    private function putSessionCoupon(): void
    {
        session()->put([
            'coupon_code' => self::DISCOUNT_COUPON,
            'coupon_type' => 'discount_on_purchase',
            'coupon_discount' => 100,
            'coupon_bearer' => 'inhouse',
            'coupon_seller_id' => 0,
        ]);
    }

    private function webCheckoutRequest(User $customer, string $paymentMethod): Request
    {
        session()->put(['address_id' => 1, 'billing_address_id' => 1]);
        $request = Request::create('/checkout-complete', 'GET', [
            'payment_method' => $paymentMethod,
            'payment_platform' => 'web',
            'method_id' => 1,
        ]);
        $request->setUserResolver(fn () => $customer);
        return $request;
    }

    private function getConflict(User $customer, ?string $couponCode): ?array
    {
        $request = request();
        $request->setUserResolver(fn () => $customer);
        return OrderManager::getLuckyWheelGiftCouponConflict(request: $request, couponCode: $couponCode);
    }

    private function placeOrder(User $customer, string $endpoint, string $couponCode, string $language = 'en'): TestResponse
    {
        $headers = ['Authorization' => 'Bearer token', 'lang' => $language];
        $client = $this->actingAs($customer, 'api');

        return str_ends_with($endpoint, 'offline-payment')
            ? $client->postJson($endpoint, ['coupon_code' => $couponCode], $headers)
            : $client->getJson($endpoint.'?coupon_code='.$couponCode, $headers);
    }

    private function createCustomerWithPendingGift(): User
    {
        $customer = $this->createCustomer(email: uniqid('customer').'@gmail.com');
        $this->luckyWheelService->spin(customerId: $customer->id);
        return $customer;
    }

    private function addToCart(User $customer, string $price): Cart
    {
        $product = Product::forceCreate([
            'added_by' => 'admin',
            'user_id' => 1,
            'name' => 'Salmon '.$price,
            'slug' => uniqid('salmon-'),
            'product_type' => 'physical',
            'unit_price' => $price,
            'discount' => 0,
            'discount_type' => 'flat',
            'current_stock' => 100,
            'variation' => '[]',
            'status' => 1,
            'request_status' => 1,
        ]);
        $cartItem = Cart::forceCreate([
            'customer_id' => $customer->id,
            'is_guest' => 0,
            'cart_group_id' => $customer->id.'-group',
            'product_id' => $product->id,
            'product_type' => 'physical',
            'variant' => '',
            'quantity' => 1,
            'price' => $price,
            'discount' => 0,
            'tax' => 0,
            'is_checked' => 1,
            'seller_id' => 1,
            'seller_is' => 'admin',
            'shipping_cost' => 0,
        ]);
        $this->forgetCartCache(customer: $customer);
        return $cartItem;
    }

    private function forgetCartCache(User $customer): void
    {
        Cache::forget('cache_cart_list_for_customer_'.$customer->id);
    }

    private function createCoupon(string $code, string $couponType, int $discount): void
    {
        Coupon::forceCreate([
            'code' => $code,
            'coupon_type' => $couponType,
            'coupon_bearer' => 'inhouse',
            'seller_id' => 0,
            'customer_id' => 0,
            'discount' => $discount,
            'discount_type' => 'amount',
            'min_purchase' => 0,
            'max_discount' => 0,
            'limit' => 100,
            'status' => 1,
            'start_date' => now()->subDay()->toDateString(),
            'expire_date' => now()->addYear()->toDateString(),
        ]);
    }

    private function createCheckoutTables(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('coupon_bearer')->default('inhouse');
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('customer_id')->default(0);
            $table->decimal('discount')->default(0);
            $table->string('discount_type')->default('amount');
            $table->decimal('min_purchase')->default(0);
            $table->decimal('max_discount')->default(0);
            $table->integer('limit')->nullable();
            $table->boolean('status')->default(1);
            $table->date('start_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_group_id')->nullable();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('wallet_balance')->default(0);
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('added_by');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('slug');
            $table->string('product_type');
            $table->string('digital_product_type')->nullable();
            $table->decimal('unit_price');
            $table->decimal('discount')->default(0);
            $table->string('discount_type')->default('flat');
            $table->decimal('tax')->default(0);
            $table->string('tax_model')->default('exclude');
            $table->integer('current_stock')->default(0);
            $table->text('variation')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('request_status')->default(1);
            $table->timestamps();
        });
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('delivery_man_id')->nullable();
            $table->boolean('status')->default(1);
        });
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('approved');
        });
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->boolean('is_guest')->default(0);
            $table->string('cart_group_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_type');
            $table->string('digital_product_type')->nullable();
            $table->text('variations')->nullable();
            $table->string('variant')->nullable();
            $table->integer('quantity');
            $table->decimal('price');
            $table->decimal('discount')->default(0);
            $table->decimal('tax')->default(0);
            $table->string('tax_model')->default('exclude');
            $table->boolean('is_checked')->default(1);
            $table->unsignedBigInteger('seller_id');
            $table->string('seller_is');
            $table->decimal('shipping_cost')->default(0);
            $table->string('shipping_type')->nullable();
            $table->timestamps();
        });
        Schema::create('stock_clearance_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('setup_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->string('added_by')->default('admin');
        });
        Schema::create('stock_clearance_setups', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(1);
            $table->dateTime('duration_start_date')->nullable();
            $table->dateTime('duration_end_date')->nullable();
            $table->string('offer_active_time')->nullable();
            $table->time('offer_active_range_start')->nullable();
            $table->time('offer_active_range_end')->nullable();
        });
        Schema::create('digital_product_variations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
        });
        Schema::create('shipping_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->string('shipping_type');
            $table->timestamps();
        });
        Schema::create('cart_shippings', function (Blueprint $table) {
            $table->id();
            $table->string('cart_group_id');
            $table->unsignedBigInteger('shipping_method_id')->nullable();
            $table->decimal('shipping_cost')->default(0);
            $table->timestamps();
        });
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(1);
        });
        Schema::create('taxables', function (Blueprint $table) {
            $table->id();
            $table->string('taxable_type');
            $table->unsignedBigInteger('taxable_id');
            $table->unsignedBigInteger('tax_id');
            $table->string('tax_for')->nullable();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('creator_type')->default('admin');
            $table->unsignedBigInteger('creator_id')->default(0);
            $table->decimal('cost')->default(0);
            $table->boolean('status')->default(1);
        });
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('author_type')->nullable();
            $table->string('name')->nullable();
            $table->boolean('temporary_close')->default(0);
            $table->boolean('vacation_status')->default(0);
        });
        DB::table('shops')->insert(['author_type' => 'admin', 'name' => 'Seahum']);
        Schema::create('referral_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_used')->default(0);
            $table->decimal('customer_discount_amount')->default(0);
            $table->string('customer_discount_amount_type')->nullable();
            $table->integer('customer_discount_validity')->default(0);
            $table->string('customer_discount_validity_type')->nullable();
            $table->timestamps();
        });
    }
}
