@php($galleryImages = ($product->product_type === 'physical' && !empty($product->color_image) && count($product->color_images_full_url) > 0)
    ? $product->color_images_full_url
    : $product->images_full_url)
@php($gallerySlides = collect([['url' => getStorageImages(path: $product->thumbnail_full_url, type: 'product'), 'color' => '']])
    ->concat(collect($galleryImages)->map(fn ($photo) => [
        'url' => getStorageImages(path: $photo['image_name'] ?? $photo, type: 'backend-product'),
        'color' => $photo['color'] ?? '',
    ]))
    ->unique('url')
    ->values())
@php($discountValue = getProductPriceByType(product: $product, type: 'discount', result: 'value'))

<div class="sf-gallery">
    <div class="sf-gallery-stage" style="position: relative; border: 1px solid #E4EFF1; border-radius: 20px; overflow: hidden; background: #EAF4F5;">
        @if($discountValue > 0)
            <span class="sf-gallery-badge" style="position: absolute; top: 14px; inset-inline-start: 14px; z-index: 2; display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 999px; background: #F2A43A; color: #3A2400; font-family: Cairo, sans-serif; font-size: 12.5px; font-weight: 800;">
                <span style="direction: ltr;">-{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}</span>
            </span>
        @endif

        <img id="seafood-gallery-image" src="{{ $gallerySlides[0]['url'] }}"
             alt="{{ $product['name'] }}"
             style="width: 100%; aspect-ratio: 1 / 1; object-fit: cover; display: block;">
    </div>

    <div class="sf-gallery-thumbs" style="display: flex; gap: 10px; margin-top: 12px; overflow-x: auto; padding-bottom: 4px;">
        @foreach($gallerySlides as $slideIndex => $gallerySlide)
            <button type="button" class="sf-gallery-thumb{{ $slideIndex === 0 ? ' active' : '' }}"
                    data-image="{{ $gallerySlide['url'] }}" data-color="{{ $gallerySlide['color'] }}"
                    aria-label="{{ translate('product') }} {{ $slideIndex + 1 }}">
                <img src="{{ $gallerySlide['url'] }}" alt="{{ $product['name'] }}" loading="lazy"
                     style="width: 100%; height: 100%; object-fit: cover; display: block;">
            </button>
        @endforeach
    </div>
</div>
