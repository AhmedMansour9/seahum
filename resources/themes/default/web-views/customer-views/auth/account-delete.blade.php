@extends('web-views.seafood.auth-layout')

@section('title', translate('delete_account'))

@section('auth_heading', translate('delete_account'))

@section('auth_icon')
    <path d="M5 7h14M10 7V5h4v2M7 7l.8 12.2A1.8 1.8 0 0 0 9.6 21h4.8a1.8 1.8 0 0 0 1.8-1.8L17 7"></path><path d="M10.5 11v6M13.5 11v6"></path>
@endsection

@section('auth_intro', translate('enter_your_email_and_password_to_confirm'))

@push('css_or_js')
    <style>
        .sf-account-delete-warning { display: flex; gap: 12px; align-items: flex-start; padding: 14px 16px; margin-bottom: 22px; border: 1px solid #F4CFCC; border-radius: 14px; background: #FDF0EF; color: #C63F38; font-size: 14px; line-height: 1.8; }
        .sf-account-delete-warning > svg { flex-shrink: 0; margin-top: 3px; }
        .sf-account-delete-warning p { margin: 0; overflow-wrap: anywhere; }
        .sf-btn-block.sf-btn-block-danger { background: #E4574F; box-shadow: 0 16px 32px -14px rgba(228,87,79,1); }
        .sf-btn-block.sf-btn-block-danger:hover { background: #C63F38; color: #FFFFFF; box-shadow: 0 20px 36px -14px rgba(228,87,79,1); }
        .sf-btn-block.sf-btn-block-danger:focus-visible { box-shadow: 0 0 0 3px #FFFFFF, 0 0 0 5px #C63F38; }
    </style>
@endpush

@section('auth_content')
    <form method="post" action="{{ route('account-delete-submit') }}" id="account-delete-form" novalidate>
        @csrf

        <div class="sf-account-delete-warning" role="note">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3.5 2.5 20h19z"></path><path d="M12 10v4.5M12 17.5h.01"></path></svg>
            <p>{{ translate('deleting_your_account_is_permanent_and_cannot_be_undone') }}</p>
        </div>

        <label class="sf-form-label" for="account-delete-email">{{ translate('email') }}<span class="sf-required">*</span></label>
        <input type="email" class="sf-filter-field" id="account-delete-email" name="email"
               value="{{ old('email') }}" autocomplete="email" maxlength="255" dir="ltr"
               placeholder="{{ translate('enter_email_address') }}" required
               @error('email') aria-invalid="true" aria-describedby="account-delete-email-error" @enderror>
        @error('email')
            <span class="sf-field-note" id="account-delete-email-error" role="alert">{{ $message }}</span>
        @enderror

        <label class="sf-form-label" for="account-delete-password" style="margin-top: 18px;">{{ translate('password') }}<span class="sf-required">*</span></label>
        <div class="sf-input-group">
            <input type="password" class="sf-filter-field" id="account-delete-password" name="password"
                   autocomplete="current-password" dir="ltr"
                   placeholder="{{ translate('enter_your_password') }}" required
                   @error('password') aria-invalid="true" aria-describedby="account-delete-password-error" @enderror>
            @include('web-views.seafood.partials._password-toggle', ['toggleTarget' => 'account-delete-password'])
        </div>
        @error('password')
            <span class="sf-field-note" id="account-delete-password-error" role="alert">{{ $message }}</span>
        @enderror
        <a href="{{ route('customer.auth.recover-password') }}" class="sf-field-hint" style="display: inline-block; margin-top: 8px; color: #0E8E88;">
            {{ translate('forgot_password') }}
        </a>

        <button type="submit" class="sf-btn-block sf-btn-block-danger" id="account-delete-submit" style="margin-top: 22px;">
            {{ translate('delete_my_account') }}
        </button>
    </form>
@endsection

@push('script')
    <script>
        "use strict";

        $('#account-delete-form').on('submit', function () {
            $('#account-delete-submit').prop('disabled', true);
        });
    </script>
@endpush
