<?php

namespace Tests\Feature;

use App\Enums\DeliveryManAppRole;
use App\Enums\OrderSource;
use App\Events\OrderStatusEvent;
use App\Http\Middleware\ActivationCheckMiddleware;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Repositories\DeliveryManRepository;
use App\Services\OrderStatusUpdateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class DeliveryAppStaffApiTest extends TestCase
{
    private const STAFF_ENDPOINT = '/api/v2/delivery-man/staff/';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->withoutMiddleware(ActivationCheckMiddleware::class);
        Event::fake([OrderStatusEvent::class]);
    }

    public function test_delivery_man_cannot_open_staff_endpoints(): void
    {
        $token = $this->createAccount(role: DeliveryManAppRole::DeliveryMan);

        $this->getJson(self::STAFF_ENDPOINT . 'orders', $this->authHeader($token))->assertForbidden();
    }

    public function test_customer_service_sees_every_order_with_its_source(): void
    {
        $token = $this->createAccount(role: DeliveryManAppRole::CustomerService);
        $deliveryManId = $this->createDeliveryMan(phone: '500000002');
        $this->createOrder(deliveryManId: $deliveryManId);
        $easyOrderId = $this->createOrder(source: OrderSource::EasyOrders, externalOrderId: 'eo-1');

        $response = $this->getJson(self::STAFF_ENDPOINT . 'orders', $this->authHeader($token))->assertOk();

        $response->assertJsonPath('total_size', 2);
        $response->assertJsonPath('orders.0.id', $easyOrderId);
        $response->assertJsonPath('orders.0.order_source', OrderSource::EasyOrders->value);
        $response->assertJsonPath('orders.0.customer.name', 'Mona Adel');
        $response->assertJsonPath('orders.0.total_amount', 450);
        $response->assertJsonPath('orders.1.delivery_man.id', $deliveryManId);
    }

    public function test_orders_can_be_filtered_by_status(): void
    {
        $token = $this->createAccount(role: DeliveryManAppRole::CustomerService);
        $this->createOrder(status: 'pending');
        $confirmedOrderId = $this->createOrder(status: 'confirmed');

        $this->getJson(self::STAFF_ENDPOINT . 'orders?status=confirmed', $this->authHeader($token))
            ->assertOk()
            ->assertJsonPath('total_size', 1)
            ->assertJsonPath('orders.0.id', $confirmedOrderId);
    }

    public function test_order_details_include_order_items(): void
    {
        $token = $this->createAccount(role: DeliveryManAppRole::CustomerService);
        $orderId = $this->createOrder(source: OrderSource::EasyOrders, externalOrderId: 'eo-1');

        $this->getJson(self::STAFF_ENDPOINT . 'order-details?order_id=' . $orderId, $this->authHeader($token))
            ->assertOk()
            ->assertJsonPath('items.0.name', 'Grilled Sea Bass')
            ->assertJsonPath('items.0.qty', 2);

        $this->getJson(self::STAFF_ENDPOINT . 'order-details?order_id=999', $this->authHeader($token))->assertNotFound();
    }

    public function test_customer_service_status_change_on_easy_orders_order_uses_the_easy_orders_lifecycle(): void
    {
        $staffId = null;
        $token = $this->createAccount(role: DeliveryManAppRole::CustomerService, accountId: $staffId);
        $orderId = $this->createOrder(source: OrderSource::EasyOrders, externalOrderId: 'eo-1');
        $this->mock(OrderStatusUpdateService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('updateStatus'));

        $this->putJson(self::STAFF_ENDPOINT . 'update-order-status', ['order_id' => $orderId, 'status' => 'confirmed'], $this->authHeader($token))
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertSame('confirmed', Order::findOrFail($orderId)->order_status);
        $history = OrderStatusHistory::sole();
        $this->assertSame(['delivery_man', $staffId], [$history->user_type, (int)$history->user_id]);
    }

    public function test_customer_service_status_change_on_app_order_uses_the_admin_status_flow(): void
    {
        $staffId = null;
        $token = $this->createAccount(role: DeliveryManAppRole::CustomerService, accountId: $staffId);
        $orderId = $this->createOrder();
        $this->mock(OrderStatusUpdateService::class, function (MockInterface $mock) use ($orderId, $staffId) {
            $mock->shouldReceive('updateStatus')
                ->once()
                ->with($orderId, 'delivered', $staffId, 'delivery_man')
                ->andReturn(['status' => 1, 'message' => 'ok']);
        });

        $this->putJson(self::STAFF_ENDPOINT . 'update-order-status', ['order_id' => $orderId, 'status' => 'delivered'], $this->authHeader($token))
            ->assertOk()
            ->assertJsonPath('status', 1);
    }

    public function test_status_change_rejects_unknown_status(): void
    {
        $token = $this->createAccount(role: DeliveryManAppRole::CustomerService);
        $orderId = $this->createOrder();

        $this->putJson(self::STAFF_ENDPOINT . 'update-order-status', ['order_id' => $orderId, 'status' => 'lost'], $this->authHeader($token))
            ->assertForbidden();
    }

    public function test_customer_service_cannot_use_admin_only_endpoints(): void
    {
        $token = $this->createAccount(role: DeliveryManAppRole::CustomerService);
        $orderId = $this->createOrder();

        $this->putJson(self::STAFF_ENDPOINT . 'assign-delivery-man', ['order_id' => $orderId, 'delivery_man_id' => 1], $this->authHeader($token))->assertForbidden();
        $this->putJson(self::STAFF_ENDPOINT . 'update-payment-status', ['order_id' => $orderId, 'payment_status' => 'paid'], $this->authHeader($token))->assertForbidden();
        $this->getJson(self::STAFF_ENDPOINT . 'delivery-men', $this->authHeader($token))->assertForbidden();
        $this->getJson(self::STAFF_ENDPOINT . 'dashboard-stats', $this->authHeader($token))->assertForbidden();
    }

    public function test_admin_assigns_only_active_delivery_men(): void
    {
        $token = $this->createAccount(role: DeliveryManAppRole::Admin);
        $deliveryManId = $this->createDeliveryMan(phone: '500000002');
        $customerServiceId = $this->createDeliveryMan(phone: '500000003', role: DeliveryManAppRole::CustomerService);
        $orderId = $this->createOrder();

        $this->putJson(self::STAFF_ENDPOINT . 'assign-delivery-man', ['order_id' => $orderId, 'delivery_man_id' => $customerServiceId], $this->authHeader($token))
            ->assertNotFound();
        $this->assertNull(Order::findOrFail($orderId)->delivery_man_id);

        $this->putJson(self::STAFF_ENDPOINT . 'assign-delivery-man', ['order_id' => $orderId, 'delivery_man_id' => $deliveryManId], $this->authHeader($token))
            ->assertOk();
        $this->assertSame($deliveryManId, (int)Order::findOrFail($orderId)->delivery_man_id);
        Event::assertDispatched(OrderStatusEvent::class, fn (OrderStatusEvent $event): bool => $event->key === 'new_order_assigned_message');
    }

    public function test_admin_updates_payment_status(): void
    {
        $token = $this->createAccount(role: DeliveryManAppRole::Admin);
        $orderId = $this->createOrder();

        $this->putJson(self::STAFF_ENDPOINT . 'update-payment-status', ['order_id' => $orderId, 'payment_status' => 'paid'], $this->authHeader($token))
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertSame('paid', Order::findOrFail($orderId)->payment_status);
    }

    public function test_admin_sees_delivery_men_and_today_stats_without_staff_accounts(): void
    {
        $token = $this->createAccount(role: DeliveryManAppRole::Admin);
        $deliveryManId = $this->createDeliveryMan(phone: '500000002', isOnline: true);
        $this->createOrder(deliveryManId: $deliveryManId, status: 'out_for_delivery');
        $this->createOrder(status: 'delivered');
        $this->createOrder(source: OrderSource::EasyOrders, externalOrderId: 'eo-1');

        $this->getJson(self::STAFF_ENDPOINT . 'delivery-men', $this->authHeader($token))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $deliveryManId)
            ->assertJsonPath('0.open_orders_count', 1);

        $this->getJson(self::STAFF_ENDPOINT . 'dashboard-stats', $this->authHeader($token))
            ->assertOk()
            ->assertJsonPath('total_orders', 3)
            ->assertJsonPath('status_counts.delivered', 1)
            ->assertJsonPath('easy_orders_count', 1)
            ->assertJsonPath('open_orders', 2)
            ->assertJsonPath('unassigned_open_orders', 1)
            ->assertJsonPath('online_delivery_men', 1);
    }

    public function test_auto_assignment_never_picks_a_staff_account(): void
    {
        $this->createAccount(role: DeliveryManAppRole::CustomerService);
        $deliveryManId = $this->createDeliveryMan(phone: '500000002');
        $this->createOrder(deliveryManId: $deliveryManId);

        $this->assertSame($deliveryManId, app(DeliveryManRepository::class)->getActiveWithFewestOpenOrders(sellerId: 0)->id);
    }

    /**
     * @return array<string, string>
     */
    private function authHeader(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    private function createAccount(DeliveryManAppRole $role, ?int &$accountId = null): string
    {
        $accountId = $this->createDeliveryMan(phone: '500000001', role: $role);
        $token = Str::random(50);
        DB::table('delivery_man_devices')->insert(['delivery_man_id' => $accountId, 'auth_token' => $token]);

        return $token;
    }

    private function createDeliveryMan(string $phone, DeliveryManAppRole $role = DeliveryManAppRole::DeliveryMan, bool $isOnline = false): int
    {
        return DB::table('delivery_men')->insertGetId([
            'f_name' => 'Omar',
            'l_name' => 'Hassan',
            'country_code' => '+20',
            'phone' => $phone,
            'is_active' => 1,
            'is_online' => $isOnline ? 1 : 0,
            'app_role' => $role->value,
        ]);
    }

    private function createOrder(OrderSource $source = OrderSource::App, ?string $externalOrderId = null, ?int $deliveryManId = null, string $status = 'pending'): int
    {
        $orderId = DB::table('orders')->insertGetId([
            'is_guest' => 1,
            'order_status' => $status,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash_on_delivery',
            'order_amount' => 450,
            'shipping_cost' => 50,
            'delivery_man_id' => $deliveryManId,
            'seller_id' => 1,
            'seller_is' => 'admin',
            'order_type' => 'default_type',
            'order_source' => $source->value,
            'external_order_id' => $externalOrderId,
            'shipping_address_data' => json_encode(['contact_person_name' => 'Mona Adel', 'phone' => '01000000000', 'address' => 'Nasr City', 'city' => 'Cairo']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('order_details')->insert([
            'order_id' => $orderId,
            'product_id' => null,
            'seller_id' => 1,
            'product_details' => json_encode(['name' => 'Grilled Sea Bass', 'sku' => 'SB-1']),
            'qty' => 2,
            'price' => 200,
            'delivery_status' => $status,
            'payment_status' => 'unpaid',
            'is_stock_decreased' => 0,
        ]);

        return $orderId;
    }

    private function createTables(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->longText('value')->nullable();
            $table->timestamps();
        });
        Schema::create('storages', function (Blueprint $table) {
            $table->id();
            $table->string('data_type');
            $table->string('data_id', 100)->index();
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->timestamps();
        });
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->timestamps();
        });
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('contact_person_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();
        });
        Schema::create('delivery_men', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('country_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('image')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_online')->default(0);
            $table->string('auth_token')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('app_language')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->nullable();
            $table->boolean('is_guest')->default(0);
            $table->boolean('is_pause')->default(0);
            $table->string('order_status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->decimal('order_amount', 40, 20)->default(0);
            $table->decimal('shipping_cost', 40, 20)->default(0);
            $table->decimal('discount_amount', 40, 20)->default(0);
            $table->unsignedBigInteger('delivery_man_id')->nullable();
            $table->string('delivery_type')->nullable();
            $table->string('delivery_service_name')->nullable();
            $table->string('third_party_delivery_tracking_id')->nullable();
            $table->timestamp('deliveryman_assigned_at')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('seller_is')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->text('shipping_address_data')->nullable();
            $table->text('order_note')->nullable();
            $table->string('order_type')->default('default_type');
            $table->timestamps();
        });
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->longText('product_details')->nullable();
            $table->integer('qty')->default(0);
            $table->decimal('price', 40, 20)->default(0);
            $table->decimal('discount', 40, 20)->default(0);
            $table->string('delivery_status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('variant')->nullable();
            $table->boolean('is_stock_decreased')->default(1);
            $table->timestamps();
        });
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id');
            $table->bigInteger('user_id');
            $table->string('user_type');
            $table->string('status');
            $table->string('cause')->nullable();
            $table->timestamps();
        });

        foreach ([
            '2026_10_29_002728_create_delivery_man_devices_table.php',
            '2026_11_07_180000_add_order_source_to_orders_table.php',
            '2026_11_08_100000_add_app_role_to_delivery_men_table.php',
        ] as $migration) {
            (require base_path('database/migrations/' . $migration))->up();
        }
    }
}
