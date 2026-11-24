<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session()->get('direction') ?? 'rtl' }}">

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:site_name" content="{{ $web_config['company_name'] }}"/>

    <meta name="google-site-verification" content="{{ getWebConfig('google_search_console_code') }}">
    <meta name="msvalidate.01" content="{{ getWebConfig('bing_webmaster_code') }}">
    <meta name="baidu-site-verification" content="{{ getWebConfig('baidu_webmaster_code') }}">
    <meta name="yandex-verification" content="{{ getWebConfig('yandex_webmaster_code') }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/back-end/css/toastr.css') }}"/>
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/font-awesome.min.css') }}"/>

    <style>
        :root {
            --sh-navy: #082B49;
            --sh-navy-rgb: 8, 43, 73;
            --sh-navy-800: #0F3352;
            --sh-navy-800-rgb: 15, 51, 82;
            --sh-abyss: #051D33;
            --sh-abyss-rgb: 5, 29, 51;
            --sh-navy-50: #E7EDF3;

            --sh-sea: #087EAD;
            --sh-sea-rgb: 8, 126, 173;
            --sh-sea-700: #066A93;
            --sh-sea-700-rgb: 6, 106, 147;
            --sh-sea-400: #2A9BCB;
            --sh-sea-300: #7CC4E4;
            --sh-sea-300-rgb: 124, 196, 228;
            --sh-sea-200: #B5DDF0;
            --sh-sea-100: #CFE6F2;
            --sh-sea-50: #EAF4FA;

            --sh-salmon: #F47A45;
            --sh-salmon-rgb: 244, 122, 69;
            --sh-salmon-700: #C9441D;
            --sh-salmon-800: #A8381A;
            --sh-salmon-50: #FCE7DE;
            --sh-tuna: #D92F45;
            --sh-tuna-rgb: 217, 47, 69;
            --sh-tuna-700: #B3243A;
            --sh-tuna-100: #F6CDD2;
            --sh-tuna-50: #FCECEE;
            --sh-gold: #F2A43A;
            --sh-success: #1E8A5B;

            --sh-paper: #FFFDF9;
            --sh-surface: #FFFFFF;
            --sh-mist: #F4F7FA;
            --sh-line: #E2E8F0;
            --sh-ink-2: #3A5066;
            --sh-muted: #64748B;
            --sh-muted-2: #94A3B8;
            --sh-on-dark: #C9D6E2;
            --sh-on-dark-2: #B6C2CF;
            --sh-on-dark-3: #94A6B8;

            --sh-action: var(--sh-salmon-700);
            --sh-action-hover: var(--sh-salmon);
            --sh-action-pressed: var(--sh-salmon-800);
            --sh-on-action-hover: var(--sh-abyss);

            --sh-grad-deep: linear-gradient(200deg, var(--sh-navy-800) 0%, var(--sh-abyss) 58%, #063A5C 100%);
            --sh-grad-catch: linear-gradient(135deg, var(--sh-salmon) 0%, var(--sh-tuna) 100%);
            --sh-grad-shallows: linear-gradient(120deg, var(--sh-sea-50) 0%, #FFF4EE 100%);

            --sh-shadow-card: 0 10px 26px -24px rgba(var(--sh-navy-rgb), .9);
            --sh-shadow-cta: 0 16px 32px -14px rgba(var(--sh-salmon-rgb), .95);

            --sh-font-body: Tajawal, "Segoe UI", sans-serif;
            --sh-font-display: Cairo, sans-serif;
        }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: var(--sh-font-body);
            background: var(--sh-paper);
            color: var(--sh-navy);
            -webkit-font-smoothing: antialiased;
        }
        * { box-sizing: border-box; }
        a { color: var(--sh-sea-700); text-decoration: none; }
        a:hover { color: var(--sh-navy); }
        img { max-width: 100%; }
        ::selection { background: rgba(var(--sh-salmon-rgb), .28); color: var(--sh-navy); }
        @keyframes marqueeTicker { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        @keyframes marqueeTickerRtl { from { transform: translateX(0); } to { transform: translateX(50%); } }
        .sf-ticker-track { animation: marqueeTicker 38s linear infinite; }
        [dir="rtl"] .sf-ticker-track { animation-name: marqueeTickerRtl; }
        @keyframes pulseRing {
            0% { box-shadow: 0 0 0 0 rgba(var(--sh-salmon-rgb), .45); }
            70% { box-shadow: 0 0 0 14px rgba(var(--sh-salmon-rgb), 0); }
            100% { box-shadow: 0 0 0 0 rgba(var(--sh-salmon-rgb), 0); }
        }
    </style>

    @stack('css_or_js')

    @include(VIEW_FILE_NAMES['robots_meta_content_partials'])

    {!! getSystemDynamicPartials(type: 'analytics_script') !!}
</head>

<body>

@yield('content')

<span id="route-cart-add" data-url="{{ route('cart.add') }}"></span>
<span id="route-cart-remove" data-url="{{ route('cart.remove') }}"></span>
<span id="route-store-wishlist" data-url="{{ route('store-wishlist') }}"></span>
<span id="route-change-language" data-url="{{ route('change-language') }}"></span>
<span id="is-customer-auth-active" data-value="{{ auth('customer')->check() ? 1 : 0 }}"></span>
<span id="route-customer-login" data-url="{{ route('customer.auth.login') }}"></span>

<script src="{{ theme_asset(path: 'public/assets/front-end/vendor/jquery/dist/jquery-2.2.4.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/back-end/js/toastr.js') }}"></script>

{!! Toastr::message() !!}

@include('web-views.seafood.partials._lucky-wheel-popup')

@stack('script')
</body>
</html>
