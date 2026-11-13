<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Services\CouponService;
use App\Utils\OrderManager;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CouponRequiredCategoryTest extends TestCase
{
    private const CAVIAR_CATEGORY_ID = 16;

    private const CAVIAR_PRODUCT_ID = 1;

    private const SECOND_CAVIAR_PRODUCT_ID = 2;

    private const SALMON_PRODUCT_ID = 3;

    private const SIMILAR_ID_CATEGORY_PRODUCT_ID = 4;

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
            ['id' => self::CAVIAR_PRODUCT_ID, 'category_ids' => json_encode([['id' => '16', 'position' => 1]])],
            ['id' => self::SECOND_CAVIAR_PRODUCT_ID, 'category_ids' => json_encode([['id' => 16, 'position' => 1]])],
            ['id' => self::SALMON_PRODUCT_ID, 'category_ids' => json_encode([['id' => '5', 'position' => 1]])],
            ['id' => self::SIMILAR_ID_CATEGORY_PRODUCT_ID, 'category_ids' => json_encode([['id' => '160', 'position' => 1]])],
        ]);
    }

    public function test_caviar_with_a_product_of_another_category_meets_the_coupon(): void
    {
        $coupon = new Coupon(['required_category_id' => self::CAVIAR_CATEGORY_ID]);

        $this->assertTrue(OrderManager::doesCartMeetCouponRequiredCategory(
            coupon: $coupon,
            cartItems: $this->makeCartItems(productIds: [self::CAVIAR_PRODUCT_ID, self::SALMON_PRODUCT_ID]),
        ));
    }

    public function test_cart_of_only_caviar_or_without_caviar_does_not_meet_the_coupon(): void
    {
        $coupon = new Coupon(['required_category_id' => self::CAVIAR_CATEGORY_ID]);

        $this->assertFalse(OrderManager::doesCartMeetCouponRequiredCategory(
            coupon: $coupon,
            cartItems: $this->makeCartItems(productIds: [self::CAVIAR_PRODUCT_ID, self::SECOND_CAVIAR_PRODUCT_ID]),
        ));
        $this->assertFalse(OrderManager::doesCartMeetCouponRequiredCategory(
            coupon: $coupon,
            cartItems: $this->makeCartItems(productIds: [self::SALMON_PRODUCT_ID, self::SIMILAR_ID_CATEGORY_PRODUCT_ID]),
        ));
    }

    public function test_coupon_without_required_category_always_passes(): void
    {
        $coupon = new Coupon(['required_category_id' => null]);

        $this->assertTrue(OrderManager::doesCartMeetCouponRequiredCategory(
            coupon: $coupon,
            cartItems: $this->makeCartItems(productIds: [self::SALMON_PRODUCT_ID]),
        ));
    }

    public function test_required_category_is_kept_for_first_order_and_discount_coupons_but_not_free_delivery(): void
    {
        $service = new CouponService();
        $makeRequest = fn (string $couponType) => new Request(['coupon_type' => $couponType, 'required_category_id' => '16']);

        $this->assertSame(16, $service->getRequiredCategoryId(request: $makeRequest('first_order')));
        $this->assertSame(16, $service->getRequiredCategoryId(request: $makeRequest('discount_on_purchase')));
        $this->assertNull($service->getRequiredCategoryId(request: $makeRequest('free_delivery')));
        $this->assertNull($service->getRequiredCategoryId(request: new Request(['coupon_type' => 'discount_on_purchase', 'required_category_id' => ''])));
    }

    private function makeCartItems(array $productIds): Collection
    {
        return collect($productIds)->map(fn (int $productId) => ['product_id' => $productId]);
    }
}
