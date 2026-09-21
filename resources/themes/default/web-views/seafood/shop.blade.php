@extends('layouts.front-end.seafood')

@section('title', $shopInfoArray['name'])

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    @include('web-views.seafood.partials._listing-styles')

    @php
        $shopShareImage = $shopInfoArray['author_type'] != 'admin'
            ? $shopInfoArray['image_full_url']['path']
            : getStorageImages(path: getInHouseShopConfig(key: 'image_full_url'), type: 'shop');
    @endphp
    <meta property="og:image" content="{{ $shopShareImage }}"/>
    <meta property="og:title" content="{{ $shopInfoArray['name'] }}"/>
    <meta property="og:url" content="{{ route('vendor-shop', ['slug' => $shopInfoArray['slug']]) }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $shopShareImage }}"/>
    <meta property="twitter:title" content="{{ $shopInfoArray['name'] }}"/>
    <meta property="twitter:url" content="{{ route('vendor-shop', ['slug' => $shopInfoArray['slug']]) }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    @php
        $isInHouseShop = $shopInfoArray['author_type'] == 'admin';
        $shopAbilityType = $isInHouseShop ? 'inhouse' : 'vendor';
        $isShopTemporarilyClosed = checkVendorAbility(type: $shopAbilityType, status: 'temporary_close', vendor: $shopInfoArray);
        $isShopOnVacation = !$isShopTemporarilyClosed && checkVendorAbility(type: $shopAbilityType, status: 'vacation_status', vendor: $shopInfoArray);
        $shopBannerUrl = getStorageImages(path: $isInHouseShop ? getInHouseShopConfig(key: 'banner_full_url') : $shopInfoArray['banner_full_url'], type: 'wide-banner');
        $shopLogoUrl = getStorageImages(path: $isInHouseShop ? getInHouseShopConfig(key: 'image_full_url') : $shopInfoArray['image_full_url'], type: 'shop');
        $shopAverageRating = round((float) $shopInfoArray['average_rating'], 1);
        $showMinimumOrderAmount = getWebConfig(name: 'minimum_order_amount_status') == 1
            && getWebConfig(name: 'minimum_order_amount_by_seller') == 1
            && $shopInfoArray['minimum_order_amount'] > 0;
    @endphp

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'products'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => $shopInfoArray['name']]],
                ])

                <div style="margin-bottom: 28px;">
                    <div class="sf-shop-hero">
                        <img class="sf-shop-hero-banner" src="{{ $shopBannerUrl }}" alt="">
                    </div>

                    <div class="sf-shop-card">
                        <span class="sf-shop-logo">
                            <img src="{{ $shopLogoUrl }}" alt="{{ $shopInfoArray['name'] }}">
                        </span>

                        <div class="sf-shop-body">
                            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                                <h1 class="sf-shop-name">{{ $shopInfoArray['name'] }}</h1>
                                @if($isShopTemporarilyClosed)
                                    <span class="sf-badge sf-badge-danger">{{ translate('Temporary_OFF') }}</span>
                                @elseif($isShopOnVacation)
                                    <span class="sf-badge sf-badge-warning">{{ translate('closed_now') }}</span>
                                @endif
                            </div>

                            <div class="sf-row-meta">
                                <span style="display: inline-flex; align-items: center; gap: 6px;">
                                    <span class="sf-stars" aria-hidden="true">
                                        @for($starIndex = 1; $starIndex <= 5; $starIndex++)
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="{{ $starIndex <= round($shopAverageRating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="m12 3.6 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.6 9.7l5.8-.8z"></path></svg>
                                        @endfor
                                    </span>
                                    <span class="sr-only">{{ translate('rating') }} {{ $shopAverageRating }}/5</span>
                                    <span><bdi dir="ltr">{{ $shopInfoArray['total_review'] }}</bdi> {{ translate('reviews') }}</span>
                                </span>
                                <span><bdi dir="ltr">{{ $shopInfoArray['total_order'] }}</bdi> {{ translate('orders') }}</span>
                                <span><bdi dir="ltr">{{ $products->total() }}</bdi> {{ translate('products') }}</span>
                                @if($showMinimumOrderAmount)
                                    <span>
                                        {{ translate('minimum_order_amount') }}:
                                        <strong style="color: #0B2C3F;">@include('web-views.seafood.partials._price', ['amount' => $shopInfoArray['minimum_order_amount']])</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="sf-shop-actions">
                            @if(auth('customer')->check())
                                <button type="button" class="sf-btn-sm sf-btn-sm-primary" data-sf-modal-open="sf-shop-chat-modal"
                                        @disabled($isShopTemporarilyClosed)>
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12.5a7.5 7.5 0 0 1-11 6.6L4 20.5l1.4-4.6A7.5 7.5 0 1 1 20 12.5z"></path></svg>
                                    {{ translate('chat') }}
                                </button>
                            @else
                                <a href="{{ route('customer.auth.login') }}" class="sf-btn-sm sf-btn-sm-primary">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12.5a7.5 7.5 0 0 1-11 6.6L4 20.5l1.4-4.6A7.5 7.5 0 1 1 20 12.5z"></path></svg>
                                    {{ translate('chat') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @include('web-views.seafood.partials._listing', [
                    'listingTitle' => translate('all_products'),
                    'listingHeadingTag' => 'h2',
                    'listingProducts' => $products,
                    'listingSearchPlaceholder' => translate('search_in_this_shop'),
                    'listingFilterCategories' => $categories,
                    'listingQuery' => [
                        'offer_type' => $data['offer_type'] ?? '',
                        'sort_by' => $data['sort_by'] ?? '',
                        'product_type' => $data['product_type'] ?? '',
                        'min_price' => $data['min_price'] ?? '',
                        'max_price' => $data['max_price'] ?? '',
                        'product_name' => $data['product_name'] ?? '',
                    ],
                    'listingScope' => array_filter(['category_ids[]' => ((array) request('category_ids', []))[0] ?? null]),
                ])
            </section>
        </main>

        <div style="background: #F4FAFB; border-top: 1px solid #E4EFF1; padding-top: clamp(44px, 7vw, 76px);">
            @include('web-views.seafood.partials._help-links')
        </div>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')
    </div>

    @if(auth('customer')->check())
        @include('web-views.seafood.partials._chat-modal', [
            'chatModalId' => 'sf-shop-chat-modal',
            'chatModalTitle' => translate('Send_Message_to_vendor'),
            'chatRecipientField' => 'vendor_id',
            'chatRecipientId' => $shopInfoArray['seller_id'],
            'chatInboxType' => 'vendor',
        ])
    @endif
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')

    @include('web-views.seafood.partials._listing-scripts')

@endpush
