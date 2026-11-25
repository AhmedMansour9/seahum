<script>
    "use strict";

    $.ajaxSetup({headers: {"X-CSRF-TOKEN": $('meta[name="_token"]').attr("content")}});

    $(document).on('click', '#seafood-menu-toggle', function () {
        const menu = $('#seafood-mobile-menu');
        const willOpen = menu.attr('data-open') !== 'true';
        menu.attr('data-open', willOpen ? 'true' : 'false').prop('hidden', !willOpen);
        $(this).attr('aria-expanded', willOpen ? 'true' : 'false');
    });

    $(document).on('click', '#seafood-mobile-menu a', function () {
        $('#seafood-mobile-menu').attr('data-open', 'false').prop('hidden', true);
        $('#seafood-menu-toggle').attr('aria-expanded', 'false');
    });

    $(window).on('resize', function () {
        if (window.innerWidth >= 980) {
            $('#seafood-mobile-menu').attr('data-open', 'false').prop('hidden', true);
            $('#seafood-menu-toggle').attr('aria-expanded', 'false');
        }
    });

    function setSeafoodCardQuantity(productId, quantity) {
        $('.sf-card-stepper').filter(function () {
            return String($(this).data('product-id')) === String(productId);
        }).each(function () {
            const stepper = $(this);
            const minimumQuantity = parseInt(stepper.data('min'), 10) || 1;
            const decreaseButton = stepper.find('[data-card-qty-step="-1"]');
            stepper.find('.sf-card-stepper-value').text(quantity);
            stepper.toggleClass('is-at-min', quantity <= minimumQuantity);
            decreaseButton.attr('aria-label', quantity <= minimumQuantity ? decreaseButton.data('remove-label') : decreaseButton.data('decrease-label'));
        });
    }

    function setSeafoodCartToggleState(button, cartKey, quantity) {
        button.data('cart-key', cartKey || '');
        button.toggleClass('in-cart', !!cartKey);
        button.closest('.sf-product-card').toggleClass('is-in-cart', !!cartKey);
        const cartToggleLabel = cartKey ? button.data('remove-label') : button.data('add-label');
        button.find('.seafood-cart-toggle-label').text(cartToggleLabel);
        button.attr('title', cartToggleLabel);
        if (cartKey && quantity) {
            setSeafoodCardQuantity(button.data('product-id'), quantity);
        }
    }

    /* "Added to cart" sheet: keep shopping, or go straight to checkout. */
    function openSeafoodCartSheet(button) {
        const sheet = $('#sf-cart-added');
        const card = button.closest('.sf-product-card');
        if (!sheet.length || !card.length) {
            return;
        }
        sheet.find('.sf-cart-sheet-image').attr('src', card.find('.sf-product-media').attr('src'));
        sheet.find('.sf-cart-sheet-name').text(card.find('.sf-product-title').text().trim());
        sheet.find('.sf-cart-sheet-price').html(card.find('.sf-product-price').html());
        const dialog = sheet.find('.sf-cart-sheet-dialog');
        dialog.removeClass('is-animated');
        openSeafoodModal('sf-cart-added');
        void dialog[0].offsetWidth; // restarts the check animation on every add
        dialog.addClass('is-animated');
        sheet.find('.sf-cart-sheet-checkout').trigger('focus');
    }

    $('.sf-card-stepper').each(function () {
        const stepper = $(this);
        setSeafoodCardQuantity(stepper.data('product-id'), parseInt(stepper.find('.sf-card-stepper-value').text(), 10) || 1);
    });

    $(document).on('click', '[data-card-qty-step]', function () {
        const stepper = $(this).closest('.sf-card-stepper');
        const toggle = stepper.closest('.sf-product-card').find('.seafood-cart-toggle');
        const cartKey = toggle.data('cart-key');
        if (!cartKey || stepper.hasClass('is-busy')) {
            return;
        }
        const minimumQuantity = parseInt(stepper.data('min'), 10) || 1;
        const currentQuantity = parseInt(stepper.find('.sf-card-stepper-value').text(), 10) || minimumQuantity;
        const nextQuantity = currentQuantity + parseInt($(this).data('card-qty-step'), 10);

        if (nextQuantity < minimumQuantity) {
            toggle.trigger('click');
            return;
        }

        stepper.addClass('is-busy');
        $.post({
            url: $('#sf-cart-added').data('update-url'),
            data: {key: cartKey, quantity: nextQuantity},
            success: function (response) {
                if (response.status === 0) {
                    toastr.warning(response.message);
                    return;
                }
                setSeafoodCardQuantity(stepper.data('product-id'), nextQuantity);
            },
            error: function () {
                toastr.error($('#sf-cart-added').data('error-message'));
            },
            complete: function () {
                stepper.removeClass('is-busy');
            },
        });
    });

    $(document).on('click', '.seafood-cart-toggle', function () {
        const button = $(this);
        const cartKey = button.data('cart-key');

        if (cartKey) {
            $.post({
                url: $('#route-cart-remove').data('url'),
                data: {key: cartKey},
                success: function (response) {
                    toastr.success(response.message);
                    $('.seafood-cart-count').text(response.cart_items_count);
                    $('.seafood-cart-toggle').filter(function () {
                        return String($(this).data('product-id')) === String(button.data('product-id'));
                    }).each(function () {
                        setSeafoodCartToggleState($(this), null);
                    });
                }
            });
            return;
        }

        $.post({
            url: $('#route-cart-add').data('url'),
            data: {id: button.data('product-id'), quantity: button.data('quantity')},
            success: function (response) {
                if (response.status === 1) {
                    $('.seafood-cart-count').text(response.cart_items_count);
                    $('.seafood-cart-toggle').filter(function () {
                        return String($(this).data('product-id')) === String(button.data('product-id'));
                    }).each(function () {
                        setSeafoodCartToggleState($(this), response.in_cart_key || (response.cart && response.cart.id), parseInt(button.data('quantity'), 10) || 1);
                    });
                    openSeafoodCartSheet(button);
                } else if (response.status === 2) {
                    location.href = button.data('product-url');
                } else {
                    toastr.warning(response.message);
                }
            }
        });
    });

    function setSeafoodWishlistState(button, isWishlisted) {
        button.attr('data-wishlisted', isWishlisted ? 'true' : 'false');
        button.attr('aria-label', isWishlisted ? button.data('remove-label') : button.data('add-label'));
        button.find('svg').attr('fill', isWishlisted ? '#D92F45' : 'none').attr('stroke', isWishlisted ? '#D92F45' : '#082B49');
    }

    function setSeafoodWishlistCount(count) {
        if (typeof count !== 'undefined') {
            $('.seafood-wishlist-count').text(count);
        }
    }

    /* App promo ticket: copies the welcome code and flips the button to "copied" for a moment. */
    function copySeafoodTextWithSelection(text) {
        const field = $('<textarea readonly class="sr-only"></textarea>').val(text).appendTo('body');
        field[0].select();
        document.execCommand('copy');
        field.remove();
    }

    function copySeafoodTextToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text).catch(function () {
                copySeafoodTextWithSelection(text);
            });
        }
        copySeafoodTextWithSelection(text);
        return Promise.resolve();
    }

    $(document).on('click', '[data-promo-code]', function () {
        const button = $(this);
        const hint = button.find('.sf-promo-code-hint');
        hint.data('copy-label', hint.data('copy-label') || hint.text());
        copySeafoodTextToClipboard(String(button.data('promo-code'))).then(function () {
            button.addClass('is-copied');
            hint.text(hint.data('copied-label'));
            toastr.success(button.data('copied-message'));
            clearTimeout(button.data('copied-timer'));
            button.data('copied-timer', setTimeout(function () {
                button.removeClass('is-copied');
                hint.text(hint.data('copy-label'));
            }, 2200));
        });
    });

    $(document).on('click', '.seafood-wishlist-btn', function () {
        if ($('#is-customer-auth-active').data('value') !== 1) {
            location.href = $('#route-customer-login').data('url');
            return;
        }
        const button = $(this);
        const productId = button.data('product-id');
        $.post({
            url: $('#route-store-wishlist').data('url'),
            data: {product_id: productId},
            success: function (response) {
                if (response.value === 0) {
                    toastr.warning(response.error);
                    return;
                }

                const isWishlisted = response.value === 1;
                $('.seafood-wishlist-btn').filter(function () {
                    return String($(this).data('product-id')) === String(productId);
                }).each(function () {
                    setSeafoodWishlistState($(this), isWishlisted);
                });
                $('.wishlist_icon_' + productId).attr('fill', isWishlisted ? '#D92F45' : 'none');
                $('.countWishlist-' + productId).text(response.product_count);
                setSeafoodWishlistCount(response.count);
                toastr.success(isWishlisted ? response.success : response.error);
            }
        });
    });

    $(document).on('click', '.seafood-change-language', function () {
        $.post({
            url: $('#route-change-language').data('url'),
            data: {language_code: $(this).data('language-code')},
            success: function () {
                location.reload();
            }
        });
    });

    /* ---- Password reveal (shared by the auth and account forms) ---- */
    $(document).on('click', '.sf-password-toggle', function () {
        const button = $(this);
        const field = $('#' + button.data('target'));
        const willReveal = field.attr('type') === 'password';

        field.attr('type', willReveal ? 'text' : 'password');
        button.attr('aria-pressed', willReveal ? 'true' : 'false');
        button.find('.sf-password-show').toggle(!willReveal);
        button.find('.sf-password-hide').toggle(willReveal);
        button.attr('aria-label', willReveal ? button.data('hide-label') || button.attr('aria-label') : button.data('show-label') || button.attr('aria-label'));
    });

    /* ---- Account nav drawer ---- */
    function setSeafoodAccountNavState(isOpen) {
        $('#sf-account-nav').attr('data-open', isOpen ? 'true' : 'false');
        $('#sf-account-nav-backdrop').attr('data-open', isOpen ? 'true' : 'false').prop('hidden', !isOpen);
        $('#sf-account-nav-toggle').attr('aria-expanded', isOpen ? 'true' : 'false');
        $('body').css('overflow', isOpen ? 'hidden' : '');
    }

    $(document).on('click', '#sf-account-nav-toggle', function () {
        setSeafoodAccountNavState($('#sf-account-nav').attr('data-open') !== 'true');
    });

    $(document).on('click', '#sf-account-nav-close, #sf-account-nav-backdrop', function () {
        setSeafoodAccountNavState(false);
    });

    $(window).on('resize', function () {
        if (window.innerWidth >= 980) {
            setSeafoodAccountNavState(false);
        }
    });

    /* ---- Dropdowns ---- */
    $(document).on('click', '[data-sf-dropdown-toggle]', function (event) {
        event.stopPropagation();
        const menu = $('#' + $(this).attr('data-sf-dropdown-toggle'));
        const willOpen = menu.prop('hidden');
        $('.sf-dropdown-menu').prop('hidden', true);
        $('[data-sf-dropdown-toggle]').attr('aria-expanded', 'false');
        menu.prop('hidden', !willOpen);
        $(this).attr('aria-expanded', willOpen ? 'true' : 'false');
    });

    $(document).on('click', function () {
        $('.sf-dropdown-menu').prop('hidden', true);
        $('[data-sf-dropdown-toggle]').attr('aria-expanded', 'false');
    });

    $(document).on('click', '.sf-dropdown-menu', function (event) {
        event.stopPropagation();
    });

    /* ---- Copy to clipboard ---- */
    function copySeafoodText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        // Plain-http dev hosts have no async clipboard, so fall back to a selected textarea.
        const helper = $('<textarea readonly style="position: fixed; opacity: 0;"></textarea>').val(text).appendTo('body');
        helper[0].select();
        const isCopied = document.execCommand('copy');
        helper.remove();
        return isCopied ? Promise.resolve() : Promise.reject();
    }

    $(document).on('click', '[data-sf-copy]', function () {
        copySeafoodText(String($(this).data('sf-copy')))
            .then(() => toastr.success(@json(translate('successfully_copied'))))
            .catch(() => toastr.error(@json(translate('copied_failed'))));
    });

    /* ---- Image picker: keeps an accumulated FileList on the input so plain form posts send every file ---- */
    function renderSeafoodImagePicker(picker, files) {
        const input = picker.find('.sf-image-picker-input')[0];
        const transfer = new DataTransfer();
        files.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;

        const list = picker.find('.sf-image-picker-list').empty();
        files.forEach((file, index) => {
            const item = $('<li class="sf-image-picker-item"><img alt=""><button type="button" class="sf-image-picker-remove"></button></li>');
            item.find('img').attr('src', URL.createObjectURL(file));
            item.find('button').attr({'data-index': index, 'aria-label': @json(translate('remove')) + ' ' + file.name})
                .html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>');
            list.append(item);
        });
        picker.find('.sf-image-picker-add').prop('hidden', files.length >= parseInt(picker.data('max-count'), 10));
        picker.data('files', files);
    }

    $(document).on('change', '.sf-image-picker-input', function () {
        const picker = $(this).closest('.sf-image-picker');
        const maxCount = parseInt(picker.data('max-count'), 10);
        const maxSizeMb = parseFloat($(this).data('max-size')) || 0;
        const files = (picker.data('files') || []).slice();

        Array.from(this.files).forEach((file) => {
            if (!file.type.startsWith('image/')) {
                toastr.error(@json(translate('input_png_or_jpg')));
            } else if (maxSizeMb && file.size > maxSizeMb * 1024 * 1024) {
                toastr.error(@json(translate('file_size_too_big')));
            } else if (files.length < maxCount && !files.some((existing) => existing.name === file.name && existing.size === file.size)) {
                files.push(file);
            }
        });
        renderSeafoodImagePicker(picker, files);
    });

    $(document).on('click', '.sf-image-picker-remove', function () {
        const picker = $(this).closest('.sf-image-picker');
        const files = (picker.data('files') || []).slice();
        files.splice(parseInt($(this).attr('data-index'), 10), 1);
        renderSeafoodImagePicker(picker, files);
    });

    /* ---- Quick chat modal (vendor / delivery man) ---- */
    $(document).on('submit', '.sf-chat-form', function (event) {
        event.preventDefault();
        const chatForm = $(this);
        const submitButton = chatForm.find('[type="submit"]').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: chatForm.attr('action'),
            data: chatForm.serialize(),
            success: function () {
                toastr.success(@json(translate('send_successfully')));
                chatForm.trigger('reset');
                closeSeafoodModal(chatForm.closest('.sf-modal'));
            },
            error: function () {
                toastr.error(@json(translate('something_went_wrong')));
            },
            complete: function () {
                submitButton.prop('disabled', false);
            },
        });
    });

    /* ---- Modals ---- */
    function openSeafoodModal(modalId) {
        $('#' + modalId).prop('hidden', false);
        $('body').css('overflow', 'hidden');
    }

    function closeSeafoodModal(modal) {
        $(modal).prop('hidden', true);
        $('body').css('overflow', '');
    }

    $(document).on('click', '[data-sf-modal-open]', function () {
        openSeafoodModal($(this).attr('data-sf-modal-open'));
    });

    $(document).on('click', '[data-sf-modal-close]', function () {
        closeSeafoodModal($(this).closest('.sf-modal'));
    });

    $(document).on('click', '.sf-modal', function (event) {
        if (event.target === this) {
            closeSeafoodModal(this);
        }
    });

    $(document).on('keydown', function (event) {
        if (event.key === 'Escape') {
            $('.sf-modal').each(function () {
                if (!$(this).prop('hidden')) {
                    closeSeafoodModal(this);
                }
            });
            setSeafoodAccountNavState(false);
        }
    });

    /* ---- Confirm dialog: replaces the SweetAlert route_alert of the legacy layout ---- */
    let seafoodConfirmRoute = null;

    function route_alert(route, message) {
        seafoodConfirmRoute = route;
        $('#sf-confirm-message').text(message || '');
        openSeafoodModal('sf-confirm-modal');
    }

    $(document).on('click', '.call-route-alert, .customer-account-delete-by-route', function (event) {
        event.preventDefault();
        setSeafoodAccountNavState(false);
        route_alert($(this).data('route'), $(this).data('message'));
    });

    $(document).on('click', '#sf-confirm-accept', function () {
        if (seafoodConfirmRoute) {
            location.href = seafoodConfirmRoute;
        }
    });
</script>

<div class="sf-modal" id="sf-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="sf-confirm-title" hidden>
    <div class="sf-modal-dialog" style="max-width: 420px;">
        <div class="sf-modal-head">
            <h2 class="sf-modal-title" id="sf-confirm-title">{{ translate('are_you_sure') }}?</h2>
            <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </div>
        <div class="sf-modal-body">
            <p id="sf-confirm-message" style="font-size: 14.5px; line-height: 1.85; color: var(--sh-muted); margin: 0 0 20px; overflow-wrap: anywhere;"></p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" class="sf-btn-sm sf-btn-sm-primary" id="sf-confirm-accept" style="flex: 1 1 120px;">{{ translate('yes') }}</button>
                <button type="button" class="sf-btn-sm" data-sf-modal-close style="flex: 1 1 120px;">{{ translate('no') }}</button>
            </div>
        </div>
    </div>
</div>

@php($cartSheetCheckoutUrl = ($web_config['guest_checkout_status'] ?? false) || auth('customer')->check() ? route('checkout-details') : route('customer.auth.login'))
<div class="sf-modal sf-cart-sheet" id="sf-cart-added" role="dialog" aria-modal="true" aria-labelledby="sf-cart-added-title" hidden
     data-update-url="{{ route('cart.updateQuantity.guest') }}" data-error-message="{{ translate('something_went_wrong') }}">
    <div class="sf-modal-dialog sf-cart-sheet-dialog">
        <span class="sf-cart-sheet-handle" aria-hidden="true"></span>
        <div class="sf-cart-sheet-status">
            <span class="sf-cart-sheet-check" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg>
            </span>
            <h2 class="sf-cart-sheet-title" id="sf-cart-added-title">{{ translate('added_to_your_cart') }}</h2>
            <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </div>
        <div class="sf-cart-sheet-product">
            <img class="sf-cart-sheet-image" src="data:," alt="">
            <div class="sf-cart-sheet-info">
                <strong class="sf-cart-sheet-name"></strong>
                <span class="sf-cart-sheet-price"></span>
            </div>
        </div>
        <p class="sf-cart-sheet-count">
            {{ translate('items_in_your_cart') }}: <strong class="seafood-cart-count"></strong>
        </p>
        <div class="sf-cart-sheet-actions">
            <a href="{{ $cartSheetCheckoutUrl }}" class="sf-btn-block sf-cart-sheet-checkout">
                {{ translate('checkout_now') }}
                <svg class="sf-cart-sheet-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 6 8 12l6 6"></path></svg>
            </a>
            <button type="button" class="sf-cart-sheet-continue" data-sf-modal-close>{{ translate('continue_shopping') }}</button>
        </div>
    </div>
</div>
