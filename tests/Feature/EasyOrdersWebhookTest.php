<?php

namespace Tests\Feature;

use App\Enums\OrderSource;
use App\Events\OrderStatusEvent;
use App\Models\AdminWallet;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\OrderTransaction;
use App\Models\Shop;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EasyOrdersWebhookTest extends TestCase
{
    private const ENDPOINT = '/api/v1/webhooks/easy-orders';
    private const SECRET = 'easy-orders-test-secret';
    private const SECRET_HEADER = ['secret' => self::SECRET];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createOrderTables();
        BusinessSetting::insert(['type' => 'currency_model', 'value' => 'single_currency']);
        config(['easy_orders.webhook_secret' => self::SECRET]);
    }

    public function test_returns_401_when_secret_header_is_wrong(): void
    {
        $this->postJson(self::ENDPOINT, $this->orderPayload(), ['secret' => 'wrong'])->assertUnauthorized();

        $this->assertSame(0, Order::count());
    }

    public function test_returns_401_when_no_secret_is_configured(): void
    {
        config(['easy_orders.webhook_secret' => null]);

        $this->postJson(self::ENDPOINT, $this->orderPayload(), ['secret' => ''])->assertUnauthorized();
    }

    public function test_returns_422_when_order_has_no_cart_items(): void
    {
        $this->postJson(self::ENDPOINT, $this->orderPayload(['cart_items' => []]), self::SECRET_HEADER)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cart_items']);
    }

    public function test_created_order_is_stored_with_easy_orders_source_and_product_details(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->orderPayload(), self::SECRET_HEADER)->assertOk();

        $order = Order::firstOrFail();
        $response->assertJson(['order_id' => $order->id]);
        $this->assertSame(OrderSource::EasyOrders, $order->order_source);
        $this->assertSame('eo-order-1', $order->external_order_id);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('cash_on_delivery', $order->payment_method);
        $this->assertSame(400.0, $order->order_amount);
        $this->assertSame(400.0, $order->init_order_amount);
        $this->assertSame(50.0, $order->shipping_cost);
        $this->assertSame('Ahmed Ali', $order->shipping_address_data->contact_person_name);
        $this->assertSame('Cairo', $order->shipping_address_data->city);

        $detail = OrderDetail::firstOrFail();
        $productDetails = json_decode($detail->product_details, true);
        $this->assertNull($detail->product_id);
        $this->assertSame(2, $detail->qty);
        $this->assertSame('Grilled Sea Bass', $productDetails['name']);
        $this->assertSame('SB-1', $productDetails['sku']);
        $this->assertSame('Size: Large', $detail->variant);
    }

    public function test_order_amount_excludes_shipping_when_cost_is_missing(): void
    {
        $payload = $this->orderPayload();
        unset($payload['cost']);

        $this->postJson(self::ENDPOINT, $payload, self::SECRET_HEADER)->assertOk();

        $order = Order::firstOrFail();
        $this->assertSame(400.0, $order->order_amount);
        $this->assertSame(50.0, $order->shipping_cost);
    }

    public function test_repeated_delivery_of_same_order_does_not_duplicate_it(): void
    {
        $this->postJson(self::ENDPOINT, $this->orderPayload(), self::SECRET_HEADER)->assertOk();
        $this->postJson(self::ENDPOINT, $this->orderPayload(), self::SECRET_HEADER)->assertOk();

        $this->assertSame(1, Order::count());
        $this->assertSame(1, OrderDetail::count());
    }

    public function test_status_update_changes_order_and_line_statuses(): void
    {
        $this->postJson(self::ENDPOINT, $this->orderPayload(), self::SECRET_HEADER)->assertOk();
        Shop::create(['author_type' => 'admin']);
        Event::fake([OrderStatusEvent::class]);

        $this->postJson(self::ENDPOINT, $this->statusPayload(newStatus: 'delivered'), self::SECRET_HEADER)->assertOk();
        $this->postJson(self::ENDPOINT, $this->statusPayload(newStatus: 'delivered'), self::SECRET_HEADER)->assertOk();

        $order = Order::firstOrFail();
        $detail = OrderDetail::firstOrFail();
        $history = OrderStatusHistory::firstOrFail();

        $this->assertSame('delivered', $order->order_status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('delivered', $detail->delivery_status);
        $this->assertSame('paid', $detail->payment_status);
        $this->assertSame(0, (int)$detail->is_stock_decreased);
        $this->assertSame('delivered', $history->status);
        $this->assertSame('easy_orders', $history->user_type);
        $this->assertSame(1, OrderStatusHistory::count());
        $this->assertSame(1, OrderTransaction::where('order_id', $order->id)->count());
        $this->assertSame(400.0, OrderTransaction::where('order_id', $order->id)->firstOrFail()->seller_amount);
        $this->assertSame(400.0, AdminWallet::firstOrFail()->inhouse_earning);
        $this->assertSame(50.0, AdminWallet::firstOrFail()->delivery_charge_earned);
        Event::assertDispatchedTimes(OrderStatusEvent::class, 1);
        Event::assertDispatched(OrderStatusEvent::class, fn (OrderStatusEvent $event): bool => $event->key === 'delivered' && $event->type === 'customer');
    }

    public function test_paid_status_update_marks_order_paid_and_keeps_order_status(): void
    {
        $this->postJson(self::ENDPOINT, $this->orderPayload(), self::SECRET_HEADER)->assertOk();

        $this->postJson(self::ENDPOINT, $this->statusPayload(newStatus: 'paid', paymentRefId: 'ref-9'), self::SECRET_HEADER)->assertOk();

        $order = Order::firstOrFail();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('ref-9', $order->transaction_ref);
        $this->assertSame('pending', $order->order_status);
    }

    public function test_canceled_status_updates_catalog_free_order_lines_without_marking_stock_decreased(): void
    {
        $this->postJson(self::ENDPOINT, $this->orderPayload(), self::SECRET_HEADER)->assertOk();
        Event::fake([OrderStatusEvent::class]);

        $this->postJson(self::ENDPOINT, $this->statusPayload(newStatus: 'canceled'), self::SECRET_HEADER)->assertOk();

        $detail = OrderDetail::firstOrFail();
        $this->assertSame('canceled', $detail->delivery_status);
        $this->assertSame(0, (int)$detail->is_stock_decreased);
    }

    public function test_unknown_status_leaves_order_unchanged(): void
    {
        $this->postJson(self::ENDPOINT, $this->orderPayload(), self::SECRET_HEADER)->assertOk();

        $this->postJson(self::ENDPOINT, $this->statusPayload(newStatus: 'something_new'), self::SECRET_HEADER)->assertOk();

        $this->assertSame('pending', Order::firstOrFail()->order_status);
    }

    public function test_status_update_returns_404_for_unknown_order(): void
    {
        $this->postJson(self::ENDPOINT, $this->statusPayload(newStatus: 'delivered'), self::SECRET_HEADER)->assertNotFound();
    }

    private function orderPayload(array $overrides = []): array
    {
        return array_merge([
            'id' => 'eo-order-1',
            'status' => 'pending',
            'payment_method' => 'cod',
            'cost' => 400,
            'shipping_cost' => 50,
            'total_cost' => 450,
            'full_name' => 'Ahmed Ali',
            'phone' => '01000000000',
            'government' => 'Cairo',
            'address' => 'Nasr City',
            'cart_items' => [[
                'id' => 'eo-item-1',
                'product_id' => 'eo-product-1',
                'variant_id' => 'eo-variant-1',
                'price' => 200,
                'quantity' => 2,
                'product' => ['id' => 'eo-product-1', 'name' => 'Grilled Sea Bass', 'sku' => 'SB-1'],
                'variant' => ['id' => 'eo-variant-1', 'variation_props' => [['variation' => 'Size', 'variation_prop' => 'Large']]],
            ]],
        ], $overrides);
    }

    private function statusPayload(string $newStatus, ?string $paymentRefId = null): array
    {
        return [
            'event_type' => 'order-status-update',
            'order_id' => 'eo-order-1',
            'old_status' => 'pending',
            'new_status' => $newStatus,
            'payment_ref_id' => $paymentRefId,
        ];
    }

    private function createOrderTables(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->longText('value')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->nullable();
            $table->boolean('is_guest')->default(0);
            $table->boolean('is_pause')->default(0);
            $table->string('customer_type')->nullable();
            $table->string('order_status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->string('transaction_ref')->nullable();
            $table->decimal('order_amount', 40, 20)->default(0);
            $table->decimal('init_order_amount', 40, 20)->default(0);
            $table->decimal('shipping_cost', 40, 20)->default(0);
            $table->decimal('discount_amount', 40, 20)->default(0);
            $table->string('coupon_code')->default('0');
            $table->decimal('refer_and_earn_discount', 40, 20)->default(0);
            $table->decimal('admin_commission', 40, 20)->default(0);
            $table->decimal('total_tax_amount', 40, 20)->default(0);
            $table->string('shipping_responsibility')->nullable();
            $table->boolean('is_shipping_free')->default(0);
            $table->decimal('extra_discount', 40, 20)->default(0);
            $table->unsignedBigInteger('delivery_man_id')->nullable();
            $table->decimal('deliveryman_charge', 40, 20)->default(0);
            $table->decimal('edit_due_amount', 40, 20)->default(0);
            $table->decimal('edit_return_amount', 40, 20)->default(0);
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('seller_is')->nullable();
            $table->string('verification_code')->default('0');
            $table->text('shipping_address_data')->nullable();
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
            $table->timestamp('refund_started_at')->nullable();
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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('author_type')->nullable();
            $table->timestamps();
        });
        Schema::create('order_edit_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->decimal('order_due_amount', 40, 20)->default(0);
            $table->string('order_due_payment_status')->nullable();
            $table->string('order_due_payment_method')->nullable();
            $table->decimal('order_return_amount', 40, 20)->default(0);
            $table->string('order_return_payment_status')->nullable();
            $table->timestamps();
        });
        Schema::create('admin_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->decimal('inhouse_earning', 40, 20)->default(0);
            $table->decimal('withdrawn', 40, 20)->default(0);
            $table->decimal('commission_earned', 40, 20)->default(0);
            $table->decimal('delivery_charge_earned', 40, 20)->default(0);
            $table->decimal('pending_amount', 40, 20)->default(0);
            $table->decimal('total_tax_collected', 40, 20)->default(0);
            $table->timestamps();
        });
        Schema::create('seller_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->decimal('total_earning', 40, 20)->default(0);
            $table->decimal('withdrawn', 40, 20)->default(0);
            $table->decimal('commission_given', 40, 20)->default(0);
            $table->decimal('pending_withdraw', 40, 20)->default(0);
            $table->decimal('delivery_charge_earned', 40, 20)->default(0);
            $table->decimal('collected_cash', 40, 20)->default(0);
            $table->decimal('total_tax_collected', 40, 20)->default(0);
            $table->timestamps();
        });
        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('shop_id');
            $table->string('seller_is')->nullable();
            $table->unsignedBigInteger('order_id');
            $table->decimal('order_amount', 40, 20)->default(0);
            $table->decimal('seller_amount', 40, 20)->default(0);
            $table->decimal('admin_commission', 40, 20)->default(0);
            $table->string('received_by');
            $table->string('delivered_by')->nullable();
            $table->string('status')->nullable();
            $table->decimal('delivery_charge', 40, 20)->default(0);
            $table->decimal('tax', 40, 20)->default(0);
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
        Schema::create('lucky_wheel_won_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
        });

        $this->artisan('migrate', ['--path' => 'database/migrations/2026_11_07_180000_add_order_source_to_orders_table.php'])->assertSuccessful();
    }
}
