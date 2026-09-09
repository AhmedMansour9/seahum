@extends('layouts.front-end.seafood')

@section('title', translate('all_categories'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    <style>
        .sf-crumb-link { color: #6E8C99; }
        .sf-crumb-link:hover { color: #0E8E88; }

        @media (max-width: 759px) {
            .sf-page-head { flex-direction: column !important; align-items: stretch !important; }
            .sf-page-head-search { width: 100% !important; }
        }
    </style>

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ translate('all_categories') }} — {{ $web_config['company_name'] }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ translate('all_categories') }} — {{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    @php($searchedCategoryName = request('search'))

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'categories'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                <nav aria-label="{{ translate('breadcrumb') }}"
                     style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 13.5px; color: #6E8C99; margin-bottom: 18px;">
                    <a href="{{ route('home') }}" class="sf-crumb-link">{{ translate('home') }}</a>
                    <span aria-hidden="true" style="color: #A8B9C1;">/</span>
                    <span style="font-family: Cairo, sans-serif; font-weight: 700; color: #0B2C3F;">{{ translate('all_categories') }}</span>
                </nav>

                <form method="GET" action="{{ route('categories') }}" class="sf-page-head"
                      style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding: 16px 18px; background: #F4FAFB; border: 1px solid #E4EFF1; border-radius: 16px;">
                    <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                        <h1 style="font-family: Cairo, sans-serif; font-size: 20px; font-weight: 800; line-height: 1.4; margin: 0; color: #0B2C3F; text-wrap: pretty;">{{ translate('all_categories') }}</h1>
                        <span style="font-size: 14px; color: #45677A;">
                            <strong style="font-family: Cairo, sans-serif; font-weight: 700; color: #0B2C3F;">{{ count($categories) }}</strong>
                            {{ translate('categories_found') }}
                        </span>
                    </div>

                    <span class="sf-search-wrap sf-page-head-search" style="width: 280px;">
                        <input type="search" class="sf-filter-field sf-search-field" name="search" autocomplete="off"
                               value="{{ $searchedCategoryName }}"
                               placeholder="{{ translate('search_categories') }}"
                               style="padding-inline-end: 46px;">
                        <button type="submit" class="sf-search-submit" aria-label="{{ translate('search') }}">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.2-3.2"></path></svg>
                        </button>
                    </span>
                </form>

                @if(count($categories) > 0)
                    <div class="sf-cat-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(200px, 100%), 1fr)); gap: 16px; margin-top: 24px;">
                        @foreach($categories as $category)
                            @include('web-views.seafood.partials._category-card', ['category' => $category, 'categoryTitleLimit' => 40])
                        @endforeach
                    </div>
                @else
                    <div class="sf-empty-state" style="background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; padding: 44px 22px; text-align: center; margin-top: 24px;">
                        <span style="width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: inline-grid; place-items: center;">
                            <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.2-3.2"></path></svg>
                        </span>
                        <h4 style="font-family: Cairo, sans-serif; font-size: 17px; font-weight: 700; margin: 16px 0 8px; color: #0B2C3F;">
                            {{ filled($searchedCategoryName) ? translate('no_results_found') : translate('there_is_no_category') }}
                        </h4>
                        <p style="font-size: 14px; color: #6E8C99; line-height: 1.8; margin: 0 auto 18px; max-width: 420px;">
                            {{ filled($searchedCategoryName) ? translate('no_category_matches_this_name_try_a_shorter_word') : translate('categories_will_appear_here_once_they_are_added') }}
                        </p>
                        @if(filled($searchedCategoryName))
                            <a href="{{ route('categories') }}" class="sf-see-more" style="min-height: 44px; padding: 0 24px; font-size: 14.5px;">
                                {{ translate('show_all_categories') }}
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="sf-see-more" style="min-height: 44px; padding: 0 24px; font-size: 14.5px;">
                                {{ translate('back_to_home') }}
                            </a>
                        @endif
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
