@extends('layouts.front-end.seafood')

@section('title', translate('contact_us'))

@php($storePhone = $web_config['phone'])
@php($storeEmail = getWebConfig(name: 'company_email'))
@php($whatsappConfig = getWebConfig(name: 'whatsapp'))
@php($whatsappNumber = ($whatsappConfig['status'] ?? 0) ? $whatsappConfig['phone'] : null)
@php($recaptchaConfig = getWebConfig(name: 'recaptcha'))
@php($isRecaptchaEnabled = ($recaptchaConfig['status'] ?? 0) == 1)

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    @include('web-views.seafood.partials._phone-input-styles')

    @if($isRecaptchaEnabled)
        <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/libs/google-recaptcha/google-recaptcha-init.css') }}">
    @endif

    <style>
        .sf-contact-grid { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); gap: clamp(20px, 3vw, 32px); align-items: start; }
        .sf-contact-method { display: flex; align-items: flex-start; gap: 14px; padding: 16px; border: 1px solid #E4EFF1; border-radius: 16px; background: #FFFFFF; }
        .sf-contact-method-icon { width: 44px; height: 44px; flex-shrink: 0; border-radius: 14px; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; }
        .sf-contact-method-label { display: block; font-family: Cairo, sans-serif; font-size: 13.5px; font-weight: 700; color: #0B2C3F; margin-bottom: 4px; }
        .sf-contact-method-value { display: block; font-size: 14.5px; line-height: 1.75; color: #55747F; overflow-wrap: anywhere; }

        @media (max-width: 979px) {
            .sf-contact-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 639px) {
            .sf-form-grid { grid-template-columns: 1fr; }
        }
    </style>

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ translate('contact_us') }} — {{ $web_config['company_name'] }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ translate('contact_us') }} — {{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ url()->current() }}">
@endpush

@section('content')
    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'page'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => translate('contact_us')]],
                ])

                <div class="sf-page-hero">
                    <div class="sf-page-hero-body" style="text-align: center;">
                        <span style="display: inline-flex; align-items: center; gap: 9px; padding: 7px 14px; border-radius: 999px; background: rgba(23,176,167,.22); border: 1px solid rgba(127,227,220,.4); color: #7FE3DC; font-family: Cairo, sans-serif; font-size: 12.5px; font-weight: 700;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12a8 8 0 0 1 16 0"></path><path d="M4 12v4a2 2 0 0 0 2 2h1v-6H6a2 2 0 0 0-2 2Z"></path><path d="M20 12v4a2 2 0 0 1-2 2h-1v-6h1a2 2 0 0 1 2 2Z"></path></svg>
                            {{ translate('We_are_Here_to_Help') }}
                        </span>
                        <h1 style="font-family: Cairo, sans-serif; font-size: clamp(24px, 4vw, 38px); font-weight: 900; line-height: 1.25; margin: 14px 0 0; color: #FFFFFF; overflow-wrap: anywhere; text-wrap: pretty;">
                            {{ translate('contact_us') }}
                        </h1>
                        <p style="font-size: 15px; line-height: 1.85; color: #C6DDE6; margin: 12px auto 0; max-width: 520px; text-wrap: pretty;">
                            {{ translate('send_us_your_question_or_order_details_and_our_team_will_get_back_to_you') }}
                        </p>
                    </div>
                </div>

                <div class="sf-contact-grid" style="margin-top: 24px;">

                    <div class="sf-card" style="padding: clamp(20px, 3.5vw, 34px); min-width: 0;">
                        <h2 style="font-family: Cairo, sans-serif; font-size: clamp(19px, 2.4vw, 23px); font-weight: 800; line-height: 1.4; margin: 0 0 6px; color: #0B2C3F;">
                            {{ translate('send_us_a_message') }}
                        </h2>
                        <p style="font-size: 14.5px; line-height: 1.8; color: #6E8C99; margin: 0 0 clamp(20px, 3vw, 28px);">
                            {{ translate('all_fields_marked_with_an_asterisk_are_required') }}
                        </p>

                        <form action="{{ route('contact.store') }}" method="POST" id="contact-form">
                            @csrf

                            <div class="sf-form-grid">
                                <div>
                                    <label class="sf-form-label" for="cf-name">{{ translate('your_name') }}</label>
                                    <input class="sf-filter-field @error('name') is-invalid @enderror" type="text" id="cf-name"
                                           name="name" value="{{ old('name') }}"
                                           placeholder="{{ translate('John_Doe') }}">
                                    @error('name')
                                        <span class="sf-field-note">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="sf-form-label" for="cf-email">
                                        {{ translate('email_address') }}
                                        <span class="sf-required" aria-hidden="true">*</span>
                                    </label>
                                    <input class="sf-filter-field @error('email') is-invalid @enderror" type="email" id="cf-email"
                                           name="email" value="{{ old('email') }}"
                                           placeholder="{{ translate('enter_email_address') }}" required>
                                    @error('email')
                                        <span class="sf-field-note">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="sf-form-label" for="cf-phone">
                                        {{ translate('your_phone') }}
                                        <span class="sf-required" aria-hidden="true">*</span>
                                    </label>
                                    <input class="sf-filter-field phone-input-with-country-picker @error('mobile_number') is-invalid @enderror"
                                           type="tel" id="cf-phone" name="mobile_number" value="{{ old('mobile_number') }}" data-locked-country="eg"
                                           placeholder="{{ translate('contact_number') }}" required>
                                    @error('mobile_number')
                                        <span class="sf-field-note">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="sf-form-label" for="cf-subject">
                                        {{ translate('subject') }}
                                        <span class="sf-required" aria-hidden="true">*</span>
                                    </label>
                                    <input class="sf-filter-field @error('subject') is-invalid @enderror" type="text" id="cf-subject"
                                           name="subject" value="{{ old('subject') }}"
                                           placeholder="{{ translate('short_title') }}" required>
                                    @error('subject')
                                        <span class="sf-field-note">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="sf-form-full">
                                    <label class="sf-form-label" for="cf-message">
                                        {{ translate('message') }}
                                        <span class="sf-required" aria-hidden="true">*</span>
                                    </label>
                                    <textarea class="sf-filter-field @error('message') is-invalid @enderror" id="cf-message"
                                              name="message" rows="6"
                                              placeholder="{{ translate('tell_us_how_we_can_help_you') }}" required>{{ old('message') }}</textarea>
                                    @error('message')
                                        <span class="sf-field-note">{{ $message }}</span>
                                    @enderror
                                </div>

                                @if($isRecaptchaEnabled)
                                    <div class="sf-form-full dynamic-default-and-recaptcha-section">
                                        <input type="hidden" name="g-recaptcha-response" class="render-grecaptcha-response"
                                               data-action="contact"
                                               data-default-captcha="#contact-default-captcha-section">

                                        <div class="default-captcha-container d-none" id="contact-default-captcha-section"
                                             data-placeholder="{{ translate('enter_captcha_value') }}"
                                             data-base-url="{{ route('g-recaptcha-session-store') }}"
                                             data-session="default_captcha_value_contact">
                                        </div>
                                    </div>
                                @endif

                                <div class="sf-form-full">
                                    <button class="sf-btn-block" type="submit" id="contact-form-btn">{{ translate('send') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <aside style="display: flex; flex-direction: column; gap: 14px; min-width: 0;">

                        @if($storePhone)
                            <a class="sf-lift sf-contact-method" href="tel:{{ $storePhone }}">
                                <span class="sf-contact-method-icon">
                                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 1.9.6 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.8.6a2 2 0 0 1 1.7 2Z"></path></svg>
                                </span>
                                <span style="min-width: 0;">
                                    <span class="sf-contact-method-label">{{ translate('call_us') }}</span>
                                    <span class="sf-contact-method-value" style="direction: ltr; text-align: start; display: block;">{{ $storePhone }}</span>
                                </span>
                            </a>
                        @endif

                        @if($storeEmail)
                            <a class="sf-lift sf-contact-method" href="mailto:{{ $storeEmail }}">
                                <span class="sf-contact-method-icon">
                                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="4.5" width="19" height="15" rx="2.5"></rect><path d="m3 7 9 6 9-6"></path></svg>
                                </span>
                                <span style="min-width: 0;">
                                    <span class="sf-contact-method-label">{{ translate('email_us') }}</span>
                                    <span class="sf-contact-method-value">{{ $storeEmail }}</span>
                                </span>
                            </a>
                        @endif

                        @if($whatsappNumber)
                            <a class="sf-lift sf-contact-method" href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener">
                                <span class="sf-contact-method-icon">
                                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 11.6a8.2 8.2 0 0 1-8.8 8.2L7 21l1.3-3.4A8.2 8.2 0 1 1 20.5 11.6Z"></path><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5"></path></svg>
                                </span>
                                <span style="min-width: 0;">
                                    <span class="sf-contact-method-label">{{ translate('order_via_whatsapp') }}</span>
                                    <span class="sf-contact-method-value" style="direction: ltr; text-align: start; display: block;">{{ $whatsappNumber }}</span>
                                </span>
                            </a>
                        @endif

                        <div class="sf-contact-method">
                            <span class="sf-contact-method-icon">
                                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
                            </span>
                            <span style="min-width: 0;">
                                <span class="sf-contact-method-label">{{ translate('working_hours') }}</span>
                                <span class="sf-contact-method-value">{{ translate('daily_from_6_am_to_11_pm') }}</span>
                            </span>
                        </div>
                    </aside>
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

    @include('web-views.seafood.partials._phone-input-scripts')

    @if($isRecaptchaEnabled)
        @include('web-views.seafood.partials._recaptcha-scripts')
    @endif
@endpush
