<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\DeliveryAppStaffOrderCreateService;
use App\Services\DeliveryAppStaffService;
use App\Services\StaffNotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
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
            $table->string('governorate', 100)->nullable();
            $table->timestamps();
        });
        Schema::create('delivery_men', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->boolean('is_active')->default(1);
            $table->string('app_role')->default('delivery_man');
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

    public function test_staff_postponed_count_ignores_status_and_postponed_filters(): void
    {
        $this->createOrder(status: 'pending', scheduledFor: '2026-11-16');
        $this->createOrder(status: 'pending', scheduledFor: null);
        $this->createOrder(status: 'delivered', scheduledFor: '2026-11-12');
        $service = app(DeliveryAppStaffService::class);

        $this->assertSame(1, $service->getPostponedCount(filters: []));
        $this->assertSame(1, $service->getPostponedCount(filters: ['status' => 'delivered', 'postponed' => '1']));
    }

    public function test_staff_create_order_accepts_only_today_or_later_scheduled_day(): void
    {
        $rules = ['scheduled_for' => DeliveryAppStaffOrderCreateService::getValidationRules()['scheduled_for']];

        $this->assertTrue(Validator::make(['scheduled_for' => now()->addDay()->format('Y-m-d')], $rules)->passes());
        $this->assertTrue(Validator::make(['scheduled_for' => null], $rules)->passes());
        $this->assertTrue(Validator::make(['scheduled_for' => now()->subDay()->format('Y-m-d')], $rules)->fails());
    }

    public function test_scheduled_day_is_serialized_as_plain_date_for_the_apps(): void
    {
        $orderId = $this->createOrder(status: 'pending', scheduledFor: '2026-11-16');

        $this->assertSame('2026-11-16', Order::findOrFail($orderId)->toArray()['scheduled_for']);
    }

    public function test_due_today_notification_counts_only_open_orders_scheduled_for_today(): void
    {
        $this->createOrder(status: 'pending', scheduledFor: today()->format('Y-m-d'));
        $this->createOrder(status: 'confirmed', scheduledFor: today()->format('Y-m-d'));
        $this->createOrder(status: 'delivered', scheduledFor: today()->format('Y-m-d'));
        $this->createOrder(status: 'pending', scheduledFor: today()->addDay()->format('Y-m-d'));
        $this->createOrder(status: 'pending', scheduledFor: today()->format('Y-m-d'), type: 'POS');

        $this->assertSame(2, app(StaffNotificationService::class)->notifyPostponedOrdersDueToday());
    }

    public function test_governorate_scope_returns_only_orders_with_an_assigned_governorate(): void
    {
        $alexandria = DB::table('orders')->insertGetId(['governorate' => 'الإسكندرية']);
        $this->createOrder(status: 'pending', scheduledFor: null);
        $aswan = DB::table('orders')->insertGetId(['governorate' => 'أسوان']);

        $this->assertSame([$alexandria, $aswan], Order::governorate()->orderBy('id')->pluck('id')->all());
    }

    public function test_staff_service_assigns_and_clears_governorate_but_ignores_pos_orders(): void
    {
        $orderId = $this->createOrder(status: 'pending', scheduledFor: null);
        $posOrderId = $this->createOrder(status: 'pending', scheduledFor: null, type: 'POS');
        $service = app(DeliveryAppStaffService::class);

        $service->updateGovernorate(orderId: $orderId, governorate: 'الإسكندرية');
        $service->updateGovernorate(orderId: $posOrderId, governorate: 'الإسكندرية');
        $this->assertSame('الإسكندرية', Order::findOrFail($orderId)['governorate']);
        $this->assertNull(Order::findOrFail($posOrderId)['governorate']);

        $service->updateGovernorate(orderId: $orderId, governorate: null);
        $this->assertNull(Order::findOrFail($orderId)['governorate']);
    }

    public function test_governorate_options_exclude_greater_cairo(): void
    {
        $options = config('order_regions.governorates');

        $this->assertContains('الإسكندرية', $options);
        $this->assertNotContains('القاهرة', $options);
        $this->assertNotContains('الجيزة', $options);
    }
}
