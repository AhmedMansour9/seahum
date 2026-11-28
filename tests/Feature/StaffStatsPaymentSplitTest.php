<?php

namespace Tests\Feature;

use App\Services\DeliveryAppStaffService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The admin stats screen shows all delivered sales, split into paid online and offline (unpaid, cash).
 */
class StaffStatsPaymentSplitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_type')->default('default_type');
            $table->string('order_status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('order_source')->nullable();
            $table->decimal('order_amount', 24, 3)->default(0);
            $table->decimal('shipping_cost', 24, 3)->default(0);
            $table->unsignedBigInteger('delivery_man_id')->nullable();
            $table->timestamps();
        });
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('status');
            $table->timestamps();
        });
        Schema::create('delivery_men', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->boolean('is_online')->default(0);
            $table->string('app_role')->default('delivery_man');
            $table->timestamps();
        });
    }

    private function createDeliveredOrder(string $paymentStatus, float $amount, string $deliveredOn): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'order_status' => 'delivered',
            'payment_status' => $paymentStatus,
            'order_amount' => $amount,
            'updated_at' => $deliveredOn,
        ]);
        DB::table('order_status_histories')->insert(['order_id' => $orderId, 'status' => 'delivered', 'created_at' => $deliveredOn]);
    }

    public function test_delivered_sales_total_includes_paid_and_unpaid_and_splits_them(): void
    {
        $this->createDeliveredOrder(paymentStatus: 'paid', amount: 500, deliveredOn: '2026-11-28 10:00:00');
        $this->createDeliveredOrder(paymentStatus: 'paid', amount: 250, deliveredOn: '2026-11-28 11:00:00');
        $this->createDeliveredOrder(paymentStatus: 'unpaid', amount: 300, deliveredOn: '2026-11-28 12:00:00');
        $this->createDeliveredOrder(paymentStatus: 'unpaid', amount: 999, deliveredOn: '2026-11-27 12:00:00');

        $stats = app(DeliveryAppStaffService::class)->getStats(dateFrom: Carbon::parse('2026-11-28'), dateTo: Carbon::parse('2026-11-28'));

        $this->assertEquals(1050, $stats['delivered_amount']);
        $this->assertSame(3, $stats['delivered_orders_count']);
        $this->assertEquals(750, $stats['online_paid_amount']);
        $this->assertSame(2, $stats['online_paid_orders_count']);
        $this->assertEquals(300, $stats['offline_amount']);
        $this->assertSame(1, $stats['offline_orders_count']);
    }

    public function test_split_is_zero_without_delivered_orders(): void
    {
        $stats = app(DeliveryAppStaffService::class)->getStats(dateFrom: Carbon::parse('2026-11-28'), dateTo: Carbon::parse('2026-11-28'));

        $this->assertEquals(0, $stats['delivered_amount']);
        $this->assertEquals(0, $stats['online_paid_amount']);
        $this->assertEquals(0, $stats['offline_amount']);
    }
}
