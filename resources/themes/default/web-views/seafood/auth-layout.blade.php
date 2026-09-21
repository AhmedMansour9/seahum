{{-- Centered card shell for the secondary sign-in steps (OTP, reset password, profile completion). --}}
@extends('layouts.front-end.seafood')

@push('css_or_js')
    @include('web-views.seafood.partials._styles')
    @include('web-views.seafood.partials._auth-styles')
@endpush

@section('content')
    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'auth'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [
                        ['label' => translate('sign_in'), 'url' => route('customer.auth.login')],
                        ['label' => trim($__env->yieldContent('auth_heading'))],
                    ],
                ])

                <div class="sf-card" style="max-width: 560px; margin: 0 auto; padding: clamp(22px, 4vw, 34px);">
                    <header style="text-align: center; margin-bottom: 24px;">
                        <span class="sf-empty-icon" style="display: inline-grid; margin: 0;">
                            <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                @hasSection('auth_icon')
                                    @yield('auth_icon')
                                @else
                                    <rect x="4" y="10.5" width="16" height="10" rx="3"></rect><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"></path>
                                @endif
                            </svg>
                        </span>
                        <h1 style="font-family: Cairo, sans-serif; font-size: clamp(21px, 3vw, 26px); font-weight: 800; line-height: 1.35; margin: 14px 0 6px; color: #0B2C3F; text-wrap: pretty;">@yield('auth_heading')</h1>
                        @hasSection('auth_intro')
                            <p style="font-size: 14.5px; line-height: 1.8; color: #6E8C99; margin: 0 auto; max-width: 440px; overflow-wrap: anywhere;">@yield('auth_intro')</p>
                        @endif
                    </header>

                    @yield('auth_content')
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
    @include('web-views.seafood.partials._auth-scripts')
@endpush
