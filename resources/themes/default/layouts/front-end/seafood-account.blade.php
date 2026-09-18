@extends('layouts.front-end.seafood')

@push('css_or_js')
    @include('web-views.seafood.partials._styles')
@endpush

@section('content')
    @php($accountPageTitle = trim($__env->yieldContent('page_title')))

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'account'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => $accountPageTitle]],
                ])

                <div class="sf-account-layout">
                    @include('web-views.seafood.partials._account-nav')

                    <div class="sf-account-main">
                        <div class="sf-page-head">
                            <div class="sf-page-head-title">
                                <button type="button" class="sf-account-nav-toggle" id="sf-account-nav-toggle"
                                        aria-expanded="false" aria-controls="sf-account-nav"
                                        aria-label="{{ translate('open_account_menu') }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                </button>
                                <h1 class="sf-page-title">{{ $accountPageTitle }}</h1>
                                @hasSection('page_count')
                                    <span class="sf-page-count"><bdi dir="ltr">@yield('page_count')</bdi></span>
                                @endif
                            </div>

                            <div class="sf-page-head-actions">
                                @yield('page_actions')
                            </div>
                        </div>

                        @yield('account_content')
                    </div>
                </div>
            </section>
        </main>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')
    </div>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')
@endpush
