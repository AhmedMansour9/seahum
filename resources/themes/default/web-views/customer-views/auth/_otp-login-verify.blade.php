@extends('web-views.seafood.auth-layout')

@section('title', translate('verify'))

@section('auth_heading', $userVerify == 0 ? translate('OTP_verification') : translate('verification_Successfully_Done!'))

@section('auth_icon')
    @if($userVerify == 0)
        <rect x="6" y="2.5" width="12" height="19" rx="2.5"></rect><path d="M10 18.5h4"></path>
    @else
        <circle cx="12" cy="12" r="9"></circle><path d="m8.2 12.4 2.6 2.6 5-5.4"></path>
    @endif
@endsection

@section('auth_intro')
    @if($userVerify == 0)
        {{ translate('we_have_sent_a_verification_code_to') }} <bdi dir="ltr">******{{ substr(base64_decode($identity), -4) }}</bdi>
    @else
        {{ translate('hello') }}, {{ $user->f_name }}
    @endif
@endsection

@section('auth_content')
    @if($userVerify == 0)
        @php
            $otpFlow = isset($otpFromType) ? base64_decode($otpFromType) : null;
            [$otpVerifyRoute, $otpResendRoute] = match ($otpFlow) {
                'social-login-verify' => [route('customer.auth.login.social.verify-account'), route('customer.auth.resend_otp')],
                'password-reset' => [route('customer.auth.verify-recover-password'), route('customer.auth.resend-otp-reset-password')],
                default => [route('customer.auth.login.verify-account.submit'), route('customer.auth.resend_otp')],
            };
        @endphp

        <form method="post" id="customer-otp-login-form" action="{{ $otpVerifyRoute }}">
            @csrf
            <input type="hidden" name="identity" value="{{ $identity }}">
            <input type="hidden" name="type" value="{{ request('type') }}">

            @include('web-views.seafood.partials._otp-input', ['otpFieldName' => 'token', 'otpCountdownSeconds' => $getTimeInSecond])

            @include('web-views.customer-views.auth.partials._firebase-recaptcha-container')

            <div class="sf-otp-actions">
                <button class="sf-btn-block" type="button" data-form-action="{{ $otpVerifyRoute }}" data-requires-code="1">{{ translate('verify') }}</button>
                <button class="sf-btn-outline resend-otp-button" type="button" data-form-action="{{ $otpResendRoute }}">{{ translate('resend_OTP') }}</button>
            </div>
        </form>
    @else
        <a href="{{ route('customer.auth.login') }}" class="sf-btn-block">{{ translate('sign_in') }}</a>
    @endif
@endsection
