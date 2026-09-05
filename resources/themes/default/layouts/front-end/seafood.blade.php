<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session()->get('direction') ?? 'rtl' }}">

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:site_name" content="{{ $web_config['company_name'] }}"/>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/back-end/css/toastr.css') }}"/>
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/font-awesome.min.css') }}"/>

    <style>
        html, body { margin: 0; padding: 0; }
        body {
            font-family: Tajawal, "Segoe UI", sans-serif;
            background: #FFFFFF;
            color: #0B2C3F;
            -webkit-font-smoothing: antialiased;
        }
        * { box-sizing: border-box; }
        a { color: #0E8E88; text-decoration: none; }
        a:hover { color: #0B2C3F; }
        img { max-width: 100%; }
        @keyframes marqueeTicker { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        @keyframes marqueeTickerRtl { from { transform: translateX(0); } to { transform: translateX(50%); } }
        .sf-ticker-track { animation: marqueeTicker 38s linear infinite; }
        [dir="rtl"] .sf-ticker-track { animation-name: marqueeTickerRtl; }
        @keyframes pulseRing {
            0% { box-shadow: 0 0 0 0 rgba(23, 176, 167, .45); }
            70% { box-shadow: 0 0 0 14px rgba(23, 176, 167, 0); }
            100% { box-shadow: 0 0 0 0 rgba(23, 176, 167, 0); }
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

@stack('script')
</body>
</html>
