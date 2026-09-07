@php($storePhone = $storePhone ?? $web_config['phone'])
@php($whatsappConfig = $whatsappConfig ?? getWebConfig(name: 'whatsapp'))
@php($whatsappNumber = $whatsappNumber ?? (($whatsappConfig['status'] ?? 0) ? $whatsappConfig['phone'] : null))
@php($whatsappLink = $whatsappLink ?? ($whatsappNumber ? 'https://wa.me/' . $whatsappNumber : route('contacts')))
@php($homeSectionUrl = ($isHomePage ?? false) ? '' : route('home'))

        <footer id="contact" style="background: #062A3E; color: #C6DDE6;">
            <div class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(38px, 6vw, 70px) 20px 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr)); gap: 36px;">
                <div style="max-width: 330px;">
                    <div style="display: flex; align-items: center; gap: 11px;">
                        <img src="{{ getStorageImages(path: $web_config['footer_logo'], type: 'logo') }}"
                             alt="{{ $web_config['company_name'] }}" style="height: 42px; width: auto; max-width: 150px; object-fit: contain;">
                        <strong style="font-family: Cairo, sans-serif; font-size: 20px; font-weight: 800; color: #FFFFFF;">{{ $web_config['company_name'] }}</strong>
                    </div>
                    <p style="font-size: 14.5px; line-height: 1.95; margin: 16px 0 0; color: #9FBDCB;">
                        {{ translate('an_online_store_for_fresh_fish_and_seafood_daily_catch_chilled_sealed_packing_and_delivery_to_your_door') }}
                    </p>
                    @if($web_config['social_media']->count() > 0)
                        <div style="display: flex; gap: 10px; margin-top: 22px;">
                            @foreach($web_config['social_media'] as $socialMedia)
                                <a href="{{ $socialMedia['link'] }}" target="_blank" class="sf-social"
                                   aria-label="{{ $socialMedia['name'] }}" title="{{ $socialMedia['name'] }}"
                                   style="width: 40px; height: 40px; border-radius: 12px; border: 1px solid rgba(255,255,255,.16); background: rgba(255,255,255,.04); display: grid; place-items: center; color: #C6DDE6;">
                                    <i class="fa fa-{{ $socialMedia['name'] }}"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    <h4 style="font-family: Cairo, sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0 0 16px;">{{ translate('quick_links') }}</h4>
                    <div class="sf-footer-links" style="display: flex; flex-direction: column; gap: 11px; font-size: 14.5px;">
                        <a href="{{ route('home') }}" class="sf-footer-link">{{ translate('home') }}</a>
                        <a href="{{ route('products') }}" class="sf-footer-link">{{ translate('products') }}</a>
                        <a href="{{ route('categories') }}" class="sf-footer-link">{{ translate('categories') }}</a>
                        <a href="{{ route('discounted-products') }}" class="sf-footer-link">{{ translate('offers') }}</a>
                        <a href="{{ route('business-page.view', ['slug' => 'about-us']) }}" class="sf-footer-link">{{ translate('about_us') }}</a>
                        <a href="{{ route('contacts') }}" class="sf-footer-link">{{ translate('contact_us') }}</a>
                    </div>
                </div>

                <div>
                    <h4 style="font-family: Cairo, sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0 0 16px;">{{ translate('customer_service') }}</h4>
                    <div class="sf-footer-links" style="display: flex; flex-direction: column; gap: 11px; font-size: 14.5px;">
                        @foreach($web_config['business_pages']->where('default_status', 1) as $businessPage)
                            <a href="{{ route('business-page.view', ['slug' => $businessPage['slug']]) }}" class="sf-footer-link">
                                {{ Str::limit($businessPage['title'], 25) }}
                            </a>
                        @endforeach
                        <a href="{{ $homeSectionUrl }}#faq" class="sf-footer-link">{{ translate('faq') }}</a>
                        <a href="{{ route('track-order.index') }}" class="sf-footer-link">{{ translate('track_your_order') }}</a>
                    </div>
                </div>

                <div>
                    <h4 style="font-family: Cairo, sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0 0 16px;">{{ translate('contact_us') }}</h4>
                    <div style="display: flex; flex-direction: column; gap: 13px; font-size: 14.5px; color: #9FBDCB; line-height: 1.75;">
                        <span>{{ getWebConfig(name: 'company_email') }}</span>
                        <span style="direction: ltr; text-align: start; color: #FFFFFF; font-weight: 700; font-size: 16px;">{{ $storePhone }}</span>
                        <span>{{ translate('working_hours') }}: {{ translate('daily_from_6_am_to_11_pm') }}</span>
                        <a href="{{ $whatsappLink }}" target="_blank"
                           style="display: inline-flex; align-items: center; gap: 8px; align-self: flex-start; background: rgba(23,176,167,.16); border: 1px solid rgba(23,176,167,.4); color: #7FE3DC; padding: 9px 16px; border-radius: 999px; font-weight: 700; font-size: 14px;">
                            {{ translate('order_via_whatsapp') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: 26px 20px 30px;">
                <div style="border-top: 1px solid rgba(255,255,255,.12); padding-top: 22px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px 16px;">
                    <span style="font-size: 13.5px; color: #8FB0BF;">{{ $web_config['copyright_text'] }}</span>
                    <div style="display: flex; align-items: center; gap: 9px; flex-wrap: wrap;">
                        <span style="font-size: 13px; color: #8FB0BF; margin-inline-end: 4px;">{{ translate('payment_methods') }}:</span>
                        @foreach($web_config['payments_list'] as $paymentGateway)
                            <span style="height: 30px; padding: 0 12px; border-radius: 8px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14); display: inline-flex; align-items: center; font-size: 12.5px; font-weight: 600; color: #D8E9EF;">
                                {{ translate(str_replace('_', ' ', $paymentGateway['key_name'])) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </footer>
