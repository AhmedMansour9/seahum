{{-- Re-rendered by CartController after every quantity, selection and removal change, so it carries the order summary with it. --}}
@php
    $shippingMethod = getWebConfig(name: 'shipping_method');
    $cartGroups = \App\Utils\CartManager::getCartListGroupQuery();
    $adminShippingType = \App\Models\ShippingType::where('seller_id', 0)->first()?->shipping_type ?? 'order_wise';
    $isRtl = (session('direction') ?? 'rtl') === 'rtl';
    $isAnyPhysicalItemChecked = false;
    $firstCartGroupId = null;
@endphp

<div class="sf-cart-layout">
    <section class="sf-cart-main" aria-labelledby="sf-cart-title">
        <h1 class="sf-page-title" id="sf-cart-title" style="margin-bottom: 18px;">{{ translate('shopping_cart') }}</h1>

        @forelse($cartGroups as $cartGroupKey => $cartGroup)
            @php
                $firstGroupItem = $cartGroup->first();
                $firstCartGroupId ??= $firstGroupItem['cart_group_id'];
                $isVendorGroup = $firstGroupItem->seller_is != 'admin';
                $groupShop = $isVendorGroup ? \App\Models\Shop::where(['seller_id' => $firstGroupItem['seller_id']])->first() : null;
                $groupShopName = $isVendorGroup ? $groupShop?->name : getInHouseShopConfig(key: 'name');
                $groupShopUrl = $isVendorGroup
                    ? ($groupShop ? route('vendor-shop', ['slug' => $groupShop->slug]) : null)
                    : route('vendor-shop', ['slug' => getInHouseShopConfig(key: 'slug')]);

                if ($shippingMethod == 'inhouse_shipping' || !$isVendorGroup) {
                    $groupShippingType = $adminShippingType;
                } else {
                    $groupShippingType = \App\Models\ShippingType::where('seller_id', $firstGroupItem->seller_id)->first()?->shipping_type ?? 'order_wise';
                }

                $isGroupPhysicalChecked = $cartGroup->contains(fn ($groupItem) => $groupItem->product_type == 'physical' && $groupItem->is_checked);
                $isAnyPhysicalItemChecked = $isAnyPhysicalItemChecked || $isGroupPhysicalChecked;
                $groupItemWiseShippingCost = $cartGroup
                    ->filter(fn ($groupItem) => $groupItem->product_type == 'physical' && $groupItem->is_checked && $groupItem->shipping_type != 'order_wise')
                    ->sum('shipping_cost');
                $minimumOrderStatus = \App\Utils\OrderManager::verifyCartListMinimumOrderAmount($request, $cartGroupKey);
                $chosenShipping = \App\Models\CartShipping::where(['cart_group_id' => $firstGroupItem['cart_group_id']])->first();
                $isVendorOrderWiseShipping = $isGroupPhysicalChecked && $shippingMethod == 'sellerwise_shipping' && $groupShippingType == 'order_wise';
                $vendorShippingMethods = $isVendorOrderWiseShipping ? \App\Utils\Helpers::getShippingMethods($firstGroupItem['seller_id'], $firstGroupItem['seller_is']) : [];
                $freeDeliveryStatus = \App\Utils\OrderManager::getFreeDeliveryOrderAmountArray($firstGroupItem['cart_group_id']);
                $isAllGroupItemsChecked = $cartGroup->every(fn ($groupItem) => $groupItem->is_checked);
            @endphp

            <article class="sf-cart-group" data-cart-group="{{ $firstGroupItem['cart_group_id'] }}">
                <header class="sf-cart-group-head">
                    <label class="sf-check" style="min-height: 0;">
                        <input type="checkbox" class="sf-cart-group-check" @checked($isAllGroupItemsChecked)
                               aria-label="{{ translate('select_all_items_from') }} {{ $groupShopName }}">
                        <span class="sf-check-box" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg></span>
                    </label>

                    @if($groupShopUrl)
                        <a href="{{ $groupShopUrl }}" class="sf-cart-shop">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9.5 5.5 4h13L20 9.5M4 9.5V20h16V9.5M4 9.5h16M9.5 20v-5.5h5V20"></path></svg>
                            <span>{{ $groupShopName }}</span>
                        </a>
                    @else
                        <span class="sf-badge sf-badge-danger">{{ translate('vendor_not_available') }}</span>
                    @endif

                    @if($minimumOrderStatus['minimum_order_amount'] > $minimumOrderStatus['amount'])
                        <span class="sf-badge sf-badge-warning">
                            {{ translate('minimum_Order_Amount') }}
                            @include('web-views.seafood.partials._price', ['amount' => $minimumOrderStatus['minimum_order_amount'], 'symbolColor' => 'currentColor', 'symbolSize' => '11px'])
                        </span>
                    @endif

                    <div class="sf-cart-group-shipping">
                        @if($isVendorOrderWiseShipping)
                            @if(count($vendorShippingMethods) > 0)
                                <label class="sr-only" for="sf-shipping-{{ $firstGroupItem['cart_group_id'] }}">{{ translate('shipping_method') }}</label>
                                <select class="sf-filter-field sf-shipping-method-select" id="sf-shipping-{{ $firstGroupItem['cart_group_id'] }}"
                                        data-cart-group="{{ $firstGroupItem['cart_group_id'] }}" style="height: 40px;">
                                    <option value="">{{ translate('choose_shipping_method') }}</option>
                                    @foreach($vendorShippingMethods as $vendorShippingMethod)
                                        <option value="{{ $vendorShippingMethod['id'] }}" @selected(($chosenShipping['shipping_method_id'] ?? 0) == $vendorShippingMethod['id'])>
                                            {{ ucfirst($vendorShippingMethod['title']) }} ({{ $vendorShippingMethod['duration'] }}) {{ webCurrencyConverter(amount: $vendorShippingMethod['cost']) }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <span class="sf-badge sf-badge-danger" title="{{ translate('No_shipping_options_available_at_this_shop') }}, {{ translate('please_remove_all_items_from_this_shop') }}">
                                    {{ translate('shipping_Not_Available') }}
                                </span>
                            @endif
                        @elseif($isGroupPhysicalChecked && $groupShippingType != 'order_wise')
                            <span class="sf-row-meta">{{ translate('total_shipping_cost') }}: <strong style="color: #0B2C3F;">@include('web-views.seafood.partials._price', ['amount' => $groupItemWiseShippingCost])</strong></span>
                        @elseif($isGroupPhysicalChecked && $chosenShipping)
                            <span class="sf-row-meta">{{ translate('total_shipping_cost') }}: <strong style="color: #0B2C3F;">@include('web-views.seafood.partials._price', ['amount' => $chosenShipping->shipping_cost])</strong></span>
                        @endif
                    </div>
                </header>

                <ul class="sf-cart-items">
                    @foreach($cartGroup as $cartItem)
                        @php
                            $cartProduct = $cartItem->allProducts;
                            $productStock = $cartProduct?->current_stock ?? 0;
                            foreach (json_decode($cartProduct?->variation ?? '[]', true) ?: [] as $productVariation) {
                                if ($productVariation['type'] == $cartItem->variant) {
                                    $productStock = $productVariation['qty'];
                                }
                            }

                            $isProductAvailable = ($cartProduct?->status ?? 0) == 1;
                            if ($cartItem->seller_is == 'admin' && (checkVendorAbility(type: 'inhouse', status: 'temporary_close') || checkVendorAbility(type: 'inhouse', status: 'vacation_status'))) {
                                $isProductAvailable = false;
                            } elseif ($cartItem->seller_is == 'seller' && $cartProduct && (checkVendorAbility(type: 'vendor', status: 'temporary_close', vendor: $cartProduct->seller->shop) || checkVendorAbility(type: 'vendor', status: 'vacation_status', vendor: $cartProduct->seller->shop))) {
                                $isProductAvailable = false;
                            }

                            $minimumQuantity = $cartProduct?->minimum_order_qty ?: 1;
                            $unitPrice = $cartItem['price'] - $cartItem['discount'];
                            $isOutOfStock = $cartProduct?->product_type == 'physical' && $productStock < $cartItem['quantity'];
                            $isAtMinimum = $cartItem['quantity'] <= $minimumQuantity || $isOutOfStock;
                            $productUrl = $isProductAvailable ? route('product', $cartItem['slug']) : null;
                        @endphp

                        <li class="sf-cart-item{{ $isProductAvailable ? '' : ' is-unavailable' }}">
                            <label class="sf-check" style="min-height: 0;">
                                <input type="checkbox" class="sf-cart-item-check" value="{{ $cartItem['id'] }}" @checked($cartItem['is_checked'])
                                       aria-label="{{ translate('select') }} {{ $cartItem['name'] }}">
                                <span class="sf-check-box" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg></span>
                            </label>

                            <a class="sf-cart-item-media" @if($productUrl) href="{{ $productUrl }}" @endif>
                                <img src="{{ getStorageImages(path: $cartItem?->product?->thumbnail_full_url, type: 'product') }}" alt="" loading="lazy">
                                @unless($isProductAvailable)
                                    <span class="sf-cart-item-na">{{ translate('N/A') }}</span>
                                @endunless
                            </a>

                            <div class="sf-cart-item-body">
                                <h2 class="sf-row-title" style="font-size: 15px;">
                                    @if($productUrl)
                                        <a href="{{ $productUrl }}" style="color: inherit;">{{ $cartItem['name'] }}</a>
                                    @else
                                        {{ $cartItem['name'] }}
                                    @endif
                                </h2>
                                <div class="sf-row-meta">
                                    @if(!empty($cartItem['variant']))
                                        <span>{{ translate('variant') }}: <bdi>{{ $cartItem['variant'] }}</bdi></span>
                                    @endif
                                    <span>{{ translate('unit_price') }}: @include('web-views.seafood.partials._price', ['amount' => $unitPrice])</span>
                                    @if($cartProduct?->product_type == 'physical' && $groupShippingType != 'order_wise')
                                        <span>{{ translate('shipping_cost') }}: @include('web-views.seafood.partials._price', ['amount' => $cartItem['shipping_cost']])</span>
                                    @endif
                                </div>
                                @if($isOutOfStock)
                                    <span class="sf-badge sf-badge-danger" style="align-self: flex-start;">{{ translate('Out_Of_Stock') }}</span>
                                @elseif(!$isProductAvailable)
                                    <span class="sf-badge sf-badge-neutral" style="align-self: flex-start;">{{ translate('product_not_available_right_now') }}</span>
                                @endif
                            </div>

                            <div class="sf-cart-item-side">
                                <strong class="sf-cart-item-total">@include('web-views.seafood.partials._price', ['amount' => $unitPrice * $cartItem['quantity']])</strong>

                                @if($cartItem?->product && $isProductAvailable)
                                    <div class="sf-qty" role="group" aria-label="{{ translate('qty') }}">
                                        <button type="button" class="sf-qty-btn{{ $isAtMinimum ? ' is-remove' : '' }}" data-cart-qty-step="-1" data-cart-id="{{ $cartItem['id'] }}"
                                                aria-label="{{ $isAtMinimum ? translate('remove') : translate('decrease_quantity') }}">
                                            @if($isAtMinimum)
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4.5h6V7M6.5 7l1 13h9l1-13"></path></svg>
                                            @else
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14"></path></svg>
                                            @endif
                                        </button>
                                        <input type="text" inputmode="numeric" class="sf-qty-input" value="{{ $cartItem['quantity'] }}"
                                               data-cart-id="{{ $cartItem['id'] }}" data-min="{{ $minimumQuantity }}" data-stock="{{ $productStock }}"
                                               data-quantity="{{ $cartItem['quantity'] }}" aria-label="{{ translate('qty') }}">
                                        <button type="button" class="sf-qty-btn" data-cart-qty-step="1" data-cart-id="{{ $cartItem['id'] }}"
                                                aria-label="{{ translate('increase_quantity') }}">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"></path></svg>
                                        </button>
                                    </div>
                                @else
                                    <button type="button" class="sf-btn-sm sf-btn-sm-danger sf-cart-remove" data-cart-id="{{ $cartItem['id'] }}">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4.5h6V7M6.5 7l1 13h9l1-13"></path></svg>
                                        {{ translate('remove') }}
                                    </button>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if($freeDeliveryStatus['status'] && (session()->missing('coupon_type') || session('coupon_type') != 'free_delivery'))
                    <div class="sf-free-delivery">
                        <span class="sf-row-meta">
                            @if($freeDeliveryStatus['amount_need'] <= 0)
                                {{ translate('you_Get_Free_Delivery_Bonus') }}
                            @else
                                <strong style="color: #0E8E88;">@include('web-views.seafood.partials._price', ['amount' => $freeDeliveryStatus['amount_need'], 'symbolColor' => 'currentColor'])</strong>
                                {{ translate('add_more_for_free_delivery') }}
                            @endif
                        </span>
                        <span class="sf-progress" role="progressbar" aria-valuenow="{{ $freeDeliveryStatus['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            <span style="width: {{ $freeDeliveryStatus['percentage'] }}%;"></span>
                        </span>
                    </div>
                @endif
            </article>
        @empty
            @include('web-views.seafood.partials._empty-state', [
                'emptyStateTitle' => translate('your_cart_is_empty'),
                'emptyStateText' => translate('your_cart_is_empty,_and_it_looks_like_you_haven’t_added_anything_yet.'),
                'emptyStateActionUrl' => route('products'),
                'emptyStateActionLabel' => translate('continue_Shopping'),
            ])
        @endforelse

        @if($shippingMethod == 'inhouse_shipping' && $adminShippingType == 'order_wise' && $isAnyPhysicalItemChecked)
            @php
                $inhouseShippingMethods = \App\Utils\Helpers::getShippingMethods(1, 'admin');
                $chosenInhouseShipping = \App\Models\CartShipping::where(['cart_group_id' => $firstCartGroupId])->first();
            @endphp
            <div class="sf-panel">
                <label class="sf-form-label" for="sf-shipping-all">{{ translate('shipping_method') }}</label>
                <select class="sf-filter-field sf-shipping-method-select" id="sf-shipping-all" data-cart-group="all_cart_group">
                    <option value="">{{ translate('choose_shipping_method') }}</option>
                    @foreach($inhouseShippingMethods as $inhouseShippingMethod)
                        <option value="{{ $inhouseShippingMethod['id'] }}" @selected(($chosenInhouseShipping['shipping_method_id'] ?? 0) == $inhouseShippingMethod['id'])>
                            {{ $inhouseShippingMethod['title'] }} ({{ $inhouseShippingMethod['duration'] }}) {{ webCurrencyConverter(amount: $inhouseShippingMethod['cost']) }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($cartGroups->count() > 0)
            <div class="sf-panel">
                <label class="sf-form-label" for="order_note">
                    {{ translate('order_note') }}
                    <span style="font-weight: 500; color: #6E8C99;">({{ translate('optional') }})</span>
                </label>
                <textarea class="sf-filter-field" id="order_note" name="order_note" style="min-height: 110px;"
                          placeholder="{{ translate('write_a_note_for_your_order') }}">{{ session('order_note') }}</textarea>
            </div>
        @endif
    </section>

    @include('web-views.seafood.partials._order-summary', ['summaryStep' => 'cart'])
</div>
