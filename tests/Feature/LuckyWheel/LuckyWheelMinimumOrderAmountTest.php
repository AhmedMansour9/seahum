<?php

namespace Tests\Feature\LuckyWheel;

use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Http\Middleware\ActivationCheckMiddleware;
use App\Models\Admin;
use App\Models\LuckyWheelWonGift;
use App\Models\Order;
use App\Models\User;
use App\Services\LuckyWheelService;
use Database\Seeders\LuckyWheelSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LuckyWheelMinimumOrderAmountTest extends TestCase
{
    use InteractsWithLuckyWheel;

    private LuckyWheelService $luckyWheelService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createLuckyWheelTables();
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->setBusinessSetting(type: LuckyWheelService::MINIMUM_ORDER_AMOUNT_SETTING, value: '1000.00');
        $this->luckyWheelService = app(LuckyWheelService::class);
        $this->createReward(name: '50g Salmon');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_seeded_default_minimum_is_1000_and_reseeding_keeps_the_admin_value(): void
    {
        DB::table('business_settings')->where('type', LuckyWheelService::MINIMUM_ORDER_AMOUNT_SETTING)->delete();
        clearWebConfigCacheKeys();

        $this->seed(LuckyWheelSeeder::class);
        $this->assertSame('1000.00', $this->luckyWheelService->getMinimumOrderAmount());

        $this->setBusinessSetting(type: LuckyWheelService::MINIMUM_ORDER_AMOUNT_SETTING, value: '250.50');
        $this->seed(LuckyWheelSeeder::class);
        $this->assertSame('250.50', $this->luckyWheelService->getMinimumOrderAmount());
    }

    public function test_gifts_attach_only_when_the_subtotal_reaches_the_minimum(): void
    {
        foreach (['800.00' => 0, '999.99' => 0, '1000.00' => 1, '1500.00' => 1] as $itemPrice => $expectedAttachedGifts) {
            $customer = $this->createCustomerWithPendingGift();
            $order = $this->createOrder(customer: $customer, itemPrice: $itemPrice);

            $this->assertSame($expectedAttachedGifts, $this->attachToCheckout(customer: $customer, order: $order), "subtotal {$itemPrice}");
            $this->assertSame(1, LuckyWheelWonGift::where('customer_id', $customer->id)->count());
        }
    }

    public function test_pending_gift_survives_an_ineligible_order_and_joins_the_next_eligible_one(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $smallOrder = $this->createOrder(customer: $customer, itemPrice: '400');

        $this->attachToCheckout(customer: $customer, order: $smallOrder);
        $this->assertNull(LuckyWheelWonGift::sole()->order_id);

        $largeOrder = $this->createOrder(customer: $customer, itemPrice: '1200');
        $this->attachToCheckout(customer: $customer, order: $largeOrder);
        $this->assertSame($largeOrder->id, LuckyWheelWonGift::sole()->order_id);
    }

    public function test_shipping_and_tax_do_not_count_toward_the_minimum(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $order = $this->createOrder(customer: $customer, itemPrice: '900', orderAttributes: ['shipping_cost' => 500, 'total_tax_amount' => 300]);

        $this->assertSame(0, $this->attachToCheckout(customer: $customer, order: $order));
    }

    public function test_product_coupon_and_referral_discounts_reduce_the_subtotal_but_free_delivery_coupons_do_not(): void
    {
        $productDiscounted = $this->createCustomerWithPendingGift();
        $this->assertSame(0, $this->attachToCheckout(customer: $productDiscounted, order: $this->createOrder(customer: $productDiscounted, itemPrice: '1100', itemDiscount: '150')));

        $couponDiscounted = $this->createCustomerWithPendingGift();
        $this->assertSame(0, $this->attachToCheckout(customer: $couponDiscounted, order: $this->createOrder(customer: $couponDiscounted, itemPrice: '1100', orderAttributes: ['discount_amount' => 101, 'coupon_code' => 'SAVE'])));

        $referralDiscounted = $this->createCustomerWithPendingGift();
        $this->assertSame(0, $this->attachToCheckout(customer: $referralDiscounted, order: $this->createOrder(customer: $referralDiscounted, itemPrice: '1050', orderAttributes: ['refer_and_earn_discount' => 60])));

        DB::table('coupons')->insert(['code' => 'FREESHIP', 'coupon_type' => 'free_delivery']);
        $freeDelivery = $this->createCustomerWithPendingGift();
        $this->assertSame(1, $this->attachToCheckout(customer: $freeDelivery, order: $this->createOrder(customer: $freeDelivery, itemPrice: '1000', orderAttributes: ['discount_amount' => 80, 'coupon_code' => 'FREESHIP'])));
    }

    public function test_split_vendor_orders_count_together_and_gifts_attach_to_the_first_order(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $firstOrder = $this->createOrder(customer: $customer, itemPrice: '600');
        $secondOrder = $this->createOrder(customer: $customer, itemPrice: '400');

        $this->assertSame(1, $this->luckyWheelService->attachPendingGiftToQualifyingOrders(customerId: $customer->id, orderIds: [$firstOrder->id, $secondOrder->id]));
        $this->assertSame($firstOrder->id, LuckyWheelWonGift::sole()->order_id);
    }

    public function test_admin_change_to_the_minimum_applies_to_the_next_checkout_immediately(): void
    {
        $this->withoutMiddleware(ActivationCheckMiddleware::class);
        $this->createAdminTables();
        $customer = $this->createCustomerWithPendingGift();
        $this->assertSame(0, $this->attachToCheckout(customer: $customer, order: $this->createOrder(customer: $customer, itemPrice: '500')));

        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'admin_role_id' => 1, 'status' => 1]), 'admin')
            ->post(route('admin.lucky-wheel.settings'), ['is_enabled' => 1, 'spin_frequency' => 'DAILY', 'minimum_order_amount' => '450.5'])
            ->assertRedirect(route('admin.lucky-wheel.index'));

        $this->assertSame('450.50', $this->luckyWheelService->getMinimumOrderAmount());
        $this->assertSame(1, $this->attachToCheckout(customer: $customer, order: $this->createOrder(customer: $customer, itemPrice: '500')));
    }

    public function test_admin_cannot_save_a_negative_or_non_numeric_minimum(): void
    {
        $this->withoutMiddleware(ActivationCheckMiddleware::class);
        $this->createAdminTables();
        $admin = Admin::forceCreate(['name' => 'Admin', 'admin_role_id' => 1, 'status' => 1]);

        foreach (['-1', 'abc', ''] as $invalidAmount) {
            $this->actingAs($admin, 'admin')
                ->post(route('admin.lucky-wheel.settings'), ['spin_frequency' => 'DAILY', 'minimum_order_amount' => $invalidAmount])
                ->assertSessionHasErrors('minimum_order_amount');
        }
        $this->assertSame('1000.00', $this->luckyWheelService->getMinimumOrderAmount());
    }

    public function test_repeated_or_concurrent_checkouts_never_attach_a_gift_twice(): void
    {
        $customer = $this->createCustomerWithPendingGift();
        $firstOrder = $this->createOrder(customer: $customer, itemPrice: '1200');
        $secondOrder = $this->createOrder(customer: $customer, itemPrice: '1200');

        $this->assertSame(1, $this->attachToCheckout(customer: $customer, order: $firstOrder));
        $this->assertSame(0, $this->attachToCheckout(customer: $customer, order: $secondOrder));
        $this->assertSame(0, $this->attachToCheckout(customer: $customer, order: $firstOrder));
        $this->assertSame([$firstOrder->id], LuckyWheelWonGift::pluck('order_id')->all());
    }

    public function test_customer_without_gifts_orders_normally(): void
    {
        $customer = $this->createCustomer(email: 'nogifts@gmail.com');

        $this->assertSame(0, $this->attachToCheckout(customer: $customer, order: $this->createOrder(customer: $customer, itemPrice: '5000')));
        $this->assertSame(0, LuckyWheelWonGift::count());
    }

    public function test_minimum_order_amount_does_not_affect_spinning(): void
    {
        $this->setBusinessSetting(type: LuckyWheelService::MINIMUM_ORDER_AMOUNT_SETTING, value: '999999');
        $customer = $this->createCustomer();

        $this->actingAs($customer, 'api')->getJson('/api/v1/customer/lucky-wheel')->assertOk()->assertJsonPath('can_spin', true);
        $this->actingAs($customer, 'api')->postJson('/api/v1/customer/lucky-wheel/spin')->assertOk()->assertJsonPath('outcome_type', 'GIFT');
    }

    public function test_checkout_summary_reports_eligibility_and_remaining_amount(): void
    {
        $customer = $this->createCustomerWithPendingGift();

        $belowMinimum = $this->luckyWheelService->getCheckoutGiftSummary(customerId: $customer->id, itemSubtotal: '900', productDiscount: '150', couponDiscount: 0, referralDiscount: 0);
        $this->assertSame([1000.0, 750.0, false, 250.0], [$belowMinimum['minimum_order_amount'], $belowMinimum['current_order_subtotal'], $belowMinimum['is_eligible'], $belowMinimum['remaining_amount']]);
        $this->assertSame(['50g Salmon'], $belowMinimum['pending_gifts']->pluck('name')->all());
        $this->assertSame($belowMinimum['pending_gifts']->first()->id, $belowMinimum['next_gift']->id);

        $atMinimum = $this->luckyWheelService->getCheckoutGiftSummary(customerId: $customer->id, itemSubtotal: '1000', productDiscount: 0, couponDiscount: 0, referralDiscount: 0);
        $this->assertSame([true, 0.0], [$atMinimum['is_eligible'], $atMinimum['remaining_amount']]);

        $this->assertNull($this->luckyWheelService->getCheckoutGiftSummary(customerId: $this->createCustomer(email: 'nogifts@gmail.com')->id, itemSubtotal: '1000', productDiscount: 0, couponDiscount: 0, referralDiscount: 0));
    }

    public function test_checkout_summary_api_returns_null_without_pending_gifts(): void
    {
        $this->actingAs($this->createCustomer(), 'api')
            ->getJson('/api/v1/customer/lucky-wheel/checkout-summary')
            ->assertOk()
            ->assertExactJson(['lucky_wheel' => null]);
    }

    private function attachToCheckout(User $customer, Order $order): int
    {
        return $this->luckyWheelService->attachPendingGiftToQualifyingOrders(customerId: $customer->id, orderIds: [$order->id]);
    }

    private function createCustomerWithPendingGift(): User
    {
        $customer = $this->createCustomer(email: uniqid('customer').'@gmail.com');
        $this->luckyWheelService->spin(customerId: $customer->id);
        return $customer;
    }

    private function createOrder(User $customer, string $itemPrice, string $itemDiscount = '0', array $orderAttributes = []): Order
    {
        $order = Order::forceCreate([
            'customer_id' => $customer->id,
            'is_guest' => 0,
            'order_status' => 'pending',
            ...$orderAttributes,
        ]);
        DB::table('order_details')->insert(['order_id' => $order->id, 'price' => $itemPrice, 'qty' => 1, 'discount' => $itemDiscount]);
        return $order;
    }

    private function createAdminTables(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('admin_role_id')->default(1);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->text('module_access')->nullable();
            $table->boolean('status')->default(1);
        });
    }
}
