@extends('layouts.front-end.seafood')

@section('title', $pageTitleContent)

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    <style>
        .sf-filter-field { width: 100%; height: 48px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; padding: 0 14px; font-family: Tajawal, sans-serif; font-size: 15px; outline: none; transition: border-color .2s, box-shadow .2s; }
        .sf-filter-field:focus { border: 1.5px solid #17B0A7; box-shadow: 0 0 0 3px rgba(23,176,167,.22); }
        .sf-filter-field::placeholder { color: #7B98A4; }
        .sf-filter-group-title { display: block; font-family: Cairo, sans-serif; font-size: 13.5px; font-weight: 700; color: #0B2C3F; margin-bottom: 12px; }
        .sf-filter-card { background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; padding: 22px; }
        .sf-filter-nav-link { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 44px; padding: 0 12px; border-radius: 10px; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 600; color: #34586B; transition: background .2s, color .2s; }
        .sf-filter-nav-link:hover { background: #F1F7F8; color: #0B2C3F; }
        .sf-filter-nav-link.active { background: #ECF8F8; color: #0E8E88; }
        .sf-category-tree { display: flex; flex-direction: column; }
        .sf-category-row { display: flex; align-items: center; gap: 4px; }
        .sf-category-link { flex: 1; min-width: 0; display: flex; align-items: center; gap: 10px; min-height: 44px; padding: 0 10px; border-radius: 10px; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 600; color: #34586B; transition: background .2s, color .2s; }
        .sf-category-link:hover { background: #F1F7F8; color: #0B2C3F; }
        .sf-category-link.active { background: #ECF8F8; color: #0E8E88; }
        .sf-category-link--sub { font-size: 14px; font-weight: 500; min-height: 40px; }
        .sf-category-icon { width: 24px; height: 24px; border-radius: 50%; object-fit: cover; flex-shrink: 0; background: #EAF4F5; }
        .sf-category-label { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sf-category-caret { flex-shrink: 0; width: 32px; height: 32px; border-radius: 9px; border: none; background: transparent; color: #A8B9C1; display: grid; place-items: center; cursor: pointer; transition: background .2s, color .2s, transform .22s; }
        .sf-category-caret:hover { background: #ECF8F8; color: #0E8E88; }
        .sf-category-children { display: none; padding-inline-start: 14px; margin-inline-start: 10px; border-inline-start: 1px solid #EDF5F6; }
        .sf-category-branch[data-open="true"] > .sf-category-children { display: block; }
        .sf-category-branch[data-open="true"] > .sf-category-row > .sf-category-caret { transform: rotate(-90deg); color: #0E8E88; }
        [dir="ltr"] .sf-category-branch[data-open="true"] > .sf-category-row > .sf-category-caret { transform: rotate(90deg); }
        .sf-range-wrap { position: relative; height: 22px; margin: 22px 0 4px; }
        .sf-range-track { position: absolute; inset-inline: 0; top: 8px; height: 6px; border-radius: 999px; background: #EDF5F6; }
        .sf-range-fill { position: absolute; top: 8px; height: 6px; border-radius: 999px; background: #17B0A7; }
        .sf-range { position: absolute; inset-inline: 0; top: 0; width: 100%; height: 22px; margin: 0; padding: 0; background: transparent; outline: none; pointer-events: none; -webkit-appearance: none; appearance: none; }
        .sf-range::-webkit-slider-runnable-track { height: 22px; background: transparent; border: none; }
        .sf-range::-moz-range-track { height: 22px; background: transparent; border: none; }
        .sf-range::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; pointer-events: auto; width: 22px; height: 22px; border-radius: 50%; background: #FFFFFF; border: 2px solid #17B0A7; cursor: pointer; }
        .sf-range::-moz-range-thumb { pointer-events: auto; width: 22px; height: 22px; border-radius: 50%; background: #FFFFFF; border: 2px solid #17B0A7; cursor: pointer; }
        .sf-range:focus-visible::-webkit-slider-thumb { box-shadow: 0 0 0 3px rgba(23,176,167,.25); }
        .sf-page-link:hover { border-color: #17B0A7 !important; background: #ECF8F8 !important; color: #0B2C3F !important; }

        .sf-help-card { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; padding: 28px 20px; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; box-shadow: 0 10px 26px -24px rgba(11,44,63,.9); }
        .sf-help-icon { width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; }
        .sf-help-title { font-family: Cairo, sans-serif; font-size: 17px; font-weight: 700; color: #0B2C3F; margin-top: 6px; }
        .sf-help-text { font-size: 14px; line-height: 1.8; color: #6E8C99; }
        .sf-icon-button { width: 44px; height: 44px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; display: grid; place-items: center; cursor: pointer; transition: background .2s, border-color .2s; }
        .sf-icon-button:hover { background: #ECF8F8; border-color: #BEE6E4; }
        .sf-filter-toggle { display: none; }
        .sf-filter-sheet-head { display: none; }
        .sf-filter-backdrop { display: none; position: fixed; inset: 0; z-index: 90; background: rgba(6,42,62,.55); }
        .sf-filter-backdrop[data-open="true"] { display: block; }
        .sf-skeleton-line { border-radius: 6px; background: linear-gradient(90deg, #EDF5F6 25%, #F8FCFC 50%, #EDF5F6 75%); background-size: 200% 100%; animation: shimmer 1.4s linear infinite; display: block; }
        .sf-skeleton-card { display: flex; flex-direction: column; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; overflow: hidden; }
        .sf-skeleton-media { aspect-ratio: 1 / 1; background: #EAF4F5; display: block; }
        .sf-skeleton-body { padding: 16px 18px 18px; display: flex; flex-direction: column; gap: 11px; }
        .sf-skeleton-action { height: 44px; border-radius: 13px; background: #F4FAFB; display: block; }
        @keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

        /* The old price widens the price group; above the mobile breakpoint the
           card is wide enough to keep it on one row with the CTA, as on the home page. */
        @media (min-width: 760px) {
            .sf-product-actions { flex-wrap: nowrap !important; gap: 8px !important; }
            .sf-product-actions > span:first-child { flex: 1 1 auto; min-width: 0; }
            .sf-product-price, .sf-product-old-price { white-space: nowrap; }
            .sf-product-actions .seafood-cart-toggle, .sf-product-actions .sf-product-action { flex-shrink: 0; padding: 0 13px; gap: 6px; }
        }

        @media (min-width: 980px) {
            .sf-filter-panel { position: sticky; top: 92px; max-height: calc(100vh - 112px); overflow-y: auto; }
        }

        @media (max-width: 979px) {
            .sf-listing-layout { grid-template-columns: 1fr !important; }
            .sf-filter-toggle { display: inline-flex; }
            .sf-filter-panel {
                position: fixed; inset-inline: 0; bottom: 0; top: auto; z-index: 100;
                max-height: 86vh; overflow-y: auto;
                border-radius: 24px 24px 0 0; background: #FFFFFF;
                box-shadow: 0 -22px 40px -18px rgba(0,0,0,.55);
                padding: 0 20px calc(20px + env(safe-area-inset-bottom));
                transform: translateY(100%); transition: transform .3s ease;
                overscroll-behavior: contain;
            }
            /* The sheet covers the listing; the page behind it must not scroll away. */
            body.sf-filter-sheet-open { position: fixed; inset-inline: 0; width: 100%; overflow: hidden; }
            .sf-category-caret { width: 44px; height: 44px; }
            .sf-category-link--sub { min-height: 44px; }
            .sf-search-submit { width: 44px !important; height: 44px !important; inset-inline-end: 2px !important; }
            .sf-filter-panel[data-open="true"] { transform: translateY(0); }
            .sf-filter-sheet-head { display: block; position: sticky; top: 0; background: #FFFFFF; padding: 14px 0 12px; z-index: 2; }
            .sf-filter-card { border: none; border-radius: 0; padding: 18px 0; border-bottom: 1px solid #EDF5F6; }
            .sf-filter-stack { gap: 0 !important; }
        }

        @media (max-width: 759px) {
            .sf-help-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 12px !important; }
            .sf-help-card { padding: 20px 14px; }
            .sf-help-text { display: none; }
            .sf-listing-toolbar { flex-direction: column !important; align-items: stretch !important; }
            .sf-listing-toolbar-actions { width: 100%; }
            .sf-search-wrap { flex: 1 1 100%; }
            .sf-search-field { width: 100% !important; }
            .sf-sort-field, .sf-data-from-field { flex: 1 1 auto; min-width: 0; }
            .sf-sort-field select, .sf-data-from-field select { min-width: 0 !important; flex: 1; }
            .sf-filter-toggle { flex: 0 0 auto; }
        }
    </style>

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
    @php($maxUnitPrice = getProductMaxUnitPriceRange(type: 'web'))
    @php($isBrandFilterActive = $web_config['brand_setting'] ?? false)
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
                <form class="product-list-filter" method="GET" action="{{ url()->current() }}" onsubmit="return false;">
                    <div class="sf-listing-toolbar"
                         style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding: 16px 18px; background: #F4FAFB; border: 1px solid #E4EFF1; border-radius: 16px;">
                        <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                            <h1 style="font-family: Cairo, sans-serif; font-size: 20px; font-weight: 800; line-height: 1.4; margin: 0; color: #0B2C3F; text-wrap: pretty;">{{ $category['name'] }}</h1>
                            <span style="font-size: 14px; color: #45677A;">
                                <strong class="view-page-item-count" style="font-family: Cairo, sans-serif; font-weight: 700; color: #0B2C3F;">{{ $products->total() }}</strong>
                                {{ translate('items_found') }}
                            </span>
                        </div>

                        <div class="sf-listing-toolbar-actions" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span class="sf-search-wrap" style="position: relative; display: flex; align-items: center;">
                                <input type="search" class="sf-filter-field sf-search-field" name="product_name" autocomplete="off"
                                       value="{{ request('product_name') }}"
                                       placeholder="{{ translate('search_in_this_category') }}"
                                       style="width: 240px; padding-inline-end: 46px;">
                                <button type="button" class="sf-search-submit" aria-label="{{ translate('search') }}"
                                        style="position: absolute; inset-inline-end: 6px; width: 36px; height: 36px; border-radius: 10px; border: none; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; cursor: pointer;">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.2-3.2"></path></svg>
                                </button>
                            </span>

                            <label class="sf-sort-field" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                                <span style="font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; color: #45677A; white-space: nowrap;">{{ translate('sort_by') }}</span>
                                <select class="sf-filter-field sf-filter-input" name="sort_by" style="width: auto; min-width: 170px;">
                                    <option value="latest" {{ request('sort_by') == 'latest' ? 'selected' : '' }}>{{ translate('Default') }}</option>
                                    <option value="low-high" {{ request('sort_by') == 'low-high' ? 'selected' : '' }}>{{ translate('Price') }} ({{ translate('Low_to_High') }})</option>
                                    <option value="high-low" {{ request('sort_by') == 'high-low' ? 'selected' : '' }}>{{ translate('Price') }} ({{ translate('High_to_Low') }})</option>
                                    <option value="rating-low-high" {{ request('sort_by') == 'rating-low-high' ? 'selected' : '' }}>{{ translate('Rating') }} ({{ translate('Low_to_High') }})</option>
                                    <option value="rating-high-low" {{ request('sort_by') == 'rating-high-low' ? 'selected' : '' }}>{{ translate('Rating') }} ({{ translate('High_to_Low') }})</option>
                                    <option value="a-z" {{ request('sort_by') == 'a-z' ? 'selected' : '' }}>{{ translate('Alphabetical') }} ({{ 'A ' . translate('to') . ' Z' }})</option>
                                    <option value="z-a" {{ request('sort_by') == 'z-a' ? 'selected' : '' }}>{{ translate('Alphabetical') }} ({{ 'Z ' . translate('to') . ' A' }})</option>
                                </select>
                            </label>

                            <label class="sf-data-from-field" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                                <span style="font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; color: #45677A; white-space: nowrap;">{{ translate('Filter_By') }}</span>
                                <select class="sf-filter-field sf-filter-input" name="data_from" style="width: auto; min-width: 160px;">
                                    <option value="category" {{ !in_array(request('data_from'), ['best-selling', 'top-rated', 'most-favorite']) ? 'selected' : '' }}>{{ translate('Default') }}</option>
                                    <option value="best-selling" {{ request('data_from') == 'best-selling' ? 'selected' : '' }}>{{ translate('Best_Selling') }}</option>
                                    <option value="top-rated" {{ request('data_from') == 'top-rated' ? 'selected' : '' }}>{{ translate('Top_Rated') }}</option>
                                    <option value="most-favorite" {{ request('data_from') == 'most-favorite' ? 'selected' : '' }}>{{ translate('Most_Favorite') }}</option>
                                </select>
                            </label>

                            <button type="button" class="sf-filter-toggle sf-see-more" id="seafood-filter-toggle"
                                    aria-expanded="false" aria-controls="seafood-filter-panel"
                                    style="min-height: 48px; padding: 0 18px; font-size: 14.5px; gap: 8px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M7 12h10M10 18h4"></path></svg>
                                {{ translate('filters') }}
                            </button>
                        </div>
                    </div>

                    <div class="sf-listing-layout" style="display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 24px; margin-top: 24px; align-items: start;">

                        <aside class="sf-filter-panel" id="seafood-filter-panel" data-open="false">
                            <div class="sf-filter-sheet-head">
                                <div style="display: grid; place-items: center; padding-bottom: 12px;">
                                    <span style="width: 44px; height: 4px; border-radius: 999px; background: #E4EFF1; display: block;"></span>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                    <strong style="font-family: Cairo, sans-serif; font-size: 17px; font-weight: 800; color: #0B2C3F;">{{ translate('filters') }}</strong>
                                    <button type="button" class="sf-icon-button" id="seafood-filter-close" aria-label="{{ translate('close') }}">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="sf-filter-stack" style="display: flex; flex-direction: column; gap: 16px;">
                                <div class="sf-filter-card">
                                    <span class="sf-filter-group-title">{{ translate('price') }}</span>
                                    <div style="display: flex; align-items: flex-end; gap: 10px;">
                                        <label style="flex: 1; margin: 0;">
                                            <span style="display: block; font-size: 12.5px; font-weight: 600; color: #6E8C99; margin-bottom: 6px;">{{ translate('Min') }}</span>
                                            <input type="number" min="0" id="min_price" name="min_price" class="sf-filter-field"
                                                   value="{{ request('min_price') }}" placeholder="0">
                                        </label>
                                        <label style="flex: 1; margin: 0;">
                                            <span style="display: block; font-size: 12.5px; font-weight: 600; color: #6E8C99; margin-bottom: 6px;">{{ translate('Max') }}</span>
                                            <input type="number" min="0" id="max_price" name="max_price" class="sf-filter-field"
                                                   value="{{ request('max_price') }}" placeholder="{{ $maxUnitPrice }}">
                                        </label>
                                    </div>
                                    <div class="sf-range-wrap">
                                        <span class="sf-range-track"></span>
                                        <span class="sf-range-fill" id="price_range_fill"></span>
                                        <input type="range" class="sf-range" id="min_price_range" min="0" max="{{ $maxUnitPrice }}"
                                               value="{{ request('min_price', 0) }}" aria-label="{{ translate('Min') }}">
                                        <input type="range" class="sf-range" id="max_price_range" min="0" max="{{ $maxUnitPrice }}"
                                               value="{{ request('max_price', $maxUnitPrice) }}" aria-label="{{ translate('Max') }}">
                                    </div>
                                    <button type="button" class="sf-apply-price"
                                            style="width: 100%; margin-top: 16px; min-height: 44px; border-radius: 14px; border: none; background: #17B0A7; color: #FFFFFF; font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 800; cursor: pointer;">
                                        {{ translate('apply') }}
                                    </button>
                                </div>

                                <div class="sf-filter-card">
                                    <span class="sf-filter-group-title">{{ translate('categories') }}</span>
                                    <div style="max-height: 380px; overflow-y: auto;">
                                        @include('web-views.seafood.partials._category-tree', ['treeCategories' => $categories])
                                    </div>
                                </div>

                                @if($isBrandFilterActive && $activeBrands->count() > 0)
                                    <div class="sf-filter-card">
                                        <span class="sf-filter-group-title">{{ translate('brands') }}</span>
                                        <span style="position: relative; display: flex; align-items: center; margin-bottom: 12px;">
                                            <input type="search" class="sf-filter-field sf-brand-search" autocomplete="off"
                                                   placeholder="{{ translate('search_by_brands') }}"
                                                   aria-label="{{ translate('search_by_brands') }}"
                                                   style="height: 44px; padding-inline-end: 42px;">
                                            <span aria-hidden="true"
                                                  style="position: absolute; inset-inline-end: 14px; color: #7B98A4; display: grid; place-items: center;">
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.2-3.2"></path></svg>
                                            </span>
                                        </span>
                                        <div style="display: flex; flex-direction: column; gap: 2px; max-height: 280px; overflow-y: auto;">
                                            @foreach($activeBrands as $brand)
                                                <a href="{{ route('brand-products', ['slug' => $brand['slug']]) }}"
                                                   class="sf-filter-nav-link sf-brand-item{{ request('brand_id') == $brand['id'] ? ' active' : '' }}">
                                                    <span style="min-width: 0; overflow: hidden; text-overflow: ellipsis;">{{ $brand['name'] }}</span>
                                                    <span style="flex-shrink: 0; padding: 2px 9px; border-radius: 999px; background: #ECF8F8; color: #0E8E88; font-size: 11.5px; font-weight: 700;">{{ $brand['brand_products_count'] }}</span>
                                                </a>
                                            @endforeach
                                            <span class="sf-brand-empty" hidden
                                                  style="padding: 12px 2px; font-size: 14px; color: #6E8C99;">{{ translate('No_Data_Found') }}</span>
                                        </div>
                                    </div>
                                @endif

                                <button type="button" class="sf-see-more sf-filter-reset" style="min-height: 48px; font-size: 15px;">
                                    {{ translate('reset_filters') }}
                                </button>
                            </div>
                        </aside>

                        <div id="ajax-products-view">
                            @include('web-views.seafood.partials._product-grid', ['products' => $products])
                        </div>
                    </div>
                </form>
            </section>
        </main>

        <div style="background: #F4FAFB; border-top: 1px solid #E4EFF1; padding-top: clamp(44px, 7vw, 76px);">
            @include('web-views.seafood.partials._help-links')
        </div>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')

        <div class="sf-filter-backdrop" id="seafood-filter-backdrop" data-open="false"></div>
        <template id="seafood-skeleton-card">
            <article class="sf-skeleton-card" aria-hidden="true">
                <span class="sf-skeleton-media"></span>
                <span class="sf-skeleton-body">
                    <span class="sf-skeleton-line" style="height: 14px; width: 82%;"></span>
                    <span class="sf-skeleton-line" style="height: 14px; width: 54%;"></span>
                    <span class="sf-skeleton-action"></span>
                </span>
            </article>
        </template>
    </div>

    <span id="products-search-data-backup"
          data-url="{{ url()->current() }}"
          data-category="{{ $scopedCategoryId }}"
          data-sub-category="{{ $scopedSubCategoryId }}"
          data-sub-sub-category="{{ $scopedSubSubCategoryId }}"
          data-brand="{{ $data['brand_id'] ?? '' }}"
          data-from="{{ $data['data_from'] ?? '' }}"
          data-offer-type="{{ $data['offer_type'] ?? '' }}"
          data-sort="{{ $data['sort_by'] ?? '' }}"
          data-product-type="{{ $data['product_type'] ?? '' }}"
          data-min-price="{{ $data['min_price'] ?? '' }}"
          data-max-price="{{ $data['max_price'] ?? '' }}"
          data-product-name="{{ $data['product_name'] ?? '' }}"
    ></span>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')

    <script>
        "use strict";

        const seafoodListingBackup = $('#products-search-data-backup');

        // The category scope travels as *_ids[] arrays so it survives a data_from
        // change: ProductManager only honours category_id when data_from is 'category'.
        const seafoodCategoryScope = {
            'category_ids[]': seafoodListingBackup.data('category'),
            'sub_category_ids[]': seafoodListingBackup.data('sub-category'),
            'sub_sub_category_ids[]': seafoodListingBackup.data('sub-sub-category'),
        };

        let seafoodListingQuery = {
            category_id: seafoodListingBackup.data('category'),
            sub_category_id: seafoodListingBackup.data('sub-category'),
            sub_sub_category_id: seafoodListingBackup.data('sub-sub-category'),
            brand_id: seafoodListingBackup.data('brand'),
            data_from: seafoodListingBackup.data('from'),
            offer_type: seafoodListingBackup.data('offer-type'),
            sort_by: seafoodListingBackup.data('sort'),
            product_type: seafoodListingBackup.data('product-type'),
            min_price: seafoodListingBackup.data('min-price'),
            max_price: seafoodListingBackup.data('max-price'),
            product_name: seafoodListingBackup.data('product-name'),
        };

        function renderSeafoodListingSkeleton() {
            const skeletonTemplate = document.getElementById('seafood-skeleton-card');
            const placeholderCount = Math.min(Math.max($('.sf-product-card').length, 4), 8);
            const grid = $('<div class="sf-product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr)); gap: 20px;" role="status" aria-live="polite"></div>');

            for (let index = 0; index < placeholderCount; index++) {
                grid.append(skeletonTemplate.content.cloneNode(true));
            }
            grid.append('<span class="sr-only" style="position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0);">{{ translate('loading_products') }}</span>');

            $('#ajax-products-view').html(grid);
        }

        function renderSeafoodListing() {
            const requestData = {};
            Object.assign(seafoodListingQuery, seafoodCategoryScope);
            Object.keys(seafoodListingQuery).forEach(function (key) {
                const value = seafoodListingQuery[key];
                if (value !== null && value !== undefined && value !== '') {
                    requestData[key] = value;
                }
            });

            history.pushState(null, null, seafoodListingBackup.data('url') + '?' + $.param(requestData));

            $.get({
                url: seafoodListingBackup.data('url'),
                data: requestData,
                dataType: 'json',
                beforeSend: renderSeafoodListingSkeleton,
                success: function (response) {
                    $('#ajax-products-view').html(response?.html_products);
                    $('.view-page-item-count').text(response?.total_product);
                },
            });
        }

        $(document).on('change', '.sf-filter-input', function () {
            seafoodListingQuery[$(this).attr('name')] = $(this).val();
            renderSeafoodListing();
        });

        $(document).on('click', '.sf-search-submit', function () {
            seafoodListingQuery.product_name = $('.sf-search-field').val();
            renderSeafoodListing();
        });

        $(document).on('keydown', '.sf-search-field', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                seafoodListingQuery.product_name = $(this).val();
                renderSeafoodListing();
            }
        });

        $(document).on('click', '.sf-apply-price', function () {
            seafoodListingQuery.min_price = $('#min_price').val();
            seafoodListingQuery.max_price = $('#max_price').val();
            renderSeafoodListing();
            closeSeafoodFilterSheet();
        });

        $(document).on('click', '.sf-filter-reset', function () {
            seafoodListingQuery.min_price = '';
            seafoodListingQuery.max_price = '';
            seafoodListingQuery.product_name = '';
            seafoodListingQuery.sort_by = '';
            seafoodListingQuery.data_from = 'category';
            $('#min_price, #max_price, .sf-search-field').val('');
            $('.sf-filter-input').each(function () {
                $(this).val($(this).find('option:first').val());
            });
            syncSeafoodPriceRange();
            renderSeafoodListing();
            closeSeafoodFilterSheet();
        });

        function renderSeafoodPriceFill() {
            const rangeMax = parseInt($('#max_price_range').attr('max'), 10) || 1;
            const minPercentage = (parseInt($('#min_price_range').val(), 10) / rangeMax) * 100;
            const maxPercentage = (parseInt($('#max_price_range').val(), 10) / rangeMax) * 100;
            $('#price_range_fill').css({
                'inset-inline-start': minPercentage + '%',
                'inset-inline-end': (100 - maxPercentage) + '%',
            });
        }

        function syncSeafoodPriceRange() {
            $('#min_price_range').val($('#min_price').val() || $('#min_price_range').attr('min'));
            $('#max_price_range').val($('#max_price').val() || $('#max_price_range').attr('max'));
            renderSeafoodPriceFill();
        }

        $(document).on('input', '#min_price_range', function () {
            const maxRangeValue = parseInt($('#max_price_range').val(), 10);
            if (parseInt(this.value, 10) > maxRangeValue) {
                this.value = maxRangeValue;
            }
            $('#min_price').val(this.value);
            renderSeafoodPriceFill();
        });

        $(document).on('input', '#max_price_range', function () {
            const minRangeValue = parseInt($('#min_price_range').val(), 10);
            if (parseInt(this.value, 10) < minRangeValue) {
                this.value = minRangeValue;
            }
            $('#max_price').val(this.value);
            renderSeafoodPriceFill();
        });

        $(document).on('input', '#min_price, #max_price', syncSeafoodPriceRange);

        let seafoodScrollPositionBeforeSheet = 0;

        function lockSeafoodPageScroll() {
            seafoodScrollPositionBeforeSheet = window.scrollY;
            $('body').addClass('sf-filter-sheet-open').css('top', (-seafoodScrollPositionBeforeSheet) + 'px');
        }

        function unlockSeafoodPageScroll() {
            if (!$('body').hasClass('sf-filter-sheet-open')) {
                return;
            }
            $('body').removeClass('sf-filter-sheet-open').css('top', '');
            window.scrollTo(0, seafoodScrollPositionBeforeSheet);
        }

        function openSeafoodFilterSheet() {
            $('#seafood-filter-panel').attr('data-open', 'true');
            $('#seafood-filter-backdrop').attr('data-open', 'true');
            $('#seafood-filter-toggle').attr('aria-expanded', 'true');
            lockSeafoodPageScroll();
        }

        function closeSeafoodFilterSheet() {
            $('#seafood-filter-panel').attr('data-open', 'false');
            $('#seafood-filter-backdrop').attr('data-open', 'false');
            $('#seafood-filter-toggle').attr('aria-expanded', 'false');
            unlockSeafoodPageScroll();
        }

        $(window).on('resize', function () {
            if (window.innerWidth >= 980) {
                closeSeafoodFilterSheet();
            }
        });

        $(document).on('input keyup search', '.sf-brand-search', function () {
            const searchTerm = this.value.toLowerCase().trim();
            const brandItems = $('.sf-brand-item');

            brandItems.each(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) !== -1);
            });
            $('.sf-brand-empty').prop('hidden', brandItems.filter(':visible').length > 0);
        });

        $(document).on('click', '.sf-category-caret', function () {
            const branch = $(this).closest('.sf-category-branch');
            const willOpen = branch.attr('data-open') !== 'true';
            branch.attr('data-open', willOpen ? 'true' : 'false');
            $(this).attr('aria-expanded', willOpen ? 'true' : 'false');
        });

        $(document).on('click', '#seafood-filter-toggle', openSeafoodFilterSheet);
        $(document).on('click', '#seafood-filter-close, #seafood-filter-backdrop', closeSeafoodFilterSheet);

        syncSeafoodPriceRange();
    </script>
@endpush
