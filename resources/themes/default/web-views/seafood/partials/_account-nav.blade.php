@php($businessMode = getWebConfig(name: 'business_mode'))
@php($customerName = trim(auth('customer')->user()?->f_name . ' ' . auth('customer')->user()?->l_name))

@php($accountNavItems = array_values(array_filter([
    [
        'label' => translate('profile_info'),
        'url' => route('user-account'),
        'active' => Request::is('user-account*'),
        'icon' => 'user',
    ],
    [
        'label' => translate('my_order'),
        'url' => route('account-oder'),
        'active' => Request::is('account-oder*') || Request::is('account-order-details*') || Request::is('refund-request*') || Request::is('refund-details*'),
        'icon' => 'order',
    ],
    [
        'label' => translate('Restock_Requests'),
        'url' => route('user-restock-requests'),
        'active' => Request::is('user-restock-requests*'),
        'icon' => 'restock',
    ],
    [
        'label' => translate('wish_list'),
        'url' => route('wishlists'),
        'active' => Request::is('wishlists*'),
        'icon' => 'heart',
    ],
    $web_config['wallet_status'] == 1 ? [
        'label' => translate('my_wallet'),
        'url' => route('wallet'),
        'active' => Request::is('wallet*'),
        'icon' => 'wallet',
    ] : null,
    $web_config['loyalty_point_status'] == 1 ? [
        'label' => translate('my_loyalty_point'),
        'url' => route('loyalty'),
        'active' => Request::is('loyalty*'),
        'icon' => 'award',
    ] : null,
    [
        'label' => translate('inbox'),
        'url' => route('chat', ['type' => $businessMode == 'multi' ? 'vendor' : 'delivery-man']),
        'active' => Request::is('chat/vendor') || Request::is('chat/delivery-man'),
        'icon' => 'chat',
    ],
    [
        'label' => translate('my_Address'),
        'url' => route('account-address'),
        'active' => Request::is('account-address*'),
        'icon' => 'pin',
    ],
    [
        'label' => translate('support_ticket'),
        'url' => route('account-tickets'),
        'active' => Request::is('account-ticket*') || Request::is('support-ticket*'),
        'icon' => 'support',
    ],
    $web_config['ref_earning_status'] ? [
        'label' => translate('refer_&_earn'),
        'url' => route('refer-earn'),
        'active' => Request::is('refer-earn*'),
        'icon' => 'share',
    ] : null,
    [
        'label' => translate('coupons'),
        'url' => route('user-coupons'),
        'active' => Request::is('user-coupons*'),
        'icon' => 'coupon',
    ],
    [
        'label' => translate('track_order'),
        'url' => route('track-order.index'),
        'active' => Request::is('track-order*'),
        'icon' => 'truck',
    ],
])))

@php($accountNavIcons = [
    'user' => '<circle cx="12" cy="8" r="3.6"></circle><path d="M4.8 20a7.2 7.2 0 0 1 14.4 0"></path>',
    'order' => '<path d="M6 3.6h12v16.8l-3-2.2-3 2.2-3-2.2-3 2.2z"></path><path d="M9 8h6M9 12h4"></path>',
    'restock' => '<path d="M20 12a8 8 0 1 1-2.4-5.7"></path><path d="M20 4v4h-4"></path>',
    'heart' => '<path d="M12 20s-7-4.4-7-9.2A3.9 3.9 0 0 1 12 8a3.9 3.9 0 0 1 7 2.8C19 15.6 12 20 12 20z"></path>',
    'wallet' => '<rect x="3" y="6" width="18" height="13" rx="3"></rect><path d="M16 12.5h3"></path><path d="M3 9.5h13"></path>',
    'award' => '<circle cx="12" cy="9" r="5"></circle><path d="m8.6 13.4-1.1 6.6 4.5-2.4 4.5 2.4-1.1-6.6"></path>',
    'chat' => '<path d="M20 12.5a7 7 0 0 1-9.9 6.4L4 20.5l1.6-5.4A7 7 0 1 1 20 12.5z"></path>',
    'pin' => '<path d="M12 21s6.5-5.4 6.5-10.2A6.5 6.5 0 0 0 5.5 10.8C5.5 15.6 12 21 12 21z"></path><circle cx="12" cy="10.6" r="2.4"></circle>',
    'support' => '<path d="M4.5 13v-1a7.5 7.5 0 0 1 15 0v1"></path><rect x="2.6" y="12.4" width="4" height="5.6" rx="2"></rect><rect x="17.4" y="12.4" width="4" height="5.6" rx="2"></rect><path d="M19.5 18v.6a3 3 0 0 1-3 3H13"></path>',
    'share' => '<circle cx="18" cy="6" r="2.6"></circle><circle cx="6" cy="12" r="2.6"></circle><circle cx="18" cy="18" r="2.6"></circle><path d="m8.3 10.8 7.4-3.6M8.3 13.2l7.4 3.6"></path>',
    'coupon' => '<path d="M3 8.4A2.4 2.4 0 0 1 5.4 6h13.2A2.4 2.4 0 0 1 21 8.4v1.2a2.4 2.4 0 0 0 0 4.8v1.2a2.4 2.4 0 0 1-2.4 2.4H5.4A2.4 2.4 0 0 1 3 15.6v-1.2a2.4 2.4 0 0 0 0-4.8z"></path><path d="M14 9.5 10 14.5"></path>',
    'truck' => '<rect x="2.6" y="6.6" width="11" height="9" rx="1.6"></rect><path d="M13.6 10h3.6l3.2 3.2v2.4h-6.8z"></path><circle cx="7" cy="17.6" r="1.8"></circle><circle cx="17" cy="17.6" r="1.8"></circle>',
])

<div class="sf-account-nav-backdrop" id="sf-account-nav-backdrop" hidden></div>

<aside class="sf-account-nav" id="sf-account-nav" aria-label="{{ translate('account_menu') }}">

    <div class="sf-account-nav-head">
        <span class="sf-account-avatar" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.6"></circle><path d="M4.8 20a7.2 7.2 0 0 1 14.4 0"></path></svg>
        </span>
        <span class="sf-account-nav-identity">
            <strong>{{ $customerName !== '' ? $customerName : translate('my_account') }}</strong>
            <span>{{ auth('customer')->user()?->email }}</span>
        </span>
        <button type="button" class="sf-account-nav-close" id="sf-account-nav-close" aria-label="{{ translate('close') }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
        </button>
    </div>

    <nav class="sf-account-nav-list">
        @foreach($accountNavItems as $accountNavItem)
            <a href="{{ $accountNavItem['url'] }}"
               class="sf-account-link {{ $accountNavItem['active'] ? 'active' : '' }}"
               @if($accountNavItem['active']) aria-current="page" @endif>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    {!! $accountNavIcons[$accountNavItem['icon']] !!}
                </svg>
                <span class="sf-account-link-label">{{ $accountNavItem['label'] }}</span>
            </a>
        @endforeach

        <a href="{{ route('customer.auth.logout') }}" class="sf-account-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"></path><path d="M10 16l-4-4 4-4M6 12h9"></path></svg>
            <span class="sf-account-link-label">{{ translate('logout') }}</span>
        </a>

        <a href="javascript:" class="sf-account-link sf-account-link-danger customer-account-delete-by-route"
           data-route="{{ route('account-delete', [auth('customer')->id()]) }}"
           data-message="{{ translate('want_to_delete_this_account') }}?">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14M10 7V5h4v2M7 7l.8 12.2A1.8 1.8 0 0 0 9.6 21h4.8a1.8 1.8 0 0 0 1.8-1.8L17 7"></path><path d="M10.5 11v6M13.5 11v6"></path></svg>
            <span class="sf-account-link-label">{{ translate('delete_account') }}</span>
        </a>
    </nav>

    @if(function_exists('shouldShowAuctionMenu') && shouldShowAuctionMenu())
        @php($auctionListActive = Route::is('auction.list', 'auction.auction-update-product', 'auction.auction-recreate-product'))
        <div class="sf-account-nav-group">
            <span class="sf-account-nav-group-title">{{ translate('Auction') }}</span>
            <nav class="sf-account-nav-list">
                <a href="{{ route('auction.bids.list') }}" class="sf-account-link {{ Route::is('auction.bids.list', 'auction.bids-product-details*', 'auction.profile-view.product-details') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m3 21h8M6.5 13.5 13 7M9 4.5 15.5 11M12.8 8.2l6 6"></path><path d="m14.5 6.5 3-3 3 3-3 3z"></path></svg>
                    <span class="sf-account-link-label">{{ translate('My Bids') }}</span>
                </a>
                @if(getWebConfig(name: 'auction_feature_status') && getWebConfig(name: 'active_auction_for_customer') == 1)
                    <a href="{{ route('auction.acution-create-product') }}" class="sf-account-link {{ Route::is('auction.acution-create-product') ? 'active' : '' }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                        <span class="sf-account-link-label">{{ translate('Create Auction') }}</span>
                    </a>
                @endif
                <a href="{{ route('auction.list') }}" class="sf-account-link {{ $auctionListActive ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6h11M9 12h11M9 18h11"></path><path d="M4.5 6h.01M4.5 12h.01M4.5 18h.01"></path></svg>
                    <span class="sf-account-link-label">{{ translate('Auction List') }}</span>
                </a>
                <a href="{{ route('auction.auctions-request-list') }}" class="sf-account-link {{ Route::is('auction.auctions-request-list') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M7 4.5h10v15H7z"></path><path d="M10 9h4M10 13h4"></path></svg>
                    <span class="sf-account-link-label">{{ translate('Auctions Request List') }}</span>
                </a>
                <a href="{{ route('auction.saved-products-list') }}" class="sf-account-link {{ Route::is('auction.saved-products-list') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M7 4h10v16l-5-3.4L7 20z"></path></svg>
                    <span class="sf-account-link-label">{{ translate('Saved Auction') }}</span>
                </a>
                <a href="{{ route('auction.sales-report') }}" class="sf-account-link {{ Route::is('auction.sales-report') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"></path><path d="M7 16v-4M12 16V7M17 16v-6"></path></svg>
                    <span class="sf-account-link-label">{{ translate('Auction Sales Report') }}</span>
                </a>
                <a href="{{ route('auction.transaction-history') }}" class="sf-account-link {{ Route::is('auction.transaction-history') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h13l-3-3M20 16H7l3 3"></path></svg>
                    <span class="sf-account-link-label">{{ translate('Transaction with Admin') }}</span>
                </a>
            </nav>
        </div>
    @endif
</aside>
