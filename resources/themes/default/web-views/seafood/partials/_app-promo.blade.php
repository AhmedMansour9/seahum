@php($promoPercentLabel = $appPromoDiscountPercent !== null ? rtrim(rtrim(number_format($appPromoDiscountPercent, 2, '.', ''), '0'), '.').'%' : null)
@php($phonePreviewProducts = $phonePreviewProducts->take(2))

<section id="app" class="sf-gutter sf-app-promo-section" aria-labelledby="sf-app-promo-title">
    <div class="sf-app-promo">
        <span class="sf-app-promo-glow" aria-hidden="true"></span>
        <svg class="sf-app-promo-waves" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 70c120-40 240-40 360 0s240 40 360 0 240-40 360 0 120 20 120 20v50H0Z" fill="rgba(255,255,255,.04)"></path>
            <path d="M0 90c150-30 300-30 450 0s300 30 450 0 300-30 300-30v60H0Z" fill="rgba(255,255,255,.05)"></path>
        </svg>

        <div class="sf-app-promo-copy">
            <span class="sf-app-promo-eyebrow">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="2" width="12" height="20" rx="3"></rect><path d="M11 18h2"></path></svg>
                {{ translate('the_sea_hum_app') }}
            </span>
            <h2 class="sf-app-promo-title" id="sf-app-promo-title">{{ translate('the_whole_sea_in_your_pocket') }}</h2>
            <p class="sf-app-promo-text">{{ translate('order_your_fresh_catch_in_seconds_follow_your_order_step_by_step_and_be_the_first_to_get_app_offers') }}</p>

        <div class="sf-promo-ticket">
                <div class="sf-promo-ticket-value">
                    <strong>
                        @if($promoPercentLabel)
                            <bdi dir="ltr">{{ $promoPercentLabel }}</bdi>
                        @else
                            @include('web-views.seafood.partials._price', ['amount' => $appPromoCoupon->discount, 'symbolSize' => '.5em', 'symbolColor' => 'currentColor'])
                        @endif
                    </strong>
                    <span>{{ translate('off') }}</span>
                </div>
                <div class="sf-promo-ticket-body">
                    <span class="sf-promo-ticket-label">{{ translate('your_welcome_code_from_the_sea') }}</span>
                    <button type="button" class="sf-promo-code" data-promo-code="{{ $appPromoCode }}"
                            data-copied-message="{{ translate('code_copied_use_it_at_checkout') }}"
                            aria-label="{{ translate('copy_the_code') }} {{ $appPromoCode }}">
                        <bdi dir="ltr" class="sf-promo-code-value">{{ $appPromoCode }}</bdi>
                        <span class="sf-promo-code-action">
                            <svg class="sf-promo-code-copy" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"></rect><path d="M5 15V5a2 2 0 0 1 2-2h10"></path></svg>
                            <svg class="sf-promo-code-done" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg>
                            <span class="sf-promo-code-hint" data-copied-label="{{ translate('copied') }}">{{ translate('copy') }}</span>
                        </span>
                    </button>
                    @if($appPromoCoupon)
                    <span class="sf-promo-ticket-terms">
                        @if($appPromoCoupon->coupon_type === 'first_order')
                            {{ translate('on_your_first_order') }}
                            ·
                        @endif
                        @if($appPromoCoupon->min_purchase > 0)
                            {{ translate('on_orders_from') }} @include('web-views.seafood.partials._price', ['amount' => $appPromoCoupon->min_purchase, 'symbolSize' => '11px', 'symbolColor' => 'currentColor'])
                            ·
                        @endif
                        {{ translate('valid_until') }} <bdi dir="ltr">{{ $appPromoCoupon->expire_date->format('d/m/Y') }}</bdi>
                    </span>
                    @endif
                </div>
            </div>

            @include('web-views.seafood.partials._store-badges', ['googlePlayUrl' => $googlePlayUrl, 'appStoreUrl' => $appStoreUrl, 'showComingSoon' => true])
        </div>

        <div class="sf-app-promo-visual" aria-hidden="true">
            <div class="sf-app-phone">
                <span class="sf-app-phone-notch"></span>
                <div class="sf-app-phone-screen">
                    <div class="sf-app-phone-bar">
                        <strong>{{ $web_config['company_name'] }}</strong>
                        <span class="sf-app-phone-bag"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5h2l2 10h10.5l2-7H9"></path><circle cx="9" cy="20" r="1.3"></circle><circle cx="17" cy="20" r="1.3"></circle></svg></span>
                    </div>
                    <span class="sf-app-phone-search"></span>
                    <div class="sf-app-phone-banner">
                        <span>{{ translate('fresh_today') }}</span>
                        <bdi dir="ltr">{{ $appPromoCode }}</bdi>
                    </div>
                    <div class="sf-app-phone-grid">
                        @foreach($phonePreviewProducts as $previewProduct)
                            <span class="sf-app-phone-tile">
                                <img src="{{ getStorageImages(path: $previewProduct->thumbnail_full_url, type: 'product') }}" alt="" loading="lazy">
                                <span class="sf-app-phone-line"></span>
                                <span class="sf-app-phone-line is-short"></span>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @if($promoPercentLabel)
                <span class="sf-app-sticker">
                    <bdi dir="ltr">−{{ $promoPercentLabel }}</bdi>
                </span>
            @endif
            <span class="sf-app-chip">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h11v9H2z"></path><path d="M13 10h4l3 3v3h-7z"></path><circle cx="6" cy="18" r="1.6"></circle><circle cx="17" cy="18" r="1.6"></circle></svg>
                {{ translate('your_order_is_on_the_way') }}
            </span>
        </div>
    </div>
</section>
