@php($recaptchaConfig = $recaptchaConfig ?? getWebConfig(name: 'recaptcha'))

<span id="get-google-recaptcha-key"
      data-value="{{ isset($recaptchaConfig) && $recaptchaConfig['status'] == 1 ? $recaptchaConfig['site_key'] : '' }}"></span>
<span id="route-get-session-recaptcha-code"
      data-route="{{ route('get-session-recaptcha-code') }}"
      data-mode="{{ config('app.mode') }}"></span>

<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/google-recaptcha/google-recaptcha-init.js') }}"></script>

@if(isset($recaptchaConfig) && $recaptchaConfig['status'] == 1)
    <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaConfig['site_key'] }}"></script>
@endif
