<?php

namespace Tests\Feature;

use App\Services\OrderEditService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderEditManualPriceTest extends TestCase
{
    private OrderEditService $orderEditService;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        $this->orderEditService = app(OrderEditService::class);
    }

    public function test_stored_line_without_discount_and_other_than_catalog_price_is_a_manual_price(): void
    {
        $manualPrice = $this->orderEditService->getStoredCustomUnitPrice(detail: ['price' => 120, 'discount' => 0], catalogUnitPrice: 150.0);

        $this->assertSame(120.0, $manualPrice);
    }

    public function test_catalog_priced_lines_are_not_manual_prices(): void
    {
        $this->assertNull($this->orderEditService->getStoredCustomUnitPrice(detail: ['price' => 150, 'discount' => 0], catalogUnitPrice: 150.0));
        $this->assertNull($this->orderEditService->getStoredCustomUnitPrice(detail: ['price' => 120, 'discount' => 15], catalogUnitPrice: 150.0));
        $this->assertNull($this->orderEditService->getStoredCustomUnitPrice(detail: null, catalogUnitPrice: 150.0));
    }

    public function test_manual_price_replaces_price_and_discount_of_the_line(): void
    {
        $line = $this->orderEditService->applyCustomUnitPrice(line: ['price' => 150, 'discount' => 15, 'discounted_price' => 135, 'qty' => 2], unitPrice: 100.0);

        $this->assertSame(100.0, $line['price']);
        $this->assertSame(0, $line['discount']);
        $this->assertSame(100.0, $line['discounted_price']);
        $this->assertSame(2, $line['qty']);
    }

    public function test_digital_only_and_pos_orders_are_editable_for_admin_but_not_for_vendors(): void
    {
        $order = (object)[
            'order_type' => 'POS',
            'order_status' => 'pending',
            'details' => collect([(object)['product_details' => json_encode(['product_type' => 'digital'])]]),
        ];

        $adminResult = $this->orderEditService->checkIsOrderEditable(order: $order, type: 'admin', isOfflinePaymentConfirmationRequired: false);
        $vendorResult = $this->orderEditService->checkIsOrderEditable(order: $order, type: 'vendor', isOfflinePaymentConfirmationRequired: false);

        $this->assertTrue($adminResult['status']);
        $this->assertFalse($vendorResult['status']);
    }
}
