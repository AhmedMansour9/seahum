@extends('layouts.front-end.seafood')

@section('title', $pageTitleContent)

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    @include('web-views.seafood.partials._listing-styles')

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ $pageTitleContent }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ $pageTitleContent }}"/>
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    @php($parentCategory = $category->parent_id ? $category->parent : null)
    @php($grandParentCategory = $parentCategory?->parent_id ? $parentCategory->parent : null)
    @php($scopedCategoryId = $grandParentCategory['id'] ?? $parentCategory['id'] ?? $category['id'])
    @php($scopedSubCategoryId = $grandParentCategory ? $parentCategory['id'] : ($parentCategory ? $category['id'] : null))
    @php($scopedSubSubCategoryId = $grandParentCategory ? $category['id'] : null)

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'categories'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">
                @include('web-views.seafood.partials._listing', [
                    'listingTitle' => $category['name'],
                    'listingProducts' => $products,
                    'listingSearchPlaceholder' => translate('search_in_this_category'),
                    'listingDataFromOptions' => [
                        'category' => translate('Default'),
                        'best-selling' => translate('Best_Selling'),
                        'top-rated' => translate('Top_Rated'),
                        'most-favorite' => translate('Most_Favorite'),
                    ],
                    'listingDefaultDataFrom' => 'category',
                    'listingTreeCategories' => $categories,
                    'listingBrands' => $activeBrands,
                    'listingQuery' => [
                        'category_id' => $scopedCategoryId,
                        'sub_category_id' => $scopedSubCategoryId,
                        'sub_sub_category_id' => $scopedSubSubCategoryId,
                        'brand_id' => $data['brand_id'] ?? '',
                        'data_from' => $data['data_from'] ?? '',
                        'offer_type' => $data['offer_type'] ?? '',
                        'sort_by' => $data['sort_by'] ?? '',
                        'product_type' => $data['product_type'] ?? '',
                        'min_price' => $data['min_price'] ?? '',
                        'max_price' => $data['max_price'] ?? '',
                        'product_name' => $data['product_name'] ?? '',
                    ],
                    'listingScope' => array_filter([
                        'category_ids[]' => $scopedCategoryId,
                        'sub_category_ids[]' => $scopedSubCategoryId,
                        'sub_sub_category_ids[]' => $scopedSubSubCategoryId,
                    ]),
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
