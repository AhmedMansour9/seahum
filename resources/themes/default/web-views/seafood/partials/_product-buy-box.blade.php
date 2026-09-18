@php($discountValue = getProductPriceByType(product: $product, type: 'discount', result: 'value'))
@php($authorType = $product->added_by == 'admin' ? 'inhouse' : ($product->added_by == 'seller' ? 'vendor' : ''))
@php($vendorShop = $authorType === 'vendor' ? $product->seller->shop : null)
@php($isTemporaryClose = checkVendorAbility(type: $authorType, status: 'temporary_close', vendor: $vendorShop))
@php($isVacationMode = !$isTemporaryClose ? checkVendorAbility(type: $authorType, status: 'vacation_status', vendor: $vendorShop) : false)
@php($isShopUnavailable = $isTemporaryClose || $isVacationMode)
@php($isInStock = $product['product_type'] === 'digital' || $firstVariationQuantity > 0)
@php($stockColor = $isInStock ? '#0E8E88' : '#C9821B')

<div class="sf-buy-box product-cart-option-container" style="display: flex; flex-direction: column; gap: 20px; min-width: 0;">

    <div style="display: flex; flex-direction: column; gap: 12px;">
        <span class="sf-stock-badge" style="align-self: flex-start; display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; background: #ECF8F8; color: {{ $stockColor }}; font-size: 12.5px; font-weight: 700;">
            <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $stockColor }};"></span>
            {{ $isInStock ? translate('in_stock') : translate('out_of_stock') }}
        </span>

        <h1 style="font-family: Cairo, sans-serif; font-size: clamp(22px, 3vw, 30px); font-weight: 800; line-height: 1.35; margin: 0; color: #0B2C3F; text-wrap: pretty; overflow-wrap: anywhere;">{{ $product['name'] }}</h1>

        <div class="sf-product-meta" style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px 16px; font-size: 13.5px; color: #6E8C99;">
            @if($overallRating[1] > 0)
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#F2A43A" stroke="none"><path d="m12 3.6 2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.5 9.8l5.9-.9z"></path></svg>
                    <strong style="font-family: Cairo, sans-serif; color: #0B2C3F;">{{ $overallRating[0] }}</strong>
                    <a href="#reviews" class="sf-jump-to-reviews" style="color: #0E8E88;">({{ $overallRating[1] }} {{ translate('reviews') }})</a>
                </span>
            @endif
            <span><strong style="font-family: Cairo, sans-serif; color: #0B2C3F;">{{ $countOrder }}</strong> {{ translate('orders') }}</span>
            <span><strong class="countWishlist-{{ $product['id'] }}" style="font-family: Cairo, sans-serif; color: #0B2C3F;">{{ $countWishlist }}</strong> {{ translate('wish_listed') }}</span>
        </div>
    </div>

    <form class="addToCartDynamicForm add-to-cart-details-form" style="display: flex; flex-direction: column; gap: 20px;">
        @csrf
        <input type="hidden" name="id" value="{{ $product['id'] }}">

        <div style="display: flex; align-items: baseline; flex-wrap: wrap; gap: 10px;">
            <strong class="discounted-unit-price" style="font-family: Cairo, sans-serif; font-size: clamp(26px, 3.4vw, 34px); font-weight: 800; color: #0E8E88; letter-spacing: -.4px;">
                @include('web-views.seafood.partials._price', ['amount' => getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'value'), 'symbolSize' => '16px'])
            </strong>
            <span class="product-total-unit-price" style="font-size: 16px; color: #A8B9C1; text-decoration: line-through;" @if($discountValue <= 0) hidden @endif>
                @include('web-views.seafood.partials._price', ['amount' => $product->unit_price, 'symbolSize' => '13px', 'symbolColor' => '#A8B9C1'])
            </span>
        </div>

        @if(count(json_decode($product->colors)) > 0)
            <div class="sf-option-group">
                <span class="sf-option-label">{{ translate('color') }}</span>
                <div class="sf-swatch-list">
                    @foreach(json_decode($product->colors) as $key => $color)
                        @php($colorInputId = str_replace(' ', '', $product->id . '-color-' . str_replace('#', '', $color)))
                        <span>
                            <input type="radio" id="{{ $colorInputId }}" name="color" value="{{ $color }}" @if($key == 0) checked @endif>
                            <label for="{{ $colorInputId }}" class="sf-swatch" style="background: {{ $color }};"
                                   data-color="{{ $color }}" title="{{ getColorNameByCode(code: $color) }}"></label>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        @php($extensionIndex = 0)
        @if($product['product_type'] == 'digital' && $product['digital_product_file_types'] && count($product['digital_product_file_types']) > 0 && $product['digital_product_extensions'])
            @foreach($product['digital_product_extensions'] as $extensionKey => $extensionGroup)
                @if(count($extensionGroup) > 0)
                    <div class="sf-option-group">
                        <span class="sf-option-label">{{ translate($extensionKey) }}</span>
                        <div class="sf-option-list">
                            @foreach($extensionGroup as $extension)
                                @php($extensionInputId = 'extension_' . str_replace(' ', '-', $extension))
                                <span>
                                    <input type="radio" id="{{ $extensionInputId }}" name="variant_key"
                                           value="{{ $extensionKey . '-' . preg_replace('/\s+/', '-', $extension) }}"
                                           {{ $extensionIndex == 0 ? 'checked' : '' }}>
                                    <label for="{{ $extensionInputId }}" class="sf-option-pill">{{ $extension }}</label>
                                </span>
                                @php($extensionIndex++)
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        @foreach(json_decode($product->choice_options) as $choice)
            <div class="sf-option-group">
                <span class="sf-option-label">{{ $choice->title }}</span>
                <div class="sf-option-list">
                    @foreach($choice->options as $index => $option)
                        @php($optionInputId = str_replace(' ', '', $choice->name . '-' . $option))
                        <span>
                            <input type="radio" id="{{ $optionInputId }}" name="{{ $choice->name }}" value="{{ $option }}" @if($index == 0) checked @endif>
                            <label for="{{ $optionInputId }}" class="sf-option-pill">{{ $option }}</label>
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 14px 22px;">
            <div class="sf-option-group" style="gap: 8px;">
                <span class="sf-option-label">{{ translate('qty') }}</span>
                <div class="sf-qty">
                    <button class="btn-number sf-qty-btn" type="button" data-type="minus" data-field="quantity"
                            data-producttype="{{ $product->product_type }}" aria-label="{{ translate('decrease') }}" disabled>&minus;</button>
                    <input type="text" name="quantity" inputmode="numeric"
                           class="input-number product-details-cart-qty sf-qty-input"
                           value="{{ $initialProductConfig['quantity'] ?? 1 }}"
                           data-producttype="{{ $product->product_type }}"
                           min="{{ $product->minimum_order_qty ?? 1 }}"
                           max="{{ $product['product_type'] == 'physical' ? $product->current_stock : 100 }}">
                    <button class="btn-number sf-qty-btn" type="button" data-type="plus" data-field="quantity"
                            data-producttype="{{ $product->product_type }}" aria-label="{{ translate('increase') }}">+</button>
                </div>
            </div>

            <div class="product-details-chosen-price-section" style="display: flex; align-items: baseline; gap: 8px;">
                <span style="font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; color: #45677A;">{{ translate('total_price') }}</span>
                <strong class="product-details-chosen-price-amount" style="font-family: Cairo, sans-serif; font-size: 22px; font-weight: 800; color: #0E8E88; letter-spacing: -.4px;">
                    @include('web-views.seafood.partials._price', ['amount' => $initialProductConfig['total_quantity_price']])
                </strong>
            </div>
        </div>

        <input type="hidden" class="product-generated-variation-code" name="product_variation_code" data-product-id="{{ $product['id'] }}">
        <input type="hidden" class="product-exist-in-cart-list" name="key" value="">

        <div class="product-add-and-buy-section-parent" style="display: flex; flex-direction: column; gap: 12px;">
            <div class="product-add-and-buy-section" style="display: {{ $isInStock ? 'flex' : 'none' }}; align-items: center; gap: 12px; flex-wrap: wrap;">
                @if($isShopUnavailable)
                    <button class="sf-buy-primary" type="button" disabled>{{ translate('add_to_cart') }}</button>
                    <button class="sf-buy-ghost" type="button" disabled>{{ translate('buy_now') }}</button>
                @else
                    <button type="button" class="sf-buy-primary product-add-to-cart-button"
                            data-form=".add-to-cart-details-form"
                            data-update="{{ translate('update_cart') }}"
                            data-add="{{ translate('add_to_cart') }}">
                        {{ $initialProductConfig['first_variant_in_cart'] ? translate('update_cart') : translate('add_to_cart') }}
                    </button>
                    <button type="button" class="sf-buy-ghost product-buy-now-button"
                            data-form=".add-to-cart-details-form"
                            data-auth="{{ (getWebConfig(name: 'guest_checkout') == 1 || Auth::guard('customer')->check()) ? 'true' : 'false' }}"
                            data-route="{{ route('shop-cart') }}">
                        {{ translate('buy_now') }}
                    </button>
                @endif

                <button type="button" class="sf-icon-action product-action-add-wishlist" data-product-id="{{ $product['id'] }}"
                        aria-label="{{ translate('wishlist') }}">
                    <svg class="wishlist_icon_{{ $product['id'] }}" width="20" height="20" viewBox="0 0 24 24"
                         fill="{{ $wishlistStatus == 1 ? '#E4574F' : 'none' }}" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20s-7-4.4-7-9.3A4 4 0 0 1 12 8a4 4 0 0 1 7 2.7c0 4.9-7 9.3-7 9.3Z"></path>
                    </svg>
                </button>
            </div>

            @if($product['product_type'] == 'physical')
                <div class="product-restock-request-section" style="display: {{ $isInStock ? 'none' : 'block' }};">
                    <button type="button" class="sf-buy-ghost product-restock-request-button"
                            data-auth="{{ auth('customer')->check() ? 'true' : 'false' }}"
                            data-form=".add-to-cart-details-form"
                            data-default="{{ translate('Request_Restock') }}"
                            data-requested="{{ translate('Request_Sent') }}">
                        {{ translate('Request_Restock') }}
                    </button>
                </div>
            @endif

            @if($isShopUnavailable)
                <p class="sf-inline-alert" role="alert">
                    {{ translate('this_shop_is_temporary_closed_or_on_vacation._You_cannot_add_product_to_cart_from_this_shop_for_now') }}
                </p>
            @endif
        </div>
    </form>
</div>
