{{-- schema.org Product data, so search results can show the price and stock next to the product. --}}
@php
    $structuredProductUrl = rtrim(config('app.url'), '/').route('product', ['slug' => $product['slug']], false);
    $structuredProductData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['name'],
        'url' => $structuredProductUrl,
        'image' => [getStorageImages(path: $product->thumbnail_full_url, type: 'product')],
        'description' => \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string) $product['details'])))), 500, ''),
        'sku' => (string) ($product['code'] ?: $product['id']),
        'brand' => ['@type' => 'Brand', 'name' => $web_config['company_name']],
        'offers' => [
            '@type' => 'Offer',
            'url' => $structuredProductUrl,
            'priceCurrency' => app(\App\Services\MetaPixelService::class)->getCurrencyCode(),
            'price' => round((float) webCurrencyConverterOnlyDigit(amount: getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'value')), 2),
            'availability' => ($product['product_type'] === 'digital' || $product['current_stock'] > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => ['@type' => 'Organization', 'name' => $web_config['company_name']],
        ],
    ];
    if (($ratingCount ?? 0) > 0) {
        $structuredProductData['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => round((float) $ratingValue, 1),
            'reviewCount' => (int) $ratingCount,
        ];
    }
@endphp
<script type="application/ld+json">{!! json_encode($structuredProductData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
