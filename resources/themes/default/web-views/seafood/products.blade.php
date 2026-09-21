@extends('layouts.front-end.seafood')

@section('title', $pageTitleContent)

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    @include('web-views.seafood.partials._listing-styles')

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ $pageTitleContent }} — {{ $web_config['company_name'] }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ $pageTitleContent }} — {{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    {{-- Only the general listing lets the shopper switch the source; the dedicated routes (featured, brand, …) fix it server-side. --}}
    @php($isGeneralListing = request()->routeIs('products'))

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'products'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => $pageTitleContent]],
                ])

                @include('web-views.seafood.partials._listing', [
                    'listingTitle' => $pageTitleContent,
                    'listingProducts' => $products,
                    'listingDataFromOptions' => $isGeneralListing ? [
                        '' => translate('Default'),
                        'best-selling' => translate('Best_Selling'),
                        'top-rated' => translate('Top_Rated'),
                        'most-favorite' => translate('Most_Favorite'),
                    ] : null,
                    'listingFilterCategories' => $categories,
                    'listingBrands' => $activeBrands,
                    'listingQuery' => [
                        'name' => $data['name'] ?? '',
                        'brand_id' => $data['brand_id'] ?? '',
                        'data_from' => $data['data_from'] ?? '',
                        'offer_type' => $data['offer_type'] ?? '',
                        'sort_by' => $data['sort_by'] ?? '',
                        'product_type' => $data['product_type'] ?? '',
                        'min_price' => $data['min_price'] ?? '',
                        'max_price' => $data['max_price'] ?? '',
                        'product_name' => $data['product_name'] ?? '',
                        'publishing_house_id' => $data['publishing_house_id'] ?? '',
                        'author_id' => $data['author_id'] ?? '',
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
@endpush
