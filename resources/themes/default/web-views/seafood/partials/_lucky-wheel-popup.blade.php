@php
    $isLuckyWheelPopupPage = isset(VIEW_FILE_NAMES['lucky_wheel']) && auth('customer')->check()
        && !Request::routeIs('lucky-wheel', 'checkout-details', 'checkout-payment', 'checkout-review', 'checkout-complete', 'checkout-complete-wallet', 'order-placed', 'order-placed-success');
    $luckyWheelService = $isLuckyWheelPopupPage ? app(\App\Services\LuckyWheelService::class) : null;
    $canSpinLuckyWheel = $luckyWheelService && $luckyWheelService->isEnabled()
        && $luckyWheelService->getCustomerStatus(customerId: auth('customer')->id())['can_spin'];
@endphp

@if($canSpinLuckyWheel)
    <div class="sf-lw-popup" id="sf-lw-popup" role="dialog" aria-modal="true" aria-labelledby="sf-lw-popup-title" hidden
         data-dismiss-key="sf-lucky-wheel-popup:{{ auth('customer')->id() }}:{{ $luckyWheelService->getBusinessNow()->toDateString() }}">
        <div class="sf-lw-popup-dialog">
            <button type="button" class="sf-lw-popup-close" data-lw-popup-close aria-label="{{ translate('close') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
            <span class="sf-lw-popup-icon" aria-hidden="true">
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.4"></circle><path d="M12 3.6v16.8M3.6 12h16.8M6.1 6.1l11.8 11.8M17.9 6.1 6.1 17.9"></path><circle cx="12" cy="12" r="2" fill="currentColor"></circle></svg>
            </span>
            <h2 class="sf-lw-popup-title" id="sf-lw-popup-title">{{ translate('try_your_luck_today') }}</h2>
            <p class="sf-lw-popup-text">{{ translate('spin_the_lucky_wheel_for_a_chance_to_win_a_free_gift_with_your_next_order') }}</p>
            <div class="sf-lw-popup-actions">
                <a href="{{ route('lucky-wheel') }}" class="sf-lw-popup-cta" data-lw-popup-go>{{ translate('spin_the_wheel') }}</a>
                <button type="button" class="sf-lw-popup-later" data-lw-popup-close>{{ translate('maybe_later') }}</button>
            </div>
        </div>
    </div>

    <style>
        .sf-lw-popup { position: fixed; inset: 0; z-index: 1060; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(11,44,63,.55); }
        .sf-lw-popup[hidden] { display: none; }
        .sf-lw-popup-dialog { position: relative; width: 100%; max-width: 400px; background: #FFFFFF; border-radius: 24px; padding: 34px 24px 24px; text-align: center; box-shadow: 0 30px 60px -30px rgba(11,44,63,.9); }
        .sf-lw-popup-close { position: absolute; top: 12px; inset-inline-end: 12px; width: 40px; height: 40px; border: none; border-radius: 12px; background: #F4FAFB; color: #0B2C3F; display: grid; place-items: center; cursor: pointer; }
        .sf-lw-popup-icon { width: 84px; height: 84px; margin: 0 auto 14px; border-radius: 50%; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; animation: pulseRing 2s infinite; }
        .sf-lw-popup-title { font-family: Cairo, sans-serif; font-size: 21px; font-weight: 800; color: #0B2C3F; margin: 0 0 8px; overflow-wrap: anywhere; }
        .sf-lw-popup-text { font-size: 15px; line-height: 1.8; color: #4F6E7B; margin: 0 0 20px; overflow-wrap: anywhere; }
        .sf-lw-popup-actions { display: flex; flex-direction: column; gap: 10px; }
        .sf-lw-popup-cta { display: flex; align-items: center; justify-content: center; min-height: 50px; border-radius: 14px; background: #17B0A7; color: #FFFFFF; font-family: Cairo, sans-serif; font-size: 16px; font-weight: 700; }
        .sf-lw-popup-cta:hover { background: #0E8E88; color: #FFFFFF; }
        .sf-lw-popup-later { min-height: 44px; border: none; background: none; color: #6E8C99; font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 600; cursor: pointer; }
        @media (prefers-reduced-motion: reduce) { .sf-lw-popup-icon { animation: none; } }
    </style>

    <script>
        'use strict';
        (function () {
            const popup = document.getElementById('sf-lw-popup');
            const dismissKey = popup.dataset.dismissKey;
            const readDismissed = () => { try { return window.localStorage.getItem(dismissKey) === '1'; } catch (error) { return false; } };
            const rememberDismissed = () => { try { window.localStorage.setItem(dismissKey, '1'); } catch (error) {} };
            const closePopup = () => { rememberDismissed(); popup.hidden = true; document.body.style.overflow = ''; };

            if (readDismissed()) {
                return;
            }
            popup.querySelectorAll('[data-lw-popup-close]').forEach((button) => button.addEventListener('click', closePopup));
            popup.querySelector('[data-lw-popup-go]').addEventListener('click', rememberDismissed);
            popup.addEventListener('click', (event) => { if (event.target === popup) { closePopup(); } });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !popup.hidden) { closePopup(); } });
            setTimeout(() => { popup.hidden = false; document.body.style.overflow = 'hidden'; }, 1200);
        })();
    </script>
@endif
