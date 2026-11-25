@php($hasVariation = !empty(json_decode($product['variation'] ?? '[]', true)) || !empty(json_decode($product['choice_options'] ?? '[]', true)))
@php($isInStock = $product['product_type'] === 'digital' || $product['current_stock'] > 0)
@php($stockColor = $isInStock ? 'var(--sh-sea-700)' : '#C9821B')
@php($thumbnailUrl = getStorageImages(path: $product->thumbnail_full_url, type: 'product'))
@php($isVisibleCard = $isVisibleCard ?? true)
@php($cartItemKey = $cartItemKey ?? null)
@php($showRating = $showRating ?? false)
@php($isWishlisted = $isWishlisted ?? isProductInWishList(productId: $product['id']))
@php($wishlistRemovesCard = $wishlistRemovesCard ?? false)
@php($unitPrice = (float) $product['unit_price'])
@php($discountedUnitPrice = (float) getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'value'))
@php($hasDiscount = $discountedUnitPrice > 0 && $discountedUnitPrice < $unitPrice)
@php($discountPercentage = $hasDiscount ? (int) round((($unitPrice - $discountedUnitPrice) / $unitPrice) * 100) : 0)
@php($averageRating = $showRating ? round((float) ($product['reviews_avg_rating'] ?? 0), 1) : 0)
@php($reviewsCount = $showRating ? (int) ($product['reviews_count'] ?? 0) : 0)

<article class="sf-product-card" data-category-id="{{ $product['category_id'] }}"
         style="display: {{ $isVisibleCard ? 'flex' : 'none' }}; flex-direction: column; background: #FFFFFF; border: 1px solid var(--sh-line); border-radius: 20px; overflow: hidden; box-shadow: 0 10px 26px -24px rgba(var(--sh-navy-rgb), .9); transition: transform .28s, box-shadow .28s, border-color .28s;">
    <div style="position: relative; background: #FFFFFF; border-bottom: 1px solid var(--sh-line); overflow: hidden;">
        <a href="{{ route('product', $product['slug']) }}" style="display: block;">
            <img src="{{ $thumbnailUrl }}"
                 alt="{{ $product['name'] }}" loading="lazy"
                 class="sf-product-media" style="width: 100%; aspect-ratio: 1 / 1; height: auto; object-fit: cover; object-position: center; display: block;">
        </a>
        <span class="sf-product-badges" style="position: absolute; top: 12px; inset-inline-start: 12px; display: flex; flex-direction: column; align-items: flex-start; gap: 6px;">
            <span class="sf-product-badge" style="display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; background: rgba(255,255,255,.94); color: {{ $stockColor }}; box-shadow: 0 6px 16px -8px rgba(var(--sh-navy-rgb), .6);">
                <span style="width: 6px; height: 6px; border-radius: 50%; display: inline-block; background: {{ $stockColor }};"></span>
                {{ $isInStock ? translate('in_stock') : translate('out_of_stock') }}
            </span>
            @if($hasDiscount && $discountPercentage > 0)
                <span class="sf-product-badge" style="display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; border-radius: 999px; font-family: var(--sh-font-display); font-size: 11.5px; font-weight: 800; white-space: nowrap; background: var(--sh-tuna); color: #FFFFFF; box-shadow: 0 6px 16px -8px rgba(var(--sh-navy-rgb), .6);">
                    {{ translate('discount') }} <bdi dir="ltr">{{ $discountPercentage }}%</bdi>
                </span>
            @endif
        </span>
        <button type="button" class="{{ $wishlistRemovesCard ? 'function-remove-wishList' : 'seafood-wishlist-btn' }}"
                data-product-id="{{ $product['id'] }}" data-id="{{ $product['id'] }}"
                data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
                data-add-label="{{ translate('add_to_wishlist') }}" data-remove-label="{{ translate('remove_from_wishlist') }}"
                aria-label="{{ $isWishlisted ? translate('remove_from_wishlist') : translate('add_to_wishlist') }}"
                style="position: absolute; top: 7px; inset-inline-end: 7px; width: 44px; height: 44px; border-radius: 50%; border: none; background: rgba(255,255,255,.92); display: grid; place-items: center; cursor: pointer; transition: transform .2s;">
            <svg width="17" height="17" viewBox="0 0 24 24" stroke="{{ $isWishlisted ? '#D92F45' : '#082B49' }}" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" fill="{{ $isWishlisted ? '#D92F45' : 'none' }}">
                <path d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 4.6-7 9-7 9Z"></path>
            </svg>
        </button>
    </div>

    <div class="sf-product-body" style="padding: 12px 14px 14px; display: flex; flex-direction: column; flex: 1;">
        <h3 class="sf-product-title" style="font-family: var(--sh-font-display); font-size: 16px; font-weight: 700; margin: 0; color: var(--sh-navy); line-height: 1.5; text-wrap: pretty; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 3em;">
            <a href="{{ route('product', $product['slug']) }}" style="color: inherit;">{{ Str::limit($product['name'], 60) }}</a>
        </h3>

        @if($showRating && $reviewsCount > 0)
            <span class="sf-product-rating" style="display: inline-flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 13.5px; font-weight: 700; color: #B47B12;">
                <span aria-hidden="true" style="color: var(--sh-gold); font-size: 15px; letter-spacing: 1px;">{{ str_repeat('★', (int) round($averageRating)) }}{{ str_repeat('☆', 5 - (int) round($averageRating)) }}</span>
                <bdi dir="ltr">{{ number_format($averageRating, 1) }}</bdi>
                <span style="color: var(--sh-muted-2); font-weight: 400;">(<bdi dir="ltr">{{ $reviewsCount }}</bdi>)</span>
            </span>
        @endif

        <div class="sf-product-rule" style="height: 1px; background: var(--sh-navy-50); margin: 10px 0;"></div>

        <div class="sf-product-actions" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-top: auto;">
            <span style="display: flex; align-items: baseline; flex-wrap: wrap; gap: 0 5px; min-width: 0;">
                <strong class="sf-product-price" style="font-family: var(--sh-font-display); font-size: 22px; font-weight: 800; color: var(--sh-navy); letter-spacing: -.4px;">
                    @include('web-views.seafood.partials._price', ['amount' => $discountedUnitPrice])
                </strong>
                @if($hasDiscount)
                    <span class="sf-product-old-price" style="font-size: 13px; color: var(--sh-muted-2); text-decoration: line-through;">
                        @include('web-views.seafood.partials._price', ['amount' => $unitPrice, 'symbolSize' => '11px', 'symbolColor' => 'var(--sh-muted-2)'])
                    </span>
                @endif
            </span>

            @if($hasVariation || !$isInStock)
                <a href="{{ route('product', $product['slug']) }}" class="sf-product-action"
                   style="flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; gap: 7px; height: 44px; padding: 0 16px; border-radius: 13px; background: var(--sh-sea-50); color: var(--sh-navy); font-family: var(--sh-font-display); font-size: 14px; font-weight: 700; white-space: nowrap;">
                    {{ translate('view_details') }}
                </a>
            @else
                <button type="button" class="seafood-cart-toggle{{ $cartItemKey ? ' in-cart' : '' }}" data-product-id="{{ $product['id'] }}"
                        data-quantity="{{ $product['minimum_order_qty'] > 0 ? $product['minimum_order_qty'] : 1 }}"
                        data-product-url="{{ route('product', $product['slug']) }}"
                        data-cart-key="{{ $cartItemKey }}"
                        data-add-label="{{ translate('add_to_cart') }}" data-remove-label="{{ translate('remove_from_cart') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 5h2l2.2 10.2a2 2 0 0 0 2 1.6h6.9a2 2 0 0 0 2-1.5L21 8H7"></path>
                        <circle cx="10" cy="20" r="1.4"></circle>
                        <circle cx="18" cy="20" r="1.4"></circle>
                    </svg>
                    <span class="seafood-cart-toggle-label">{{ $cartItemKey ? translate('remove_from_cart') : translate('add_to_cart') }}</span>
                </button>
            @endif
        </div>
    </div>
</article>
