@extends('layouts.front-end.seafood')

@section('title', $product['name'])

@push('css_or_js')
    @include('web-views.seafood.partials._styles')
    @include(VIEW_FILE_NAMES['product_seo_meta_content_partials'], ['metaContentData' => $product?->seoInfo, 'productDetails' => $product])

    <style>
        .sf-product-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 32px; align-items: start; }
        .sf-gallery-thumb { flex: 0 0 auto; width: 72px; height: 72px; padding: 0; border-radius: 12px; border: 1.5px solid #E4EFF1; background: #EAF4F5; overflow: hidden; cursor: pointer; transition: border-color .2s, transform .2s; }
        .sf-gallery-thumb:hover { border-color: #BEE6E4; }
        .sf-gallery-thumb.active { border-color: #17B0A7; }
        .sf-gallery-thumbs::-webkit-scrollbar { height: 6px; }
        .sf-gallery-thumbs::-webkit-scrollbar-thumb { background: #E4EFF1; border-radius: 999px; }

        .sf-option-group { display: flex; flex-direction: column; gap: 10px; }
        .sf-option-label { font-family: Cairo, sans-serif; font-size: 13.5px; font-weight: 700; color: #45677A; }
        .sf-option-list, .sf-swatch-list { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
        .sf-option-list input, .sf-swatch-list input { position: absolute; opacity: 0; width: 0; height: 0; }
        .sf-option-pill { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 18px; border-radius: 999px; border: 1.4px solid #E4EFF1; background: #FFFFFF; color: #45677A; font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; cursor: pointer; transition: background .2s, color .2s, border-color .2s; }
        .sf-option-pill:hover { border-color: #17B0A7; color: #0B2C3F; }
        .sf-option-list input:checked + .sf-option-pill { background: #0B2C3F; color: #FFFFFF; border-color: #17B0A7; }
        .sf-option-list input:focus-visible + .sf-option-pill,
        .sf-swatch-list input:focus-visible + .sf-swatch { box-shadow: 0 0 0 3px rgba(23,176,167,.35); }
        .sf-swatch { display: block; width: 36px; height: 36px; border-radius: 50%; border: 2px solid #E4EFF1; cursor: pointer; transition: border-color .2s, transform .2s; }
        .sf-swatch-list input:checked + .sf-swatch { border-color: #17B0A7; transform: scale(1.08); }

        .sf-qty { display: inline-flex; align-items: center; border: 1px solid #E4EFF1; border-radius: 12px; overflow: hidden; background: #FFFFFF; }
        .sf-qty-btn { width: 44px; height: 48px; border: none; background: #F4FAFB; color: #0B2C3F; font-size: 18px; font-weight: 700; cursor: pointer; transition: background .2s, color .2s; }
        .sf-qty-btn:hover:not([disabled]) { background: #ECF8F8; color: #0E8E88; }
        .sf-qty-btn[disabled] { color: #A8B9C1; cursor: not-allowed; }
        .sf-qty-input { width: 60px; height: 48px; border: none; text-align: center; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 700; color: #0B2C3F; outline: none; }

        .sf-buy-primary { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 52px; padding: 0 30px; border: none; border-radius: 14px; background: #17B0A7; color: #062A3E; font-family: Cairo, sans-serif; font-size: 16px; font-weight: 800; cursor: pointer; transition: background .22s, transform .22s; }
        .sf-buy-primary:hover:not([disabled]) { background: #14C6BB; transform: translateY(-2px); }
        .sf-buy-primary[disabled], .sf-buy-ghost[disabled] { opacity: .55; cursor: not-allowed; }
        .sf-buy-ghost { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 52px; padding: 0 26px; border: 1.5px solid #CFE9EA; border-radius: 14px; background: #FFFFFF; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 15.5px; font-weight: 700; cursor: pointer; transition: background .22s, border-color .22s, transform .22s; }
        .sf-buy-ghost:hover:not([disabled]) { background: #ECF8F8; border-color: #17B0A7; transform: translateY(-2px); }
        .sf-icon-action { width: 52px; height: 52px; border-radius: 14px; border: 1.5px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; display: grid; place-items: center; cursor: pointer; transition: background .2s, border-color .2s, color .2s; }
        .sf-icon-action:hover { background: #ECF8F8; border-color: #BEE6E4; color: #0E8E88; }
        .sf-inline-alert { margin: 0; padding: 14px 16px; border-radius: 14px; background: #FDF3E3; border: 1px solid #F2D9AE; color: #C9821B; font-size: 14px; line-height: 1.8; }

        .sf-panel { background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; padding: 24px; box-shadow: 0 10px 26px -24px rgba(11,44,63,.9); }
        .sf-tabs-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .sf-tab-panel[hidden] { display: none; }
        .sf-rich-text { font-size: 15px; line-height: 1.9; color: #45677A; overflow-wrap: anywhere; }
        .sf-rich-text img { max-width: 100%; height: auto; }
        .sf-rich-text table { display: block; overflow-x: auto; max-width: 100%; }
        .sf-collapsible { position: relative; max-height: 320px; overflow: hidden; }
        .sf-collapsible.expanded { max-height: none; }
        .sf-collapsible:not(.expanded)::after { content: ""; position: absolute; inset-inline: 0; bottom: 0; height: 90px; background: linear-gradient(to bottom, rgba(255,255,255,0), #FFFFFF); }

        .sf-rating-bar { height: 8px; border-radius: 999px; background: #EDF5F6; overflow: hidden; }
        .sf-rating-bar > span { display: block; height: 100%; border-radius: 999px; background: #F2A43A; }

        .sf-sticky-buy { display: none; position: fixed; inset-inline: 0; bottom: 0; z-index: 80; background: rgba(255,255,255,.97); backdrop-filter: blur(14px); border-top: 1px solid #E4EFF1; padding: 10px 16px calc(10px + env(safe-area-inset-bottom)); box-shadow: 0 -10px 30px -22px rgba(11,44,63,.9); align-items: center; gap: 12px; }
        .sf-sticky-buy .sf-buy-primary { flex: 1; min-height: 48px; padding: 0 18px; font-size: 15px; }

        @media (max-width: 979px) {
            .sf-product-layout { grid-template-columns: minmax(0, 1fr) !important; gap: 24px; }
        }

        @media (max-width: 759px) {
            .sf-sticky-buy { display: flex; }
            .sf-sticky-buy-spacer { height: calc(72px + env(safe-area-inset-bottom)); }
            .sf-buy-primary, .sf-buy-ghost { flex: 1 1 100%; }
            .sf-icon-action { flex: 0 0 auto; }
        }
    </style>
@endpush

@section('content')
    @php($cartItems = \App\Utils\CartManager::getCartListQuery())
    @php($cartItemsCount = $cartItems->count())
    @php($cartItemKeysByProduct = $cartItems->pluck('id', 'product_id'))
    @php($isMultiVendor = getWebConfig(name: 'business_mode') == 'multi')
    @php($shop = $product->added_by == 'seller' ? $product?->seller?->shop : null)
    @php($hasDetails = !empty($product['details']))
    @php($hasVideo = $product->video_url != null && str_contains($product->video_url, 'youtube.com/embed/'))

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'products'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(20px, 3vw, 32px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', ['breadcrumbItems' => array_values(array_filter([
                    !empty($product?->category?->slug) ? ['label' => $product->category->name, 'url' => route('category-products', ['slug' => $product->category->slug])] : null,
                    ['label' => $product['name']],
                ]))])

                <div class="sf-product-layout">
                    @include('web-views.seafood.partials._product-gallery')
                    @include('web-views.seafood.partials._product-buy-box')
                </div>

                @if($isMultiVendor && $shop)
                    <div class="sf-panel" style="margin-top: 32px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                            <img src="{{ getStorageImages(path: $shop->image_full_url, type: 'shop') }}" alt="{{ $shop->name }}"
                                 style="width: 56px; height: 56px; border-radius: 16px; object-fit: cover; background: #EAF4F5; flex-shrink: 0;">
                            <span style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                                <strong style="font-family: Cairo, sans-serif; font-size: 17px; font-weight: 700; color: #0B2C3F; overflow-wrap: anywhere;">{{ $shop->name }}</strong>
                                <span style="font-size: 13.5px; color: #6E8C99;">{{ $totalReviews }} {{ translate('reviews') }}</span>
                            </span>
                        </div>
                        <a href="{{ route('vendor-shop', ['slug' => $shop->slug]) }}" class="sf-see-more" style="min-height: 44px; padding: 0 22px; font-size: 14.5px;">
                            {{ translate('visit_store') }}
                        </a>
                    </div>
                @endif

                <div class="sf-panel" style="margin-top: 32px;">
                    <div class="sf-tabs-bar" role="tablist">
                        <button type="button" class="sf-tab active" role="tab" aria-selected="true" aria-controls="overview" id="tab-overview">{{ translate('overview') }}</button>
                        <button type="button" class="sf-tab" role="tab" aria-selected="false" aria-controls="reviews" id="tab-reviews">{{ translate('reviews') }} ({{ $overallRating[1] }})</button>
                    </div>

                    <div class="sf-tab-panel" id="overview" role="tabpanel" aria-labelledby="tab-overview" style="margin-top: 22px;">
                        @if($hasVideo)
                            <div style="position: relative; aspect-ratio: 16 / 9; max-width: 100%; border-radius: 16px; overflow: hidden; margin-bottom: 20px; background: #EAF4F5;">
                                <iframe src="{{ $product->video_url }}" title="{{ $product['name'] }}" loading="lazy"
                                        style="position: absolute; inset: 0; width: 100%; height: 100%; border: 0;"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                            </div>
                        @endif

                        @if($hasDetails)
                            <h2 style="font-family: Cairo, sans-serif; font-size: 18px; font-weight: 800; color: #0B2C3F; margin: 0 0 12px;">{{ translate('Detail_Description') }}</h2>
                            <div class="sf-rich-text sf-collapsible" id="seafood-product-details">{!! $product['details'] !!}</div>
                            <div style="display: flex; justify-content: center; margin-top: 16px;">
                                <button type="button" class="sf-see-more" id="seafood-details-toggle"
                                        data-more="{{ translate('see_more') }}" data-less="{{ translate('see_less') }}"
                                        style="min-height: 44px; padding: 0 24px; font-size: 14.5px;">{{ translate('see_more') }}</button>
                            </div>
                        @elseif(!$hasVideo)
                            <div class="sf-empty-state" style="text-align: center; padding: 32px 16px;">
                                <span style="width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: inline-grid; place-items: center;">
                                    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h9l5 5v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"></path><path d="M14 4v5h5"></path></svg>
                                </span>
                                <p style="font-size: 14px; color: #6E8C99; margin: 14px 0 0;">{{ translate('product_details_not_found') }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="sf-tab-panel" id="reviews" role="tabpanel" aria-labelledby="tab-reviews" style="margin-top: 22px;" hidden>
                        @if($overallRating[1] == 0)
                            <div class="sf-empty-state" style="text-align: center; padding: 32px 16px;">
                                <span style="width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: inline-grid; place-items: center;">
                                    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3.6 2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.5 9.8l5.9-.9z"></path></svg>
                                </span>
                                <p style="font-size: 14px; color: #6E8C99; margin: 14px 0 0;">{{ translate('No_review_given_yet') }}</p>
                            </div>
                        @else
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(260px, 100%), 1fr)); gap: 24px; align-items: start; margin-bottom: 26px;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 22px; background: #F4FAFB; border: 1px solid #E4EFF1; border-radius: 16px;">
                                    <strong style="font-family: Cairo, sans-serif; font-size: 40px; font-weight: 800; color: #0B2C3F; line-height: 1;">{{ $overallRating[0] }}</strong>
                                    <span style="display: flex; gap: 3px;">
                                        @for($star = 1; $star <= 5; $star++)
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="{{ $star <= round($overallRating[0]) ? '#F2A43A' : '#DCE8EC' }}"><path d="m12 3.6 2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.5 9.8l5.9-.9z"></path></svg>
                                        @endfor
                                    </span>
                                    <span style="font-size: 13.5px; color: #6E8C99;">{{ $overallRating[1] }} {{ translate('ratings') }}</span>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    @foreach([translate('excellent'), translate('good'), translate('average'), translate('below_average'), translate('poor')] as $ratingIndex => $ratingLabel)
                                        <div style="display: grid; grid-template-columns: minmax(72px, auto) minmax(0, 1fr) 36px; align-items: center; gap: 12px;">
                                            <span style="font-size: 13px; color: #6E8C99;">{{ $ratingLabel }}</span>
                                            <span class="sf-rating-bar"><span style="width: {{ $overallRating[1] > 0 ? ($rating[$ratingIndex] / $overallRating[1]) * 100 : 0 }}%;"></span></span>
                                            <span style="font-size: 13px; color: #45677A; text-align: end;">{{ $rating[$ratingIndex] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div id="product-review-list" style="display: flex; flex-direction: column; gap: 16px;">
                                @include(VIEW_FILE_NAMES['product_reviews_partials'], ['productReviews' => $productReviews])
                            </div>

                            @if($productReviews->total() > $productReviews->count())
                                <div style="display: flex; justify-content: center; margin-top: 20px;">
                                    <button type="button" class="sf-see-more view_more_button" style="min-height: 44px; padding: 0 24px; font-size: 14.5px;">
                                        {{ translate('view_more') }}
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                @if(count($relatedProducts) > 0)
                    <section style="margin-top: clamp(36px, 5vw, 60px);">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 20px;">
                            <h2 style="font-family: Cairo, sans-serif; font-size: clamp(20px, 2.6vw, 26px); font-weight: 800; color: #0B2C3F; margin: 0;">{{ translate('similar_products') }}</h2>
                            @if(!empty($product?->category?->slug))
                                <a href="{{ route('category-products', ['slug' => $product->category->slug]) }}" class="sf-section-link" style="font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; color: #0E8E88;">{{ translate('View_All') }}</a>
                            @endif
                        </div>
                        <div class="sf-product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(240px, 100%), 1fr)); gap: 20px;">
                            @foreach($relatedProducts->take(8) as $relatedProduct)
                                @include('web-views.seafood.partials._product-card', [
                                    'product' => $relatedProduct,
                                    'cartItemKey' => $cartItemKeysByProduct[$relatedProduct['id']] ?? null,
                                    'mediaRatio' => 'square',
                                    'showPricingDetails' => true,
                                    'showRating' => true,
                                ])
                            @endforeach
                        </div>
                    </section>
                @endif
            </section>
        </main>

        <div style="background: #F4FAFB; border-top: 1px solid #E4EFF1; padding-top: clamp(44px, 7vw, 76px);">
            @include('web-views.seafood.partials._help-links')
        </div>

        @include('web-views.seafood.partials._footer')

        <div class="sf-sticky-buy">
            <span style="display: flex; flex-direction: column; line-height: 1.3; min-width: 0;">
                <span style="font-size: 11.5px; color: #6E8C99;">{{ translate('total_price') }}</span>
                <strong class="product-details-chosen-price-amount" style="font-family: Cairo, sans-serif; font-size: 17px; font-weight: 800; color: #0E8E88; letter-spacing: -.4px;">
                    @include('web-views.seafood.partials._price', ['amount' => $initialProductConfig['total_quantity_price'], 'symbolSize' => '12px'])
                </strong>
            </span>
            <button type="button" class="sf-buy-primary sf-sticky-add-to-cart">{{ translate('add_to_cart') }}</button>
        </div>
        <div class="sf-sticky-buy-spacer"></div>
    </div>

    <span id="route-cart-variant-price" data-url="{{ route('cart.variant_price') }}"></span>
    <span id="route-cart-updateQuantity-guest" data-url="{{ route('cart.updateQuantity.guest') }}"></span>
    <span id="route-product-restock-request" data-url="{{ route('cart.product-restock-request') }}"></span>
    <span id="route-review-list-product" data-url="{{ route('review-list-product') }}"></span>
    <span id="products-details-page-data" data-id="{{ $product['id'] }}"></span>
    <span id="seafood-product-messages"
          data-choose-options="{{ translate('please_choose_all_options') }}"
          data-stock-limit="{{ translate('sorry_stock_limit_exceeded') }}"
          data-minimum-order="{{ translate('minimum_order_quantity_cannot_be_less_than') }}"></span>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')
    @include('web-views.seafood.partials._product-scripts')
@endpush
