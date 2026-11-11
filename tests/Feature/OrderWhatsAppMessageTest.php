<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderWhatsAppMessageTest extends TestCase
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_guest')->default(1);
            $table->decimal('shipping_cost', 40, 20)->default(0);
            $table->decimal('discount_amount', 40, 20)->default(0);
            $table->text('shipping_address_data')->nullable();
            $table->timestamps();
        });
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->longText('product_details')->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('price', 40, 20)->default(0);
            $table->decimal('tax', 40, 20)->default(0);
            $table->decimal('discount', 40, 20)->default(0);
            $table->string('tax_model')->nullable();
            $table->string('variant')->nullable();
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
        BusinessSetting::insert(['type' => 'currency_model', 'value' => 'single_currency']);
    }

    public function test_message_contains_customer_items_total_and_delivery_area(): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'shipping_cost' => 45.5,
            'shipping_address_data' => json_encode(['contact_person_name' => 'Ahmed Mansour', 'address' => 'ain shams', 'city' => 'Cairo']),
        ]);
        DB::table('order_details')->insert([
            ['order_id' => $orderId, 'product_details' => json_encode(['name' => 'Sweet Chili 740 ml']), 'qty' => 1, 'price' => 190],
            ['order_id' => $orderId, 'product_details' => json_encode(['name' => 'Smoked Salmon']), 'qty' => 2, 'price' => 0],
        ]);

        $message = getOrderWhatsAppMessage(order: Order::with('details')->findOrFail($orderId));

        $this->assertStringContainsString('مرحبًا Ahmed Mansour ✨', $message);
        $this->assertStringContainsString("Sweet Chili 740 ml:\nعدد القطع: 1", $message);
        $this->assertStringContainsString("Smoked Salmon:\nعدد القطع: 2", $message);
        $this->assertStringContainsString('*الإجمالي:* 235.50 ج.م', $message);
        $this->assertStringContainsString('*التوصيل إلى:* ain shams', $message);
        $this->assertStringContainsString('تطبيق SEA HUM', $message);
        $this->assertStringContainsString('WELCOME200', $message);
        $this->assertStringNotContainsString('{', $message);
    }

    public function test_chat_url_carries_the_encoded_message(): void
    {
        $this->assertSame('https://wa.me/201012345678?text=%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%20%2A1%2A', getWhatsAppChatUrl(phone: '01012345678', message: 'مرحبا *1*'));
    }
}
