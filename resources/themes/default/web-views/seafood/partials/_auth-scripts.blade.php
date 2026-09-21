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
    /* ---- One-time code boxes ---- */
    function syncSeafoodOtpValue(group) {
        const digits = group.find('.sf-otp-field').map(function () { return this.value; }).get().join('');
        group.nextAll('.sf-otp-value').first().val(digits);
    }

    $(document).on('input', '.sf-otp-field', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(-1);
        if (this.value) {
            $(this).next('.sf-otp-field').trigger('focus');
        }
        syncSeafoodOtpValue($(this).closest('.sf-otp'));
    });

    $(document).on('keydown', '.sf-otp-field', function (event) {
        if (event.key === 'Backspace' && !this.value) {
            $(this).prev('.sf-otp-field').val('').trigger('focus');
            syncSeafoodOtpValue($(this).closest('.sf-otp'));
        } else if (event.key === 'ArrowLeft') {
            $(this).prev('.sf-otp-field').trigger('focus');
        } else if (event.key === 'ArrowRight') {
            $(this).next('.sf-otp-field').trigger('focus');
        }
    });

    $(document).on('paste', '.sf-otp-field', function (event) {
        const pasted = (event.originalEvent.clipboardData.getData('text') || '').replace(/[^0-9]/g, '').slice(0, 6);
        if (!pasted) {
            return;
        }
        event.preventDefault();
        const fields = $(this).closest('.sf-otp').find('.sf-otp-field');
        pasted.split('').forEach((digit, index) => fields.eq(index).val(digit));
        fields.eq(Math.min(pasted.length, 5)).trigger('focus');
        syncSeafoodOtpValue($(this).closest('.sf-otp'));
    });

    let seafoodOtpCountdownTimer = null;

    function startSeafoodOtpCountdown(seconds) {
        const countdown = $('.sf-otp-countdown');
        clearInterval(seafoodOtpCountdownTimer);
        let remaining = parseInt(seconds, 10) || 0;

        const render = () => {
            countdown.find('.sf-otp-countdown-value').text(Math.floor(remaining / 60) + ':' + String(remaining % 60).padStart(2, '0'));
            $('.resend-otp-button').prop('disabled', remaining > 0);
            countdown.prop('hidden', remaining <= 0);
        };

        render();
        seafoodOtpCountdownTimer = setInterval(() => {
            remaining = Math.max(0, remaining - 1);
            render();
            if (remaining === 0) {
                clearInterval(seafoodOtpCountdownTimer);
            }
        }, 1000);
    }

    if ($('.sf-otp-countdown').length) {
        startSeafoodOtpCountdown($('.sf-otp-countdown').data('second'));
        $('.sf-otp-field').first().trigger('focus');
    }

    /* Verify and resend share one form; each button points it at its own route before submitting. */
    $(document).on('click', '[data-form-action]', function () {
        const form = $(this).closest('form');
        const otpValue = form.find('.sf-otp-value');
        if ($(this).data('requires-code') && otpValue.length && otpValue.val().length < 6) {
            form.find('.sf-otp-field').addClass('is-invalid');
            toastr.error(@json(translate('please_enter_the_full_code')));
            return;
        }
        if (!$(this).data('requires-code')) {
            otpValue.prop('required', false);
        }
        form.attr('action', $(this).data('form-action'))[0].submit();
    });

    $(document).on('click', '.sf-otp-resend-ajax', function () {
        const button = $(this);
        $('.sf-otp-field').val('');
        $('.sf-otp-value').val('');
        $.post({
            url: button.data('url'),
            data: {identity: button.data('identity')},
            dataType: 'json',
            beforeSend: () => button.prop('disabled', true),
            success: function (response) {
                if (response.status == 1) {
                    toastr.success(response.message);
                    startSeafoodOtpCountdown(response.new_time);
                } else {
                    toastr.error(response.message);
                    button.prop('disabled', false);
                }
            },
            error: () => button.prop('disabled', false),
        });
    });
</script>
