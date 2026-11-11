<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\EasyOrdersCustomerLinkService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EasyOrdersCustomerLinkTest extends TestCase
{
    private EasyOrdersCustomerLinkService $linkService;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('phone')->nullable();
        });
        Schema::create('orders', function ($table) {
            $table->bigInteger('id')->primary();
            $table->string('order_source')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->boolean('is_guest')->default(0);
            $table->text('shipping_address_data')->nullable();
        });

        $this->linkService = new EasyOrdersCustomerLinkService();
    }

    private function createOrder(int $id, string $phone, string $source = 'easy_orders', ?int $customerId = null): void
    {
        DB::table('orders')->insert([
            'id' => $id,
            'order_source' => $source,
            'customer_id' => $customerId,
            'is_guest' => $customerId ? 0 : 1,
            'shipping_address_data' => json_encode(['phone' => $phone]),
        ]);
    }

    public function test_phone_variants_cover_local_and_international_formats(): void
    {
        $variants = $this->linkService->getPhoneVariants(phone: '+20 100 123 4567');

        $this->assertEqualsCanonicalizing(
            ['1001234567', '01001234567', '201001234567', '+201001234567', '00201001234567'],
            $variants
        );
        $this->assertSame([], $this->linkService->getPhoneVariants(phone: '12345'));
    }

    public function test_guest_easy_orders_with_the_same_phone_are_linked_to_the_customer(): void
    {
        $customerId = DB::table('users')->insertGetId(['phone' => '01001234567']);
        $customer = User::find($customerId);

        $this->createOrder(id: 1, phone: '01001234567');
        $this->createOrder(id: 2, phone: '+201001234567');
        $this->createOrder(id: 3, phone: '01009999999');
        $this->createOrder(id: 4, phone: '01001234567', source: 'app');
        $this->createOrder(id: 5, phone: '01001234567', customerId: 99);

        $linkedCount = $this->linkService->linkOrdersToCustomer(customer: $customer);

        $this->assertSame(2, $linkedCount);
        $this->assertSame([1, 2], Order::where('customer_id', $customerId)->orderBy('id')->pluck('id')->all());
        $this->assertSame(0, (int)DB::table('orders')->where('id', 1)->value('is_guest'));
        $this->assertNull(DB::table('orders')->where('id', 3)->value('customer_id'));
        $this->assertNull(DB::table('orders')->where('id', 4)->value('customer_id'));
        $this->assertSame(99, (int)DB::table('orders')->where('id', 5)->value('customer_id'));
    }

    public function test_customer_is_found_by_any_phone_format(): void
    {
        $customerId = DB::table('users')->insertGetId(['phone' => '01001234567']);

        $this->assertSame($customerId, $this->linkService->findCustomerIdByPhone(phone: '+201001234567'));
        $this->assertNull($this->linkService->findCustomerIdByPhone(phone: '01111111111'));
    }
}
