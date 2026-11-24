<span id="sf-checkout-config" hidden
      data-update-quantity-url="{{ route('cart.updateQuantity') }}"
      data-remove-url="{{ route('cart.remove') }}"
      data-select-items-url="{{ route('cart.select-cart-items') }}"
      data-set-shipping-url="{{ route('customer.set-shipping-method') }}"
      data-coupon-apply-url="{{ route('coupon.apply') }}"
      data-order-note-url="{{ route('order_note') }}"
      data-choose-address-url="{{ route('customer.choose-shipping-address-other') }}"
      data-checkout-details-url="{{ route('checkout-details') }}"
      data-message-minimum-quantity="{{ translate('minimum_order_quantity_cannot_be_less_than_') }}"
      data-message-item-removed="{{ translate('item_has_been_removed_from_cart') }}"
      data-message-coupon-required="{{ translate('please_provide_coupon_code') }}"
      data-message-something-wrong="{{ translate('something_went_wrong') }}"
      data-message-pick-location="{{ translate('please_pick_your_delivery_location_on_the_map') }}"></span>

<script>
    "use strict";

    const seafoodCheckoutConfig = $('#sf-checkout-config');

    function seafoodCheckoutUrl(key) {
        return seafoodCheckoutConfig.data(key);
    }

    function setSeafoodCheckoutBusy(isBusy) {
        $('#cart-summary, .sf-checkout-page').attr('aria-busy', isBusy ? 'true' : 'false').toggleClass('is-busy', isBusy);
    }

    function renderSeafoodCartSummary(html) {
        $('#cart-summary').html(html);
    }

    function showSeafoodCheckoutErrors(response) {
        const messages = [].concat(response?.message || response?.messages || response?.errors || seafoodCheckoutUrl('message-something-wrong'));
        messages.forEach(function (message) {
            toastr.error(typeof message === 'object' ? message.message : message);
        });
    }

    /* ---- Cart: quantity, removal and selection ---- */
    function removeSeafoodCartItem(cartId) {
        $.post({
            url: seafoodCheckoutUrl('remove-url'),
            data: {key: cartId},
            beforeSend: function () { setSeafoodCheckoutBusy(true); },
            success: function (response) {
                toastr.info(seafoodCheckoutUrl('message-item-removed'));
                $('.seafood-cart-count').text(response.cart_items_count);
                if (!$('#cart-summary').length) {
                    location.reload();
                    return;
                }
                renderSeafoodCartSummary(response.data);
            },
            complete: function () { setSeafoodCheckoutBusy(false); },
        });
    }

    function updateSeafoodCartQuantity(input, quantity) {
        const cartId = input.data('cart-id');
        $.post({
            url: seafoodCheckoutUrl('update-quantity-url'),
            data: {key: cartId, quantity: quantity},
            beforeSend: function () { setSeafoodCheckoutBusy(true); },
            success: function (response) {
                // Success returns the re-rendered cart as a bare JSON string; failures return {status: 0, message, qty}.
                if (typeof response === 'string') {
                    renderSeafoodCartSummary(response);
                    return;
                }
                toastr.error(response.message);
                input.val(response.qty ?? input.data('quantity'));
            },
            complete: function () { setSeafoodCheckoutBusy(false); },
        });
    }

    function requestSeafoodCartQuantity(input, quantity) {
        const minimumQuantity = parseInt(input.data('min'), 10) || 1;
        if (quantity < minimumQuantity) {
            toastr.error(seafoodCheckoutUrl('message-minimum-quantity') + minimumQuantity);
            input.val(input.data('quantity'));
            return;
        }
        updateSeafoodCartQuantity(input, quantity);
    }

    $(document).on('click', '[data-cart-qty-step]', function () {
        const input = $(this).siblings('.sf-qty-input');
        const currentQuantity = parseInt(input.data('quantity'), 10);
        const step = parseInt($(this).data('cart-qty-step'), 10);

        if ($(this).hasClass('is-remove')) {
            removeSeafoodCartItem(input.data('cart-id'));
            return;
        }
        requestSeafoodCartQuantity(input, currentQuantity + step);
    });

    $(document).on('input', '.sf-qty-input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $(document).on('change', '.sf-qty-input', function () {
        const input = $(this);
        const quantity = parseInt(input.val(), 10);
        if (isNaN(quantity) || quantity === parseInt(input.data('quantity'), 10)) {
            input.val(input.data('quantity'));
            return;
        }
        requestSeafoodCartQuantity(input, quantity);
    });

    $(document).on('click', '.sf-cart-remove', function () {
        removeSeafoodCartItem($(this).data('cart-id'));
    });

    function selectSeafoodCartItems() {
        const checkedIds = $('.sf-cart-item-check:checked').map(function () { return this.value; }).get();
        $.post({
            url: seafoodCheckoutUrl('select-items-url'),
            data: {ids: checkedIds},
            beforeSend: function () { setSeafoodCheckoutBusy(true); },
            success: function (response) {
                renderSeafoodCartSummary(response.htmlView);
            },
            complete: function () { setSeafoodCheckoutBusy(false); },
        });
    }

    $(document).on('change', '.sf-cart-group-check', function () {
        $(this).closest('.sf-cart-group').find('.sf-cart-item-check').prop('checked', this.checked);
        selectSeafoodCartItems();
    });

    $(document).on('change', '.sf-cart-item-check', selectSeafoodCartItems);

    $(document).on('change', '.sf-shipping-method-select', function () {
        if (!this.value) {
            return;
        }
        $.get({
            url: seafoodCheckoutUrl('set-shipping-url'),
            dataType: 'json',
            data: {id: this.value, cart_group_id: $(this).data('cart-group')},
            beforeSend: function () { setSeafoodCheckoutBusy(true); },
            success: function () { location.reload(); },
            error: function () { setSeafoodCheckoutBusy(false); },
        });
    });

    /* ---- Order summary: coupon ---- */
    $(document).on('submit', '#sf-coupon-form', function (event) {
        event.preventDefault();
        const codeInput = $('#sf-coupon-code');
        if (!codeInput.val().trim()) {
            codeInput.addClass('is-invalid').trigger('focus');
            toastr.error(seafoodCheckoutUrl('message-coupon-required'));
            return;
        }
        codeInput.removeClass('is-invalid');
        $.post({
            url: seafoodCheckoutUrl('coupon-apply-url'),
            data: {code: codeInput.val().trim()},
            beforeSend: function () { setSeafoodCheckoutBusy(true); },
            success: function (response) {
                response.status ? toastr.success(response.messages) : toastr.error(response.messages);
                setTimeout(function () { location.reload(); }, 1200);
            },
            error: function () { setSeafoodCheckoutBusy(false); },
        });
    });

    /* ---- Proceed buttons: one per checkout step ---- */
    function proceedSeafoodFromCart() {
        $.post({
            url: seafoodCheckoutUrl('order-note-url'),
            data: {order_note: $('#order_note').val()},
            beforeSend: function () { setSeafoodCheckoutBusy(true); },
            success: function (response) {
                if (response.status === 0) {
                    [].concat(response.message).forEach(function (message) { toastr.error(message); });
                    setSeafoodCheckoutBusy(false);
                    return;
                }
                location.href = response.redirect ? response.redirect : seafoodCheckoutUrl('checkout-details-url');
            },
            error: function () { setSeafoodCheckoutBusy(false); },
        });
    }

    function areSeafoodRequiredFieldsFilled(formElement) {
        if (!formElement) {
            return true;
        }
        let isComplete = true;
        formElement.querySelectorAll('[required]').forEach(function (field) {
            // Hidden fields (the map-picked address) flag the visible box that fills them.
            const invalidTarget = field.dataset.invalidTarget ? $(field.dataset.invalidTarget) : $(field);
            if (!field.value.trim()) {
                isComplete = false;
                invalidTarget.addClass('is-invalid');
            } else {
                invalidTarget.removeClass('is-invalid');
            }
        });
        return isComplete;
    }

    function focusFirstInvalidSeafoodField() {
        const firstInvalidField = $('.sf-checkout-main .is-invalid').first();
        if (firstInvalidField.length) {
            firstInvalidField[0].scrollIntoView({behavior: 'smooth', block: 'center'});
            firstInvalidField.trigger('focus');
        }
        if (firstInvalidField.is('#sf-location-picker')) {
            toastr.warning(seafoodCheckoutUrl('message-pick-location'));
        }
    }

    /* Saves the delivery address to the session; resolves only when the server accepted it. */
    function saveSeafoodCheckoutAddress() {
        const hasPhysicalProduct = $('#physical_product').val() === 'yes';
        const sameAsShipping = hasPhysicalProduct;
        const createAccountToggle = $('#is_check_create_account');

        let isComplete = areSeafoodRequiredFieldsFilled(document.getElementById('address-form'));
        if (!sameAsShipping) {
            isComplete = areSeafoodRequiredFieldsFilled(document.getElementById('billing-address-form')) && isComplete;
        }
        if (!isComplete) {
            focusFirstInvalidSeafoodField();
            return $.Deferred().reject().promise();
        }

        const request = $.Deferred();
        $.post({
            url: seafoodCheckoutUrl('choose-address-url'),
            data: {
                physical_product: hasPhysicalProduct ? 'yes' : 'no',
                shipping: hasPhysicalProduct ? $('#address-form').serialize() : null,
                billing: $('#billing-address-form').serialize(),
                billing_addresss_same_shipping: sameAsShipping,
                is_check_create_account: createAccountToggle.prop('checked') ? 1 : 0,
                customer_password: $('#customer_password').val() ?? null,
                customer_confirm_password: $('#customer_confirm_password').val() ?? null,
            },
            beforeSend: function () { setSeafoodCheckoutBusy(true); },
            success: function (response) {
                if (response.errors) {
                    showSeafoodCheckoutErrors(response);
                    setSeafoodCheckoutBusy(false);
                    request.reject();
                    return;
                }
                request.resolve();
            },
            error: function (response) {
                showSeafoodCheckoutErrors(response.responseJSON);
                setSeafoodCheckoutBusy(false);
                request.reject();
            },
        });
        return request.promise();
    }

    /* ---- Payment choice ---- */
    function getSelectedSeafoodPaymentMethod() {
        return $('input[name="sf_payment_method"]:checked').val();
    }

    function updateSeafoodPaymentState() {
        const selectedMethod = getSelectedSeafoodPaymentMethod();
        const hasAcceptedTerms = $('.sf-payment-terms').length === $('.sf-payment-terms:checked').length;
        const hasPaymentChoice = $('input[name="sf_payment_method"]').length > 0;

        $('.sf-payment-extra').each(function () {
            $(this).prop('hidden', $(this).data('payment-extra') !== selectedMethod);
        });
        if (hasPaymentChoice || $('.sf-payment-terms').length) {
            $('.sf-checkout-proceed[data-step="checkout"], .sf-checkout-proceed[data-step="payment"]')
                .prop('disabled', !(hasAcceptedTerms && selectedMethod));
        }
    }

    $(document).on('change', 'input[name="sf_payment_method"]', updateSeafoodPaymentState);

    $(document).on('change', '.sf-payment-terms', function () {
        $('.sf-payment-terms').prop('checked', this.checked);
        updateSeafoodPaymentState();
    });

    function openSeafoodOfflinePayment(methodId) {
        $('#pay_offline_method').val(methodId);
        $.get({
            url: $('#route-pay-offline-method-list').data('url') + '?method_id=' + encodeURIComponent(methodId),
            success: function (response) {
                $('#payment_method_field').html(response.methodHtml);
                openSeafoodModal('sf-offline-payment-modal');
            },
            error: function () {
                toastr.error(seafoodCheckoutUrl('message-something-wrong'));
            },
        });
    }

    $(document).on('click', '.sf-offline-method', function () {
        $('.sf-offline-method').removeClass('is-active').attr('aria-pressed', 'false');
        $(this).addClass('is-active').attr('aria-pressed', 'true');
        $('#pay_offline_method').val($(this).data('method-id'));
    });

    $(document).on('change', '#pay_offline_method', function () {
        openSeafoodOfflinePayment(this.value);
    });

    function submitSeafoodSelectedPayment() {
        const selectedMethod = getSelectedSeafoodPaymentMethod();
        if (selectedMethod === 'pay_offline') {
            setSeafoodCheckoutBusy(false);
            const chosenOfflineMethod = $('.sf-offline-method.is-active').data('method-id') ?? $('.sf-offline-method').first().data('method-id');
            openSeafoodOfflinePayment(chosenOfflineMethod);
            return;
        }
        const paymentForm = $('#' + selectedMethod + '_form');
        if (!paymentForm.length) {
            setSeafoodCheckoutBusy(false);
            return;
        }
        $('.sf-checkout-proceed').prop('disabled', true);
        setSeafoodCheckoutBusy(true);
        paymentForm.trigger('submit');
    }

    function placeSeafoodOrderFromCheckout() {
        if (!getSelectedSeafoodPaymentMethod()) {
            document.getElementById('sf-payment-methods')?.scrollIntoView({behavior: 'smooth', block: 'center'});
            return;
        }
        saveSeafoodCheckoutAddress().done(submitSeafoodSelectedPayment);
    }

    $(document).on('click', '.sf-checkout-proceed', function () {
        const step = $(this).data('step');
        if (step === 'cart') {
            proceedSeafoodFromCart();
        } else if (step === 'checkout') {
            placeSeafoodOrderFromCheckout();
        } else if (step === 'payment') {
            submitSeafoodSelectedPayment();
        }
    });

    updateSeafoodPaymentState();
</script>
