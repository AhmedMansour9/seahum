{{--
    Six single-digit boxes folded into one hidden field ($otpFieldName) by _auth-scripts.
    $otpCountdownSeconds > 0 shows the resend countdown and keeps .resend-otp-button disabled until it ends.
--}}
@php
    $otpFieldName = $otpFieldName ?? 'token';
    $otpCountdownSeconds = (int) ($otpCountdownSeconds ?? 0);
@endphp

<div class="sf-otp" dir="ltr" role="group" aria-label="{{ translate('verification_code') }}">
    @for($otpIndex = 1; $otpIndex <= 6; $otpIndex++)
        <input class="sf-otp-field" type="text" inputmode="numeric" maxlength="1" autocomplete="{{ $otpIndex === 1 ? 'one-time-code' : 'off' }}"
               aria-label="{{ translate('digit') }} {{ $otpIndex }}">
    @endfor
</div>
<input class="sf-otp-value" type="hidden" name="{{ $otpFieldName }}" required>

<p class="sf-otp-countdown" data-second="{{ $otpCountdownSeconds }}" @if($otpCountdownSeconds <= 0) hidden @endif>
    {{ translate('resend_code_within') }} <strong class="sf-otp-countdown-value" dir="ltr">0:00</strong>
</p>
