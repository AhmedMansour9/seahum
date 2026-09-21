<span id="sf-order-config" hidden
      data-order-again-url="{{ route('cart.order-again') }}"
      data-message-something-wrong="{{ translate('something_went_wrong') }}"></span>

<script>
    "use strict";

    $(document).on('click', '.sf-refund-details-trigger', function () {
        $.get($(this).data('route'), function (response) {
            if (response.status == 1) {
                $('#refund_details_field').html(response.view);
                closeSeafoodModal('#refund_request');
                openSeafoodModal('refund_details_modal');
            } else {
                toastr.error(response.message);
            }
        });
    });

    $(document).on('click', '.sf-digital-download', function () {
        const button = $(this).prop('disabled', true);
        $.get({
            url: button.data('link'),
            success: function (response) {
                if (response.status == 1 && response.file_path) {
                    const link = document.createElement('a');
                    link.href = response.file_path;
                    link.setAttribute('download', response.file_path.substring(response.file_path.lastIndexOf('/') + 1));
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                } else if (response.message) {
                    toastr.error(response.message);
                }
            },
            error: function () {
                toastr.error($('#sf-order-config').data('message-something-wrong'));
            },
            complete: function () {
                button.prop('disabled', false);
            },
        });
    });

    $(document).on('click', '.sf-order-again', function () {
        const button = $(this).prop('disabled', true);
        $.post({
            url: $('#sf-order-config').data('order-again-url'),
            data: {order_id: button.data('id')},
            success: function (response) {
                if (response.status === 1) {
                    toastr.success(response.message);
                    location.href = response.redirect_url;
                } else {
                    toastr.warning(response.message);
                    button.prop('disabled', false);
                }
            },
            error: function () {
                button.prop('disabled', false);
            },
        });
    });

    $(document).on('click', '.sf-review-image-delete', function () {
        const button = $(this);
        $.post({
            url: button.data('route'),
            data: {id: button.data('review-id'), name: button.data('photo')},
            success: function (response) {
                if (response.message) {
                    toastr.success(response.message);
                }
                button.closest('.sf-image-picker-item').remove();
            },
        });
    });

    $(document).on('change', '.sf-star-input input[type="radio"]', function () {
        const fieldset = $(this).closest('.sf-star-input');
        fieldset.find('.sf-star-input-label').text((fieldset.data('labels') || {})[this.value] || '');
    });
</script>
