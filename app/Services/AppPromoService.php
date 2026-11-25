<?php

namespace App\Services;

use App\Models\Coupon;

/**
 * The welcome offer advertised next to the app download badges (home promo section and mobile bar).
 * The admin coupon with this code drives the amount and terms; the defaults describe the same offer
 * so the storefront still advertises it correctly before the coupon is created or after it expires.
 */
class AppPromoService
{
    public const COUPON_CODE = 'BAHR100';

    private const DEFAULT_DISCOUNT_AMOUNT = 100;

    private const DEFAULT_MINIMUM_PURCHASE = 1000;

    /**
     * Amounts are whole numbers in the shop currency; the labels are short enough for the mobile bar.
     *
     * @return array{code: string, coupon: ?Coupon, is_percentage: bool, discount_value: string, minimum_purchase: float, minimum_purchase_value: string, currency_symbol: string}
     */
    public function getOffer(): array
    {
        return once(function (): array {
            $coupon = $this->getActiveCoupon();
            $isPercentage = $coupon?->discount_type === 'percentage';
            $discountAmount = (float) ($coupon?->discount ?? self::DEFAULT_DISCOUNT_AMOUNT);
            $minimumPurchase = (float) ($coupon?->min_purchase ?? self::DEFAULT_MINIMUM_PURCHASE);

            return [
                'code' => $coupon?->code ?? self::COUPON_CODE,
                'coupon' => $coupon,
                'is_percentage' => $isPercentage,
                'discount_value' => $isPercentage ? $this->formatWholeNumber(amount: $discountAmount).'%' : $this->formatMoney(amount: $discountAmount),
                'minimum_purchase' => $minimumPurchase,
                'minimum_purchase_value' => $this->formatMoney(amount: $minimumPurchase),
                'currency_symbol' => getCurrencySymbol(currencyCode: getCurrencyCode(type: 'web'), type: 'web'),
            ];
        });
    }

    private function formatMoney(float $amount): string
    {
        return $this->formatWholeNumber(amount: (float) webCurrencyConverterOnlyDigit(amount: $amount));
    }

    private function formatWholeNumber(float $amount): string
    {
        return number_format($amount, fmod($amount, 1) == 0 ? 0 : 2);
    }

    private function getActiveCoupon(): ?Coupon
    {
        return Coupon::active()
            ->where('code', self::COUPON_CODE)
            ->whereIn('coupon_type', ['discount_on_purchase', 'first_order'])
            ->whereDate('start_date', '<=', now())
            ->whereDate('expire_date', '>=', now())
            ->first();
    }
}
