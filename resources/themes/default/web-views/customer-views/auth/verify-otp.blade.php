@extends('web-views.seafood.auth-layout')

@section('title', translate('OTP_verification'))

@section('auth_heading', translate('OTP_verification'))

@section('auth_icon')
    <rect x="6" y="2.5" width="12" height="19" rx="2.5"></rect><path d="M10 18.5h4"></path>
@endsection

@section('auth_intro')
    {{ translate('We’ve sent a verification code to ') }} <bdi dir="ltr">{{ request('identity') }}</bdi>
    {{ translate('& your OTP will be expired within 2min.') }}
@endsection

@section('auth_content')
    <form method="post" action="{{ route('customer.auth.otp-verification') }}">
        @csrf
        @include('web-views.seafood.partials._otp-input', ['otpFieldName' => 'otp', 'otpCountdownSeconds' => $time_count])

        <button class="sf-btn-block" type="button" data-form-action="{{ route('customer.auth.otp-verification') }}" data-requires-code="1" style="margin-top: 20px;">
            {{ translate('verify') }}
        </button>

        <p class="sf-otp-resend-row">
            {{ translate("Didn't receive the code?") }}
            <button type="button" class="sf-text-link resend-otp-button sf-otp-resend-ajax"
                    data-identity="{{ request('identity') }}" data-url="{{ route('customer.auth.resend-otp-reset-password') }}">
                {{ translate('resend_OTP') }}
            </button>
        </p>
    </form>
@endsection
