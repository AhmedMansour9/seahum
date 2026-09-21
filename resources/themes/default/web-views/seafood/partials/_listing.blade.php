{{--
    Shared product listing: toolbar, filter sheet, ajax product grid and skeleton.
    The request state lives on #products-search-data-backup and is driven by _listing-scripts.

    Categories render either as navigation links to the category pages ($listingTreeCategories)
    or as in-page filters scoped to the current listing ($listingFilterCategories).
--}}
@php
    $listingTitle = $listingTitle ?? translate('products');
    $listingHeadingTag = ($listingHeadingTag ?? 'h1') === 'h2' ? 'h2' : 'h1';
    $listingSearchPlaceholder = $listingSearchPlaceholder ?? translate('search_for_products');
    $listingDataFromOptions = $listingDataFromOptions ?? null;
    $listingDefaultDataFrom = $listingDefaultDataFrom ?? '';
    $listingTreeCategories = $listingTreeCategories ?? null;
    $listingFilterCategories = $listingFilterCategories ?? null;
    $listingBrands = $listingBrands ?? null;
    $listingQuery = $listingQuery ?? [];
    $listingScope = $listingScope ?? [];
    $listingMaxUnitPrice = getProductMaxUnitPriceRange(type: 'web');
    $listingSelectedCategoryIds = array_map('strval', (array) request('category_ids', []));
@endphp

<form class="product-list-filter" method="GET" action="{{ url()->current() }}" onsubmit="return false;">
    <div class="sf-listing-toolbar"
         style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding: 16px 18px; background: #F4FAFB; border: 1px solid #E4EFF1; border-radius: 16px;">
        <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
            <{{ $listingHeadingTag }} style="font-family: Cairo, sans-serif; font-size: 20px; font-weight: 800; line-height: 1.4; margin: 0; color: #0B2C3F; text-wrap: pretty; overflow-wrap: anywhere;">{{ $listingTitle }}</{{ $listingHeadingTag }}>
            <span style="font-size: 14px; color: #45677A;">
                <strong class="view-page-item-count" style="font-family: Cairo, sans-serif; font-weight: 700; color: #0B2C3F;">{{ $listingProducts->total() }}</strong>
                {{ translate('items_found') }}
            </span>
        </div>

        <div class="sf-listing-toolbar-actions" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span class="sf-search-wrap">
                <input type="search" class="sf-filter-field sf-search-field" name="product_name" autocomplete="off"
                       value="{{ request('product_name') }}"
                       placeholder="{{ $listingSearchPlaceholder }}"
                       aria-label="{{ $listingSearchPlaceholder }}"
                       style="width: 240px; padding-inline-end: 46px;">
                <button type="button" class="sf-search-submit" aria-label="{{ translate('search') }}">
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

            @if($listingDataFromOptions)
                <label class="sf-data-from-field" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span style="font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; color: #45677A; white-space: nowrap;">{{ translate('Filter_By') }}</span>
                    <select class="sf-filter-field sf-filter-input" name="data_from" style="width: auto; min-width: 160px;">
                        @foreach($listingDataFromOptions as $dataFromValue => $dataFromLabel)
                            <option value="{{ $dataFromValue }}" {{ (request('data_from') ?: $listingDefaultDataFrom) == $dataFromValue ? 'selected' : '' }}>{{ $dataFromLabel }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

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
                                   value="{{ request('max_price') }}" placeholder="{{ $listingMaxUnitPrice }}">
                        </label>
                    </div>
                    <div class="sf-range-wrap">
                        <span class="sf-range-track"></span>
                        <span class="sf-range-fill" id="price_range_fill"></span>
                        <input type="range" class="sf-range" id="min_price_range" min="0" max="{{ $listingMaxUnitPrice }}"
                               value="{{ request('min_price', 0) }}" aria-label="{{ translate('Min') }}">
                        <input type="range" class="sf-range" id="max_price_range" min="0" max="{{ $listingMaxUnitPrice }}"
                               value="{{ request('max_price', $listingMaxUnitPrice) }}" aria-label="{{ translate('Max') }}">
                    </div>
                    <button type="button" class="sf-apply-price sf-btn-block" style="margin-top: 16px; min-height: 44px; font-size: 14.5px; box-shadow: none;">
                        {{ translate('apply') }}
                    </button>
                </div>

                @if($listingTreeCategories && count($listingTreeCategories) > 0)
                    <div class="sf-filter-card">
                        <span class="sf-filter-group-title">{{ translate('categories') }}</span>
                        <div style="max-height: 380px; overflow-y: auto;">
                            @include('web-views.seafood.partials._category-tree', ['treeCategories' => $listingTreeCategories])
                        </div>
                    </div>
                @elseif($listingFilterCategories && count($listingFilterCategories) > 0)
                    <div class="sf-filter-card">
                        <span class="sf-filter-group-title">{{ translate('categories') }}</span>
                        <div style="display: flex; flex-direction: column; gap: 2px; max-height: 380px; overflow-y: auto;">
                            <button type="button" class="sf-filter-nav-link sf-listing-category{{ empty($listingSelectedCategoryIds) ? ' active' : '' }}"
                                    data-category-id="" aria-pressed="{{ empty($listingSelectedCategoryIds) ? 'true' : 'false' }}">
                                <span style="min-width: 0; overflow: hidden; text-overflow: ellipsis;">{{ translate('all_categories') }}</span>
                            </button>
                            @foreach($listingFilterCategories as $filterCategory)
                                @php($isFilterCategoryActive = in_array((string) $filterCategory['id'], $listingSelectedCategoryIds, true))
                                <button type="button" class="sf-filter-nav-link sf-listing-category{{ $isFilterCategoryActive ? ' active' : '' }}"
                                        data-category-id="{{ $filterCategory['id'] }}" aria-pressed="{{ $isFilterCategoryActive ? 'true' : 'false' }}">
                                    <span style="min-width: 0; overflow: hidden; text-overflow: ellipsis;">{{ $filterCategory['name'] }}</span>
                                    @if(isset($filterCategory['product_count']))
                                        <span style="flex-shrink: 0; padding: 2px 9px; border-radius: 999px; background: #ECF8F8; color: #0E8E88; font-size: 11.5px; font-weight: 700;"><bdi dir="ltr">{{ $filterCategory['product_count'] }}</bdi></span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(($web_config['brand_setting'] ?? false) && $listingBrands && $listingBrands->count() > 0)
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
                            @foreach($listingBrands as $brand)
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
            @include('web-views.seafood.partials._product-grid', ['products' => $listingProducts])
        </div>
    </div>
</form>

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

<span id="products-search-data-backup"
      data-url="{{ url()->current() }}"
      data-query="{{ json_encode((object) array_filter($listingQuery, fn ($value) => $value !== null && $value !== '')) }}"
      data-scope="{{ json_encode((object) $listingScope) }}"
      data-default-from="{{ $listingDefaultDataFrom }}"
      data-loading-text="{{ translate('loading_products') }}"
></span>
