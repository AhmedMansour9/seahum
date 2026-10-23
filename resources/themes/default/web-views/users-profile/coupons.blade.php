@extends('layouts.front-end.seafood-account')

@section('title', translate('coupons'))

@section('page_title', translate('coupons'))

@if($coupons->total() > 0)
    @section('page_count', $coupons->total())
@endif

@section('account_content')
    @if($coupons->count() > 0)
        <div class="sf-coupon-grid">
            @foreach($coupons as $coupon)
                @php
                    $isFreeDelivery = $coupon->coupon_type == 'free_delivery';
                    if ($coupon->seller_id == '0') {
                        $couponShopName = translate('All_Shops');
                        $couponShopUrl = null;
                    } elseif ($coupon->seller_id == null) {
                        $couponShopName = getInHouseShopConfig(key: 'name');
                        $couponShopUrl = route('vendor-shop', ['slug' => getInHouseShopConfig(key: 'slug')]);
                    } else {
                        $couponShopName = $coupon->seller?->shop?->name ?? translate('shop_not_found');
                        $couponShopUrl = $coupon->seller?->shop ? route('vendor-shop', ['slug' => $coupon->seller->shop['slug']]) : null;
                    }
                @endphp

                <article class="sf-coupon-card">
                    <div class="sf-coupon-card-value">
                        <span class="sf-coupon-card-amount">
                            @if($isFreeDelivery)
                                {{ translate('free_Delivery') }}
                            @elseif($coupon->discount_type == 'percentage')
                                <bdi dir="ltr">{{ $coupon->discount }}%</bdi>
                            @else
                                @include('web-views.seafood.partials._price', ['amount' => $coupon->discount, 'symbolColor' => 'currentColor', 'symbolSize' => '.6em'])
                            @endif
                        </span>
                        <span class="sf-coupon-card-scope">
                            {{ translate('on') }}
                            @if($couponShopUrl)
                                <a href="{{ $couponShopUrl }}">{{ $couponShopName }}</a>
                            @else
                                {{ $couponShopName }}
                            @endif
                        </span>
                    </div>

                    <div class="sf-coupon-card-body">
                        <button type="button" class="sf-coupon-code" data-sf-copy="{{ $coupon->code }}"
                                aria-label="{{ translate('copy') }} {{ $coupon->code }}">
                            <bdi dir="ltr">{{ $coupon->code }}</bdi>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2.5"></rect><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path></svg>
                        </button>
                        <span class="sf-row-meta">{{ translate('valid_till') }} <bdi dir="ltr">{{ $coupon->expire_date->format('d M, Y') }}</bdi></span>
                        <span class="sf-row-meta">
                            {{ translate('available_from_minimum_purchase') }}
                            @include('web-views.seafood.partials._price', ['amount' => $coupon->min_purchase])
                        </span>
                    </div>
                </article>
            @endforeach
        </div>

        {!! $coupons->links('web-views.seafood.partials._pagination') !!}
    @else
        @include('web-views.seafood.partials._empty-state', [
            'emptyStateIcon' => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"></path><circle cx="7.5" cy="7.5" r="1.5"></circle>',
            'emptyStateTitle' => translate('no_Coupon_Found').'!',
            'emptyStateActionUrl' => route('products'),
            'emptyStateActionLabel' => translate('explore_more'),
        ])
    @endif
@endsection
