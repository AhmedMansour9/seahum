@extends('layouts.front-end.seafood')

@section('title', $web_config['meta_title'])


@push('css_or_js')
    @include('web-views.seafood.partials._styles')
@endpush

@section('content')
    @php($storePhone = $web_config['phone'])
    @php($whatsappConfig = getWebConfig(name: 'whatsapp'))
    @php($whatsappNumber = $whatsappConfig['status'] ?? 0 ? $whatsappConfig['phone'] : null)
    @php($whatsappLink = $whatsappNumber ? 'https://wa.me/' . $whatsappNumber : route('contacts'))
    @php($flashDealEndDate = $web_config['flash_deals']['end_date'] ?? null)
    @php($shopCategories = $categories->take(6))
    @php($cartItems = \App\Utils\CartManager::getCartListQuery())
    @php($cartItemsCount = $cartItems->count())
    @php($cartItemKeysByProduct = $cartItems->pluck('id', 'product_id'))
    @php($auctionActive = function_exists('getCheckAddonPublishedStatus') && getCheckAddonPublishedStatus(moduleName: 'Auction') && getWebConfig(name: 'auction_feature_status'))

    <?php
    $orderSuccessIds = session('order_success_ids');
    $isNewCustomerInSession = session('isNewCustomerInSession');
    session()->forget('order_success_ids');
    session()->forget('isNewCustomerInSession');
    ?>

    <div style="background: #FFFFFF;">

        @if($orderSuccessIds)
            <div style="max-width: 1240px; margin: 0 auto; padding: 20px 20px 0;">
                <div style="border-radius: 20px; background: #ECF8F8; border: 1px solid #CFE9EA; padding: 22px 24px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <span style="width: 46px; height: 46px; border-radius: 50%; background: #17B0A7; color: #FFFFFF; display: grid; place-items: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg>
                    </span>
                    <span style="display: flex; flex-direction: column; gap: 6px;">
                        <strong style="font-family: Cairo, sans-serif; font-size: 18px; color: #0B2C3F;">
                            {{ $isNewCustomerInSession ? translate('Order_placed_&_Account_Created_Successfully') : translate('thank_you_for_your_purchase') }}
                        </strong>
                        <span style="font-size: 14.5px; color: #45677A;">
                            {{ count($orderSuccessIds) > 1 ? translate('Order IDs') : translate('Order ID') }}:
                            <span style="direction: ltr; font-weight: 700;">{{ implode(', ', $orderSuccessIds) }}</span>
                        </span>
                    </span>
                    <a href="{{ auth('customer')->check() ? route('account-oder') : route('track-order.index') }}" class="sf-btn-primary"
                       style="margin-inline-start: auto; display: inline-flex; align-items: center; height: 44px; padding: 0 22px; border-radius: 13px; background: #17B0A7; color: #FFFFFF; font-family: Cairo, sans-serif; font-weight: 700; font-size: 15px;">
                        {{ translate('track_your_order') }}
                    </a>
                </div>
            </div>
        @endif

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['isHomePage' => true, 'activeNavKey' => 'home'])

        <section id="home" style="position: relative; background: #04202F; color: #FFFFFF; overflow: hidden;">
            <img src="{{ dynamicAsset(path: 'public/assets/front-end/img/seafood/hero-bg.jpg') }}"
                 alt="{{ translate('fresh_fish_and_seafood') }}"
                 style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; filter: saturate(1.25) contrast(1.12) brightness(1.04);">
            <div style="position: absolute; inset: 0; background: radial-gradient(ellipse at 50% 46%, rgba(4,26,38,.62) 0%, rgba(4,26,38,.34) 48%, rgba(4,26,38,.1) 100%);"></div>
            <div style="position: absolute; inset: 0; background: radial-gradient(ellipse at 50% 44%, rgba(4,26,38,.5) 0%, transparent 58%); mix-blend-mode: multiply;"></div>

            <div class="sf-hero-inner" style="position: relative; max-width: 1240px; margin: 0 auto; padding: clamp(64px, 9vw, 140px) 20px; display: flex; justify-content: center;">
                <div style="max-width: 700px; text-align: center; display: flex; flex-direction: column; align-items: center;">
                    <span style="display: inline-flex; align-items: center; gap: 8px; background: rgba(23,176,167,.18); border: 1px solid rgba(127,227,220,.4); color: #9FEEE7; padding: 7px 15px; border-radius: 999px; font-size: 13.5px; font-weight: 700;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #7FE3DC; display: inline-block;"></span>
                        {{ translate('todays_catch_delivered_to_your_door_tomorrow') }}
                    </span>

                    <h1 style="font-family: Cairo, sans-serif; font-weight: 900; font-size: clamp(29px, 5.4vw, 60px); line-height: 1.2; margin: 18px 0 0; letter-spacing: -.5px; text-wrap: balance;">
                        {{ translate('fresh_from_the_sea') }}<br>
                        <span style="color: #7FE3DC;">{{ translate('to_your_doorstep') }}</span>
                    </h1>

                    <p style="font-size: clamp(15.5px, 1.5vw, 18.5px); line-height: 1.95; color: #C6DDE6; margin: 18px auto 0; max-width: 600px; text-wrap: pretty;">
                        {{ translate('we_pick_the_finest_fish_shrimp_and_crab_from_the_boats_every_morning_and_deliver_them_to_you_just_as_they_are_chilled_and_tightly_sealed_the_next_day') }}
                    </p>

                    <div class="sf-hero-actions" style="display: flex; align-self: stretch; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 28px;">
                        <a href="#products" class="sf-btn-primary"
                           style="display: inline-flex; align-items: center; justify-content: center; gap: 9px; background: #17B0A7; color: #FFFFFF; font-family: Cairo, sans-serif; font-weight: 800; font-size: 16px; padding: 16px 30px; border-radius: 14px; box-shadow: 0 16px 32px -14px rgba(23,176,167,1);">
                            {{ translate('shop_now') }}
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 6 8 12l6 6"></path></svg>
                        </a>
                        <a href="#deal" class="sf-btn-ghost"
                           style="display: inline-flex; align-items: center; justify-content: center; gap: 9px; border: 1.5px solid rgba(255,255,255,.32); color: #FFFFFF; font-family: Cairo, sans-serif; font-weight: 700; font-size: 16px; padding: 16px 28px; border-radius: 14px;">
                            {{ translate('see_todays_offers') }}
                        </a>
                    </div>

                    <div class="sf-hero-facts" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr)); gap: 12px; margin-top: 32px; width: 100%;">
                        <div style="display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,.09); border: 1px solid rgba(255,255,255,.2); border-radius: 16px; padding: 13px 18px; backdrop-filter: blur(8px);">
                            <span style="flex-shrink: 0; width: 38px; height: 38px; border-radius: 11px; background: rgba(23,176,167,.24); display: grid; place-items: center; color: #7FE3DC;">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h11v9H2z"></path><path d="M13 10h4l3 3v3h-7z"></path><circle cx="6" cy="18" r="1.6"></circle><circle cx="17" cy="18" r="1.6"></circle></svg>
                            </span>
                            <span style="display: flex; flex-direction: column; gap: 2px; line-height: 1.35; text-align: start;">
                                <strong style="font-family: Cairo, sans-serif; font-size: 15px; color: #FFFFFF;">{{ translate('next_day_delivery') }}</strong>
                                <span style="font-size: 12.5px; color: #A9C7D3;">{{ translate('order_today_receive_tomorrow') }}</span>
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,.09); border: 1px solid rgba(255,255,255,.2); border-radius: 16px; padding: 13px 18px; backdrop-filter: blur(8px);">
                            <span style="flex-shrink: 0; width: 38px; height: 38px; border-radius: 11px; background: rgba(23,176,167,.24); display: grid; place-items: center; color: #7FE3DC;">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13a8 8 0 1 1-8-8"></path><path d="M12 5V2"></path><path d="m8.5 12.5 3 2.5 4-5"></path></svg>
                            </span>
                            <span style="display: flex; flex-direction: column; gap: 2px; line-height: 1.35; text-align: start;">
                                <strong style="font-family: Cairo, sans-serif; font-size: 15px; color: #FFFFFF;">{{ translate('chilled_and_tightly_sealed') }}</strong>
                                <span style="font-size: 12.5px; color: #A9C7D3;">{{ translate('full_cold_chain_to_your_door') }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if($shopProducts->count() > 0)
            <div style="background: #17B0A7; color: #FFFFFF; overflow: hidden; padding: 13px 0; position: relative;">
                <div class="sf-ticker-track" style="display: flex; width: max-content;">
                    @for($tickerPass = 0; $tickerPass < 2; $tickerPass++)
                        <div style="display: flex; align-items: center; flex-shrink: 0;">
                            @foreach($tickerProducts as $product)
                                <div class="sf-ticker-item" style="display: flex; align-items: center; gap: 9px; padding: 0 26px; border-inline-end: 1px solid rgba(255,255,255,.32); white-space: nowrap;">
                                    <strong style="font-family: Cairo, sans-serif; font-size: 17px; font-weight: 800;">{{ Str::limit($product['name'], 28) }}</strong>
                                    <strong style="font-family: Cairo, sans-serif; font-size: 17px; font-weight: 800;">@include('web-views.seafood.partials._price', ['amount' => getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'value'), 'symbolSize' => '13px', 'symbolColor' => 'rgba(255,255,255,.78)'])</strong>
                                </div>
                            @endforeach
                        </div>
                    @endfor
                </div>
            </div>
        @endif

        @if($categories->count() > 0)
            <section id="categories" class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(30px, 4.5vw, 54px) 20px 0;">
                <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
                    <h2 style="font-family: Cairo, sans-serif; font-weight: 800; font-size: clamp(24px, 3vw, 32px); margin: 0; color: #0B2C3F;">{{ translate('categories') }}</h2>
                    <a href="{{ route('categories') }}" class="sf-section-link" style="font-family: Cairo, sans-serif; font-weight: 700; font-size: 15px; display: inline-flex; align-items: center; gap: 7px;">
                        {{ translate('view_all_categories') }}
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 6 8 12l6 6"></path></svg>
                    </a>
                </div>

                <div class="sf-cat-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(166px, 100%), 1fr)); gap: 14px;">
                    @foreach($shopCategories as $category)
                        @include('web-views.seafood.partials._category-card', ['category' => $category])
                    @endforeach
                </div>
            </section>
        @endif

        @if($bestSellProduct->count() > 0)
            <section id="products" class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(44px, 7vw, 86px) 20px 0;">
                <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 30px;">
                    <div>
                        <span style="font-size: 13.5px; font-weight: 700; color: #0E8E88; letter-spacing: .6px;">{{ translate('our_customers_picks') }}</span>
                        <h2 style="font-family: Cairo, sans-serif; font-weight: 800; font-size: clamp(26px, 3.4vw, 38px); margin: 8px 0 0; color: #0B2C3F;">{{ translate('best_selling_this_week') }}</h2>
                    </div>
                    <span style="font-size: 14px; color: #6E8C99; max-width: 380px; line-height: 1.8;">{{ translate('prices_are_updated_daily_and_weight_is_calculated_when_the_order_is_packed') }}</span>
                </div>

                <div class="sf-product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr)); gap: 20px;">
                    @foreach($bestSellProduct->take(4) as $product)
                        @include('web-views.seafood.partials._product-card', ['product' => $product, 'cartItemKey' => $cartItemKeysByProduct[$product['id']] ?? null])
                    @endforeach
                </div>
            </section>
        @endif

        @if($shopProducts->count() > 0)
            <section id="shop" class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(44px, 7vw, 86px) 20px 0;">
                <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 22px;">
                    <div>
                        <span style="font-size: 13.5px; font-weight: 700; color: #0E8E88; letter-spacing: .6px;">{{ translate('shop_by_category') }}</span>
                        <h2 style="font-family: Cairo, sans-serif; font-weight: 800; font-size: clamp(26px, 3.4vw, 38px); margin: 8px 0 0; color: #0B2C3F;">{{ translate('all_products') }}</h2>
                    </div>
                    @php($activeShopProductsCount = $shopProducts->where('category_id', $activeShopCategoryId)->count())
                    <span id="seafood-shop-count" style="font-size: 14px; color: #6E8C99;"
                          data-showing-label="{{ translate('showing') }}" data-of-label="{{ translate('of') }}" data-products-label="{{ translate('products') }}">
                        {{ translate('showing') }} {{ min($shopInitialVisibleProducts, $activeShopProductsCount) }} {{ translate('of') }} {{ $activeShopProductsCount }} {{ translate('products') }}
                    </span>
                </div>

                <div class="sf-tabs" style="display: flex; flex-wrap: wrap; gap: 9px; margin-bottom: 26px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none;">
                    @foreach($shopTabCategories as $category)
                        <button type="button" class="sf-tab{{ $category['id'] == $activeShopCategoryId ? ' active' : '' }}" data-category-id="{{ $category['id'] }}"
                                data-category-url="{{ route('category-products', ['slug' => $category['slug']]) }}">
                            {{ Str::limit($category['name'], 20) }}
                        </button>
                    @endforeach
                </div>

                <div id="seafood-shop-grid" class="sf-product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr)); gap: 20px;">
                    @php($visibleShopProductsCount = 0)
                    @foreach($shopProducts as $product)
                        @php($isVisibleCard = $product['category_id'] == $activeShopCategoryId && $visibleShopProductsCount < $shopInitialVisibleProducts)
                        @php($visibleShopProductsCount += $isVisibleCard ? 1 : 0)
                        @include('web-views.seafood.partials._product-card', ['product' => $product, 'isVisibleCard' => $isVisibleCard, 'cartItemKey' => $cartItemKeysByProduct[$product['id']] ?? null])
                    @endforeach
                </div>

                <div style="display: flex; justify-content: center; margin-top: 32px;">
                    <a id="seafood-shop-more" class="sf-see-more" href="{{ $activeShopCategorySlug ? route('category-products', ['slug' => $activeShopCategorySlug]) : route('products') }}">
                        {{ translate('see_more') }}
                        <span class="sf-see-more-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><path d="m12 19-7-7 7-7"></path></svg>
                        </span>
                    </a>
                </div>
            </section>
        @endif

        @if(isset($dealOfTheDay->product))
            <section id="deal" class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(44px, 7vw, 86px) 20px 0;">
                <div style="position: relative; overflow: hidden; border-radius: 26px; background: linear-gradient(215deg, #0D3B58, #062A3E 55%, #0E6B72); color: #FFFFFF;">
                    <div style="position: absolute; inset-inline-start: -120px; top: -140px; width: 420px; height: 420px; border-radius: 50%; background: radial-gradient(circle, rgba(23,176,167,.35), transparent 70%);"></div>
                    <div style="position: relative; display: grid; grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr)); align-items: stretch;">
                        <div style="padding: clamp(30px, 4vw, 52px);">
                            <span style="display: inline-block; background: #F2A43A; color: #3A2400; font-family: Cairo, sans-serif; font-weight: 800; font-size: 13px; padding: 6px 14px; border-radius: 999px; white-space: nowrap;">
                                {{ translate('deal_of_the_day') }}
                            </span>
                            <h2 style="font-family: Cairo, sans-serif; font-weight: 900; font-size: clamp(27px, 3.8vw, 42px); line-height: 1.3; margin: 18px 0 0; text-wrap: balance;">
                                {{ Str::limit($dealOfTheDay->product['name'], 60) }}<br>
                                <span style="color: #7FE3DC;">@include('web-views.seafood.partials._price', ['amount' => getProductPriceByType(product: $dealOfTheDay->product, type: 'discounted_unit_price', result: 'value'), 'symbolSize' => '.55em', 'symbolColor' => 'rgba(127,227,220,.8)'])</span>
                            </h2>
                            <p style="color: #C6DDE6; font-size: 16px; line-height: 1.95; margin: 14px 0 0; max-width: 460px;">
                                {{ translate('fresh_from_todays_catch_available_while_todays_stock_lasts') }}
                            </p>

                            @if($flashDealEndDate)
                                <div id="seafood-countdown" data-end-date="{{ $flashDealEndDate }}" style="display: flex; gap: 10px; margin-top: 26px;">
                                    @foreach(['hours' => translate('hour'), 'minutes' => translate('minute'), 'seconds' => translate('second')] as $unit => $label)
                                        <div class="sf-countdown-box" style="flex: 1 1 74px; min-width: 0; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18); border-radius: 14px; padding: 10px 6px; text-align: center;">
                                            <div data-countdown-unit="{{ $unit }}" style="font-family: Cairo, sans-serif; font-size: 25px; font-weight: 800; color: #FFFFFF; direction: ltr;">00</div>
                                            <div style="font-size: 12px; color: #A9C7D3; margin-top: 2px;">{{ $label }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <a href="{{ route('product', $dealOfTheDay->product['slug']) }}" class="sf-btn-primary"
                               style="display: inline-flex; align-items: center; gap: 9px; margin-top: 28px; background: #17B0A7; color: #FFFFFF; font-family: Cairo, sans-serif; font-weight: 800; font-size: 16px; padding: 15px 32px; border-radius: 14px; box-shadow: 0 16px 32px -14px rgba(23,176,167,1);">
                                {{ translate('order_this_offer_now') }}
                            </a>
                        </div>
                        <div style="position: relative; min-height: 300px;">
                            <img src="{{ $dealOfTheDay->product?->thumbnail_full_url ? getStorageImages(path: $dealOfTheDay->product->thumbnail_full_url, type: 'product') : dynamicAsset(path: 'public/assets/front-end/img/seafood/deal.jpg') }}"
                                 alt="{{ $dealOfTheDay->product['name'] }}" loading="lazy"
                                 style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: 25% center; display: block;">
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section id="about" style="margin-top: clamp(52px, 7vw, 86px);">
            <div style="background: #062A3E; padding: clamp(42px, 6.5vw, 84px) 0;">
                <div class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(min(320px, 100%), 1fr)); gap: clamp(30px, 4.5vw, 62px); align-items: start;">
                    <div style="position: relative;">
                        <div style="border-radius: 22px; overflow: hidden; border: 1px solid rgba(255,255,255,.12); box-shadow: 0 34px 60px -30px rgba(0,0,0,.7);">
                            <img src="{{ dynamicAsset(path: 'public/assets/front-end/img/seafood/fish-trio.jpg') }}"
                                 alt="{{ translate('fresh_fish_and_seafood') }}" loading="lazy"
                                 style="width: 100%; height: clamp(340px, 44vw, 560px); object-fit: cover; display: block;">
                        </div>
                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; margin-top: 14px;">
                            <span style="font-size: 13px; font-weight: 700; color: #9FBDCB;">{{ translate('from_this_mornings_catch') }}</span>
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #17B0A7; display: inline-block; animation: pulseRing 2.4s infinite;"></span>
                        </div>
                    </div>

                    <div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 13.5px; font-weight: 800; color: #7FE3DC; letter-spacing: .5px; white-space: nowrap;">{{ translate('why_us') }}</span>
                            <span style="height: 2px; width: 46px; background: #17B0A7; display: inline-block;"></span>
                        </div>
                        <h2 style="font-family: Cairo, sans-serif; font-weight: 900; font-size: clamp(26px, 3.6vw, 42px); line-height: 1.3; margin: 14px 0 0; color: #FFFFFF; text-wrap: balance;">
                            {{ translate('everything_we_do_is_about_freshness_and_trust') }}
                        </h2>

                        <div style="display: flex; flex-direction: column; margin-top: 30px;">
                            @foreach([
                                ['title' => translate('an_honest_scale'), 'text' => translate('weight_is_measured_in_front_of_you_like_any_fish_market_you_trust_and_the_listed_price_is_the_final_price_per_kilo')],
                                ['title' => translate('fresh_or_we_do_not_sell_it'), 'text' => translate('any_item_we_are_not_happy_with_today_is_removed_from_the_site_what_you_see_available_is_what_deserves_to_reach_you')],
                                ['title' => translate('not_happy_we_make_it_right'), 'text' => translate('if_your_order_arrives_below_the_expected_quality_message_us_on_whatsapp_and_we_will_make_up_for_it_on_your_next_order')],
                            ] as $index => $promise)
                                <div style="display: flex; align-items: flex-start; gap: 18px; padding: 24px 0; border-top: 1px solid rgba(255,255,255,.1);">
                                    <span style="flex-shrink: 0; width: 48px; height: 48px; border-radius: 50%; background: #17B0A7; color: #062A3E; display: grid; place-items: center; font-family: Cairo, sans-serif; font-size: 19px; font-weight: 800; box-shadow: 0 12px 26px -12px rgba(23,176,167,.9);">{{ $index + 1 }}</span>
                                    <span style="display: flex; flex-direction: column; gap: 8px;">
                                        <strong style="font-family: Cairo, sans-serif; font-size: clamp(18px, 2vw, 21px); font-weight: 800; color: #FFFFFF;">{{ $promise['title'] }}</strong>
                                        <span style="font-size: 15px; line-height: 1.9; color: #A9C7D3; text-wrap: pretty;">{{ $promise['text'] }}</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(44px, 7vw, 86px) 20px 0;">
            <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 22px; flex-wrap: wrap; margin-bottom: 40px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 11px;">
                        <span style="height: 2px; width: 40px; background: #17B0A7; display: inline-block;"></span>
                        <span style="font-size: 13.5px; font-weight: 800; color: #0E8E88; letter-spacing: .5px;">{{ translate('easy_and_fast') }}</span>
                    </div>
                    <h2 style="font-family: Cairo, sans-serif; font-weight: 800; font-size: clamp(26px, 3.4vw, 38px); margin: 12px 0 0; color: #0B2C3F;">{{ translate('how_to_order_in_4_steps') }}</h2>
                </div>
                <span style="display: inline-flex; align-items: center; gap: 9px; background: #ECF8F8; color: #0E8E88; font-family: Cairo, sans-serif; font-weight: 700; font-size: 14px; padding: 10px 18px; border-radius: 999px; white-space: nowrap;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5V12l3 2"></path></svg>
                    {{ translate('from_order_to_delivery_the_next_day') }}
                </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr)); gap: 22px;">
                @foreach([
                    ['title' => translate('pick_your_products'), 'text' => translate('browse_the_categories_and_add_what_you_need_to_the_cart'), 'icon' => '<path d="M4 5h2l2.2 10.2a2 2 0 0 0 2 1.6h6.9a2 2 0 0 0 2-1.5L21 8H7"/><circle cx="10" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/>'],
                    ['title' => translate('choose_weight_and_quantity'), 'text' => translate('select_the_weight_or_number_of_packs_that_suits_your_family_size'), 'icon' => '<path d="M12 4v3"/><path d="M4 8h16l-2.5 9.5a2 2 0 0 1-2 1.5h-7a2 2 0 0 1-2-1.5Z"/>'],
                    ['title' => translate('enter_your_delivery_address'), 'text' => translate('add_your_address_and_pick_the_delivery_slot_that_suits_you'), 'icon' => '<path d="M12 21s-6.5-5.6-6.5-10a6.5 6.5 0 0 1 13 0c0 4.4-6.5 10-6.5 10Z"/><circle cx="12" cy="11" r="2.4"/>'],
                    ['title' => translate('receive_your_order_fresh'), 'text' => translate('your_order_arrives_chilled_and_tightly_packed_the_next_day'), 'icon' => '<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5Z"/><path d="M3 7.5 12 12l9-4.5"/><path d="M12 12v9"/>'],
                ] as $index => $step)
                    <div style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 16px;">
                        <span style="width: 56px; height: 56px; border-radius: 50%; background: #FFFFFF; border: 2px solid #17B0A7; color: #0B2C3F; display: grid; place-items: center; font-family: Cairo, sans-serif; font-size: 20px; font-weight: 800; box-shadow: 0 10px 24px -14px rgba(23,176,167,.9);">{{ $index + 1 }}</span>
                        <div class="sf-lift" style="flex: 1; width: 100%; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; padding: 24px 20px 26px; box-shadow: 0 14px 34px -28px rgba(11,44,63,.9);">
                            <span style="width: 46px; height: 46px; border-radius: 14px; background: #ECF8F8; color: #0E8E88; display: inline-grid; place-items: center;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $step['icon'] !!}</svg>
                            </span>
                            <h3 style="font-family: Cairo, sans-serif; font-size: 17.5px; font-weight: 700; margin: 16px 0 8px; color: #0B2C3F;">{{ $step['title'] }}</h3>
                            <p style="font-size: 14.5px; color: #6E8C99; line-height: 1.85; margin: 0; text-wrap: pretty;">{{ $step['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="faq" style="background: #062A3E; margin-top: clamp(52px, 7vw, 86px); padding: clamp(42px, 6.5vw, 84px) 0;">
            <div class="sf-gutter" style="max-width: 900px; margin: 0 auto; padding: 0 20px;">
                <div style="display: flex; align-items: center; gap: 11px;">
                    <span style="height: 2px; width: 40px; background: #17B0A7; display: inline-block;"></span>
                    <span style="font-size: 13.5px; font-weight: 800; color: #7FE3DC; letter-spacing: .5px;">{{ translate('faq') }}</span>
                </div>
                <h2 style="font-family: Cairo, sans-serif; font-weight: 900; font-size: clamp(26px, 3.6vw, 40px); margin: 14px 0 34px; color: #FFFFFF;">{{ translate('questions_we_get_a_lot') }}</h2>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach([
                        ['question' => translate('is_the_fish_fresh_or_frozen'), 'answer' => translate('all_fish_shrimp_and_crab_come_from_the_same_mornings_catch_imported_items_only_are_chilled_and_clearly_marked_on_the_product_page')],
                        ['question' => translate('when_will_my_order_arrive'), 'answer' => translate('delivery_is_next_day_inside_the_city_and_we_contact_you_on_whatsapp_to_agree_on_the_time_slot_before_dispatch')],
                        ['question' => translate('how_can_i_pay'), 'answer' => translate('cash_on_delivery_credit_cards_and_digital_wallets_are_all_available_at_checkout')],
                        ['question' => translate('how_is_the_weight_calculated'), 'answer' => translate('weight_is_calculated_when_the_order_is_packed_like_any_fish_market_and_the_listed_price_per_kilo_is_final_with_no_extras')],
                        ['question' => translate('what_if_an_item_is_not_available_today'), 'answer' => translate('items_we_are_not_happy_with_are_removed_from_the_site_the_same_day_what_you_see_available_is_what_can_actually_be_ordered')],
                        ['question' => translate('where_do_you_deliver'), 'answer' => translate('message_us_on_whatsapp_to_confirm_that_we_cover_your_area')],
                    ] as $index => $faq)
                        <div class="sf-faq-item" style="border-radius: 16px; overflow: hidden; background: rgba(255,255,255,.035); border: 1px solid rgba(255,255,255,.1); transition: background .25s, border-color .25s;">
                            <button type="button" class="sf-faq-toggle"
                                    style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 14px; background: transparent; border: none; padding: 20px 22px; cursor: pointer; text-align: start; font-family: Cairo, sans-serif; font-size: clamp(16px, 1.7vw, 18px); font-weight: 700; color: #FFFFFF;">
                                <span>{{ $faq['question'] }}</span>
                                <span class="sf-faq-icon" style="flex-shrink: 0; width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,.08); color: #7FE3DC; display: grid; place-items: center; transition: transform .3s, background .25s;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                                </span>
                            </button>
                            <p class="sf-faq-answer" style="display: none; margin: 0; padding: 0 22px 22px; font-size: 15px; line-height: 1.95; color: #A9C7D3; text-wrap: pretty;">{{ $faq['answer'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="whatsapp" class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(44px, 7vw, 86px) 20px 0;">
            <div style="border-radius: 26px; background: linear-gradient(120deg, #ECF8F8, #E3F2F6); border: 1px solid #CFE9EA; padding: clamp(32px, 4.5vw, 58px); display: grid; grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr)); gap: 28px; align-items: center;">
                <div>
                    <h2 style="font-family: Cairo, sans-serif; font-weight: 800; font-size: clamp(25px, 3.2vw, 36px); margin: 0 0 12px; color: #0B2C3F; text-wrap: balance;">{{ translate('need_help_choosing_your_order') }}</h2>
                    <p style="font-size: 16px; color: #45677A; line-height: 1.95; margin: 0; max-width: 520px;">
                        {{ translate('message_us_on_whatsapp_with_the_number_of_people_and_the_occasion_and_our_team_will_recommend_the_right_types_and_weights_then_deliver_the_next_day') }}
                    </p>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                    <a href="{{ $whatsappLink }}" target="_blank"
                       style="display: inline-flex; align-items: center; gap: 10px; background: #0E8E88; color: #FFFFFF; font-family: Cairo, sans-serif; font-weight: 800; font-size: 16.5px; padding: 16px 32px; border-radius: 14px; box-shadow: 0 18px 34px -16px rgba(14,142,136,1);">
                        {{ translate('contact_us_on_whatsapp') }}
                    </a>
                    <a href="tel:{{ $storePhone }}"
                       style="display: inline-flex; align-items: center; gap: 10px; background: #FFFFFF; color: #0B2C3F; border: 1.5px solid #CFE9EA; font-family: Cairo, sans-serif; font-weight: 700; font-size: 16.5px; padding: 16px 30px; border-radius: 14px;">
                        <span style="direction: ltr;">{{ $storePhone }}</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="sf-gutter" style="position: relative; overflow: hidden; margin-top: clamp(52px, 7vw, 86px); background: #08222F; padding: clamp(56px, 9vw, 130px) 20px;">
            <div style="position: absolute; inset-inline: 0; bottom: -190px; height: 380px; background: radial-gradient(ellipse at 50% 100%, rgba(23,176,167,.3), transparent 68%); pointer-events: none;"></div>
            <div style="position: relative; max-width: 780px; margin: 0 auto; text-align: center;">
                <h2 style="font-family: Cairo, sans-serif; font-weight: 900; font-size: clamp(28px, 4.4vw, 50px); line-height: 1.28; margin: 0; color: #FFFFFF; text-wrap: balance;">{{ translate('tomorrow_the_fish_is_on_your_table') }}</h2>
                <p style="font-size: clamp(15px, 1.6vw, 17.5px); color: #9FBDCB; margin: 18px 0 0;">{{ translate('order_now_receive_tomorrow') }}</p>
                <a href="#products" class="sf-btn-primary"
                   style="display: inline-flex; align-items: center; justify-content: center; margin-top: 34px; background: #17B0A7; color: #FFFFFF; font-family: Cairo, sans-serif; font-weight: 800; font-size: clamp(16px, 1.7vw, 18px); padding: 18px 44px; border-radius: 12px; box-shadow: 0 0 44px -4px rgba(23,176,167,.55);">
                    {{ translate('order_now') }}
                </a>
            </div>
        </section>

        @include('web-views.seafood.partials._footer', ['isHomePage' => true])

        @include('web-views.seafood.partials._mobile-bar')
    </div>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')

    <script>
        "use strict";

        $(window).on('resize', function () {
            renderSeafoodShopGrid();
        });

        function getSeafoodShopLimit() {
            return window.innerWidth < 760 ? 4 : {{ $shopInitialVisibleProducts }};
        }

        function renderSeafoodShopGrid() {
            const activeTab = $('.sf-tab.active');
            const categoryId = activeTab.data('category-id');
            const limit = getSeafoodShopLimit();
            let matchedCount = 0;

            $('#seafood-shop-grid > article').each(function () {
                const matched = String($(this).data('category-id')) === String(categoryId);
                if (matched) {
                    matchedCount++;
                }
                $(this).css('display', matched && matchedCount <= limit ? 'flex' : 'none');
            });

            const countElement = $('#seafood-shop-count');
            countElement.text(countElement.data('showing-label') + ' ' + Math.min(limit, matchedCount) + ' ' + countElement.data('of-label') + ' ' + matchedCount + ' ' + countElement.data('products-label'));

            $('#seafood-shop-more').attr('href', activeTab.data('category-url'));
        }

        $(document).on('click', '.sf-tab', function () {
            $('.sf-tab').removeClass('active');
            $(this).addClass('active');
            renderSeafoodShopGrid();
        });

        renderSeafoodShopGrid();

        $(document).on('click', '.sf-faq-toggle', function () {
            const item = $(this).closest('.sf-faq-item');
            const isOpen = item.find('.sf-faq-answer').is(':visible');

            $('.sf-faq-answer').hide();
            $('.sf-faq-item').css({'background': 'rgba(255,255,255,.035)', 'border-color': 'rgba(255,255,255,.1)'});
            $('.sf-faq-icon').css('transform', 'rotate(0deg)');

            if (!isOpen) {
                item.find('.sf-faq-answer').show();
                item.css({'background': 'rgba(255,255,255,.07)', 'border-color': 'rgba(23,176,167,.5)'});
                item.find('.sf-faq-icon').css('transform', 'rotate(180deg)');
            }
        });

        const countdownElement = document.getElementById('seafood-countdown');
        if (countdownElement) {
            const endTime = new Date(countdownElement.dataset.endDate.replace(/-/g, '/')).getTime();
            const padValue = (value) => String(value).padStart(2, '0');

            const renderCountdown = () => {
                const remaining = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
                countdownElement.querySelector('[data-countdown-unit="hours"]').textContent = padValue(Math.floor(remaining / 3600));
                countdownElement.querySelector('[data-countdown-unit="minutes"]').textContent = padValue(Math.floor(remaining / 60) % 60);
                countdownElement.querySelector('[data-countdown-unit="seconds"]').textContent = padValue(remaining % 60);
            };

            renderCountdown();
            setInterval(renderCountdown, 1000);
        }
    </script>
@endpush
