@php($hasVariation = !empty(json_decode($product['variation'] ?? '[]', true)) || !empty(json_decode($product['choice_options'] ?? '[]', true)))
@php($isInStock = $product['product_type'] === 'digital' || $product['current_stock'] > 0)
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
@php($productUrl = route('product', $product['slug']))

<article class="sf-product-card{{ $isInStock ? '' : ' is-sold-out' }}" data-category-id="{{ $product['category_id'] }}"
         style="display: {{ $isVisibleCard ? 'flex' : 'none' }};">
    <div class="sf-product-figure">
        <a href="{{ $productUrl }}" class="sf-product-figure-link" tabindex="-1" aria-hidden="true">
            <img src="{{ $thumbnailUrl }}" alt="{{ $product['name'] }}" loading="lazy" class="sf-product-media">
        </a>

        @if($hasDiscount && $discountPercentage > 0)
            <span class="sf-product-discount">
                <span class="sr-only">{{ translate('discount') }}</span>
                <bdi dir="ltr">−{{ $discountPercentage }}%</bdi>
            </span>
        @endif

        @unless($isInStock)
            <span class="sf-product-soldout">{{ translate('out_of_stock') }}</span>
        @endunless

        <button type="button" class="{{ $wishlistRemovesCard ? 'function-remove-wishList' : 'seafood-wishlist-btn' }} sf-product-wish"
                data-product-id="{{ $product['id'] }}" data-id="{{ $product['id'] }}"
                data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
                data-add-label="{{ translate('add_to_wishlist') }}" data-remove-label="{{ translate('remove_from_wishlist') }}"
                aria-label="{{ $isWishlisted ? translate('remove_from_wishlist') : translate('add_to_wishlist') }}">
            <svg width="17" height="17" viewBox="0 0 24 24" stroke="{{ $isWishlisted ? '#D92F45' : '#082B49' }}" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" fill="{{ $isWishlisted ? '#D92F45' : 'none' }}">
                <path d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 4.6-7 9-7 9Z"></path>
            </svg>
        </button>
    </div>

    <div class="sf-product-body">
        <h3 class="sf-product-title">
            <a href="{{ $productUrl }}">{{ Str::limit($product['name'], 60) }}</a>
        </h3>

        @if($showRating && $reviewsCount > 0)
            <span class="sf-product-rating">
                <span aria-hidden="true" class="sf-product-rating-stars">{{ str_repeat('★', (int) round($averageRating)) }}{{ str_repeat('☆', 5 - (int) round($averageRating)) }}</span>
                <bdi dir="ltr">{{ number_format($averageRating, 1) }}</bdi>
                <span class="sf-product-rating-count">(<bdi dir="ltr">{{ $reviewsCount }}</bdi>)</span>
            </span>
        @endif

        <div class="sf-product-footer">
            <div class="sf-product-pricing">
                {{-- The old-price line is always there so every card keeps the same height. --}}
                <span class="sf-product-old-price" @unless($hasDiscount) aria-hidden="true" @endunless>
                    @if($hasDiscount)
                        @include('web-views.seafood.partials._price', ['amount' => $unitPrice, 'symbolSize' => '11px', 'symbolColor' => 'var(--sh-muted-2)'])
                    @endif
                </span>
                <strong class="sf-product-price">
                    @include('web-views.seafood.partials._price', ['amount' => $discountedUnitPrice, 'symbolSize' => '12px', 'symbolColor' => 'var(--sh-muted)'])
                </strong>
            </div>

            @if($hasVariation || !$isInStock)
                <a href="{{ $productUrl }}" class="sf-product-action sf-product-cta" title="{{ translate('view_details') }}" aria-label="{{ translate('view_details') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path class="sf-product-cta-arrow" d="M14 6 8 12l6 6"></path></svg>
                </a>
            @else
                <button type="button" class="seafood-cart-toggle sf-product-cta{{ $cartItemKey ? ' in-cart' : '' }}" data-product-id="{{ $product['id'] }}"
                        data-quantity="{{ $product['minimum_order_qty'] > 0 ? $product['minimum_order_qty'] : 1 }}"
                        data-product-url="{{ $productUrl }}"
                        data-cart-key="{{ $cartItemKey }}"
                        data-add-label="{{ translate('add_to_cart') }}" data-remove-label="{{ translate('remove_from_cart') }}"
                        title="{{ $cartItemKey ? translate('remove_from_cart') : translate('add_to_cart') }}">
                    <svg class="sf-product-cta-add" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 5h2l2 10h10.5l2-7H9"></path><circle cx="9" cy="20" r="1.3"></circle><circle cx="17" cy="20" r="1.3"></circle><path d="M14 2.5v5M11.5 5h5"></path>
                    </svg>
                    <svg class="sf-product-cta-added" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m5 12.5 4.5 4.5L19 7.5"></path>
                    </svg>
                    <span class="seafood-cart-toggle-label sr-only">{{ $cartItemKey ? translate('remove_from_cart') : translate('add_to_cart') }}</span>
                </button>
            @endif
        </div>
    </div>
</article>
