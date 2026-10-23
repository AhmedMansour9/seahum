<?php

namespace Tests\Feature\LuckyWheel;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\LuckyWheel\LuckyWheelRewardType;
use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use App\Models\LuckyWheelWonGift;
use App\Models\Order;
use App\Models\User;
use App\Services\LuckyWheelService;
use App\Utils\OrderManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LuckyWheelOrderGiftTest extends TestCase
{
    use InteractsWithLuckyWheel;

    private LuckyWheelService $luckyWheelService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createLuckyWheelTables();
        $this->configureLuckyWheel(isEnabled: true, spinFrequency: LuckyWheelSpinFrequency::Daily);
        $this->luckyWheelService = app(LuckyWheelService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_winning_a_gift_stores_a_pending_gift_and_no_prize_stores_nothing(): void
    {
        $reward = $this->createReward(name: '50g Salmon');
        $customer = $this->createCustomer();

        $this->luckyWheelService->spin(customerId: $customer->id);

        $gift = LuckyWheelWonGift::sole();
        $this->assertSame([$customer->id, $reward->id, '50g Salmon', null], [$gift->customer_id, $gift->reward_id, $gift->name, $gift->order_id]);

        $reward->update(['type' => LuckyWheelRewardType::NoPrize]);
        $this->luckyWheelService->spin(customerId: $this->createCustomer(email: 'seller@gmail.com')->id);
        $this->assertSame(1, LuckyWheelWonGift::count());
    }

    public function test_all_pending_gifts_attach_to_the_next_order_once_and_do_not_change_its_total(): void
    {
        $this->createReward(name: '50g Salmon');
        $customer = $this->createCustomer();
        $this->winGiftsOnConsecutiveDays(customer: $customer, numberOfDays: 3);
        $firstOrder = $this->createOrder(customer: $customer);
        $retriedOrder = $this->createOrder(customer: $customer);

        $this->assertSame(3, $this->luckyWheelService->attachPendingGiftsToOrder(customerId: $customer->id, orderId: $firstOrder->id));
        $this->assertSame(0, $this->luckyWheelService->attachPendingGiftsToOrder(customerId: $customer->id, orderId: $retriedOrder->id));

        $this->assertSame(3, $firstOrder->luckyWheelGifts()->count());
        $this->assertSame(0, $retriedOrder->luckyWheelGifts()->count());
        $this->assertEquals(1000, $firstOrder->fresh()->order_amount);
    }

    public function test_gifts_of_other_customers_are_never_attached(): void
    {
        $this->createReward(name: '50g Salmon');
        $winner = $this->createCustomer();
        $otherCustomer = $this->createCustomer(email: 'seller@gmail.com');
        $this->luckyWheelService->spin(customerId: $winner->id);

        $this->luckyWheelService->attachPendingGiftsToOrder(customerId: $otherCustomer->id, orderId: $this->createOrder(customer: $otherCustomer)->id);

        $this->assertNull(LuckyWheelWonGift::sole()->order_id);
    }

    public function test_canceled_and_failed_orders_release_gifts_but_finished_orders_keep_them(): void
    {
        $this->createReward(name: '50g Salmon');
        $customer = $this->createCustomer();
        $orderRepo = app(OrderRepositoryInterface::class);

        foreach (['canceled', 'failed'] as $releasingStatus) {
            $order = $this->createOrderWithPendingGift(customer: $customer);
            $orderRepo->updateStockOnOrderStatusChange($order->id, $releasingStatus);
            $this->assertSame(0, $order->luckyWheelGifts()->count(), "{$releasingStatus} should release the gift");
            LuckyWheelWonGift::query()->delete();
        }

        foreach (['delivered', 'returned', 'confirmed'] as $keepingStatus) {
            $order = $this->createOrderWithPendingGift(customer: $customer);
            $orderRepo->updateStockOnOrderStatusChange($order->id, $keepingStatus);
            $this->assertSame(1, $order->luckyWheelGifts()->count(), "{$keepingStatus} should keep the gift");
            LuckyWheelWonGift::query()->delete();
        }
    }

    public function test_customer_cancellation_path_releases_gifts_for_the_next_order(): void
    {
        $this->createReward(name: '50g Salmon');
        $customer = $this->createCustomer();
        $canceledOrder = $this->createOrderWithPendingGift(customer: $customer);

        OrderManager::getStockUpdateOnOrderStatusChange(Order::with('details')->find($canceledOrder->id), 'canceled');

        $nextOrder = $this->createOrder(customer: $customer);
        $this->luckyWheelService->attachPendingGiftsToOrder(customerId: $customer->id, orderId: $nextOrder->id);
        $this->assertSame(1, $nextOrder->luckyWheelGifts()->count());
    }

    public function test_gift_snapshot_survives_reward_edits_and_deletion(): void
    {
        $reward = $this->createReward(name: '50g Salmon', arabicName: '٥٠ جرام سمك سلمون');
        $reward->update(['image' => 'original.webp', 'image_storage_type' => 'public']);
        $customer = $this->createCustomer();
        $order = $this->createOrderWithPendingGift(customer: $customer);

        $reward->update(['name' => 'Renamed', 'image' => 'replaced.webp', 'is_active' => false]);
        $reward->translations()->update(['value' => 'اسم جديد']);
        $reward->delete();

        $gift = $order->luckyWheelGifts()->sole();
        $this->assertSame('50g Salmon', $gift->name);
        $this->assertSame(['en' => '50g Salmon', 'eg' => '٥٠ جرام سمك سلمون'], $gift->name_translations);
        $this->assertSame('original.webp', $gift->image);
        $this->assertSame($reward->id, $gift->reward_id);
        $this->assertSame('50g Salmon', $gift->spin->reward_snapshot['name']);
    }

    public function test_order_api_lists_gifts_only_for_the_order_owner(): void
    {
        $this->createOrderApiTables();
        $this->createReward(name: '50g Salmon');
        $owner = $this->createCustomer();
        $order = $this->createOrderWithPendingGift(customer: $owner);

        $this->actingAs($owner, 'api')->getJson('/api/v1/customer/order/get-order-by-id?order_id='.$order->id, ['Authorization' => 'Bearer token'])
            ->assertOk()
            ->assertJsonPath('lucky_wheel_gifts.0.name', '50g Salmon')
            ->assertJsonPath('lucky_wheel_gifts.0.status', 'attached_to_order');

        $this->actingAs($this->createCustomer(email: 'seller@gmail.com'), 'api')
            ->getJson('/api/v1/customer/order/get-order-by-id?order_id='.$order->id, ['Authorization' => 'Bearer token'])
            ->assertOk()
            ->assertJsonPath('lucky_wheel_gifts', []);
    }

    private function winGiftsOnConsecutiveDays(User $customer, int $numberOfDays): void
    {
        foreach (range(1, $numberOfDays) as $day) {
            Carbon::setTestNow(Carbon::parse("2026-10-{$day} 12:00:00", 'Africa/Cairo'));
            $this->luckyWheelService->spin(customerId: $customer->id);
        }
        Carbon::setTestNow();
    }

    private function createOrder(User $customer): Order
    {
        return Order::forceCreate(['customer_id' => $customer->id, 'is_guest' => 0, 'order_status' => 'pending', 'order_amount' => 1000]);
    }

    private function createOrderWithPendingGift(User $customer): Order
    {
        Carbon::setTestNow(Carbon::now()->addDays(LuckyWheelWonGift::withoutGlobalScopes()->count() + 1 + random_int(0, 1000)));
        $this->luckyWheelService->spin(customerId: $customer->id);
        Carbon::setTestNow();

        $order = $this->createOrder(customer: $customer);
        $this->luckyWheelService->attachPendingGiftsToOrder(customerId: $customer->id, orderId: $order->id);
        return $order;
    }

    private function createOrderApiTables(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_man_id')->nullable();
        });
        Schema::create('order_delivery_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
        });
        Schema::create('order_edit_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->timestamps();
        });
        Schema::create('offline_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->text('payment_info')->nullable();
        });
    }
}
