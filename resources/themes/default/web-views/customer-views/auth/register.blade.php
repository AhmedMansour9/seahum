@extends('layouts.front-end.seafood')

@section('title', translate('sign_up'))

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
                    'breadcrumbItems' => [['label' => translate('sign_up')]],
                ])

                <div class="sf-card" style="max-width: 760px; margin: 0 auto; padding: clamp(22px, 4vw, 34px);">

                    <header style="text-align: center; margin-bottom: 26px;">
                        <span style="width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: inline-grid; place-items: center;">
                            <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4.5 20c1.4-3.4 4.2-5 7.5-5s6.1 1.6 7.5 5"></path></svg>
                        </span>
                        <h1 style="font-family: Cairo, sans-serif; font-size: clamp(21px, 3vw, 26px); font-weight: 800; line-height: 1.35; margin: 14px 0 6px; color: #0B2C3F; text-align: center !important;">{{ translate('create_your_account') }}</h1>
                        <p style="font-size: 14.5px; line-height: 1.8; color: #6E8C99; margin: 0 auto; max-width: 460px; text-align: center !important;">{{ translate('sign_up_to_track_your_orders_and_check_out_faster') }}</p>
                    </header>

                    <form id="customer-register-form" action="{{ route('customer.auth.sign-up') }}" method="post" novalidate>
                        @csrf

                        <div class="sf-form-grid">
                            <div>
                                <label class="sf-form-label" for="register-f-name">
                                    {{ translate('first_name') }}<span class="sf-required">*</span>
                                </label>
                                <input class="sf-filter-field" id="register-f-name" type="text" name="f_name"
                                       value="{{ old('f_name') }}" autocomplete="given-name"
                                       placeholder="{{ translate('ex_first_name') }}" required>
                            </div>

                            <div>
                                <label class="sf-form-label" for="register-l-name">
                                    {{ translate('last_name') }}<span class="sf-required">*</span>
                                </label>
                                <input class="sf-filter-field" id="register-l-name" type="text" name="l_name"
                                       value="{{ old('l_name') }}" autocomplete="family-name"
                                       placeholder="{{ translate('ex_family_name') }}" required>
                            </div>

                            <div>
                                <label class="sf-form-label" for="register-email">
                                    {{ translate('email_address') }}<span class="sf-required">*</span>
                                </label>
                                <input class="sf-filter-field" id="register-email" type="email" name="email"
                                       value="{{ old('email') }}" autocomplete="off"
                                       placeholder="{{ translate('enter_email_address') }}" required>
                            </div>

                            <div>
                                <label class="sf-form-label" for="register-phone">
                                    {{ translate('phone_number') }}<span class="sf-required">*</span>
                                </label>
                                <input class="sf-filter-field phone-input-with-country-picker" id="register-phone"
                                       type="tel" name="phone" value="{{ old('phone') }}" data-locked-country="eg"
                                       placeholder="{{ translate('enter_phone_number') }}" required>
                            </div>

                            <div>
                                <label class="sf-form-label" for="si-password">
                                    {{ translate('password') }}<span class="sf-required">*</span>
                                </label>
                                <span class="sf-input-group">
                                    <input class="sf-filter-field" id="si-password" type="password" name="password"
                                           autocomplete="new-password" minlength="6"
                                           placeholder="{{ translate('minimum_6_characters_long') }}" required>
                                    <button type="button" class="sf-input-action sf-password-toggle" data-target="si-password"
                                            aria-pressed="false" aria-label="{{ translate('show_password') }}">
                                        <svg class="sf-eye" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <svg class="sf-eye-off" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l16 16"></path><path d="M9.9 5.8A9.6 9.6 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a17 17 0 0 1-3.3 4"></path><path d="M6.5 8A17 17 0 0 0 2.5 12S6 18.5 12 18.5c1.4 0 2.6-.3 3.7-.8"></path></svg>
                                    </button>
                                </span>
                            </div>

                            <div>
                                <label class="sf-form-label" for="si-confirm-password">
                                    {{ translate('confirm_password') }}<span class="sf-required">*</span>
                                </label>
                                <span class="sf-input-group">
                                    <input class="sf-filter-field" id="si-confirm-password" type="password" name="con_password"
                                           autocomplete="new-password" minlength="6"
                                           placeholder="{{ translate('minimum_6_characters_long') }}" required>
                                    <button type="button" class="sf-input-action sf-password-toggle" data-target="si-confirm-password"
                                            aria-pressed="false" aria-label="{{ translate('show_password') }}">
                                        <svg class="sf-eye" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <svg class="sf-eye-off" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l16 16"></path><path d="M9.9 5.8A9.6 9.6 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a17 17 0 0 1-3.3 4"></path><path d="M6.5 8A17 17 0 0 0 2.5 12S6 18.5 12 18.5c1.4 0 2.6-.3 3.7-.8"></path></svg>
                                    </button>
                                </span>
                            </div>

                            @if($web_config['ref_earning_status'])
                                <div class="sf-form-full">
                                    <label class="sf-form-label" for="referral_code">
                                        {{ translate('refer_code') }}
                                        <span style="font-family: Tajawal, sans-serif; font-weight: 400; color: #6E8C99;">({{ translate('optional') }})</span>
                                    </label>
                                    <input class="sf-filter-field" id="referral_code" type="text" name="referral_code"
                                           placeholder="{{ translate('use_referral_code') }}">
                                </div>
                            @endif

                            <div class="sf-form-full">
                                <label class="sf-check">
                                    <input type="checkbox" name="remember" id="inputChecked">
                                    <span class="sf-check-box" aria-hidden="true">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7"></path></svg>
                                    </span>
                                    <span class="sf-check-label">
                                        {{ translate('i_agree_to_Your') }}
                                        <a class="sf-text-link" target="_blank"
                                           href="{{ route('business-page.view', ['slug' => 'terms-and-conditions']) }}">{{ translate('terms_and_condition') }}</a>
                                    </span>
                                </label>
                            </div>

                            <div class="sf-form-full">
                                @include('web-views.customer-views.auth.partials._firebase-recaptcha-container')
                            </div>

                            <div class="sf-form-full">
                                <button class="sf-btn-block" id="sign-up" type="submit" disabled>
                                    {{ translate('sign_up') }}
                                </button>
                                <span class="sf-field-hint" style="text-align: center;" id="sign-up-hint">
                                    {{ translate('accept_the_terms_and_conditions_to_continue') }}
                                </span>
                            </div>
                        </div>
                    </form>

                    @php($socialLoginServices = ($web_config['customer_login_options']['social_login'] ?? false)
                        ? array_filter($web_config['customer_social_login_options'] ?? [], fn ($isEnabled, $serviceKey) => $isEnabled && $serviceKey !== 'apple', ARRAY_FILTER_USE_BOTH)
                        : [])

                    @if(count($socialLoginServices) > 0)
                        <div class="sf-divider-text" style="margin: 26px 0 18px;">{{ translate('or_continue_with') }}</div>

                        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 12px;">
                            @foreach($socialLoginServices as $socialLoginServiceKey => $socialLoginService)
                                <a class="sf-social-btn" href="{{ route('customer.auth.service-login', $socialLoginServiceKey) }}"
                                   aria-label="{{ translate($socialLoginServiceKey) }}">
                                    <img src="{{ theme_asset(path: 'public/assets/front-end/img/icons/' . $socialLoginServiceKey . '.png') }}"
                                         alt="{{ translate($socialLoginServiceKey) }}">
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <p style="text-align: center !important; font-size: 14.5px; color: #6E8C99; margin: 26px 0 0;">
                        {{ translate('already_have_an_account_question') }}
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

    <script>
        "use strict";

        const signUpButton = $('#sign-up');
        const signUpHint = $('#sign-up-hint');

        $('#inputChecked').on('change', function () {
            const accepted = $(this).is(':checked');
            signUpButton.prop('disabled', !accepted);
            signUpHint.toggle(!accepted);
        }).trigger('change');

        // A referral link lands on this page carrying the code in the query string.
        (function prefillReferralCodeFromUrl() {
            const referralCode = new URLSearchParams(window.location.search).get('referral_code');
            if (referralCode) {
                $('#referral_code').val(referralCode);
            }
        })();

        $('#customer-register-form').on('submit', function (event) {
            event.preventDefault();

            const form = $(this);

            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                beforeSend: function () {
                    signUpButton.prop('disabled', true);
                },
                success: function (response) {
                    if (response.errors) {
                        response.errors.forEach(function (error) {
                            toastr.error(error.message);
                        });
                    } else if (response.error) {
                        toastr.error(response.error);
                    } else if (response.status === 1) {
                        toastr.success(response.message);
                    }

                    if (response?.redirect_url) {
                        window.location.href = response.redirect_url;
                    }
                },
                complete: function () {
                    signUpButton.prop('disabled', !$('#inputChecked').is(':checked'));
                }
            });
        });
    </script>
@endpush
