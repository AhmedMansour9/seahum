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

    function setSeafoodCartToggleState(button, cartKey) {
        button.data('cart-key', cartKey || '');
        button.toggleClass('in-cart', !!cartKey);
        button.find('.seafood-cart-toggle-label').text(cartKey ? button.data('remove-label') : button.data('add-label'));
    }

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
                    toastr.success(response.message);
                    $('.seafood-cart-count').text(response.cart_items_count);
                    $('.seafood-cart-toggle').filter(function () {
                        return String($(this).data('product-id')) === String(button.data('product-id'));
                    }).each(function () {
                        setSeafoodCartToggleState($(this), response.in_cart_key || (response.cart && response.cart.id));
                    });
                } else if (response.status === 2) {
                    location.href = button.data('product-url');
                } else {
                    toastr.warning(response.message);
                }
            }
        });
    });

    $(document).on('click', '.seafood-wishlist-btn', function () {
        if ($('#is-customer-auth-active').data('value') !== 1) {
            location.href = $('#route-customer-login').data('url');
            return;
        }
        const button = $(this);
        $.post({
            url: $('#route-store-wishlist').data('url'),
            data: {product_id: button.data('product-id')},
            success: function (response) {
                if (response.value === 1) {
                    button.find('svg').attr('fill', '#E4574F');
                }
                toastr.success(response.message);
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
</script>
