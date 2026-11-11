<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\DeliveryAppStaffService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduledOrdersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_type')->default('default_type');
            $table->string('order_status')->default('pending');
            $table->string('order_source')->nullable();
            $table->string('external_order_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('delivery_man_id')->nullable();
            $table->text('shipping_address_data')->nullable();
            $table->date('scheduled_for')->nullable();
            $table->timestamps();
        });
    }

    private function createOrder(string $status, ?string $scheduledFor, string $type = 'default_type'): int
    {
        return DB::table('orders')->insertGetId([
            'order_status' => $status,
            'order_type' => $type,
            'scheduled_for' => $scheduledFor,
        ]);
    }

    public function test_postponed_scope_returns_only_open_orders_with_a_scheduled_day(): void
    {
        $postponedPending = $this->createOrder(status: 'pending', scheduledFor: '2026-11-16');
        $postponedConfirmed = $this->createOrder(status: 'confirmed', scheduledFor: '2026-11-17');
        $this->createOrder(status: 'pending', scheduledFor: null);
        $this->createOrder(status: 'delivered', scheduledFor: '2026-11-12');
        $this->createOrder(status: 'canceled', scheduledFor: '2026-11-12');

        $ids = Order::postponed()->orderBy('id')->pluck('id')->all();

        $this->assertSame([$postponedPending, $postponedConfirmed], $ids);
    }

    public function test_staff_service_updates_and_clears_scheduled_day(): void
    {
        $orderId = $this->createOrder(status: 'pending', scheduledFor: null);
        $service = app(DeliveryAppStaffService::class);

        $service->updateScheduledFor(orderId: $orderId, scheduledFor: '2026-11-16');
        $this->assertSame('2026-11-16', Order::findOrFail($orderId)['scheduled_for']->format('Y-m-d'));

        $service->updateScheduledFor(orderId: $orderId, scheduledFor: null);
        $this->assertNull(Order::findOrFail($orderId)['scheduled_for']);
    }

    public function test_staff_service_ignores_pos_orders_when_updating_scheduled_day(): void
    {
        $orderId = $this->createOrder(status: 'pending', scheduledFor: null, type: 'POS');

        app(DeliveryAppStaffService::class)->updateScheduledFor(orderId: $orderId, scheduledFor: '2026-11-16');

        $this->assertNull(Order::findOrFail($orderId)['scheduled_for']);
    }

    public function test_staff_postponed_filter_and_count(): void
    {
        $this->createOrder(status: 'pending', scheduledFor: '2026-11-16');
        $this->createOrder(status: 'pending', scheduledFor: null);
        $this->createOrder(status: 'delivered', scheduledFor: '2026-11-12');
        $service = app(DeliveryAppStaffService::class);

        $orders = $service->getOrders(filters: ['postponed' => '1'], limit: 20, offset: 1);

        $this->assertSame(1, $orders->total());
        $this->assertSame(1, $service->getPostponedCount(filters: ['status' => 'delivered']));
    }
}
