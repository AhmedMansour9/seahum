<?php

namespace Tests\Feature;

use App\Enums\DeliveryManAppRole;
use App\Enums\OrderSource;
use App\Models\BusinessSetting;
use App\Models\DeliveryMan;
use App\Models\Order;
use App\Services\StaffNotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class StaffNotificationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->longText('value')->nullable();
            $table->timestamps();
        });
        Schema::create('delivery_men', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->string('f_name')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('shipping_cost', 40, 20)->default(0);
            $table->text('shipping_address_data')->nullable();
            $table->string('order_type')->default('default_type');
            $table->timestamps();
        });
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->integer('qty')->default(1);
            $table->decimal('price', 40, 20)->default(0);
            $table->decimal('tax', 40, 20)->default(0);
            $table->decimal('discount', 40, 20)->default(0);
            $table->string('tax_model')->nullable();
            $table->timestamps();
        });
        (require base_path('database/migrations/2026_11_07_180000_add_order_source_to_orders_table.php'))->up();
        (require base_path('database/migrations/2026_11_08_100000_add_app_role_to_delivery_men_table.php'))->up();
        BusinessSetting::insert(['type' => 'currency_model', 'value' => 'single_currency']);
    }

    public function test_new_order_is_pushed_to_active_admin_and_customer_service_accounts_only(): void
    {
        $adminId = $this->createAccount(role: DeliveryManAppRole::Admin);
        $customerServiceId = $this->createAccount(role: DeliveryManAppRole::CustomerService);
        $this->createAccount(role: DeliveryManAppRole::DeliveryMan);
        $this->createAccount(role: DeliveryManAppRole::Admin, isActive: false);
        $order = $this->createOrder(source: OrderSource::EasyOrders);

        $notifiedIds = [];
        $service = Mockery::mock(StaffNotificationService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('sendPushNotificationToDeliveryManDevices')
            ->andReturnUsing(function (DeliveryMan $deliveryMan, array $data) use (&$notifiedIds, $order) {
                $notifiedIds[] = $deliveryMan->id;
                $this->assertSame(StaffNotificationService::NEW_ORDER_TYPE, $data['type']);
                $this->assertSame($order->id, $data['order_id']);
            });

        $service->notifyNewOrder(order: $order);

        $this->assertEqualsCanonicalizing([$adminId, $customerServiceId], $notifiedIds);
    }

    public function test_notification_text_has_order_number_source_total_and_customer(): void
    {
        $order = $this->createOrder(source: OrderSource::EasyOrders);

        $data = app(StaffNotificationService::class)->getNewOrderNotificationData(order: $order);

        $this->assertSame('طلب جديد #' . $order->id, $data['title']);
        $this->assertSame('إيزي أوردرز • 810.00 ج.م • Mona Adel', $data['description']);
    }

    public function test_a_failing_push_never_breaks_the_order_flow(): void
    {
        $this->createAccount(role: DeliveryManAppRole::Admin);
        $order = $this->createOrder(source: OrderSource::App);
        $service = Mockery::mock(StaffNotificationService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('sendPushNotificationToDeliveryManDevices')->andThrow(new \RuntimeException('FCM down'));

        $service->notifyNewOrder(order: $order);

        $this->assertTrue(true);
    }

    private function createAccount(DeliveryManAppRole $role, bool $isActive = true): int
    {
        static $phone = 500000000;

        return DB::table('delivery_men')->insertGetId([
            'f_name' => 'Staff',
            'phone' => (string)++$phone,
            'is_active' => $isActive ? 1 : 0,
            'app_role' => $role->value,
        ]);
    }

    private function createOrder(OrderSource $source): Order
    {
        $orderId = DB::table('orders')->insertGetId([
            'shipping_cost' => 60,
            'order_source' => $source->value,
            'external_order_id' => $source === OrderSource::EasyOrders ? 'eo-' . uniqid() : null,
            'shipping_address_data' => json_encode(['contact_person_name' => 'Mona Adel']),
        ]);
        DB::table('order_details')->insert(['order_id' => $orderId, 'qty' => 2, 'price' => 375]);

        return Order::findOrFail($orderId);
    }
}
