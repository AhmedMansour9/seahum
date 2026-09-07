@php($cartItemKeysByProduct = \App\Utils\CartManager::getCartListQuery()->pluck('id', 'product_id'))
@php($hasActiveFilters = collect(['product_name', 'min_price', 'max_price', 'brand_id', 'product_type'])->contains(fn ($filterKey) => filled(request($filterKey))))

@if(count($products) > 0)
    <div class="sf-product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr)); gap: 20px;">
        @foreach($products as $product)
            @php($listedProduct = !empty($product['product_id']) ? $product->product : $product)
            @if(!empty($listedProduct))
                @include('web-views.seafood.partials._product-card', [
                    'product' => $listedProduct,
                    'cartItemKey' => $cartItemKeysByProduct[$listedProduct['id']] ?? null,
                    'mediaRatio' => 'square',
                    'showPricingDetails' => true,
                    'showRating' => true,
                ])
            @endif
        @endforeach
    </div>

    {!! $products->links('web-views.seafood.partials._pagination') !!}
@else
    <div class="sf-empty-state" style="background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; padding: 44px 22px; text-align: center;">
        <span style="width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: inline-grid; place-items: center;">
            <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.2-3.2"></path></svg>
        </span>
        <h4 style="font-family: Cairo, sans-serif; font-size: 17px; font-weight: 700; margin: 16px 0 8px; color: #0B2C3F;">
            {{ $hasActiveFilters ? translate('no_results_found') : translate('no_product_found') }}
        </h4>
        <p style="font-size: 14px; color: #6E8C99; line-height: 1.8; margin: 0 auto 18px; max-width: 420px;">
            {{ $hasActiveFilters ? translate('no_product_matches_the_selected_filters_try_widening_your_search') : translate('there_are_no_products_in_this_category_yet_browse_the_other_categories') }}
        </p>
        @if($hasActiveFilters)
            <button type="button" class="sf-see-more sf-filter-reset" style="min-height: 44px; padding: 0 24px; font-size: 14.5px;">
                {{ translate('reset_filters') }}
            </button>
        @else
            <a href="{{ route('categories') }}" class="sf-see-more" style="min-height: 44px; padding: 0 24px; font-size: 14.5px;">
                {{ translate('browse_categories') }}
            </a>
        @endif
    </div>
@endif
