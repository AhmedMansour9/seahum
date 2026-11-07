<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\DeliveryManAssignmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeliveryManAutoAssignmentTest extends TestCase
{
    private const ADMIN_OWNER_ID = 0;

    private const VENDOR_ID = 7;

    private DeliveryManAssignmentService $assignmentService;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('delivery_men', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->string('f_name')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_online')->default(1);
            $table->timestamps();
        });
        (require base_path('database/migrations/2026_11_08_100000_add_app_role_to_delivery_men_table.php'))->up();
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('seller_is')->default('admin');
            $table->string('shipping_responsibility')->default('inhouse_shipping');
            $table->string('order_status')->default('pending');
            $table->unsignedBigInteger('delivery_man_id')->nullable();
            $table->string('delivery_type')->nullable();
            $table->string('delivery_service_name')->nullable();
            $table->string('third_party_delivery_tracking_id')->nullable();
            $table->timestamp('deliveryman_assigned_at')->nullable();
            $table->timestamps();
        });
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->text('product_details')->nullable();
            $table->timestamps();
        });
        Schema::create('storages', function (Blueprint $table) {
            $table->id();
            $table->string('data_type');
            $table->unsignedBigInteger('data_id');
            $table->string('key')->nullable();
            $table->string('value')->nullable();
        });

        $this->assignmentService = app(DeliveryManAssignmentService::class);
    }

    public function test_order_goes_to_the_active_delivery_man_with_the_fewest_open_orders(): void
    {
        $busyDeliveryManId = $this->createDeliveryMan(sellerId: self::ADMIN_OWNER_ID);
        $freeDeliveryManId = $this->createDeliveryMan(sellerId: self::ADMIN_OWNER_ID);
        $inactiveDeliveryManId = $this->createDeliveryMan(sellerId: self::ADMIN_OWNER_ID, isActive: false);
        $this->createOrder(deliveryManId: $busyDeliveryManId, orderStatus: 'out_for_delivery');
        $this->createOrder(deliveryManId: $freeDeliveryManId, orderStatus: 'delivered');
        $this->createOrder(deliveryManId: $freeDeliveryManId, orderStatus: 'canceled');
        $order = $this->createOrder();

        $assignedDeliveryMan = $this->assignmentService->assignFirstAvailableDeliveryMan(order: $order);

        $this->assertSame($freeDeliveryManId, $assignedDeliveryMan->id);
        $order->refresh();
        $this->assertSame($freeDeliveryManId, (int)$order->delivery_man_id);
        $this->assertSame('self_delivery', $order->delivery_type);
        $this->assertNotNull($order->deliveryman_assigned_at);
        $this->assertNotSame($inactiveDeliveryManId, (int)$order->delivery_man_id);
    }

    public function test_tie_on_open_orders_goes_to_the_lowest_id(): void
    {
        $firstDeliveryManId = $this->createDeliveryMan(sellerId: self::ADMIN_OWNER_ID);
        $this->createDeliveryMan(sellerId: self::ADMIN_OWNER_ID);

        $assignedDeliveryMan = $this->assignmentService->assignFirstAvailableDeliveryMan(order: $this->createOrder());

        $this->assertSame($firstDeliveryManId, $assignedDeliveryMan->id);
    }

    public function test_vendor_order_uses_vendor_delivery_men_only_under_sellerwise_shipping(): void
    {
        $adminDeliveryManId = $this->createDeliveryMan(sellerId: self::ADMIN_OWNER_ID);
        $vendorDeliveryManId = $this->createDeliveryMan(sellerId: self::VENDOR_ID);

        $sellerwiseOrder = $this->createOrder(sellerIs: 'seller', sellerId: self::VENDOR_ID, shippingResponsibility: 'sellerwise_shipping');
        $inhouseOrder = $this->createOrder(sellerIs: 'seller', sellerId: self::VENDOR_ID, shippingResponsibility: 'inhouse_shipping');

        $this->assertSame($vendorDeliveryManId, $this->assignmentService->assignFirstAvailableDeliveryMan(order: $sellerwiseOrder)->id);
        $this->assertSame($adminDeliveryManId, $this->assignmentService->assignFirstAvailableDeliveryMan(order: $inhouseOrder)->id);
    }

    public function test_order_stays_unassigned_when_no_active_delivery_man_or_only_digital_products(): void
    {
        $this->createDeliveryMan(sellerId: self::ADMIN_OWNER_ID, isActive: false);
        $orderWithoutDeliveryMan = $this->createOrder();

        $this->assertNull($this->assignmentService->assignFirstAvailableDeliveryMan(order: $orderWithoutDeliveryMan));
        $this->assertNull($orderWithoutDeliveryMan->refresh()->delivery_man_id);

        $this->createDeliveryMan(sellerId: self::ADMIN_OWNER_ID);
        $digitalOrder = $this->createOrder(productType: 'digital');

        $this->assertNull($this->assignmentService->assignFirstAvailableDeliveryMan(order: $digitalOrder));
        $this->assertNull($digitalOrder->refresh()->delivery_man_id);
    }

    private function createDeliveryMan(int $sellerId, bool $isActive = true): int
    {
        return DB::table('delivery_men')->insertGetId(['seller_id' => $sellerId, 'f_name' => 'Omar', 'is_active' => $isActive]);
    }

    private function createOrder(
        ?int $deliveryManId = null,
        string $orderStatus = 'pending',
        string $sellerIs = 'admin',
        int $sellerId = 1,
        string $shippingResponsibility = 'inhouse_shipping',
        string $productType = 'physical',
    ): Order
    {
        $orderId = DB::table('orders')->insertGetId([
            'seller_id' => $sellerId,
            'seller_is' => $sellerIs,
            'shipping_responsibility' => $shippingResponsibility,
            'order_status' => $orderStatus,
            'delivery_man_id' => $deliveryManId,
        ]);
        DB::table('order_details')->insert([
            'order_id' => $orderId,
            'product_details' => json_encode(['product_type' => $productType, 'unit_price' => 1000]),
        ]);

        return Order::findOrFail($orderId);
    }
}
