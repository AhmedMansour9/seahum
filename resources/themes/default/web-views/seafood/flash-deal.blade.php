@extends('layouts.front-end.seafood')

@section('title', $deal['title'] ?: translate('flash_Deal_Products'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    @include('web-views.seafood.partials._listing-styles')

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ $deal['title'] }} — {{ $web_config['company_name'] }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ $deal['title'] }} — {{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    @php($dealBannerUrl = getStorageImages(path: $deal['banner_full_url'], type: 'banner', source: theme_asset(path: 'public/assets/front-end/img/flash-deals.png')))
    @php($dealEndsAt = \Carbon\Carbon::parse($deal['end_date'])->endOfDay())

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'offers'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => $deal['title'] ?: translate('flash_Deal_Products')]],
                ])

                <div class="sf-page-hero" style="background-image: url('{{ $dealBannerUrl }}'); margin-bottom: 24px;">
                    <div class="sf-page-hero-body sf-deal-hero">
                        <div style="min-width: 0;">
                            <span style="display: inline-block; background: #F2A43A; color: #3A2400; font-family: Cairo, sans-serif; font-weight: 800; font-size: 13px; padding: 6px 14px; border-radius: 999px; white-space: nowrap;">
                                {{ translate('flash_deal') }}
                            </span>
                            <h1 style="font-family: Cairo, sans-serif; font-size: clamp(24px, 4vw, 38px); font-weight: 900; line-height: 1.25; margin: 14px 0 0; color: #FFFFFF; overflow-wrap: anywhere; text-wrap: balance;">
                                {{ $deal['title'] }}
                            </h1>
                            <p style="color: #C6DDE6; font-size: 15.5px; line-height: 1.9; margin: 10px 0 0;">
                                {{ translate('offer_ends_on') }} <bdi dir="ltr">{{ $dealEndsAt->format('d M, Y') }}</bdi>
                            </p>
                        </div>

                        <div id="seafood-deal-countdown" data-end-time="{{ $dealEndsAt->getTimestamp() * 1000 }}"
                             role="timer" aria-label="{{ translate('time_left') }}"
                             style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; min-width: 0;">
                            @foreach(['days' => translate('day'), 'hours' => translate('hour'), 'minutes' => translate('minute'), 'seconds' => translate('second')] as $unit => $label)
                                <div style="background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18); border-radius: 14px; padding: 10px 6px; text-align: center; min-width: 0;">
                                    <div data-countdown-unit="{{ $unit }}" style="font-family: Cairo, sans-serif; font-size: clamp(20px, 3vw, 26px); font-weight: 800; color: #FFFFFF; direction: ltr;">00</div>
                                    <div style="font-size: 12px; color: #A9C7D3; margin-top: 2px;">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @include('web-views.seafood.partials._listing', [
                    'listingTitle' => translate('flash_Deal_Products'),
                    'listingHeadingTag' => 'h2',
                    'listingProducts' => $products,
                    'listingSearchPlaceholder' => translate('search_in_this_deal'),
                    'listingFilterCategories' => $productCategories,
                    'listingQuery' => [
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
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')

    @include('web-views.seafood.partials._listing-scripts')

    <script>
        "use strict";

        (function () {
            const countdownElement = document.getElementById('seafood-deal-countdown');
            if (!countdownElement) {
                return;
            }

            const endTime = parseInt(countdownElement.dataset.endTime, 10);
            const padValue = (value) => String(value).padStart(2, '0');
            const units = {
                days: countdownElement.querySelector('[data-countdown-unit="days"]'),
                hours: countdownElement.querySelector('[data-countdown-unit="hours"]'),
                minutes: countdownElement.querySelector('[data-countdown-unit="minutes"]'),
                seconds: countdownElement.querySelector('[data-countdown-unit="seconds"]'),
            };

            const renderCountdown = () => {
                const remaining = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
                units.days.textContent = padValue(Math.floor(remaining / 86400));
                units.hours.textContent = padValue(Math.floor(remaining / 3600) % 24);
                units.minutes.textContent = padValue(Math.floor(remaining / 60) % 60);
                units.seconds.textContent = padValue(remaining % 60);
            };

            renderCountdown();
            setInterval(renderCountdown, 1000);
        })();
    </script>
@endpush
