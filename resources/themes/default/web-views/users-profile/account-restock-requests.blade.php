@extends('layouts.front-end.seafood-account')

@section('title', translate('Restock_Requests'))

@section('page_title', translate('Restock_Requests'))

@if($restockProducts->count() > 0)
    @section('page_count', $restockProducts->total())
@endif

@section('page_actions')
    @if($restockProducts->count() > 0)
        <a href="javascript:" class="sf-btn-sm sf-btn-sm-danger call-route-alert"
           data-route="{{ route('user-restock-request-delete') }}"
           data-message="{{ translate('want_to_clear_all_restock_request_data') }}?">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14M10 7V5h4v2M7 7l.8 12.2A1.8 1.8 0 0 0 9.6 21h4.8a1.8 1.8 0 0 0 1.8-1.8L17 7"></path></svg>
            {{ translate('clear_all') }}
        </a>
    @endif
@endsection

@section('account_content')
    @if($restockProducts->count() > 0)
        <div class="sf-stack">
            @foreach($restockProducts as $restockProduct)
                @php($restockedProduct = $restockProduct?->product)
                @php($restockUnitPrice = $restockedProduct?->unit_price)
                @php($restockVariations = json_decode($restockedProduct?->variation ?? '[]', true))
                @foreach($restockVariations ?? [] as $restockVariation)
                    @php($restockUnitPrice = $restockVariation['type'] === $restockProduct->variant ? $restockVariation['price'] : $restockUnitPrice)
                @endforeach
                @php($restockHasDiscount = getProductPriceByType(product: $restockedProduct, type: 'discount', result: 'value') > 0)

                <article class="sf-row-card" id="product-{{ $restockProduct['id'] }}">
                    <a class="sf-row-media" href="{{ $restockedProduct?->slug ? route('product', $restockedProduct->slug) : 'javascript:' }}">
                        <img src="{{ getStorageImages(path: $restockedProduct?->thumbnail_full_url, type: 'backend-product') }}"
                             alt="{{ $restockedProduct?->name }}" loading="lazy">
                    </a>

                    <div class="sf-row-body">
                        <h2 class="sf-row-title">
                            <a href="{{ $restockedProduct?->slug ? route('product', $restockedProduct->slug) : 'javascript:' }}" style="color: inherit;">
                                {{ $restockedProduct?->name }}
                            </a>
                        </h2>

                        <div class="sf-row-meta">
                            @if($restockProduct['variant'])
                                <span>{{ translate('variant') }}: <strong style="color: #0B2C3F;">{{ $restockProduct['variant'] }}</strong></span>
                            @elseif(getWebConfig(name: 'product_brand') && $restockedProduct?->brand?->name)
                                <span>{{ translate('brand') }}: <strong style="color: #0B2C3F;">{{ $restockedProduct->brand->name }}</strong></span>
                            @endif
                            <span class="sf-badge sf-badge-warning">{{ translate('out_of_stock') }}</span>
                        </div>

                        <span style="display: flex; align-items: baseline; flex-wrap: wrap; gap: 0 8px;">
                            <strong style="font-family: Cairo, sans-serif; font-size: 18px; font-weight: 800; color: #0B2C3F;">
                                {{ $restockHasDiscount
                                    ? getProductPriceByType(product: $restockedProduct, type: 'discounted_unit_price', result: 'string', price: $restockUnitPrice)
                                    : webCurrencyConverter(amount: $restockUnitPrice) }}
                            </strong>
                            @if($restockHasDiscount)
                                <del style="font-size: 13px; color: #A8B9C1;">{{ webCurrencyConverter(amount: $restockUnitPrice) }}</del>
                            @endif
                        </span>
                    </div>

                    <div class="sf-row-actions">
                        <a href="javascript:" class="sf-icon-btn sf-icon-btn-danger call-route-alert"
                           data-route="{{ route('user-restock-request-delete', ['id' => $restockProduct['id']]) }}"
                           data-message="{{ translate('want_to_remove_this_restock_request') }}?"
                           aria-label="{{ translate('remove') }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div id="paginator-ajax">
            {!! $restockProducts->links('web-views.seafood.partials._pagination') !!}
        </div>
    @else
        @include('web-views.seafood.partials._empty-state', [
            'emptyStateIcon' => '<path d="M20 12a8 8 0 1 1-2.4-5.7"></path><path d="M20 4v4h-4"></path>',
            'emptyStateTitle' => translate('no_product_found_in_restock_request').'!',
            'emptyStateText' => translate('ask_to_be_notified_when_an_out_of_stock_product_is_available_again'),
            'emptyStateActionUrl' => route('products'),
            'emptyStateActionLabel' => translate('explore_more'),
        ])
    @endif
@endsection
