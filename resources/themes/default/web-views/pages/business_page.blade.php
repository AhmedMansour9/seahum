@extends('layouts.front-end.seafood')

@section('title', $businessPage?->title)

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ $businessPage?->title }} — {{ $web_config['company_name'] }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ $businessPage?->title }} — {{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    @php($bannerUrl = getStorageImages(path: $businessPage?->banner_full_url, type: 'business-page'))

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'page'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => $businessPage?->title]],
                ])

                <div class="sf-page-hero" style="background-image: url('{{ $bannerUrl }}');">
                    <div class="sf-page-hero-body" style="text-align: center;">
                        <span style="display: inline-flex; align-items: center; gap: 9px; padding: 7px 14px; border-radius: 999px; background: rgba(23,176,167,.22); border: 1px solid rgba(127,227,220,.4); color: #7FE3DC; font-family: Cairo, sans-serif; font-size: 12.5px; font-weight: 700;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h8.5L19 8v12.5H6z"></path><path d="M9 12h7M9 16h5"></path></svg>
                            {{ $web_config['company_name'] }}
                        </span>
                        <h1 style="font-family: Cairo, sans-serif; font-size: clamp(24px, 4vw, 38px); font-weight: 900; line-height: 1.25; margin: 14px 0 0; color: #FFFFFF; overflow-wrap: anywhere; text-wrap: pretty;">
                            {{ $businessPage?->title }}
                        </h1>
                    </div>
                </div>

                @if(!empty($businessPage?->description))
                    <article class="sf-card sf-prose" style="margin-top: 24px; padding: clamp(20px, 3.5vw, 34px);">
                        {!! $businessPage?->description !!}
                    </article>
                @else
                    <div style="background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; padding: 44px 22px; text-align: center; margin-top: 24px;">
                        <span style="width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: inline-grid; place-items: center;">
                            <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h8.5L19 8v12.5H6z"></path><path d="M9 12h7M9 16h5"></path></svg>
                        </span>
                        <h4 style="font-family: Cairo, sans-serif; font-size: 17px; font-weight: 700; margin: 16px 0 8px; color: #0B2C3F;">{{ translate('no_content_yet') }}</h4>
                        <p style="font-size: 14px; color: #6E8C99; line-height: 1.8; margin: 0 auto 18px; max-width: 420px;">{{ translate('this_page_has_no_content_yet_please_check_back_later') }}</p>
                        <a href="{{ route('home') }}" class="sf-see-more" style="min-height: 44px; padding: 0 24px; font-size: 14.5px;">
                            {{ translate('back_to_home') }}
                        </a>
                    </div>
                @endif
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
