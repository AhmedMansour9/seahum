@php($recaptchaConfig = $recaptchaConfig ?? getWebConfig(name: 'recaptcha'))

<span id="get-google-recaptcha-key"
      data-value="{{ isset($recaptchaConfig) && $recaptchaConfig['status'] == 1 ? $recaptchaConfig['site_key'] : '' }}"></span>
<span id="route-get-session-recaptcha-code"
      data-route="{{ route('get-session-recaptcha-code') }}"
      data-mode="{{ config('app.mode') }}"></span>
@include('web-views.seafood.partials._phone-input-scripts')

<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/google-recaptcha/google-recaptcha-init.js') }}"></script>

@include('layouts.front-end.partials._firebase-script')

@if(isset($recaptchaConfig) && $recaptchaConfig['status'] == 1)
    <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaConfig['site_key'] }}"></script>
@endif

<script>
    "use strict";

    $(document).on('input', '.clean-phone-input-value', function () {
        $(this).val($(this).val().replace(/\s/g, ''));
    });

    function setSeafoodLoginMethod(loginType) {
        const isOtpLogin = loginType === 'otp-login';

        $('.manual-login-items').each(function () {
            this.style.display = isOtpLogin ? 'none' : ($(this).data('sf-display') || 'block');
        });
        $('.otp-login-items').each(function () {
            this.style.display = isOtpLogin ? ($(this).data('sf-display') || 'block') : 'none';
        });
        $('.otp-login-btn').each(function () {
            this.style.display = isOtpLogin ? 'none' : 'inline-flex';
        });
        $('.manual-login-btn').each(function () {
            this.style.display = isOtpLogin ? 'inline-flex' : 'none';
        });

        $('.phone-input-with-country-picker').attr('required', isOtpLogin);
        $('.auth-email-input').attr('required', !isOtpLogin);
        $('.auth-password-input').attr('required', !isOtpLogin);
        $('.auth-login-type-input').val(loginType);
    }

    $(document).on('click', '.otp-login-btn', function () {
        setSeafoodLoginMethod('otp-login');
    });

    $(document).on('click', '.manual-login-btn', function () {
        setSeafoodLoginMethod('manual-login');
    });

    function seafoodAuthResponseManager(response) {
        if (response.status === 'success' && response.message) {
            toastr.success(response.message);
        } else if (response.status === 'error' && response.message) {
            toastr.error(response.message);
        } else if (response.status === 'warning' && response.message) {
            toastr.warning(response.message);
        }

        if (response.errors) {
            response.errors.forEach(function (error) {
                toastr.error(error.message, {CloseButton: true, ProgressBar: true});
            });
        } else if (response.error) {
            toastr.error(response.error, {CloseButton: true, ProgressBar: true});
        }

        const redirectUrl = response.redirectRoute || response.redirect_url;
        if (response.status === 'success' && redirectUrl) {
            location.href = redirectUrl;
        } else if (response.reload && !redirectUrl) {
            location.reload();
        }
    }

    $(document).on('submit', '.customer-centralize-login-form', function (event) {
        event.preventDefault();

        const form = $(this);
        const submitButton = form.find('[type="submit"]:visible');

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
            beforeSend: function () {
                submitButton.prop('disabled', true);
            },
            success: function (response) {
                seafoodAuthResponseManager(response);
            },
            error: function (jqXHR) {
                seafoodAuthResponseManager(jqXHR.responseJSON ?? {
                    status: 'error',
                    message: '{{ translate('something_went_wrong') }}',
                });
            },
            complete: function () {
                submitButton.prop('disabled', false);
            },
        });
    });
</script>
