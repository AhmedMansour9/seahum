@extends('layouts.front-end.seafood')

@section('title', translate('sign_in'))

@php($customerManualLogin = (bool) ($web_config['customer_login_options']['manual_login'] ?? 0))
@php($customerOTPLogin = (bool) ($web_config['customer_login_options']['otp_login'] ?? 0))
@php($socialLoginServices = collect($web_config['customer_social_login_options'] ?? [])->filter(fn ($status, $key) => $status && $key !== 'apple')->keys())
@php($customerSocialLogin = ($web_config['customer_login_options']['social_login'] ?? 0) && $socialLoginServices->isNotEmpty())
@php($bothLoginMethods = $customerOTPLogin && $customerManualLogin)
@php($multiColumn = ($customerOTPLogin || $customerManualLogin) && ($customerSocialLogin || $bothLoginMethods))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    <link rel="stylesheet"
          href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">

    <style>
        .sf-auth-grid { display: grid; grid-template-columns: {{ $multiColumn ? '1fr 1fr' : '1fr' }}; gap: clamp(24px, 3vw, 40px); align-items: start; }

        @media (max-width: 759px) {
            .sf-auth-grid { grid-template-columns: 1fr !important; gap: 22px !important; }
        }
    </style>

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ translate('sign_in') }} — {{ $web_config['company_name'] }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ translate('sign_in') }} — {{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ url()->current() }}">
@endpush

@section('content')
    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'account'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => translate('sign_in')]],
                ])

                <div class="sf-card"
                     style="max-width: {{ $multiColumn ? '900px' : '520px' }}; margin: 0 auto; padding: clamp(24px, 4vw, 40px);">

                    <div style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; margin-bottom: clamp(24px, 3vw, 32px);">
                        <span style="width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </span>
                        <h1 style="font-family: Cairo, sans-serif; font-size: clamp(21px, 2.6vw, 26px); font-weight: 800; line-height: 1.4; margin: 6px 0 0; color: #0B2C3F;">
                            {{ translate('sign_in') }}
                        </h1>
                        <p style="font-size: 14.5px; line-height: 1.8; color: #6E8C99; margin: 0; max-width: 380px; text-wrap: pretty;">
                            {{ translate('sign_in_to_track_your_orders_and_check_out_faster') }}
                        </p>
                    </div>

                    <div class="sf-auth-grid">

                        @if ($customerManualLogin || $customerOTPLogin)
                            <form autocomplete="off" class="customer-centralize-login-form"
                                  action="{{ route('customer.auth.login') }}" method="post"
                                  id="{{ $customerManualLogin ? 'customer-login-form' : 'customer-otp-login-form' }}"
                                  @if (!$customerManualLogin) data-firebase-auth="{{ $web_config['firebase_otp_verification_status'] ? 'active' : 'deactivate' }}" @endif
                                  style="display: flex; flex-direction: column; gap: 18px; min-width: 0;">
                                @csrf
                                <input type="hidden" name="keep_customer_login_redirect_url"
                                       value="{{ $keepCustomerLoginRedirectUrl ?? old('keep_customer_login_redirect_url', url()->previous()) }}">
                                <input type="hidden" name="login_type" class="auth-login-type-input"
                                       value="{{ $customerManualLogin ? 'manual-login' : 'otp-login' }}">

                                @if ($customerManualLogin)
                                    <div class="manual-login-items" data-sf-display="flex" style="display: flex; flex-direction: column; gap: 18px;">
                                        <div>
                                            <label class="sf-form-label" for="si-email">
                                                {{ translate('email') }} / {{ translate('phone') }}
                                                <span class="sf-required" aria-hidden="true">*</span>
                                            </label>
                                            <input class="sf-filter-field auth-email-input" type="text" name="user_identity"
                                                   id="si-email" value="{{ old('user_identity') }}"
                                                   placeholder="{{ translate('enter_email_or_phone') }}" required>
                                        </div>

                                        <div>
                                            <label class="sf-form-label" for="si-password">
                                                {{ translate('password') }}
                                                <span class="sf-required" aria-hidden="true">*</span>
                                            </label>
                                            <span class="sf-input-group">
                                                <input class="sf-filter-field auth-password-input" name="password" type="password"
                                                       id="si-password" placeholder="{{ translate('enter_password') }}" required>
                                                <button type="button" class="sf-input-action sf-password-toggle"
                                                        data-target="si-password" aria-controls="si-password" aria-pressed="false"
                                                        aria-label="{{ translate('show_password') }}">
                                                    <svg class="sf-password-show" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    <svg class="sf-password-hide" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" hidden><path d="M3 3l18 18"></path><path d="M10.6 6.2A9.9 9.9 0 0 1 12 6c6.4 0 10 6 10 6a17.6 17.6 0 0 1-3.9 4.5"></path><path d="M6.3 7.6A17.4 17.4 0 0 0 2 12s3.6 6 10 6a9.7 9.7 0 0 0 4-.8"></path><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path></svg>
                                                </button>
                                            </span>
                                        </div>

                                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                            @php($rememberId = 'si-remember')
                                            <label class="sf-check" for="{{ $rememberId }}">
                                                <input type="checkbox" name="remember" id="{{ $rememberId }}" {{ old('remember') ? 'checked' : '' }}>
                                                <span class="sf-check-box" aria-hidden="true">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7"></path></svg>
                                                </span>
                                                <span class="sf-check-label">{{ translate('remember_me') }}</span>
                                            </label>
                                            <a class="sf-text-link" style="font-size: 14px;" href="{{ route('customer.auth.recover-password') }}">
                                                {{ translate('forgot_password_question') }}
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if ($customerOTPLogin)
                                    <div class="otp-login-items" data-sf-display="flex"
                                         style="display: {{ $bothLoginMethods ? 'none' : 'flex' }}; flex-direction: column; gap: 18px;">
                                        <div>
                                            <label class="sf-form-label" for="si-phone">
                                                {{ translate('phone_number') }}
                                                <span class="sf-required" aria-hidden="true">*</span>
                                            </label>
                                            <input class="sf-filter-field phone-input-with-country-picker" type="tel" id="si-phone"
                                                   value="{{ old('phone') }}" name="phone"
                                                   placeholder="{{ translate('enter_phone_number') }}"
                                                   {{ $bothLoginMethods ? '' : 'required' }}>
                                        </div>
                                    </div>
                                @endif

                                @include('web-views.customer-views.auth.partials._firebase-recaptcha-container')

                                @if ($customerManualLogin)
                                    <div class="manual-login-items" data-sf-display="block">
                                        <button class="sf-btn-block" type="submit">{{ translate('sign_in') }}</button>
                                    </div>
                                @endif

                                @if ($customerOTPLogin)
                                    <div class="otp-login-items" data-sf-display="block"
                                         style="display: {{ $bothLoginMethods ? 'none' : 'block' }};">
                                        <button class="sf-btn-block" type="submit">{{ translate('Get_OTP') }}</button>
                                    </div>
                                @endif
                            </form>
                        @endif

                        @if ($multiColumn)
                            <div style="display: flex; flex-direction: column; gap: 14px; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 12px; color: #6E8C99; font-size: 13.5px;">
                                    <span style="height: 1px; background: #E4EFF1; flex: 1;"></span>
                                    <span>{{ translate('Or Sign in with') }}</span>
                                    <span style="height: 1px; background: #E4EFF1; flex: 1;"></span>
                                </div>

                                @if ($customerSocialLogin)
                                    @foreach ($socialLoginServices as $socialLoginServiceKey)
                                        <a class="sf-btn-outline"
                                           href="{{ route('customer.auth.service-login', $socialLoginServiceKey) }}">
                                            <img alt="" width="22" height="22"
                                                 src="{{ theme_asset(path: 'public/assets/front-end/img/icons/' . $socialLoginServiceKey . '.png') }}">
                                            <span>{{ translate($socialLoginServiceKey) }}</span>
                                        </a>
                                    @endforeach
                                @endif

                                @if ($bothLoginMethods)
                                    <button type="button" class="sf-btn-outline otp-login-btn">
                                        <img alt="" width="22" height="22"
                                             src="{{ theme_asset(path: 'public/assets/front-end/img/icons/otp-login-icon.svg') }}">
                                        <span>{{ translate('OTP_Sign_in') }}</span>
                                    </button>

                                    <button type="button" class="sf-btn-outline manual-login-btn" style="display: none;">
                                        <img alt="" width="22" height="22"
                                             src="{{ theme_asset(path: 'public/assets/front-end/img/icons/otp-login-icon.svg') }}">
                                        <span>{{ translate('Manual_Login') }}</span>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>

                    <p style="text-align: center; font-size: 14.5px; color: #6E8C99; margin: clamp(22px, 3vw, 30px) 0 0;">
                        {{ translate('Enjoy_New_experience') }}
                        <a class="sf-text-link" href="{{ route('customer.auth.sign-up') }}">{{ translate('sign_up') }}</a>
                    </p>
                </div>
            </section>
        </main>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')
    </div>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')

    @include('web-views.seafood.partials._auth-scripts')
@endpush
