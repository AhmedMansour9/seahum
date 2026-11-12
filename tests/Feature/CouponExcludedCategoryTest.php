<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use App\Services\CouponService;
use Illuminate\Http\Request;
use App\Utils\OrderManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CouponExcludedCategoryTest extends TestCase
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

    public function test_items_of_the_excluded_category_are_dropped(): void
    {
        $coupon = new Coupon(['excluded_category_id' => 7]);

        $eligible = OrderManager::excludeCartItemsOfCouponExcludedCategory(coupon: $coupon, cartItems: $this->cartItems([1, 2, 3, 4]));

        $this->assertSame([2, 4], $eligible->pluck('product_id')->all());
    }

    public function test_cart_of_only_excluded_category_products_has_no_eligible_items(): void
    {
        $coupon = new Coupon(['excluded_category_id' => 7]);

        $eligible = OrderManager::excludeCartItemsOfCouponExcludedCategory(coupon: $coupon, cartItems: $this->cartItems([1, 3]));

        $this->assertTrue($eligible->isEmpty());
    }

    public function test_coupon_without_excluded_category_keeps_every_item(): void
    {
        $coupon = new Coupon(['excluded_category_id' => null]);

        $eligible = OrderManager::excludeCartItemsOfCouponExcludedCategory(coupon: $coupon, cartItems: $this->cartItems([1, 2, 3, 4]));

        $this->assertCount(4, $eligible);
    }

    public function test_excluded_category_is_kept_for_first_order_and_discount_coupons_but_not_free_delivery(): void
    {
        $service = new CouponService();
        $request = fn (string $couponType) => new Request(['coupon_type' => $couponType, 'excluded_category_id' => '7']);

        $this->assertSame(7, $service->getExcludedCategoryId(request: $request('first_order')));
        $this->assertSame(7, $service->getExcludedCategoryId(request: $request('discount_on_purchase')));
        $this->assertNull($service->getExcludedCategoryId(request: $request('free_delivery')));
        $this->assertNull($service->getExcludedCategoryId(request: new Request(['coupon_type' => 'first_order', 'excluded_category_id' => ''])));
    }
}
