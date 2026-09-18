@extends('layouts.front-end.seafood-account')

@section('title', translate('my_Wishlists'))

@section('page_title', translate('wishlist'))

@if($wishlists->count() > 0)
    @section('page_count', $wishlists->total())
@endif

@section('account_content')
    <div id="set-wish-list">
        @include('web-views.seafood.partials._wish-list-data', ['wishlists' => $wishlists, 'brand_setting' => $brand_setting])
    </div>

    <input type="hidden" id="wishlist_paginated_page" value="{{ request('page') }}">
    <span id="route-delete-wishlist" data-url="{{ route('delete-wishlist') }}"></span>
@endsection

@push('script')
    <script>
        "use strict";

        $(document).on('click', '.function-remove-wishList', function () {
            $.post({
                url: $('#route-delete-wishlist').data('url'),
                data: {id: $(this).data('id')},
                success: function (response) {
                    toastr.success(response.message);
                    $('#set-wish-list').html(response.wishlist);
                    $('.seafood-wishlist-count').text(response.count);
                },
            });
        });
    </script>
@endpush
