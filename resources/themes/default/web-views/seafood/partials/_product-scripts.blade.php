<script>
    "use strict";

    (function () {
        const detailsForm = $('.add-to-cart-details-form');
        const messages = $('#seafood-product-messages');
        let variantPriceRequest = null;

        /* ---------- gallery ---------- */

        $(document).on('click', '.sf-gallery-thumb', function () {
            const thumb = $(this);
            $('#seafood-gallery-image').attr('src', thumb.data('image'));
            $('.sf-gallery-thumb').removeClass('active');
            thumb.addClass('active');
        });

        $(document).on('change', 'input[name="color"]', function () {
            const matchingThumb = $('.sf-gallery-thumb').filter(function () {
                return String($(this).data('color')) === String($('input[name="color"]:checked').val());
            }).first();

            if (matchingThumb.length) {
                matchingThumb.trigger('click');
            }
        });

        /* ---------- tabs ---------- */

        $(document).on('click', '.sf-tab', function () {
            const tab = $(this);
            $('.sf-tab').removeClass('active').attr('aria-selected', 'false');
            tab.addClass('active').attr('aria-selected', 'true');
            $('.sf-tab-panel').prop('hidden', true);
            $('#' + tab.attr('aria-controls')).prop('hidden', false);
        });

        $(document).on('click', '.sf-jump-to-reviews', function (event) {
            event.preventDefault();
            $('#tab-reviews').trigger('click');
            $('#reviews')[0].scrollIntoView({behavior: 'smooth', block: 'start'});
        });

        /* ---------- collapsible description ---------- */

        const detailsBlock = $('#seafood-product-details');
        const detailsToggle = $('#seafood-details-toggle');

        if (detailsBlock.length && detailsToggle.length) {
            if (detailsBlock[0].scrollHeight <= detailsBlock[0].clientHeight + 8) {
                detailsBlock.addClass('expanded');
                detailsToggle.hide();
            }

            detailsToggle.on('click', function () {
                const expanded = detailsBlock.toggleClass('expanded').hasClass('expanded');
                $(this).text(expanded ? $(this).data('less') : $(this).data('more'));
            });
        }

        /* ---------- quantity ---------- */

        function quantityInput() {
            return detailsForm.find('.product-details-cart-qty');
        }

        function syncQuantityButtons() {
            const input = quantityInput();
            const value = parseInt(input.val(), 10) || 0;
            const min = parseInt(input.attr('min'), 10) || 1;
            const max = parseInt(input.attr('max'), 10) || 0;
            const isDigital = input.data('producttype') === 'digital';

            detailsForm.find(".btn-number[data-type='minus']").prop('disabled', value <= min);
            detailsForm.find(".btn-number[data-type='plus']").prop('disabled', !isDigital && value >= max);
        }

        $(document).on('click', '.btn-number', function () {
            const input = quantityInput();
            const current = parseInt(input.val(), 10) || 0;
            const min = parseInt(input.attr('min'), 10) || 1;
            const max = parseInt(input.attr('max'), 10) || 0;
            const isDigital = input.data('producttype') === 'digital';

            if ($(this).data('type') === 'minus') {
                if (current > min) {
                    input.val(current - 1).trigger('change');
                }
                return;
            }

            if (isDigital || current < max) {
                input.val(current + 1).trigger('change');
            } else {
                toastr.warning(messages.data('stock-limit'));
            }
        });

        $(document).on('change', '.product-details-cart-qty', function () {
            const input = $(this);
            const min = parseInt(input.attr('min'), 10) || 1;
            const max = parseInt(input.attr('max'), 10) || 0;
            const isDigital = input.data('producttype') === 'digital';
            let value = parseInt(input.val(), 10);

            if (isNaN(value) || value < min) {
                value = min;
                toastr.warning(messages.data('minimum-order') + ' ' + min);
            } else if (!isDigital && max > 0 && value > max) {
                value = max;
                toastr.warning(messages.data('stock-limit'));
            }

            input.val(value);
            syncQuantityButtons();
            getVariantPrice();
        });

        $(document).on('input', '.product-details-cart-qty', function () {
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
        });

        /* ---------- variant price ---------- */

        function allOptionsChosen() {
            const radioNames = {};
            detailsForm.find('input:radio').each(function () {
                radioNames[$(this).attr('name')] = true;
            });

            return detailsForm.find('input:radio:checked').length === Object.keys(radioNames).length;
        }

        function applyVariantPrice(response) {
            detailsForm.find('.product-details-chosen-price-amount .sf-price-amount').text(response?.price_digits);
            $('.sf-sticky-buy').find('.product-details-chosen-price-amount .sf-price-amount').text(response?.price_digits);
            detailsForm.find('.discounted-unit-price .sf-price-amount').text(response?.discounted_unit_price_digits);
            detailsForm.find('.product-total-unit-price')
                .prop('hidden', !(response?.discount_amount > 0))
                .find('.sf-price-amount').text(response?.total_unit_price_digits);
            detailsForm.find('.product-generated-variation-code').val(response?.variation_code);
            detailsForm.find('.product-exist-in-cart-list').val(response?.in_cart_key);

            const quantity = quantityInput();
            quantity.attr('max', response?.quantity).val(response?.in_cart_quantity);

            const addToCartButton = detailsForm.find('.product-add-to-cart-button');
            addToCartButton.text(response?.in_cart_status === 1 ? addToCartButton.data('update') : addToCartButton.data('add'));

            if (response?.product_type?.toString() === 'physical') {
                const restockButton = detailsForm.find('.product-restock-request-button');
                const isOutOfStock = response?.quantity <= 0;

                detailsForm.find('.product-restock-request-section').css('display', isOutOfStock ? 'block' : 'none');
                detailsForm.find('.product-add-and-buy-section').css('display', isOutOfStock ? 'none' : 'flex');
                $('.sf-sticky-buy').css('display', '');

                if (response?.restock_request_status) {
                    restockButton.text(restockButton.data('requested')).prop('disabled', true);
                } else {
                    restockButton.text(restockButton.data('default')).prop('disabled', false);
                }
            }

            syncQuantityButtons();
        }

        function getVariantPrice() {
            if (!allOptionsChosen() || !(parseInt(quantityInput().val(), 10) > 0)) {
                return;
            }

            if (variantPriceRequest) {
                variantPriceRequest.abort();
            }

            variantPriceRequest = $.ajax({
                type: 'POST',
                url: $('#route-cart-variant-price').data('url'),
                data: detailsForm.serializeArray(),
                success: applyVariantPrice,
                complete: function () {
                    variantPriceRequest = null;
                },
            });
        }

        $(document).on('change', '.add-to-cart-details-form input:radio', function () {
            getVariantPrice();
        });

        /* ---------- add to cart ---------- */

        function addToCart(redirectToCheckout, checkoutUrl) {
            if (!allOptionsChosen()) {
                toastr.warning(messages.data('choose-options'));
                return;
            }

            const existingCartKey = detailsForm.find('.product-exist-in-cart-list').val();
            const url = (existingCartKey && !redirectToCheckout)
                ? $('#route-cart-updateQuantity-guest').data('url')
                : $('#route-cart-add').data('url');

            $.post({
                url: url,
                data: detailsForm.serializeArray().concat({name: 'buy_now', value: redirectToCheckout ? 1 : 0}),
                success: function (response) {
                    if (response.status === 1) {
                        toastr.success(response.message);
                        $('.seafood-cart-count').text(response.cart_items_count ?? $('.seafood-cart-count').text());
                        detailsForm.find('.product-exist-in-cart-list').val(response.in_cart_key);

                        const addToCartButton = detailsForm.find('.product-add-to-cart-button');
                        addToCartButton.text(addToCartButton.data('update'));

                        if (redirectToCheckout) {
                            location.href = response.redirect_to_url || checkoutUrl;
                        }
                        return;
                    }

                    toastr.warning(response.message);
                },
            });
        }

        $(document).on('click', '.product-add-to-cart-button', function () {
            addToCart(false, null);
        });

        $(document).on('click', '.sf-sticky-add-to-cart', function () {
            detailsForm.find('.product-add-to-cart-button').trigger('click');
        });

        $(document).on('click', '.product-buy-now-button', function () {
            const button = $(this);
            const cartUrl = button.data('route');

            addToCart(true, cartUrl);

            /* Guest checkout off: the cart page is where the flow continues, as in the legacy page. */
            if (button.data('auth')?.toString() === 'false') {
                setTimeout(function () {
                    location.href = cartUrl;
                }, 400);
            }
        });

        /* ---------- wishlist ---------- */

        $(document).on('click', '.product-action-add-wishlist', function () {
            const productId = $(this).data('product-id');

            if ($('#is-customer-auth-active').data('value') !== 1) {
                location.href = $('#route-customer-login').data('url');
                return;
            }

            $.post({
                url: $('#route-store-wishlist').data('url'),
                data: {product_id: productId},
                success: function (response) {
                    $('.wishlist_icon_' + productId).attr('fill', response.value === 1 ? '#E4574F' : 'none');
                    $('.countWishlist-' + productId).text(response.product_count);
                    toastr.success(response.message);
                },
            });
        });

        /* ---------- restock request ---------- */

        $(document).on('click', '.product-restock-request-button', function () {
            const button = $(this);

            if (button.data('auth')?.toString() !== 'true') {
                location.href = $('#route-customer-login').data('url');
                return;
            }

            $.post({
                url: $('#route-product-restock-request').data('url'),
                data: detailsForm.serializeArray(),
                success: function (response) {
                    toastr.success(response.message);
                    button.text(button.data('requested')).prop('disabled', true);
                },
            });
        });

        /* ---------- more reviews ---------- */

        let reviewPageOffset = 1;

        $(document).on('click', '.view_more_button', function () {
            const button = $(this);

            $.post({
                url: $('#route-review-list-product').data('url'),
                data: {
                    product_id: $('#products-details-page-data').data('id'),
                    offset: reviewPageOffset,
                },
                success: function (response) {
                    $('#product-review-list').append(response.productReview);
                    reviewPageOffset++;

                    if (!response.checkReviews) {
                        button.hide();
                    }
                },
            });
        });

        syncQuantityButtons();
        getVariantPrice();
    })();
</script>
