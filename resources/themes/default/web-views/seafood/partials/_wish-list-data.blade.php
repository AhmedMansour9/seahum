@php($cartItemKeysByProduct = \App\Utils\CartManager::getCartListQuery()->pluck('id', 'product_id'))

@if($wishlists->count() > 0)
    <div class="sf-product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr)); gap: 20px;">
        @foreach($wishlists as $wishlist)
            @if($wishlist->productFullInfo)
                @include('web-views.seafood.partials._product-card', [
                    'product' => $wishlist->productFullInfo,
                    'cartItemKey' => $cartItemKeysByProduct[$wishlist->productFullInfo['id']] ?? null,
                    'mediaRatio' => 'square',
                    'showPricingDetails' => true,
                    'showRating' => true,
                    'isWishlisted' => true,
                    'wishlistRemovesCard' => true,
                ])
            @endif
        @endforeach
    </div>

    {!! $wishlists->links('web-views.seafood.partials._pagination') !!}
@else
    @include('web-views.seafood.partials._empty-state', [
        'emptyStateIcon' => '<path d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 4.6-7 9-7 9Z"></path>',
        'emptyStateTitle' => translate('you_have_not_added_product_to_wishlist_yet').'!',
        'emptyStateText' => translate('save_the_products_you_like_here_so_you_can_find_them_later'),
        'emptyStateActionUrl' => route('products'),
        'emptyStateActionLabel' => translate('explore_more'),
    ])
@endif
