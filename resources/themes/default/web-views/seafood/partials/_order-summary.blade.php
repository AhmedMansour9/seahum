@php
    $summaryStep = $summaryStep ?? 'cart';
    $systemTaxConfig = getTaxModuleSystemTypesConfig();
    $totalTax = \App\Utils\CartManager::getCartListTaxAmount();
    $summaryCart = \App\Utils\CartManager::getCartListQuery(type: 'checked');
    $shippingCost = \App\Utils\CartManager::get_shipping_cost(type: 'checked');
    $shippingCostSavedForFreeDelivery = \App\Utils\CartManager::getShippingCostSavedForFreeDelivery(type: 'checked');
    $isFreeDeliveryCoupon = session('coupon_type') == 'free_delivery';

    $subTotal = 0;
    $totalDiscountOnProduct = 0;
    foreach ($summaryCart as $summaryCartItem) {
        $subTotal += $summaryCartItem['price'] * $summaryCartItem['quantity'];
        $totalDiscountOnProduct += $summaryCartItem['discount'] * $summaryCartItem['quantity'];
    }

    $totalShippingCost = $summaryCart->count() > 0
        ? ($isFreeDeliveryCoupon ? $shippingCost : $shippingCost - $shippingCostSavedForFreeDelivery)
        : 0;
    $couponDiscount = session()->has('coupon_discount') ? session('coupon_discount') : 0;
    $taxAmount = $totalTax['item_tax'] + $totalTax['shipping_cost_tax'];
    $totalAmount = $subTotal + $taxAmount + $totalShippingCost - $couponDiscount - $totalDiscountOnProduct;
    $referralAmount = \App\Utils\CustomerManager::getReferralDiscountAmount(
        user: auth('customer')->check() ? auth('customer')->user() : null,
        couponDiscount: $couponDiscount,
    );

    $totalSavedAmount = $totalDiscountOnProduct
        + ($couponDiscount > 0 && !$isFreeDeliveryCoupon ? $couponDiscount : 0)
        + max($shippingCostSavedForFreeDelivery, 0);
    $isTaxShownSeparately = $systemTaxConfig['SystemTaxVat']['is_active'] && !$systemTaxConfig['is_included'];
    $isCheckoutDisabled = $summaryCart->count() <= 0;
    $luckyWheelCheckout = $summaryStep !== 'cart' && auth('customer')->check()
        ? app(\App\Services\LuckyWheelService::class)->getCheckoutGiftSummary(
            customerId: auth('customer')->id(),
            itemSubtotal: $subTotal,
            productDiscount: $totalDiscountOnProduct,
            couponDiscount: $isFreeDeliveryCoupon ? 0 : $couponDiscount,
            referralDiscount: $referralAmount,
        )
        : null;
@endphp

<aside class="sf-summary" aria-labelledby="sf-summary-title">
    <div class="sf-panel sf-summary-panel">
        <h2 class="sf-panel-title" id="sf-summary-title">{{ translate('order_summary') }}</h2>

        @if($totalSavedAmount > 0)
            <p class="sf-summary-saved">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"></path><circle cx="7.5" cy="7.5" r="1.5"></circle></svg>
                <span>{{ translate('you_have_Saved') }} <strong>@include('web-views.seafood.partials._price', ['amount' => $totalSavedAmount, 'symbolColor' => 'currentColor'])</strong></span>
            </p>
        @endif

        @if(auth('customer')->check())
            @if(session()->has('coupon_discount'))
                <div class="sf-coupon-applied">
                    <span style="display: flex; flex-direction: column; gap: 2px; min-width: 0;">
                        <strong style="font-family: Cairo, sans-serif; color: #0B2C3F; overflow-wrap: anywhere;"><bdi dir="ltr">{{ session('coupon_code') }}</bdi></strong>
                        <span style="font-size: 12.5px; color: #0E8E88;">{{ translate('coupon_applied') }}</span>
                    </span>
                    <a href="{{ route('coupon.remove') }}" class="sf-icon-btn sf-icon-btn-danger" aria-label="{{ translate('remove_coupon') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </a>
                </div>
            @else
                <form class="sf-coupon-form" id="sf-coupon-form" action="javascript:" novalidate>
                    <label class="sr-only" for="sf-coupon-code">{{ translate('coupon_code') }}</label>
                    <input type="text" class="sf-filter-field" id="sf-coupon-code" name="code" autocomplete="off"
                           placeholder="{{ translate('coupon_code') }}" required>
                    <button type="submit" class="sf-btn-sm sf-btn-sm-primary" id="sf-coupon-apply">{{ translate('apply') }}</button>
                </form>
            @endif
        @endif

        @if($luckyWheelCheckout)
            <div class="sf-summary-gifts {{ $luckyWheelCheckout['is_eligible'] ? '' : 'is-locked' }}">
                @if($luckyWheelCheckout['is_eligible'])
                    <span class="sf-summary-gifts-title">{{ translate('free_gifts_added_to_this_order') }}</span>
                @else
                    <span class="sf-summary-gifts-title">
                        {{ translate('add') }} @include('web-views.seafood.partials._price', ['amount' => $luckyWheelCheckout['remaining_amount'], 'symbolColor' => 'currentColor'])
                        {{ translate('more_to_get_your_free_gifts_with_this_order') }}
                    </span>
                    <span class="sf-summary-gifts-hint">
                        {{ translate('minimum_order_for_gifts') }}: @include('web-views.seafood.partials._price', ['amount' => $luckyWheelCheckout['minimum_order_amount']])
                        · {{ translate('gifts_stay_saved_for_a_later_order') }}
                    </span>
                @endif
                <ul>
                    @foreach($luckyWheelCheckout['pending_gifts'] as $pendingLuckyWheelGift)
                        <li>
                            <img src="{{ getStorageImages(path: $pendingLuckyWheelGift->image_full_url, type: 'product') }}" alt="" loading="lazy">
                            <span>{{ $pendingLuckyWheelGift->getLocalizedName() }}</span>
                            <strong>{{ translate('free') }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <dl class="sf-summary-rows">
            <div class="sf-summary-row">
                <dt>{{ translate('sub_total') }}</dt>
                <dd>@include('web-views.seafood.partials._price', ['amount' => $subTotal])</dd>
            </div>
            <div class="sf-summary-row">
                <dt>{{ translate('shipping') }}</dt>
                <dd>@include('web-views.seafood.partials._price', ['amount' => $totalShippingCost])</dd>
            </div>
            <div class="sf-summary-row">
                <dt>{{ translate('discount_on_product') }}</dt>
                <dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $totalDiscountOnProduct])</dd>
            </div>
            @if($isTaxShownSeparately)
                <div class="sf-summary-row">
                    <dt>{{ translate('tax') }}</dt>
                    <dd>@include('web-views.seafood.partials._price', ['amount' => $taxAmount])</dd>
                </div>
            @endif
            @if($referralAmount > 0)
                <div class="sf-summary-row">
                    <dt>{{ translate('referral_discount') }}</dt>
                    <dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $referralAmount])</dd>
                </div>
            @endif
            @if(auth('customer')->check() && session()->has('coupon_discount'))
                <div class="sf-summary-row">
                    <dt>{{ translate('coupon_discount') }}</dt>
                    <dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $couponDiscount])</dd>
                </div>
            @endif
            <div class="sf-summary-row is-total">
                <dt>
                    {{ translate('total') }}
                    @if($systemTaxConfig['SystemTaxVat']['is_active'] && $systemTaxConfig['is_included'])
                        <span style="font-size: 12px; font-weight: 600; color: #6E8C99;">({{ translate('Tax_:_Inc.') }})</span>
                    @endif
                </dt>
                <dd class="sf-summary-total">@include('web-views.seafood.partials._price', ['amount' => $totalAmount - $referralAmount, 'symbolSize' => '15px'])</dd>
            </div>
        </dl>

        @if($summaryStep === 'payment')
            <label class="sf-check sf-summary-terms">
                <input type="checkbox" class="sf-payment-terms" checked>
                <span class="sf-check-box" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg></span>
                <span class="sf-check-label">
                    {{ translate('i_agree_to_Your') }}
                    <a class="sf-text-link" target="_blank" href="{{ route('business-page.view', ['slug' => 'terms-and-conditions']) }}">{{ translate('terms_and_condition') }}</a>@foreach($web_config['business_pages']->where('default_status', 1) as $businessPage)@if(in_array($businessPage['slug'], ['privacy-policy', 'refund-policy'])),
                        <a class="sf-text-link" target="_blank" href="{{ route('business-page.view', ['slug' => $businessPage['slug']]) }}">{{ translate(str_replace('-', '_', $businessPage['slug'])) }}</a>@endif @endforeach
                </span>
            </label>
        @endif

        <button type="button" class="sf-btn-block sf-checkout-proceed" data-step="{{ $summaryStep }}"
                @disabled($isCheckoutDisabled || $summaryStep === 'payment')>
            {{ $summaryStep === 'payment' ? translate('place_order') : translate('proceed_to_Checkout') }}
        </button>

        <a href="{{ route('home') }}" class="sf-summary-continue">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ (session('direction') ?? 'rtl') === 'rtl' ? 'm10 6 6 6-6 6' : 'M14 6 8 12l6 6' }}"></path></svg>
            {{ translate('continue_Shopping') }}
        </a>
    </div>
</aside>

<div class="sf-summary-bar">
    <span style="display: flex; flex-direction: column; min-width: 0;">
        <span style="font-size: 12.5px; color: #6E8C99;">{{ translate('total_price') }}</span>
        <strong class="sf-summary-total">@include('web-views.seafood.partials._price', ['amount' => $totalAmount - $referralAmount, 'symbolSize' => '13px'])</strong>
    </span>
    <button type="button" class="sf-btn-sm sf-btn-sm-primary sf-checkout-proceed" data-step="{{ $summaryStep }}"
            @disabled($isCheckoutDisabled || $summaryStep === 'payment')>
        {{ $summaryStep === 'payment' ? translate('place_order') : translate('proceed_to_Checkout') }}
    </button>
</div>
