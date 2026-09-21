@extends('layouts.front-end.seafood')

@section('title', translate('all_Brands'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    <style>
        @media (max-width: 759px) {
            .sf-page-head { flex-direction: column !important; align-items: stretch !important; }
            .sf-page-head-search { width: 100% !important; }
        }
    </style>

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ translate('brands') }} — {{ $web_config['company_name'] }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ translate('brands') }} — {{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    @php($searchedBrandName = request('search'))

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'products'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => translate('brands')]],
                ])

                <form method="GET" action="{{ route('brands') }}" class="sf-page-head"
                      style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding: 16px 18px; background: #F4FAFB; border: 1px solid #E4EFF1; border-radius: 16px; margin-bottom: 24px;">
                    <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                        <h1 style="font-family: Cairo, sans-serif; font-size: 20px; font-weight: 800; line-height: 1.4; margin: 0; color: #0B2C3F; text-wrap: pretty;">{{ translate('brands') }}</h1>
                        <span style="font-size: 14px; color: #45677A;">{{ translate('Find_your_favourite_brands_and_products') }}</span>
                    </div>

                    <span class="sf-search-wrap sf-page-head-search" style="width: 280px;">
                        <input type="search" class="sf-filter-field sf-search-field" name="search" autocomplete="off"
                               value="{{ $searchedBrandName }}"
                               placeholder="{{ translate('Search_Brands') }}"
                               aria-label="{{ translate('Search_Brands') }}"
                               style="padding-inline-end: 46px;">
                        <button type="submit" class="sf-search-submit" aria-label="{{ translate('search') }}">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.2-3.2"></path></svg>
                        </button>
                    </span>
                </form>

                @if(count($brands) > 0)
                    <div class="sf-brand-grid">
                        @foreach($brands as $brand)
                            <a href="{{ route('brand-products', ['slug' => $brand['slug']]) }}" class="sf-brand-card sf-lift">
                                <span class="sf-brand-card-media">
                                    <img src="{{ getStorageImages(path: $brand->image_full_url, type: 'brand') }}"
                                         alt="{{ $brand->image_alt_text ?? $brand->name }}" loading="lazy">
                                </span>
                                <span class="sf-brand-card-body">
                                    <span style="display: flex; flex-direction: column; gap: 2px; min-width: 0;">
                                        <strong class="sf-brand-card-name">{{ $brand->name }}</strong>
                                        <span class="sf-brand-card-count"><bdi dir="ltr">{{ $brand->brand_products_count }}</bdi> {{ translate('products') }}</span>
                                    </span>
                                    <span aria-hidden="true" style="width: 30px; height: 30px; border-radius: 10px; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; flex-shrink: 0;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="{{ (session()->get('direction') ?? 'rtl') === 'rtl' ? 'M14 6 8 12l6 6' : 'M10 6l6 6-6 6' }}"></path>
                                        </svg>
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>

                    {!! $brands->links('web-views.seafood.partials._pagination') !!}
                @else
                    @include('web-views.seafood.partials._empty-state', [
                        'emptyStateIcon' => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"></path><circle cx="7.5" cy="7.5" r="1.5"></circle>',
                        'emptyStateTitle' => filled($searchedBrandName) ? translate('no_results_found') : translate('There_is_no_brands'),
                        'emptyStateText' => filled($searchedBrandName) ? translate('no_brand_matches_this_name_try_a_shorter_word') : null,
                        'emptyStateActionUrl' => filled($searchedBrandName) ? route('brands') : route('home'),
                        'emptyStateActionLabel' => filled($searchedBrandName) ? translate('show_all_brands') : translate('back_to_home'),
                    ])
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
