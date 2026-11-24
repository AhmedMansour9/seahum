@php($cartItemsCount = $cartItemsCount ?? \App\Utils\CartManager::getCartListQuery()->count())

        <div class="sf-mobile-bar" style="position: fixed; inset-inline: 0; bottom: 0; z-index: 80; background: rgba(255,255,255,.97); backdrop-filter: blur(14px); border-top: 1px solid var(--sh-line); padding: 10px 14px calc(10px + env(safe-area-inset-bottom)); box-shadow: 0 -10px 30px -22px rgba(var(--sh-navy-rgb), .9);">
            <a href="{{ $web_config['guest_checkout_status'] || auth('customer')->check() ? route('shop-cart') : route('customer.auth.login') }}"
               style="display: flex; align-items: center; justify-content: center; gap: 10px; height: 52px; border-radius: 14px; background: var(--sh-action); color: #FFFFFF; font-family: var(--sh-font-display); font-size: 16px; font-weight: 800; box-shadow: var(--sh-shadow-cta);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h2l2.2 10.2a2 2 0 0 0 2 1.6h6.9a2 2 0 0 0 2-1.5L21 8H7"></path><circle cx="10" cy="20" r="1.4"></circle><circle cx="18" cy="20" r="1.4"></circle></svg>
                {{ translate('view_cart') }}
                <span class="seafood-cart-count" style="min-width: 24px; height: 24px; padding: 0 7px; border-radius: 999px; background: #FFFFFF; color: var(--sh-salmon-800); display: inline-grid; place-items: center; font-size: 13px;">{{ $cartItemsCount }}</span>
            </a>
        </div>
        <div class="sf-mobile-bar-spacer"></div>
