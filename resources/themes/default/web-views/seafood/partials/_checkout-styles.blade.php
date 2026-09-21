    <style>
        /* ---- Checkout stepper ---- */
        .sf-steps { display: flex; align-items: center; gap: 8px; margin: 0 0 24px; padding: 0; list-style: none; counter-reset: none; }
        .sf-steps-item { display: flex; align-items: center; gap: 8px; flex: 1 1 0; min-width: 0; }
        .sf-steps-item:not(:last-child)::after { content: ""; flex: 1 1 auto; height: 2px; min-width: 16px; border-radius: 999px; background: #E4EFF1; }
        .sf-steps-item.is-done:not(:last-child)::after { background: #17B0A7; }
        .sf-steps-link { display: inline-flex; align-items: center; gap: 9px; min-width: 0; color: #6E8C99; font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; }
        a.sf-steps-link:hover { color: #0E8E88; }
        .sf-steps-dot { width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; display: grid; place-items: center; border: 2px solid #E4EFF1; background: #FFFFFF; color: #6E8C99; font-size: 13.5px; font-weight: 800; }
        .sf-steps-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sf-steps-item.is-done .sf-steps-dot { background: #17B0A7; border-color: #17B0A7; color: #FFFFFF; }
        .sf-steps-item.is-done .sf-steps-link { color: #0B2C3F; }
        .sf-steps-item.is-current .sf-steps-dot { border-color: #17B0A7; background: #ECF8F8; color: #0E8E88; box-shadow: 0 0 0 4px rgba(23,176,167,.14); }
        .sf-steps-item.is-current .sf-steps-link { color: #0B2C3F; }

        /* ---- Two-column checkout layout ---- */
        .sf-cart-layout, .sf-checkout-layout { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 24px; align-items: start; }
        .sf-cart-main, .sf-checkout-main { min-width: 0; display: flex; flex-direction: column; gap: 16px; }
        .sf-cart-main > .sf-panel + .sf-panel, .sf-checkout-main > .sf-panel + .sf-panel { margin-top: 0; }
        #cart-summary.is-busy, .sf-checkout-page.is-busy { opacity: .6; pointer-events: none; transition: opacity .2s; }

        /* ---- Cart groups and rows ---- */
        .sf-cart-group { background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; box-shadow: 0 10px 26px -24px rgba(11,44,63,.9); overflow: hidden; }
        .sf-cart-group-head { display: flex; align-items: center; flex-wrap: wrap; gap: 10px 12px; padding: 14px 18px; background: #F4FAFB; border-bottom: 1px solid #EDF5F6; }
        .sf-cart-shop { display: inline-flex; align-items: center; gap: 8px; min-width: 0; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 700; color: #0B2C3F; }
        .sf-cart-shop:hover { color: #0E8E88; }
        .sf-cart-shop > span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sf-cart-group-shipping { margin-inline-start: auto; min-width: 0; }
        .sf-cart-group-shipping select { width: auto; max-width: 100%; }
        .sf-cart-items { margin: 0; padding: 0; list-style: none; }
        .sf-cart-item { display: grid; grid-template-columns: auto 76px minmax(0, 1fr) auto; align-items: center; gap: 14px; padding: 16px 18px; }
        .sf-cart-item + .sf-cart-item { border-top: 1px solid #EDF5F6; }
        .sf-cart-item-media { position: relative; display: block; width: 76px; height: 76px; border-radius: 14px; overflow: hidden; background: #EAF4F5; border: 1px solid #E4EFF1; }
        .sf-cart-item-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .sf-cart-item-na { position: absolute; inset: 0; display: grid; place-items: center; background: rgba(11,44,63,.55); color: #FFFFFF; font-family: Cairo, sans-serif; font-size: 12px; font-weight: 800; }
        .sf-cart-item.is-unavailable .sf-cart-item-body { opacity: .6; }
        .sf-cart-item-body { min-width: 0; display: flex; flex-direction: column; gap: 6px; }
        .sf-cart-item-side { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
        .sf-cart-item-total { font-family: Cairo, sans-serif; font-size: 17px; font-weight: 800; color: #0E8E88; white-space: nowrap; }
        .sf-qty { display: inline-flex; align-items: center; border: 1px solid #E4EFF1; border-radius: 12px; background: #FFFFFF; overflow: hidden; }
        .sf-qty-btn { width: 40px; height: 40px; border: none; background: transparent; color: #0B2C3F; display: grid; place-items: center; cursor: pointer; transition: background .2s, color .2s; }
        .sf-qty-btn:hover { background: #ECF8F8; color: #0E8E88; }
        .sf-qty-btn.is-remove { color: #E4574F; }
        .sf-qty-btn.is-remove:hover { background: #FDF0EF; color: #C63F38; }
        .sf-qty-btn:focus-visible { outline: none; box-shadow: inset 0 0 0 2px #17B0A7; }
        .sf-qty-input { width: 46px; height: 40px; border: none; border-inline: 1px solid #EDF5F6; text-align: center; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 700; color: #0B2C3F; background: #FFFFFF; outline: none; }
        .sf-qty-input:focus { background: #F4FAFB; }
        .sf-free-delivery { display: flex; flex-direction: column; gap: 8px; padding: 12px 18px 16px; border-top: 1px solid #EDF5F6; }
        .sf-progress { display: block; height: 6px; border-radius: 999px; background: #EDF5F6; overflow: hidden; }
        .sf-progress > span { display: block; height: 100%; border-radius: inherit; background: #17B0A7; }

        /* ---- Order summary ---- */
        .sf-summary { position: sticky; top: 18px; min-width: 0; }
        .sf-summary-saved { display: flex; align-items: center; gap: 9px; margin: 0 0 16px; padding: 10px 14px; border-radius: 12px; background: #ECF8F8; color: #0E8E88; font-size: 14px; }
        .sf-coupon-form { display: flex; gap: 8px; margin-bottom: 16px; }
        .sf-coupon-form .sf-filter-field { height: 44px; min-width: 0; }
        .sf-coupon-form .sf-btn-sm { min-height: 44px; }
        .sf-coupon-applied { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 16px; padding: 10px 12px; border: 1.5px dashed #BEE6E4; border-radius: 12px; background: #F4FAFB; }
        .sf-summary-terms { margin-top: 16px; align-items: flex-start; }
        .sf-summary-terms .sf-check-box { margin-top: 3px; }
        .sf-summary-terms .sf-check-label { font-size: 13.5px; line-height: 1.8; }
        .sf-summary .sf-checkout-proceed { margin-top: 18px; }
        .sf-summary-continue { display: flex; align-items: center; justify-content: center; gap: 7px; min-height: 44px; margin-top: 8px; font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; color: #0E8E88; }
        .sf-summary-continue:hover { color: #0B2C3F; }
        .sf-summary-bar { display: none; }

        /* ---- Address and payment choices ---- */
        .sf-choice-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(220px, 100%), 1fr)); gap: 12px; margin: 0 0 18px; padding: 0; list-style: none; }
        .sf-choice { position: relative; display: flex; align-items: flex-start; gap: 11px; padding: 14px; border: 1.5px solid #E4EFF1; border-radius: 16px; background: #FFFFFF; cursor: pointer; transition: border-color .2s, background .2s, box-shadow .2s; }
        .sf-choice:hover { border-color: #BEE6E4; }
        .sf-choice input { position: absolute; opacity: 0; width: 1px; height: 1px; }
        .sf-choice-dot { width: 20px; height: 20px; margin-top: 2px; flex-shrink: 0; border-radius: 50%; border: 2px solid #CFE9EA; background: #FFFFFF; transition: border-color .2s, box-shadow .2s; }
        .sf-choice:has(input:checked) { border-color: #17B0A7; background: #ECF8F8; }
        .sf-choice:has(input:checked) .sf-choice-dot { border-color: #17B0A7; box-shadow: inset 0 0 0 4px #FFFFFF; background: #17B0A7; }
        .sf-choice:has(input:focus-visible) { box-shadow: 0 0 0 3px rgba(23,176,167,.25); }
        .sf-choice-body { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .sf-choice-title { font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; color: #0B2C3F; text-transform: capitalize; }
        .sf-choice-text { font-size: 13px; line-height: 1.7; color: #6E8C99; overflow-wrap: anywhere; }
        .sf-choice-media { width: 32px; height: 32px; flex-shrink: 0; object-fit: contain; }

        @media (max-width: 979px) {
            .sf-cart-layout, .sf-checkout-layout { grid-template-columns: minmax(0, 1fr); }
            .sf-summary { position: static; }
            .sf-summary-bar { position: sticky; bottom: 12px; z-index: 20; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 16px; box-shadow: 0 18px 36px -18px rgba(11,44,63,.75); }
            .sf-summary-bar .sf-summary-total { font-size: 17px; }
            .sf-summary-bar .sf-btn-sm { min-height: 46px; padding: 0 18px; font-size: 14px; }
        }

        @media (max-width: 759px) {
            .sf-steps-label { font-size: 12.5px; white-space: normal; line-height: 1.35; }
            .sf-steps-item { flex-direction: column; align-items: flex-start; gap: 6px; }
            .sf-steps-item:not(:last-child)::after { display: none; }
            .sf-steps-link { flex-direction: column; align-items: center; text-align: center; gap: 6px; width: 100%; }
            .sf-cart-group-head { padding: 12px 14px; }
            .sf-cart-group-shipping { margin-inline-start: 0; width: 100%; }
            .sf-cart-group-shipping select { width: 100%; }
            .sf-cart-item { grid-template-columns: auto 64px minmax(0, 1fr); gap: 12px; padding: 14px; }
            .sf-cart-item-media { width: 64px; height: 64px; }
            .sf-cart-item-side { grid-column: 2 / -1; flex-direction: row; align-items: center; justify-content: space-between; }
            /* Clear the fixed mobile navigation bar. */
            .sf-summary-bar { bottom: calc(82px + env(safe-area-inset-bottom)); }
        }
    </style>
