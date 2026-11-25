@php($cartItemsCount = $cartItemsCount ?? \App\Utils\CartManager::getCartListQuery()->count())
@php($mobileBarGoogleStore = getWebConfig(name: 'download_app_google_store') ?? [])
@php($mobileBarAppleStore = getWebConfig(name: 'download_app_apple_store') ?? [])
@php($mobileBarPromoOffer = $appPromoOffer ?? app(\App\Services\AppPromoService::class)->getOffer())

        <div class="sf-mobile-bar">
            {{-- Opens the store that matches the phone (see _scripts); anywhere else it scrolls to the app section on the home page. --}}
            <a href="{{ route('home') }}#app" class="sf-mobile-app" title="{{ translate('get_the_app') }}"
               data-google-play-url="{{ !empty($mobileBarGoogleStore['status']) ? ($mobileBarGoogleStore['link'] ?? '') : '' }}"
               data-app-store-url="{{ !empty($mobileBarAppleStore['status']) ? ($mobileBarAppleStore['link'] ?? '') : '' }}">
                <span class="sf-mobile-app-icons" aria-hidden="true">
                    <span class="sf-mobile-app-icon">
                        <svg width="15" height="16" viewBox="0 0 24 26"><path d="M1.2 0.6 13.4 12.9 1.2 25.2c-.4-.3-.7-.8-.7-1.5V2.1c0-.7.3-1.2.7-1.5Z" fill="#00D7FE"></path><path d="m17.5 8.8-4.1 4.1 4.1 4.1 4.7-2.7c1.3-.8 1.3-2 0-2.8l-4.7-2.7Z" fill="#FFCE00"></path><path d="M17.5 17 13.4 12.9 1.2 25.2c.5.4 1.2.4 2 0L17.5 17Z" fill="#FF3A44"></path><path d="M17.5 8.8 3.2.6c-.8-.4-1.5-.4-2 0l12.2 12.3 4.1-4.1Z" fill="#00F076"></path></svg>
                    </span>
                    <span class="sf-mobile-app-icon">
                        <svg width="14" height="16" viewBox="0 0 22 26" fill="#FFFFFF"><path d="M18.2 13.8c0-3.3 2.7-4.9 2.8-5-1.5-2.2-3.9-2.5-4.7-2.6-2-.2-3.9 1.2-4.9 1.2-1 0-2.6-1.2-4.2-1.1-2.2 0-4.2 1.3-5.3 3.2-2.3 3.9-.6 9.8 1.6 13 1.1 1.6 2.4 3.3 4.1 3.3 1.6-.1 2.3-1.1 4.2-1.1 2 0 2.5 1.1 4.2 1 1.8 0 2.9-1.6 3.9-3.2 1.2-1.8 1.7-3.6 1.8-3.7-.1 0-3.5-1.3-3.5-5Z"></path><path d="M15 4.1c.9-1.1 1.5-2.6 1.3-4.1-1.3.1-2.8.9-3.7 1.9-.8.9-1.5 2.4-1.3 3.9 1.4.1 2.8-.7 3.7-1.7Z"></path></svg>
                    </span>
                </span>
                <span class="sf-mobile-app-text">
                    {{-- The store icons already say "app"; the caption carries the minimum order the code needs. --}}
                    <span class="sf-mobile-app-caption">
                        @if($mobileBarPromoOffer['minimum_purchase'] > 0)
                            {{ translate('on_orders_from') }} <bdi>{{ $mobileBarPromoOffer['minimum_purchase_value'] }} {{ $mobileBarPromoOffer['currency_symbol'] }}</bdi>
                        @else
                            {{ translate('get_the_app') }}
                        @endif
                    </span>
                    <strong class="sf-mobile-app-offer">
                        <bdi>{{ $mobileBarPromoOffer['discount_value'] }}{{ $mobileBarPromoOffer['is_percentage'] ? ' '.translate('off') : ' '.$mobileBarPromoOffer['currency_symbol'] }}</bdi> ·
                        <bdi dir="ltr" class="sf-mobile-app-code">{{ $mobileBarPromoOffer['code'] }}</bdi>
                    </strong>
                </span>
                <svg class="sf-mobile-app-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 6 8 12l6 6"></path></svg>
            </a>

            <a href="{{ $web_config['guest_checkout_status'] || auth('customer')->check() ? route('shop-cart') : route('customer.auth.login') }}"
               class="sf-mobile-cart" aria-label="{{ translate('view_cart') }}" title="{{ translate('view_cart') }}">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h2l2.2 10.2a2 2 0 0 0 2 1.6h6.9a2 2 0 0 0 2-1.5L21 8H7"></path><circle cx="10" cy="20" r="1.4"></circle><circle cx="18" cy="20" r="1.4"></circle></svg>
                <span class="sf-mobile-cart-count seafood-cart-count">{{ $cartItemsCount }}</span>
            </a>
        </div>
        <div class="sf-mobile-bar-spacer"></div>
