@php($storePhone = $storePhone ?? $web_config['phone'])
@php($whatsappConfig = $whatsappConfig ?? getWebConfig(name: 'whatsapp'))
@php($whatsappNumber = $whatsappNumber ?? (($whatsappConfig['status'] ?? 0) ? $whatsappConfig['phone'] : null))
@php($whatsappLink = $whatsappLink ?? ($whatsappNumber ? 'https://wa.me/' . $whatsappNumber : route('contacts')))


        <div style="background: #062A3E; color: #CFE9EA; font-size: 13.5px; padding: 9px 0;">
            <div class="sf-gutter sf-topbar-inner" style="max-width: 1240px; margin: 0 auto; padding: 0 20px; display: flex; flex-wrap: nowrap; align-items: center; gap: 10px 22px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #17B0A7; display: inline-block; animation: pulseRing 2.4s infinite;"></span>
                    <span style="color: #FFFFFF; font-weight: 500;">{{ translate('next_day_delivery_inside_the_city') }}</span>
                </div>
                <div class="sf-topbar-info" style="display: flex; align-items: center; gap: 8px;">
                    <span style="opacity: .65;">{{ translate('working_hours') }}:</span>
                    <span>{{ translate('daily_from_6_am_to_11_pm') }}</span>
                </div>
                <div class="sf-topbar-info" style="display: flex; align-items: center; gap: 8px;">
                    <span style="opacity: .65;">{{ translate('to_order') }}:</span>
                    <span style="direction: ltr; font-weight: 700; letter-spacing: .4px;">{{ $storePhone }}</span>
                </div>
                <a href="{{ $whatsappLink }}" target="_blank" class="sf-topbar-whatsapp"
                   style="margin-inline-start: auto; display: inline-flex; align-items: center; gap: 7px; background: rgba(23,176,167,.16); border: 1px solid rgba(23,176,167,.45); color: #7FE3DC; padding: 5px 13px; border-radius: 999px; font-weight: 700; font-size: 13px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #35D6A4; display: inline-block;"></span>
                    {{ translate('order_via_whatsapp') }}
                </a>
            </div>
        </div>
