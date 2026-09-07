    <style>
        .sf-lift { transition: transform .28s, box-shadow .28s, border-color .28s; }
        .sf-lift:hover { transform: translateY(-6px); box-shadow: 0 24px 42px -26px rgba(11, 44, 63, .85); border-color: #BEE6E4; }
        .sf-nav-link { transition: background .2s, color .2s; }
        .sf-nav-link:hover { background: #F1F7F8; color: #0B2C3F; }
        .sf-tool { transition: background .2s, border-color .2s; }
        .sf-tool:hover { background: #ECF8F8; border-color: #BEE6E4; }
        .sf-btn-primary { transition: background .22s, transform .22s; }
        .sf-btn-primary:hover { background: #14C6BB; color: #062A3E; transform: translateY(-2px); }
        .sf-btn-ghost { transition: background .22s, border-color .22s, color .22s; }
        .sf-btn-ghost:hover { background: #FFFFFF; color: #0B2C3F; border-color: #FFFFFF; }
        .seafood-cart-toggle { flex-shrink: 0; display: inline-flex; align-items: center; gap: 7px; height: 44px; padding: 0 16px; border: none; border-radius: 13px; background: #ECF8F8; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; white-space: nowrap; cursor: pointer; transition: background .22s, color .22s, transform .22s; }
        .seafood-cart-toggle:hover { background: #17B0A7; color: #FFFFFF; transform: translateY(-2px); }
        .seafood-cart-toggle.in-cart { background: #0B2C3F; color: #FFFFFF; }
        .seafood-cart-toggle.in-cart:hover { background: #17B0A7; }
        .seafood-wishlist-btn:hover { transform: scale(1.1); }
        .sf-social { transition: background .22s, color .22s, border-color .22s, transform .22s; }
        .sf-social:hover { background: #17B0A7; color: #062A3E; border-color: #17B0A7; transform: translateY(-2px); }
        .sf-footer-link { color: #9FBDCB; }
        .sf-footer-link:hover { color: #7FE3DC; }
        .sf-tab { padding: 10px 18px; border-radius: 999px; font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; cursor: pointer; white-space: nowrap; border: 1.4px solid #E4EFF1; background: #FFFFFF; color: #45677A; transition: background .22s, color .22s, border-color .22s, box-shadow .22s; }
        .sf-tab:hover { border-color: #17B0A7; color: #0B2C3F; }
        .sf-tab.active { background: #0B2C3F; color: #FFFFFF; border-color: #17B0A7; box-shadow: 0 8px 20px -12px rgba(11,44,63,.9); }
        .sf-see-more { display: inline-flex; text-decoration: none; align-items: center; justify-content: center; gap: 9px; min-height: 52px; padding: 0 34px; border-radius: 14px; border: 1.5px solid #CFE9EA; background: #FFFFFF; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 16px; font-weight: 700; cursor: pointer; transition: background .22s, border-color .22s, transform .22s; }
        .sf-see-more:hover { background: #ECF8F8; border-color: #17B0A7; transform: translateY(-2px); }
        .sf-see-more-icon { width: 24px; height: 24px; border-radius: 50%; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; }

        .sf-burger { display: none; }
        .sf-mobile-bar { display: none; }
        .sf-mobile-bar-spacer { display: none; }
        .sf-mobile-menu { display: none; }
        .sf-mobile-menu[data-open="true"] { display: flex; }
        .sf-mobile-menu a:hover { background: #F1F7F8; color: #0B2C3F; }
        .sf-tabs::-webkit-scrollbar { display: none; }

        @media (min-width: 980px) {
            .sf-mobile-menu { display: none !important; }
        }

        @media (max-width: 979px) {
            .sf-desktop-nav { display: none !important; }
            .sf-header-lang { display: none !important; }
            .sf-burger { display: grid; }
            /* Keep the bar on one row: the brand shrinks, the tools never do. */
            .sf-header-inner { gap: 10px !important; flex-wrap: nowrap !important; }
            .sf-header-brand { flex-shrink: 1 !important; overflow: hidden; }
            .sf-header-brand-text { overflow: hidden; }
            .sf-header-brand-text > strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            /* A wide logo (or the 4:1 placeholder) must not eat the store name. */
            .sf-header-logo { max-width: 120px !important; }
            .sf-header-search input { width: 150px !important; }
        }

        @media (max-width: 899px) {
            .sf-topbar-info { display: none !important; }
        }

        @media (max-width: 759px) {
            .sf-product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 12px !important; }
            .sf-product-title { font-size: 15px !important; line-height: 1.45 !important; }
            .sf-product-rule { margin: 10px 0 !important; }
            .sf-product-price { font-size: 19px !important; }
            .sf-product-badge { font-size: 10.5px !important; }
            .sf-product-actions { flex-direction: column !important; align-items: stretch !important; }
            .sf-product-actions .seafood-cart-toggle, .sf-product-actions .sf-product-action { width: 100%; padding: 0 14px; font-size: 13px; }
            .sf-tabs { flex-wrap: nowrap !important; }
            .sf-mobile-bar { display: grid; }
            .sf-mobile-bar-spacer { display: block; height: calc(70px + env(safe-area-inset-bottom)); }
            /* Comfortable tap targets for the dense text links on touch screens. */
            .sf-footer-links { gap: 2px !important; }
            .sf-footer-links a { display: flex; align-items: center; min-height: 44px; }
            .sf-social { width: 44px !important; height: 44px !important; }
            .sf-section-link { min-height: 44px; }
        }

        @media (max-width: 699px) {
            .sf-header-search { display: none !important; }
            .sf-header-account { display: none !important; }
            .sf-header-wishlist { display: none !important; }
        }

        @media (max-width: 559px) {
            .sf-topbar-whatsapp { display: none !important; }
            .sf-cart-label { display: none; }
            .sf-header-cart { padding: 0 12px !important; }
            .sf-hero-facts { grid-template-columns: 1fr !important; }
            .sf-ticker-item { padding: 0 18px !important; }
            .sf-ticker-item strong { font-size: 15px !important; }
        }

        @media (max-width: 479px) {
            .sf-gutter { padding-inline: 16px !important; }
            .sf-cat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            .sf-header-inner { padding: 10px 16px !important; }
            .sf-header-logo { max-width: 72px !important; }
            .sf-header-tagline { display: none !important; }
            .sf-hero-inner { padding: 48px 16px 52px !important; }
            .sf-hero-actions { flex-direction: column; }
        }
    </style>
