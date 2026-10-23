@extends('web-views.seafood.auth-layout')

@section('title', translate('verify'))

@section('auth_heading', $user_verify == 0 ? translate('verify') : translate('verification_Successfully_Done!'))

@section('auth_icon')
    @if($user_verify == 0)
        <rect x="3" y="5" width="18" height="14" rx="3"></rect><path d="m4 7 8 6 8-6"></path>
    @else
        <circle cx="12" cy="12" r="9"></circle><path d="m8.2 12.4 2.6 2.6 5-5.4"></path>
    @endif
@endsection

@section('auth_intro')
    @if($user_verify == 0)
        @php($verificationType = base64_decode(request('type')))
        @if($verificationType == 'phone_verification')
            {{ translate('please_provide_OTP_sent_in_your_phone') }}
        @elseif($verificationType == 'email_verification')
            {{ translate('please_provide_verification_token_sent_in_your_email') }}
        @else
            {{ translate('verification_code') }} / {{ translate('OTP') }}
        @endif
    @else
        {{ translate('hello') }}, {{ $user->f_name }}
    @endif
@endsection

@section('auth_content')
    @if($user_verify == 0)
        <form method="post" id="sign-up-form" action="{{ route('customer.auth.verify') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $user->id }}">
            <input type="hidden" name="identity" value="{{ request('identity') }}">
            <input type="hidden" name="type" value="{{ request('type') }}">

            @include('web-views.seafood.partials._otp-input', ['otpFieldName' => 'token', 'otpCountdownSeconds' => $get_time])

            @include('web-views.customer-views.auth.partials._firebase-recaptcha-container')

            <div class="sf-otp-actions">
                <button class="sf-btn-block" type="button" data-form-action="{{ route('customer.auth.verify') }}" data-requires-code="1">{{ translate('verify') }}</button>
                <button class="sf-btn-outline resend-otp-button" type="button" data-form-action="{{ route('customer.auth.resend_otp') }}">{{ translate('resend_OTP') }}</button>
            </div>
        </form>
    @else
        <a href="{{ route('customer.auth.login') }}" class="sf-btn-block">{{ translate('sign_in') }}</a>
    @endif
@endsection
