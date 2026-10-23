<?php

namespace Tests\Feature;

use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Models\LuckyWheelWonGift;
use App\Models\Order;
use App\Models\User;
use App\Services\LuckyWheelService;
use Illuminate\Support\Facades\DB;
use Tests\Feature\LuckyWheel\InteractsWithLuckyWheel;
use Tests\TestCase;

class OrderAuthorizationTest extends TestCase
{
    use InteractsWithLuckyWheel;

    private const CANCEL_ENDPOINT = '/api/v1/order/cancel-order?order_id=';
    private const ORDER_ENDPOINT = '/api/v1/customer/order/get-order-by-id?order_id=';
    private const BEARER = ['Authorization' => 'Bearer token'];

    private User $owner;
    private User $otherCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createLuckyWheelTables();
        $this->createOrderApiTables();
        $this->setBusinessSetting(type: LuckyWheelService::MINIMUM_ORDER_AMOUNT_SETTING, value: '1000.00');
        $this->owner = $this->createCustomer(email: 'buyer@gmail.com');
        $this->otherCustomer = $this->createCustomer(email: 'seller@gmail.com');
    }

    public function test_guest_cannot_cancel_an_order(): void
    {
        $order = $this->createOrder(customerId: $this->owner->id);

        $this->getJson(self::CANCEL_ENDPOINT.$order->id)->assertUnauthorized();
        $this->getJson(self::CANCEL_ENDPOINT.$order->id.'&guest_id='.$this->owner->id)->assertUnauthorized();

        $this->assertSame('pending', $order->fresh()->order_status);
    }

    public function test_customer_cannot_cancel_another_customers_order_or_release_its_gift(): void
    {
        $order = $this->createOrderWithAttachedGift(customer: $this->owner);

        $this->actingAs($this->otherCustomer, 'api')->getJson(self::CANCEL_ENDPOINT.$order->id)
            ->assertForbidden()
            ->assertJsonStructure(['message']);

        $this->assertSame('pending', $order->fresh()->order_status);
        $this->assertSame($order->id, LuckyWheelWonGift::sole()->order_id);
    }

    public function test_owner_cancels_a_pending_cash_on_delivery_order_and_gets_the_gift_back(): void
    {
        $order = $this->createOrderWithAttachedGift(customer: $this->owner);
        $giftId = LuckyWheelWonGift::sole()->id;

        $this->actingAs($this->owner, 'api')->getJson(self::CANCEL_ENDPOINT.$order->id)->assertOk();

        $this->assertSame('canceled', $order->fresh()->order_status);
        $this->assertNull(LuckyWheelWonGift::sole()->order_id);
        $this->assertSame($giftId, LuckyWheelWonGift::sole()->id);

        $this->actingAs($this->owner, 'api')->getJson(self::CANCEL_ENDPOINT.$order->id)->assertForbidden();
        $this->assertSame(1, LuckyWheelWonGift::count());

        $nextOrder = $this->createOrder(customerId: $this->owner->id);
        DB::table('order_details')->insert(['order_id' => $nextOrder->id, 'price' => 1300, 'qty' => 1]);
        $this->assertSame(1, app(LuckyWheelService::class)->attachPendingGiftsToQualifyingOrders(customerId: $this->owner->id, orderIds: [$nextOrder->id]));
        $this->assertSame([$giftId => $nextOrder->id], LuckyWheelWonGift::pluck('order_id', 'id')->all());
    }

    public function test_existing_cancellation_rules_still_apply_to_the_owner(): void
    {
        $paidOnlineOrder = $this->createOrder(customerId: $this->owner->id, paymentMethod: 'digital_payment');
        $confirmedOrder = $this->createOrder(customerId: $this->owner->id, orderStatus: 'confirmed');

        $this->actingAs($this->owner, 'api')->getJson(self::CANCEL_ENDPOINT.$paidOnlineOrder->id)->assertForbidden();
        $this->actingAs($this->owner, 'api')->getJson(self::CANCEL_ENDPOINT.$confirmedOrder->id)->assertForbidden();

        $this->assertSame(['pending', 'confirmed'], [$paidOnlineOrder->fresh()->order_status, $confirmedOrder->fresh()->order_status]);
    }

    public function test_cancelling_an_unknown_order_returns_not_found(): void
    {
        $this->actingAs($this->owner, 'api')->getJson(self::CANCEL_ENDPOINT.'999999')->assertNotFound();
        $this->actingAs($this->owner, 'api')->getJson('/api/v1/order/cancel-order')->assertForbidden()->assertJsonStructure(['errors']);
    }

    public function test_web_cancel_route_requires_login_and_ownership(): void
    {
        $order = $this->createOrder(customerId: $this->owner->id);
        $cancelUrl = route('order-cancel', ['id' => $order->id]);

        $this->assertContains('customer', app('router')->getRoutes()->getByName('order-cancel')->gatherMiddleware());

        $this->actingAs($this->otherCustomer->fresh(), 'customer')->from('/account-oder')->get($cancelUrl)->assertRedirect('/account-oder');
        $this->assertSame('pending', $order->fresh()->order_status);
    }

    public function test_order_details_require_a_caller_identity(): void
    {
        $order = $this->createOrder(customerId: $this->owner->id);

        $this->getJson(self::ORDER_ENDPOINT.$order->id, self::BEARER)->assertUnauthorized();
        $this->getJson(self::ORDER_ENDPOINT.$order->id)->assertJsonMissingPath('id')->assertJsonMissingPath('order_status');
    }

    public function test_customer_cannot_read_another_customers_order(): void
    {
        $order = $this->createOrderWithAttachedGift(customer: $this->owner);

        $this->actingAs($this->otherCustomer, 'api')->getJson(self::ORDER_ENDPOINT.$order->id, self::BEARER)
            ->assertForbidden()
            ->assertExactJson(['message' => translate('access_Denied')]);
    }

    public function test_owner_reads_their_order_with_its_existing_fields_and_gifts(): void
    {
        $order = $this->createOrderWithAttachedGift(customer: $this->owner);

        $this->actingAs($this->owner, 'api')->getJson(self::ORDER_ENDPOINT.$order->id, self::BEARER)
            ->assertOk()
            ->assertJsonPath('id', $order->id)
            ->assertJsonPath('customer_id', $this->owner->id)
            ->assertJsonPath('order_status', 'pending')
            ->assertJsonStructure(['order_details_count', 'delivery_man', 'verification_images', 'order_edit_history', 'lucky_wheel_gifts' => [['id', 'name', 'status']]]);
    }

    public function test_guest_reads_only_their_own_guest_order(): void
    {
        $guestOrder = $this->createOrder(customerId: 55, isGuest: true);
        $customerOrderWithSameId = $this->createOrder(customerId: 55);

        $this->getJson(self::ORDER_ENDPOINT.$guestOrder->id.'&guest_id=55')->assertOk()->assertJsonPath('id', $guestOrder->id);
        $this->getJson(self::ORDER_ENDPOINT.$guestOrder->id.'&guest_id=56')->assertForbidden();
        $this->getJson(self::ORDER_ENDPOINT.$customerOrderWithSameId->id.'&guest_id=55')->assertForbidden();
    }

    public function test_unknown_order_details_return_not_found(): void
    {
        $this->actingAs($this->owner, 'api')->getJson(self::ORDER_ENDPOINT.'999999', self::BEARER)->assertNotFound();
    }

    private function createOrder(int $customerId, string $orderStatus = 'pending', string $paymentMethod = 'cash_on_delivery', bool $isGuest = false): Order
    {
        return Order::forceCreate([
            'customer_id' => $customerId,
            'is_guest' => $isGuest,
            'order_status' => $orderStatus,
            'payment_method' => $paymentMethod,
            'order_amount' => 1200,
        ]);
    }

    private function createOrderWithAttachedGift(User $customer): Order
    {
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->createReward(name: '50g Salmon');
        $luckyWheelService = app(LuckyWheelService::class);
        $luckyWheelService->spin(customerId: $customer->id);

        $order = $this->createOrder(customerId: $customer->id);
        $luckyWheelService->attachPendingGiftsToOrder(customerId: $customer->id, orderId: $order->id);
        return $order;
    }
}
