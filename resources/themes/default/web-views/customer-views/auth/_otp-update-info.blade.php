@extends('web-views.seafood.auth-layout')

@section('title', translate('Update_Info'))

@section('auth_heading', translate('Update_Info'))

@section('auth_icon')
    <circle cx="12" cy="8" r="4"></circle><path d="M4.5 20.5a7.5 7.5 0 0 1 15 0"></path>
@endsection

@section('auth_intro', translate('just_one_step_away') . '! ' . translate('_this_will_help_make_your_profile_more_personalized'))

@section('auth_content')
    <form id="sign-up-form" method="post"
          action="{{ $updateType == 'social' ? route('customer.auth.social-login-confirmation.update') : route('customer.auth.login.update-info') }}">
        @csrf
        <input type="hidden" name="identity" value="{{ $identity }}">

        <label class="sf-form-label" for="user-name">{{ translate('Name') }}<span class="sf-required">*</span></label>
        <input class="sf-filter-field" type="text" name="name" id="user-name" required autocomplete="name"
               placeholder="{{ translate('Enter_your_name') }}" value="{{ request('fullName') ? base64_decode(request('fullName')) : '' }}">

        @if($updateType == 'otp')
            <label class="sf-form-label" for="user-email" style="margin-top: 18px;">{{ translate('Email') }}</label>
            <input class="sf-filter-field" type="email" name="email" id="user-email" autocomplete="email" dir="ltr"
                   placeholder="{{ translate('Enter_your_email') }}">
        @elseif($updateType == 'social')
            <label class="sf-form-label" for="user-phone" style="margin-top: 18px;">{{ translate('phone_number') }}<span class="sf-required">*</span></label>
            <input class="sf-filter-field phone-input-with-country-picker" type="tel" id="user-phone" name="phone" required
                   value="{{ old('phone') }}" placeholder="{{ translate('enter_phone_number') }}">
        @endif

        <div style="margin-top: 18px;">
            @include('web-views.customer-views.auth.partials._firebase-recaptcha-container')
        </div>

        <button type="submit" id="customerInfoUpdateBtn" class="sf-btn-block" style="margin-top: 22px;">{{ translate('Update') }}</button>
    </form>

    @if($updateType == 'social')
        @include('web-views.seafood.partials._social-user-confirm-modal')
    @endif
@endsection
