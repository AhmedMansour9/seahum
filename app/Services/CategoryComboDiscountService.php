<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Flat order discount when the cart mixes a product of the anchor category with a product of any other category.
 * The discount is spread over the anchor category lines so it lands in the order details' item discount.
 */
class CategoryComboDiscountService
{
    public function isEnabled(): bool
    {
        return (bool)config('category_combo_discount.enabled') && $this->getDiscountAmount() > 0;
    }

    public function getAnchorCategoryId(): int
    {
        return (int)config('category_combo_discount.anchor_category_id');
    }

    public function getDiscountAmount(): float
    {
        return (float)config('category_combo_discount.amount');
    }

    /**
     * @param iterable $cartItems cart rows with a loaded `product`
     */
    public function calculateDiscount(iterable $cartItems): float
    {
        return array_sum($this->getCartItemDiscountShares(cartItems: $cartItems));
    }

    /**
     * @param iterable $cartItems cart rows with a loaded `product`
     * @return array<int|string, float> discount per cart id, only for anchor category lines
     */
    public function getCartItemDiscountShares(iterable $cartItems): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $productLines = Collection::make($cartItems)->filter(fn ($cartItem) => !empty($cartItem['product']));
        $anchorLines = $productLines->filter(fn ($cartItem) => $this->isAnchorCategoryProduct(product: $cartItem['product']));
        $hasOtherCategoryLine = $productLines->contains(fn ($cartItem) => !$this->isAnchorCategoryProduct(product: $cartItem['product']));

        if ($anchorLines->isEmpty() || !$hasOtherCategoryLine) {
            return [];
        }

        $anchorLinesTotal = $anchorLines->sum(fn ($cartItem) => $this->getLineTotal(cartItem: $cartItem));
        if ($anchorLinesTotal <= 0) {
            return [];
        }

        $discount = min($this->getDiscountAmount(), $anchorLinesTotal);
        $shares = [];
        $remainingDiscount = $discount;
        $lastCartId = $anchorLines->last()['id'];
        foreach ($anchorLines as $cartItem) {
            $share = $cartItem['id'] == $lastCartId
                ? $remainingDiscount
                : round($discount * $this->getLineTotal(cartItem: $cartItem) / $anchorLinesTotal, 2);
            $shares[$cartItem['id']] = $share;
            $remainingDiscount -= $share;
        }

        return $shares;
    }

    private function isAnchorCategoryProduct(mixed $product): bool
    {
        return (int)($product['category_id'] ?? 0) === $this->getAnchorCategoryId();
    }

    private function getLineTotal(mixed $cartItem): float
    {
        return max(((float)$cartItem['price'] - (float)$cartItem['discount']) * (int)$cartItem['quantity'], 0);
    }
}
