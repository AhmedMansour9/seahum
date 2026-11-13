<?php

namespace Tests\Unit;

use App\Services\CategoryComboDiscountService;
use Tests\TestCase;

class CategoryComboDiscountServiceTest extends TestCase
{
    private const ANCHOR_CATEGORY_ID = 16;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'category_combo_discount.enabled' => true,
            'category_combo_discount.anchor_category_id' => self::ANCHOR_CATEGORY_ID,
            'category_combo_discount.amount' => 200,
        ]);
    }

    public function test_anchor_category_product_with_another_category_gets_the_discount_on_the_anchor_line(): void
    {
        $cartItems = [
            $this->makeCartItem(cartId: 1, categoryId: self::ANCHOR_CATEGORY_ID, price: 1050, discount: 300),
            $this->makeCartItem(cartId: 2, categoryId: 5, price: 500),
        ];

        $service = app(CategoryComboDiscountService::class);

        $this->assertSame([1 => 200.0], $service->getCartItemDiscountShares(cartItems: $cartItems));
        $this->assertSame(200.0, $service->calculateDiscount(cartItems: $cartItems));
    }

    public function test_discount_is_split_over_anchor_lines_and_taken_once(): void
    {
        $cartItems = [
            $this->makeCartItem(cartId: 1, categoryId: self::ANCHOR_CATEGORY_ID, price: 750, quantity: 2),
            $this->makeCartItem(cartId: 2, categoryId: self::ANCHOR_CATEGORY_ID, price: 1500),
            $this->makeCartItem(cartId: 3, categoryId: 4, price: 300),
        ];

        $shares = app(CategoryComboDiscountService::class)->getCartItemDiscountShares(cartItems: $cartItems);

        $this->assertEqualsWithDelta(100.0, $shares[1], 0.001);
        $this->assertEqualsWithDelta(100.0, $shares[2], 0.001);
        $this->assertEqualsWithDelta(200.0, array_sum($shares), 0.001);
    }

    public function test_cart_without_both_kinds_of_products_gets_no_discount(): void
    {
        $service = app(CategoryComboDiscountService::class);

        $onlyAnchorCategory = [
            $this->makeCartItem(cartId: 1, categoryId: self::ANCHOR_CATEGORY_ID, price: 750),
            $this->makeCartItem(cartId: 2, categoryId: self::ANCHOR_CATEGORY_ID, price: 1200),
        ];
        $onlyOtherCategories = [
            $this->makeCartItem(cartId: 1, categoryId: 4, price: 750),
            $this->makeCartItem(cartId: 2, categoryId: 5, price: 1200),
        ];

        $this->assertSame(0.0, (float)$service->calculateDiscount(cartItems: $onlyAnchorCategory));
        $this->assertSame(0.0, (float)$service->calculateDiscount(cartItems: $onlyOtherCategories));
    }

    public function test_discount_never_exceeds_the_anchor_lines_total(): void
    {
        $cartItems = [
            $this->makeCartItem(cartId: 1, categoryId: self::ANCHOR_CATEGORY_ID, price: 150),
            $this->makeCartItem(cartId: 2, categoryId: 5, price: 500),
        ];

        $this->assertSame(150.0, app(CategoryComboDiscountService::class)->calculateDiscount(cartItems: $cartItems));
    }

    public function test_disabled_discount_is_not_applied(): void
    {
        config(['category_combo_discount.enabled' => false]);

        $cartItems = [
            $this->makeCartItem(cartId: 1, categoryId: self::ANCHOR_CATEGORY_ID, price: 750),
            $this->makeCartItem(cartId: 2, categoryId: 5, price: 500),
        ];

        $this->assertSame([], app(CategoryComboDiscountService::class)->getCartItemDiscountShares(cartItems: $cartItems));
    }

    /**
     * @return array{id: int, price: float, discount: float, quantity: int, product: array{category_id: int}}
     */
    private function makeCartItem(int $cartId, int $categoryId, float $price, float $discount = 0, int $quantity = 1): array
    {
        return [
            'id' => $cartId,
            'price' => $price,
            'discount' => $discount,
            'quantity' => $quantity,
            'product' => ['category_id' => $categoryId],
        ];
    }
}
