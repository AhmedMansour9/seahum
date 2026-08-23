@php($hasVariation = !empty(json_decode($product['variation'] ?? '[]', true)) || !empty(json_decode($product['choice_options'] ?? '[]', true)))
@php($isInStock = $product['product_type'] === 'digital' || $product['current_stock'] > 0)
@php($stockColor = $isInStock ? '#0E8E88' : '#C9821B')

<article data-category-id="{{ $product['category_id'] }}"
         style="display: flex; flex-direction: column; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 26px -24px rgba(11,44,63,.9); transition: transform .28s, box-shadow .28s, border-color .28s;">
    <div style="position: relative; aspect-ratio: 1 / 1; background: #EAF4F5;">
        <a href="{{ route('product', $product['slug']) }}" style="display: block; height: 100%;">
            <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                 alt="{{ $product['name'] }}" loading="lazy"
                 style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;">
        </a>
        <span style="position: absolute; top: 12px; inset-inline-start: 12px; display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; background: rgba(255,255,255,.94); color: {{ $stockColor }}; box-shadow: 0 6px 16px -8px rgba(11,44,63,.6);">
            <span style="width: 6px; height: 6px; border-radius: 50%; display: inline-block; background: {{ $stockColor }};"></span>
            {{ $isInStock ? translate('in_stock') : translate('out_of_stock') }}
        </span>
        <button type="button" class="seafood-wishlist-btn" data-product-id="{{ $product['id'] }}"
                aria-label="{{ translate('add_to_wishlist') }}"
                style="position: absolute; top: 7px; inset-inline-end: 7px; width: 44px; height: 44px; border-radius: 50%; border: none; background: rgba(255,255,255,.92); display: grid; place-items: center; cursor: pointer; transition: transform .2s;">
            <svg width="17" height="17" viewBox="0 0 24 24" stroke="#0B2C3F" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" fill="none">
                <path d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 4.6-7 9-7 9Z"></path>
            </svg>
        </button>
    </div>

    <div style="padding: 16px 18px 18px; display: flex; flex-direction: column; flex: 1;">
        <h3 style="font-family: Cairo, sans-serif; font-size: 18px; font-weight: 700; margin: 0; color: #0B2C3F; line-height: 1.5; text-wrap: pretty;">
            <a href="{{ route('product', $product['slug']) }}" style="color: inherit;">{{ Str::limit($product['name'], 60) }}</a>
        </h3>

        <div style="height: 1px; background: #EDF5F6; margin: 14px 0 13px;"></div>

        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: auto;">
            <span style="display: flex; align-items: baseline; gap: 5px; white-space: nowrap;">
                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                    <del style="font-size: 13px; color: #7B98A4;">{{ webCurrencyConverter(amount: $product['unit_price']) }}</del>
                @endif
                <strong style="font-family: Cairo, sans-serif; font-size: 22px; font-weight: 800; color: #0B2C3F; letter-spacing: -.4px;">
                    {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                </strong>
            </span>

            @if($hasVariation || !$isInStock)
                <a href="{{ route('product', $product['slug']) }}"
                   style="flex-shrink: 0; display: inline-flex; align-items: center; gap: 7px; height: 44px; padding: 0 16px; border-radius: 13px; background: #ECF8F8; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; white-space: nowrap;">
                    {{ translate('view_details') }}
                </a>
            @else
                <button type="button" class="seafood-add-to-cart" data-product-id="{{ $product['id'] }}"
                        data-quantity="{{ $product['minimum_order_qty'] > 0 ? $product['minimum_order_qty'] : 1 }}"
                        data-product-url="{{ route('product', $product['slug']) }}"
                        style="flex-shrink: 0; display: inline-flex; align-items: center; gap: 7px; height: 44px; padding: 0 16px; border: none; border-radius: 13px; background: #ECF8F8; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; white-space: nowrap; cursor: pointer; transition: background .22s, color .22s, transform .22s;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 5h2l2.2 10.2a2 2 0 0 0 2 1.6h6.9a2 2 0 0 0 2-1.5L21 8H7"></path>
                        <circle cx="10" cy="20" r="1.4"></circle>
                        <circle cx="18" cy="20" r="1.4"></circle>
                    </svg>
                    {{ translate('add_to_cart') }}
                </button>
            @endif
        </div>
    </div>
</article>
