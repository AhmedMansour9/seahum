@extends('layouts.front-end.seafood-account')

@section('title', auth('customer')->user()->f_name.' '.auth('customer')->user()->l_name)

@section('page_title', translate('profile_Info'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
    @include('web-views.seafood.partials._phone-input-styles')
@endpush

@section('page_actions')
    <div class="sf-dropdown">
        <button type="button" class="sf-icon-btn" data-sf-dropdown-toggle="account-more-menu"
                aria-expanded="false" aria-haspopup="true" aria-label="{{ translate('more_options') }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.2h.01M12 12h.01M12 17.8h.01"></path></svg>
        </button>
        <div class="sf-dropdown-menu" id="account-more-menu" hidden>
            <a class="sf-dropdown-item sf-dropdown-item-danger call-route-alert" href="javascript:"
               data-route="{{ route('account-delete', [$customerDetail['id']]) }}"
               data-message="{{ translate('want_to_delete_this_account') }}?">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14M10 7V5h4v2M7 7l.8 12.2A1.8 1.8 0 0 0 9.6 21h4.8a1.8 1.8 0 0 0 1.8-1.8L17 7"></path></svg>
                {{ translate('delete_account') }}
            </a>
        </div>
    </div>
@endsection

@section('account_content')
    <form class="sf-panel" action="{{ route('user-update') }}" method="post" id="profile_form" enctype="multipart/form-data">
        @csrf

        <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; padding-bottom: clamp(20px, 3vw, 28px); border-bottom: 1px solid #EDF5F6; margin-bottom: clamp(20px, 3vw, 28px);">
            <label class="sf-avatar-upload" for="files">
                <img id="blah" alt="{{ translate('profile_photo') }}"
                     src="{{ getStorageImages(path: $customerDetail->image_full_url, type: 'avatar') }}">
                <span class="sf-avatar-upload-badge" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8.5h3l1.5-2.5h7L17 8.5h3v10H4z"></path><circle cx="12" cy="13" r="3"></circle></svg>
                </span>
                <input id="files" name="image" hidden type="file"
                       accept="{{ getFileUploadFormats(skip: '.svg,.gif') }}"
                       data-max-size="{{ getFileUploadMaxSize() }}">
                <span class="sr-only">{{ translate('change_profile_photo') }}</span>
            </label>
            <strong style="font-family: Cairo, sans-serif; font-size: 17px; font-weight: 700; color: #0B2C3F; text-align: center; overflow-wrap: anywhere;">
                {{ $customerDetail['f_name'].' '.$customerDetail['l_name'] }}
            </strong>
        </div>

        <div class="sf-form-grid">
            <div>
                <label class="sf-form-label" for="f_name">
                    {{ translate('first_name') }}<span class="sf-required">*</span>
                </label>
                <input type="text" class="sf-filter-field" id="f_name" name="f_name"
                       value="{{ $customerDetail['f_name'] }}"
                       placeholder="{{ translate('first_name') }}" required>
            </div>

            <div>
                <label class="sf-form-label" for="l_name">{{ translate('last_name') }}</label>
                <input type="text" class="sf-filter-field" id="l_name" name="l_name"
                       value="{{ $customerDetail['l_name'] }}"
                       placeholder="{{ translate('last_name') }}">
            </div>

            <div>
                <label class="sf-form-label" for="phone">
                    {{ translate('phone_number') }}<span class="sf-required">*</span>
                </label>
                <input class="sf-filter-field phone-input-with-country-picker" id="phone" type="tel" name="phone"
                       value="{{ $customerDetail['phone'] }}"
                       placeholder="{{ translate('enter_phone_number') }}" required
                       {{ $customerDetail['is_phone_verified'] ? 'disabled' : '' }}>
                @if($customerDetail['phone'] && getLoginConfig(key: 'phone_verification'))
                    @if($customerDetail['is_phone_verified'])
                        <span class="sf-field-hint" style="color: #1E8A5B;">{{ translate('Your_phone_is_verified') }}</span>
                    @else
                        <span class="sf-field-hint">{{ translate('Phone_not_verified.') }} {{ translate('Please_verify_through_the_user_app') }}</span>
                    @endif
                @endif
            </div>

            <div>
                <label class="sf-form-label" for="account-email">{{ translate('email') }}</label>
                <input type="email" class="sf-filter-field" id="account-email" name="email"
                       value="{{ $customerDetail['email'] }}"
                       placeholder="{{ translate('enter_email_address') }}">
                @if($customerDetail['email'] && getLoginConfig(key: 'email_verification'))
                    @if($customerDetail['is_email_verified'])
                        <span class="sf-field-hint" style="color: #1E8A5B;">{{ translate('Your_email_is_verified') }}</span>
                    @else
                        <span class="sf-field-hint">{{ translate('Email_not_verified.') }} {{ translate('Please_verify_through_the_user_app.') }}</span>
                    @endif
                @endif
            </div>

            <div>
                <label class="sf-form-label" for="password">{{ translate('new_password') }}</label>
                <span class="sf-input-group">
                    <input class="sf-filter-field" id="password" name="password" type="password"
                           autocomplete="new-password"
                           placeholder="{{ translate('minimum_8_characters_long') }}">
                    <button type="button" class="sf-input-action sf-password-toggle" data-target="password"
                            aria-pressed="false" aria-label="{{ translate('show_password') }}">
                        <svg class="sf-eye" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="sf-eye-off" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l16 16"></path><path d="M9.9 5.8A9.6 9.6 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a17 17 0 0 1-3.3 4"></path><path d="M6.5 8A17 17 0 0 0 2.5 12S6 18.5 12 18.5c1.4 0 2.6-.3 3.7-.8"></path></svg>
                    </button>
                </span>
            </div>

            <div>
                <label class="sf-form-label" for="confirm_password">{{ translate('confirm_password') }}</label>
                <span class="sf-input-group">
                    <input class="sf-filter-field" id="confirm_password" name="confirm_password" type="password"
                           autocomplete="new-password"
                           placeholder="{{ translate('minimum_8_characters_long') }}">
                    <button type="button" class="sf-input-action sf-password-toggle" data-target="confirm_password"
                            aria-pressed="false" aria-label="{{ translate('show_password') }}">
                        <svg class="sf-eye" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="sf-eye-off" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l16 16"></path><path d="M9.9 5.8A9.6 9.6 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a17 17 0 0 1-3.3 4"></path><path d="M6.5 8A17 17 0 0 0 2.5 12S6 18.5 12 18.5c1.4 0 2.6-.3 3.7-.8"></path></svg>
                    </button>
                </span>
                <span class="sf-field-note" id="message"></span>
            </div>

            <div class="sf-form-full" style="display: flex; justify-content: flex-end;">
                <button type="submit" class="sf-btn-block" style="width: auto; min-width: 200px;">
                    {{ translate('update') }}
                </button>
            </div>
        </div>
    </form>
@endsection

@push('script')
    @include('web-views.seafood.partials._phone-input-scripts')

    <script>
        "use strict";

        $(document).on('change', '#files', function () {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = function (event) {
                $('#blah').attr('src', event.target.result);
            };
            reader.readAsDataURL(file);
        });

        $(document).on('input', '#password, #confirm_password', function () {
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            $('#message').text(confirmPassword && password !== confirmPassword ? '{{ translate('password_does_not_match') }}' : '');
        });
    </script>
@endpush
