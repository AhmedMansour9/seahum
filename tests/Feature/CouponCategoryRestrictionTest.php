<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use App\Utils\OrderManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CouponCategoryRestrictionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('products', function ($table) {
            $table->id();
            $table->text('category_ids')->nullable();
        });

        DB::table('products')->insert([
            ['id' => 1, 'category_ids' => json_encode([['id' => '7', 'position' => 1], ['id' => '9', 'position' => 2]])],
            ['id' => 2, 'category_ids' => json_encode([['id' => '8', 'position' => 1]])],
            ['id' => 3, 'category_ids' => json_encode([['id' => 7, 'position' => 1]])],
            ['id' => 4, 'category_ids' => json_encode([['id' => '70', 'position' => 1]])],
        ]);
    }

    private function cartItems(array $productIds)
    {
        return collect($productIds)->map(fn(int $productId) => ['product_id' => $productId]);
    }

    public function test_only_items_of_the_coupon_category_are_kept(): void
    {
        $coupon = new Coupon(['category_id' => 7]);

        $eligible = OrderManager::filterCartItemsByCouponCategory(coupon: $coupon, cartItems: $this->cartItems([1, 2, 3, 4]));

        $this->assertSame([1, 3], $eligible->pluck('product_id')->all());
    }

    public function test_cart_without_category_products_has_no_eligible_items(): void
    {
        $coupon = new Coupon(['category_id' => 7]);

        $eligible = OrderManager::filterCartItemsByCouponCategory(coupon: $coupon, cartItems: $this->cartItems([2, 4]));

        $this->assertTrue($eligible->isEmpty());
    }

    public function test_coupon_without_category_keeps_every_item(): void
    {
        $coupon = new Coupon(['category_id' => null]);

        $eligible = OrderManager::filterCartItemsByCouponCategory(coupon: $coupon, cartItems: $this->cartItems([1, 2, 3, 4]));

        $this->assertCount(4, $eligible);
    }
}
