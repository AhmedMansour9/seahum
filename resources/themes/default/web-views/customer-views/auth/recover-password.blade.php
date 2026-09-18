@extends('layouts.front-end.seafood')

@section('title', translate('forgot_Password'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')
    @include('web-views.seafood.partials._auth-styles')
@endpush

@section('content')
    @php($recaptcha = getWebConfig(name: 'recaptcha'))

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'auth'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [
                        ['label' => translate('sign_in'), 'url' => route('customer.auth.login')],
                        ['label' => translate('forgot_Password')],
                    ],
                ])

                <div class="sf-card" style="max-width: 620px; margin: 0 auto; padding: clamp(22px, 4vw, 34px);">

                    <header style="text-align: center; margin-bottom: 26px;">
                        <span style="width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: inline-grid; place-items: center;">
                            <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10.5" width="16" height="10" rx="3"></rect><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"></path></svg>
                        </span>
                        <h1 style="font-family: Cairo, sans-serif; font-size: clamp(21px, 3vw, 26px); font-weight: 800; line-height: 1.35; margin: 14px 0 6px; color: #0B2C3F; text-wrap: pretty;">{{ translate('forgot_your_password') }}?</h1>
                        <p style="font-size: 14.5px; line-height: 1.8; color: #6E8C99; margin: 0 auto; max-width: 440px;">
                            {{ translate('change_your_password_in_three_easy_steps.') }}
                            {{ translate('this_helps_to_keep_your_new_password_secure.') }}
                        </p>
                    </header>

                    <ol style="list-style: none; display: flex; flex-direction: column; gap: 14px; margin: 0 0 26px; padding: 18px; background: #F4FAFB; border: 1px solid #E4EFF1; border-radius: 16px;">
                        <li class="sf-step">
                            <span class="sf-step-num" aria-hidden="true">1</span>
                            <span class="sf-step-text">{{ translate('use_your_registered_phone.') }}</span>
                        </li>
                        <li class="sf-step">
                            <span class="sf-step-num" aria-hidden="true">2</span>
                            <span class="sf-step-text">{{ translate('we_will_send_you_a_temporary_OTP_in_your_phone') }}.</span>
                        </li>
                        <li class="sf-step">
                            <span class="sf-step-num" aria-hidden="true">3</span>
                            <span class="sf-step-text">{{ translate('use_the_OTP_code_to_change_your_password_on_our_secure_website.') }}</span>
                        </li>
                    </ol>

                    <form action="{{ route('customer.auth.forgot-password') }}" method="post" id="customer-forgot-password-form">
                        @csrf

                        <label class="sf-form-label" for="recover-phone">
                            {{ translate('Phone') }}<span class="sf-required">*</span>
                        </label>
                        <input class="sf-filter-field clean-phone-input-value" id="recover-phone" type="text"
                               name="identity" inputmode="tel" autocomplete="tel"
                               placeholder="{{ translate('enter_your_phone_number') }}" required>
                        <span class="sf-field-hint">* {{ translate('must_use_country_code_before_phone_number') }}</span>

                        <div style="margin-top: 18px;">
                            @if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
                                <div class="generate-firebase-auth-recaptcha" id="firebase-auth-recaptcha-{{ rand(111, 999) }}"></div>
                            @elseif(isset($recaptcha) && $recaptcha['status'] == 1)
                                <div class="dynamic-default-and-recaptcha-section">
                                    <input type="hidden" name="g-recaptcha-response" class="render-grecaptcha-response"
                                           data-input="#login-default-captcha-section"
                                           data-default-captcha="#login-default-captcha-section" data-action="customer_auth">

                                    <div class="default-captcha-container d-none" id="login-default-captcha-section"
                                         data-placeholder="{{ translate('enter_captcha_value') }}"
                                         data-base-url="{{ route('g-recaptcha-session-store') }}"
                                         data-session="{{ 'default_recaptcha_id_customer_auth' }}">
                                    </div>
                                </div>
                            @else
                                <div class="default-captcha-container"
                                     data-placeholder="{{ translate('enter_captcha_value') }}"
                                     data-base-url="{{ route('g-recaptcha-session-store') }}"
                                     data-session="{{ 'default_recaptcha_id_customer_auth' }}">
                                </div>
                            @endif
                        </div>

                        <button class="sf-btn-block" type="submit" style="margin-top: 22px;">
                            {{ translate('send_OTP') }}
                        </button>
                    </form>

                    <p style="text-align: center; font-size: 14.5px; color: #6E8C99; margin: 24px 0 0;">
                        {{ translate('Already_have_account ') }}?
                        <a class="sf-text-link" href="{{ route('customer.auth.login') }}">{{ translate('sign_in') }}</a>
                    </p>
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
