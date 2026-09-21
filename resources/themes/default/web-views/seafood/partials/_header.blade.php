@php($whatsappConfig = $whatsappConfig ?? getWebConfig(name: 'whatsapp'))
@php($whatsappNumber = $whatsappNumber ?? (($whatsappConfig['status'] ?? 0) ? $whatsappConfig['phone'] : null))
@php($whatsappLink = $whatsappLink ?? ($whatsappNumber ? 'https://wa.me/' . $whatsappNumber : route('contacts')))
@php($cartItemsCount = $cartItemsCount ?? \App\Utils\CartManager::getCartListQuery()->count())
@php($auctionActive = $auctionActive ?? (function_exists('getCheckAddonPublishedStatus') && getCheckAddonPublishedStatus(moduleName: 'Auction') && getWebConfig(name: 'auction_feature_status')))
@php($homeSectionUrl = ($isHomePage ?? false) ? '' : route('home'))
@php($activeNavKey = $activeNavKey ?? 'home')
@php($navLinkStyle = fn (string $key) => $activeNavKey === $key ? 'color: #0E8E88; background: #ECF8F8;' : 'color: #34586B;')

        <header style="position: sticky; top: 0; z-index: 60; background: rgba(255,255,255,.92); backdrop-filter: blur(14px); border-bottom: 1px solid #E4EFF1;">
            <div class="sf-header-inner" style="max-width: 1240px; margin: 0 auto; padding: 14px 20px; display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">

                <a href="{{ route('home') }}" class="sf-header-brand" style="display: flex; align-items: center; gap: 11px; flex-shrink: 0; min-width: 0;">
                    <img class="sf-header-logo" src="{{ getStorageImages(path: $web_config['web_logo'], type: 'logo') }}"
                         alt="{{ $web_config['company_name'] }}" style="height: 44px; width: auto; max-width: 180px; object-fit: contain;">
                    <span class="sf-header-brand-text" style="display: flex; flex-direction: column; line-height: 1.15; min-width: 0;">
                        <strong style="font-family: Cairo, sans-serif; font-size: 21px; font-weight: 800; color: #0B2C3F;">{{ $web_config['company_name'] }}</strong>
                        <span class="sf-header-tagline" style="font-size: 11.5px; color: #6E8C99; letter-spacing: .3px;">{{ translate('fresh_fish_and_seafood') }}</span>
                    </span>
                </a>

                <nav class="sf-desktop-nav" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 600;">
                    <a href="{{ $homeSectionUrl }}#home" class="sf-nav-link" style="padding: 8px 13px; border-radius: 10px; {{ $navLinkStyle('home') }}">{{ translate('home') }}</a>
                    <a href="{{ $homeSectionUrl }}#products" class="sf-nav-link" style="padding: 8px 13px; border-radius: 10px; {{ $navLinkStyle('products') }}">{{ translate('products') }}</a>
                    <a href="{{ $homeSectionUrl }}#categories" class="sf-nav-link" style="padding: 8px 13px; border-radius: 10px; {{ $navLinkStyle('categories') }}">{{ translate('categories') }}</a>
                    <a href="{{ $homeSectionUrl }}#deal" class="sf-nav-link" style="padding: 8px 13px; border-radius: 10px; {{ $navLinkStyle('offers') }}">{{ translate('offers') }}</a>
                    <a href="{{ $homeSectionUrl }}#about" class="sf-nav-link" style="padding: 8px 13px; border-radius: 10px; {{ $navLinkStyle('about') }}">{{ translate('about_us') }}</a>
                    <a href="{{ route('contacts') }}" class="sf-nav-link" style="padding: 8px 13px; border-radius: 10px; {{ $navLinkStyle('contact') }}">{{ translate('contact_us') }}</a>
                    @if($auctionActive && Route::has('auction.index'))
                        <a href="{{ route('auction.index') }}" class="sf-nav-link" style="padding: 8px 13px; border-radius: 10px; color: #34586B;">{{ translate('auction') }}</a>
                    @endif
                </nav>

                <div class="sf-header-tools" style="margin-inline-start: auto; display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                    <button type="button" class="sf-tool sf-burger" id="seafood-menu-toggle" aria-label="{{ translate('menu') }}"
                            aria-expanded="false" aria-controls="seafood-mobile-menu"
                            style="width: 44px; height: 44px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; place-items: center; cursor: pointer;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>
                    </button>

                    <form class="sf-header-search" action="{{ route('products') }}" method="GET" style="display: flex; align-items: center;">
                        <input type="text" name="name" value="{{ request('name') }}" placeholder="{{ translate('search_for_products') }}"
                               style="height: 44px; width: 190px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; padding: 0 14px; font-family: Tajawal, sans-serif; font-size: 14px; outline: none;">
                        <button type="submit" class="sf-tool" aria-label="{{ translate('search') }}"
                                style="width: 44px; height: 44px; margin-inline-start: 8px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; display: grid; place-items: center; cursor: pointer;">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.2-3.2"></path></svg>
                        </button>
                    </form>

                    <a href="{{ auth('customer')->check() ? route('user-account') : route('customer.auth.login') }}" class="sf-tool sf-header-account"
                       aria-label="{{ translate('account') }}"
                       style="width: 44px; height: 44px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; display: grid; place-items: center;">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"></circle><path d="M4.5 20c1.4-3.4 4.2-5 7.5-5s6.1 1.6 7.5 5"></path></svg>
                    </a>

                    @if(auth('customer')->check())
                        <a href="{{ route('customer.auth.logout') }}" class="sf-tool sf-header-logout"
                           aria-label="{{ translate('logout') }}" title="{{ translate('logout') }}"
                           style="width: 44px; height: 44px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; display: grid; place-items: center;">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"></path><path d="M10 16l-4-4 4-4M6 12h9"></path></svg>
                        </a>
                    @endif

                    <a href="{{ route('wishlists') }}" class="sf-tool sf-header-wishlist" aria-label="{{ translate('wishlist') }}"
                       style="position: relative; width: 44px; height: 44px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; display: grid; place-items: center;">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 4.6-7 9-7 9Z"></path></svg>
                        <span class="seafood-wishlist-count" style="position: absolute; top: -6px; inset-inline-start: -6px; min-width: 19px; height: 19px; padding: 0 5px; border-radius: 999px; background: #0D3B58; color: #FFFFFF; font-size: 11px; font-weight: 700; display: grid; place-items: center;">
                            {{ session()->has('wish_list') ? count(session('wish_list')) : 0 }}
                        </span>
                    </a>

                    @foreach($web_config['language'] as $languageData)
                        @if($languageData['status'] == 1 && $languageData['code'] != getDefaultLanguage())
                            <button type="button" class="sf-tool sf-header-lang seafood-change-language" data-language-code="{{ $languageData['code'] }}"
                                    title="{{ $languageData['name'] }}"
                                    style="display: inline-flex; align-items: center; gap: 7px; height: 44px; padding: 0 13px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 13.5px; font-weight: 700; cursor: pointer;">
                                <img src="{{ theme_asset(path: 'public/assets/front-end/img/flags/'.strtolower(\Illuminate\Support\Str::before($languageData['code'], '-')).'.png') }}"
                                     alt="{{ $languageData['name'] }}" width="22" height="16" style="width: 22px; height: 16px; object-fit: cover; border-radius: 3px; box-shadow: 0 0 0 1px #E4EFF1;">
                                <span style="text-transform: uppercase;">{{ $languageData['code'] }}</span>
                            </button>
                            @break
                        @endif
                    @endforeach

                    <a href="{{ $web_config['guest_checkout_status'] || auth('customer')->check() ? route('shop-cart') : route('customer.auth.login') }}"
                       class="sf-btn-primary sf-header-cart"
                       style="position: relative; display: inline-flex; align-items: center; gap: 9px; height: 44px; padding: 0 15px; border-radius: 12px; background: #17B0A7; color: #FFFFFF; font-family: Cairo, sans-serif; font-weight: 700; font-size: 14px; box-shadow: 0 8px 18px -8px rgba(23,176,167,.9);">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h2l2.2 10.2a2 2 0 0 0 2 1.6h6.9a2 2 0 0 0 2-1.5L21 8H7"></path><circle cx="10" cy="20" r="1.4"></circle><circle cx="18" cy="20" r="1.4"></circle></svg>
                        <span class="sf-cart-label">{{ translate('cart') }}</span>
                        <span class="seafood-cart-count" style="min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px; background: rgba(255,255,255,.22); display: grid; place-items: center; font-size: 12.5px;">{{ $cartItemsCount }}</span>
                    </a>
                </div>
            </div>

            <div class="sf-mobile-menu" id="seafood-mobile-menu" hidden
                 style="border-top: 1px solid #E4EFF1; background: #FFFFFF; padding: 12px 20px 18px; flex-direction: column; gap: 4px; font-family: Cairo, sans-serif; font-size: 16px; font-weight: 600;">
                <a href="{{ $homeSectionUrl }}#home" style="padding: 13px 12px; border-radius: 10px; {{ $navLinkStyle('home') }}">{{ translate('home') }}</a>
                <a href="{{ $homeSectionUrl }}#products" style="padding: 13px 12px; border-radius: 10px; {{ $navLinkStyle('products') }}">{{ translate('products') }}</a>
                <a href="{{ $homeSectionUrl }}#categories" style="padding: 13px 12px; border-radius: 10px; {{ $navLinkStyle('categories') }}">{{ translate('categories') }}</a>
                <a href="{{ $homeSectionUrl }}#deal" style="padding: 13px 12px; border-radius: 10px; {{ $navLinkStyle('offers') }}">{{ translate('offers') }}</a>
                <a href="{{ $homeSectionUrl }}#about" style="padding: 13px 12px; border-radius: 10px; {{ $navLinkStyle('about') }}">{{ translate('about_us') }}</a>
                <a href="{{ $homeSectionUrl }}#faq" style="padding: 13px 12px; border-radius: 10px; color: #34586B;">{{ translate('faq') }}</a>
                <a href="{{ route('contacts') }}" style="padding: 13px 12px; border-radius: 10px; color: #34586B;">{{ translate('contact_us') }}</a>
                @if($auctionActive && Route::has('auction.index'))
                    <a href="{{ route('auction.index') }}" style="padding: 13px 12px; border-radius: 10px; color: #34586B;">{{ translate('auction') }}</a>
                @endif

                <a href="{{ auth('customer')->check() ? route('user-account') : route('customer.auth.login') }}"
                   style="padding: 13px 12px; border-radius: 10px; color: #34586B;">{{ translate('account') }}</a>
                <a href="{{ route('wishlists') }}" style="padding: 13px 12px; border-radius: 10px; color: #34586B;">{{ translate('wishlist') }}</a>
                @if(auth('customer')->check())
                    <a href="{{ route('customer.auth.logout') }}" style="padding: 13px 12px; border-radius: 10px; color: #34586B;">{{ translate('logout') }}</a>
                @endif

                @foreach($web_config['language'] as $languageData)
                    @if($languageData['status'] == 1 && $languageData['code'] != getDefaultLanguage())
                        <button type="button" class="seafood-change-language" data-language-code="{{ $languageData['code'] }}"
                                style="margin-top: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 46px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; cursor: pointer;">
                            <img src="{{ theme_asset(path: 'public/assets/front-end/img/flags/'.strtolower(\Illuminate\Support\Str::before($languageData['code'], '-')).'.png') }}"
                                 alt="{{ $languageData['name'] }}" width="22" height="16" style="width: 22px; height: 16px; object-fit: cover; border-radius: 3px; box-shadow: 0 0 0 1px #E4EFF1;">
                            <span style="text-transform: uppercase;">{{ $languageData['code'] }}</span>
                        </button>
                        @break
                    @endif
                @endforeach

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 4px;">
                    <a href="{{ $whatsappLink }}" target="_blank"
                       style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 48px; border-radius: 12px; background: #0E8E88; color: #FFFFFF; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 700;">{{ translate('whatsapp') }}</a>
                    <a href="{{ route('contacts') }}"
                       style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 48px; border-radius: 12px; background: #ECF8F8; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 700;">{{ translate('contact_us') }}</a>
                </div>
                <span style="margin-top: 8px; font-size: 13px; color: #6E8C99; text-align: center;">{{ translate('working_hours') }}: {{ translate('daily_from_6_am_to_11_pm') }}</span>
            </div>
        </header>
