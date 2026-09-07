@php($whatsappConfig = $whatsappConfig ?? getWebConfig(name: 'whatsapp'))
@php($whatsappNumber = $whatsappNumber ?? (($whatsappConfig['status'] ?? 0) ? $whatsappConfig['phone'] : null))
@php($whatsappLink = $whatsappLink ?? ($whatsappNumber ? 'https://wa.me/' . $whatsappNumber : route('contacts')))
@php($cartItemsCount = $cartItemsCount ?? \App\Utils\CartManager::getCartListQuery()->count())

        <div class="sf-mobile-bar" style="position: fixed; inset-inline: 0; bottom: 0; z-index: 80; background: rgba(255,255,255,.97); backdrop-filter: blur(14px); border-top: 1px solid #E4EFF1; padding: 10px 14px calc(10px + env(safe-area-inset-bottom)); grid-template-columns: 1fr 1fr; gap: 10px; box-shadow: 0 -10px 30px -22px rgba(11,44,63,.9);">
            <a href="{{ $whatsappLink }}" target="_blank"
               style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 50px; border-radius: 14px; background: #0E8E88; color: #FFFFFF; font-family: Cairo, sans-serif; font-size: 15.5px; font-weight: 800;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #7FE3DC; display: inline-block;"></span>
                {{ translate('order_via_whatsapp') }}
            </a>
            <a href="{{ $web_config['guest_checkout_status'] || auth('customer')->check() ? route('shop-cart') : route('customer.auth.login') }}"
               style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 50px; border-radius: 14px; background: #ECF8F8; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 15.5px; font-weight: 800;">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h2l2.2 10.2a2 2 0 0 0 2 1.6h6.9a2 2 0 0 0 2-1.5L21 8H7"></path><circle cx="10" cy="20" r="1.4"></circle><circle cx="18" cy="20" r="1.4"></circle></svg>
                {{ translate('cart') }} (<span class="seafood-cart-count">{{ $cartItemsCount }}</span>)
            </a>
        </div>
        <div class="sf-mobile-bar-spacer"></div>
