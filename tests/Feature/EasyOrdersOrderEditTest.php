<?php

namespace Tests\Feature;

use App\Enums\OrderSource;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\OrderActivityLog;
use App\Models\OrderDetail;
use App\Services\EasyOrdersOrderEditService;
use App\Services\OrderActivityLogService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EasyOrdersOrderEditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        BusinessSetting::insert(['type' => 'currency_model', 'value' => 'single_currency']);
    }

    public function test_updates_customer_items_note_and_recalculates_amount(): void
    {
        $orderId = $this->createOrder();
        [$keptItemId, $removedItemId] = OrderDetail::where('order_id', $orderId)->orderBy('id')->pluck('id')->all();

        $result = app(EasyOrdersOrderEditService::class)->updateOrder(orderId: $orderId, data: [
            'contact_person_name' => 'Mona Adel',
            'phone' => '01011112222',
            'address' => 'Maadi',
            'city' => 'Cairo',
            'order_note' => 'Call before delivery',
            'items' => [
                ['id' => $keptItemId, 'qty' => 3, 'price' => 150, 'remove' => '0'],
                ['id' => $removedItemId, 'qty' => 1, 'price' => 100, 'remove' => '1'],
            ],
            'new_items' => [
                ['name' => 'Shrimp Rice', 'qty' => 2, 'price' => 75],
                ['name' => '', 'qty' => 1, 'price' => 10],
            ],
        ]);

        $this->assertTrue($result['status']);
        $order = Order::findOrFail($orderId);
        $this->assertSame('Mona Adel', $order->shipping_address_data->contact_person_name);
        $this->assertSame('01011112222', $order->shipping_address_data->phone);
        $this->assertSame('Egypt', $order->shipping_address_data->country);
        $this->assertSame('Call before delivery', $order->order_note);
        $this->assertSame(600.0, (float)$order->order_amount);
        $this->assertSame(50.0, (float)$order->shipping_cost);

        $this->assertNull(OrderDetail::find($removedItemId));
        $this->assertSame(3, OrderDetail::findOrFail($keptItemId)->qty);
        $newItem = OrderDetail::where('order_id', $orderId)->whereNotIn('id', [$keptItemId])->sole();
        $this->assertSame('Shrimp Rice', json_decode($newItem->product_details, true)['name']);
        $this->assertNull($newItem->product_id);
        $this->assertSame(0, (int)$newItem->is_stock_decreased);
    }

    public function test_logs_who_changed_each_item_price_quantity_and_lines(): void
    {
        (require base_path('database/migrations/2026_11_28_065306_create_order_activity_logs_table.php'))->up();
        $orderActivityLogService = new OrderActivityLogService;
        $orderActivityLogService->setActorForTesting(['actor_type' => 'delivery_man', 'actor_id' => 12, 'actor_name' => 'Customer Service', 'source' => 'delivery_app', 'ip_address' => null]);
        $this->app->instance(OrderActivityLogService::class, $orderActivityLogService);
        $orderId = $this->createOrder();
        [$repricedItemId, $removedItemId] = OrderDetail::where('order_id', $orderId)->orderBy('id')->pluck('id')->all();

        app(EasyOrdersOrderEditService::class)->updateOrder(orderId: $orderId, data: [
            'contact_person_name' => 'Ahmed Ali',
            'phone' => '01000000000',
            'items' => [
                ['id' => $repricedItemId, 'qty' => 2, 'price' => 175],
                ['id' => $removedItemId, 'qty' => 1, 'price' => 100, 'remove' => '1'],
            ],
            'new_items' => [['name' => 'Shrimp Rice', 'qty' => 1, 'price' => 75]],
        ]);

        $itemLogs = OrderActivityLog::where(['order_id' => $orderId, 'field' => 'order_item'])->orderBy('old_value')->get(['old_value', 'new_value', 'actor_name']);
        $this->assertEqualsCanonicalizing([
            ['Grilled Sea Bass · 2 × 200', 'Grilled Sea Bass · 2 × 175'],
            ['Fried Calamari · 1 × 100', null],
            [null, 'Shrimp Rice · 1 × 75'],
        ], $itemLogs->map(fn (OrderActivityLog $log) => [$log->old_value, $log->new_value])->all());
        $this->assertSame(['Customer Service'], $itemLogs->pluck('actor_name')->unique()->values()->all());
    }

    public function test_catalog_order_edits_log_item_changes_for_admin_and_app_staff(): void
    {
        $editManager = file_get_contents(app_path('Traits/OrderEditManager.php'));

        $this->assertStringContainsString('$orderItemsBefore = $orderActivityLogService->snapshotOrderItems(orderId: (int)$order[\'id\']);', $editManager);
        $this->assertStringContainsString('$orderActivityLogService->recordItemChanges(orderId: (int)$order[\'id\'], itemsBefore: $orderItemsBefore);', $editManager);
    }

    public function test_refuses_to_remove_every_item(): void
    {
        $orderId = $this->createOrder();
        $itemIds = OrderDetail::where('order_id', $orderId)->pluck('id');

        $result = app(EasyOrdersOrderEditService::class)->updateOrder(orderId: $orderId, data: [
            'contact_person_name' => 'Mona Adel',
            'phone' => '01011112222',
            'items' => $itemIds->map(fn (int $id) => ['id' => $id, 'qty' => 1, 'price' => 100, 'remove' => '1'])->all(),
        ]);

        $this->assertFalse($result['status']);
        $this->assertSame(2, OrderDetail::where('order_id', $orderId)->count());
    }

    public function test_delivered_and_app_orders_cannot_be_edited(): void
    {
        $deliveredOrderId = $this->createOrder(status: 'delivered');
        $appOrderId = $this->createOrder(source: OrderSource::App);
        $data = ['contact_person_name' => 'Changed', 'phone' => '01000000000'];

        $this->assertFalse(app(EasyOrdersOrderEditService::class)->updateOrder(orderId: $deliveredOrderId, data: $data)['status']);
        $this->assertFalse(app(EasyOrdersOrderEditService::class)->updateOrder(orderId: $appOrderId, data: $data)['status']);
        $this->assertSame('Ahmed Ali', Order::findOrFail($deliveredOrderId)->shipping_address_data->contact_person_name);
    }

    private function createOrder(string $status = 'pending', OrderSource $source = OrderSource::EasyOrders): int
    {
        static $externalId = 0;
        $orderId = DB::table('orders')->insertGetId([
            'is_guest' => 1,
            'order_status' => $status,
            'payment_status' => 'unpaid',
            'order_amount' => 500,
            'shipping_cost' => 50,
            'seller_id' => 1,
            'seller_is' => 'admin',
            'order_source' => $source->value,
            'external_order_id' => $source === OrderSource::EasyOrders ? 'eo-' . ++$externalId : null,
            'shipping_address_data' => json_encode(['contact_person_name' => 'Ahmed Ali', 'phone' => '01000000000', 'country' => 'Egypt']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach ([['Grilled Sea Bass', 2, 200], ['Fried Calamari', 1, 100]] as [$name, $qty, $price]) {
            DB::table('order_details')->insert([
                'order_id' => $orderId,
                'seller_id' => 1,
                'product_details' => json_encode(['source' => 'easy_orders', 'name' => $name]),
                'qty' => $qty,
                'price' => $price,
                'is_stock_decreased' => 0,
            ]);
        }

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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_guest')->default(0);
            $table->string('order_status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->decimal('order_amount', 40, 20)->default(0);
            $table->decimal('shipping_cost', 40, 20)->default(0);
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('seller_is')->nullable();
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
            $table->decimal('tax', 40, 20)->default(0);
            $table->decimal('discount', 40, 20)->default(0);
            $table->string('delivery_status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('variant')->nullable();
            $table->string('variation')->nullable();
            $table->boolean('is_stock_decreased')->default(1);
            $table->timestamps();
        });

        (require base_path('database/migrations/2026_11_07_180000_add_order_source_to_orders_table.php'))->up();
    }
}
