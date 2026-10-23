@extends('layouts.front-end.seafood')

@section('title', translate('track_Order'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')
    @include('web-views.seafood.partials._track-order-styles')

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ $web_config['company_name'] }} "/>
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ config('app.url') }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'track_order'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => translate('track_order')]],
                ])

                <div class="sf-card" style="max-width: 880px; margin: 0 auto; padding: clamp(20px, 4vw, 34px);">
                    <header style="text-align: center; margin-bottom: 24px;">
                        <span class="sf-empty-icon" style="display: inline-grid; margin: 0;" aria-hidden="true">
                            <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v9H3z"></path><path d="M14 10h4l3 3v3h-7z"></path><circle cx="7" cy="18" r="1.8"></circle><circle cx="17" cy="18" r="1.8"></circle></svg>
                        </span>
                        <h1 style="font-family: Cairo, sans-serif; font-size: clamp(21px, 3vw, 26px); font-weight: 800; line-height: 1.35; margin: 14px 0 6px; color: #0B2C3F;">{{ translate('track_order') }}</h1>
                        <p style="font-size: 14.5px; line-height: 1.8; color: #6E8C99; margin: 0 auto; max-width: 440px;">{{ translate('enter_your_order_ID_&_phone_number_to_get_delivery_updates') }}</p>
                    </header>

                    @if(session()->has('Error'))
                        <p class="sf-badge sf-badge-danger" role="alert" style="display: flex; white-space: normal; margin: 0 0 18px; padding: 12px 14px; border-radius: 12px;">{{ session()->get('Error') }}</p>
                    @endif

                    @include('web-views.seafood.partials._track-order-form')
                </div>
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
@endpush
