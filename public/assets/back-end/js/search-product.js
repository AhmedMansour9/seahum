'use strict';
$('.search-product').on('input', function () {
    let name = $(this).val().trim();
    if (name.length === 0) {
        $.get($('#get-search-product-route').data('action'), {searchValue: ''}, (response) => {
            $('.search-result-box').html(response.result);
        });
        return;
    }
    $.get($('#get-search-product-route').data('action'), {searchValue: name}, (response) => {
        $('.search-result-box').html(response.result);
    });
});

let searchAllTypeProductTimeout;
let searchAllTypeProductRequest;
$('.search-all-type-product').on('focus click input', function () {
    let $input = $(this);
    $input.closest('.dropdown').find('.dropdown-menu').addClass('show');
    clearTimeout(searchAllTypeProductTimeout);
    searchAllTypeProductTimeout = setTimeout(function () {
        let searchRoute = $input.data('route') || $('#get-search-product-route').data('action');
        if (searchAllTypeProductRequest) {
            searchAllTypeProductRequest.abort();
        }
        searchAllTypeProductRequest = $.get(searchRoute, {searchValue: $input.val()}, (response) => {
            $('.search-result-box').empty().html(response.result);
        });
    }, 400);
})

let selectProductSearch = $('.select-product-search');
selectProductSearch.on('click', '.select-product-item', function () {
    let productName = $(this).find('.product-name').text() || 'All Products';
    let productId = $(this).find('.product-id').text();
    selectProductSearch.find('button.dropdown-toggle, button.dropdown--toggle ').text(productName);
    $('.product_id').val(productId);
})
$(document).on('keydown', '.select-product-search input', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        return false;
    }
});
