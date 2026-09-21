    <style>
        .sf-filter-group-title { display: block; font-family: Cairo, sans-serif; font-size: 13.5px; font-weight: 700; color: #0B2C3F; margin-bottom: 12px; }
        .sf-filter-card { background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; padding: 22px; }
        .sf-filter-nav-link { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 44px; padding: 0 12px; border-radius: 10px; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 600; color: #34586B; transition: background .2s, color .2s; }
        .sf-filter-nav-link:hover { background: #F1F7F8; color: #0B2C3F; }
        .sf-filter-nav-link.active { background: #ECF8F8; color: #0E8E88; }
        button.sf-filter-nav-link { width: 100%; border: none; background: transparent; text-align: start; cursor: pointer; }
        button.sf-filter-nav-link:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(23,176,167,.25); }
        .sf-category-tree { display: flex; flex-direction: column; }
        .sf-category-row { display: flex; align-items: center; gap: 4px; }
        .sf-category-link { flex: 1; min-width: 0; display: flex; align-items: center; gap: 10px; min-height: 44px; padding: 0 10px; border-radius: 10px; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 600; color: #34586B; transition: background .2s, color .2s; }
        .sf-category-link:hover { background: #F1F7F8; color: #0B2C3F; }
        .sf-category-link.active { background: #ECF8F8; color: #0E8E88; }
        .sf-category-link--sub { font-size: 14px; font-weight: 500; min-height: 40px; }
        .sf-category-icon { width: 24px; height: 24px; border-radius: 50%; object-fit: cover; flex-shrink: 0; background: #EAF4F5; }
        .sf-category-label { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sf-category-caret { flex-shrink: 0; width: 32px; height: 32px; border-radius: 9px; border: none; background: transparent; color: #A8B9C1; display: grid; place-items: center; cursor: pointer; transition: background .2s, color .2s, transform .22s; }
        .sf-category-caret:hover { background: #ECF8F8; color: #0E8E88; }
        .sf-category-children { display: none; padding-inline-start: 14px; margin-inline-start: 10px; border-inline-start: 1px solid #EDF5F6; }
        .sf-category-branch[data-open="true"] > .sf-category-children { display: block; }
        .sf-category-branch[data-open="true"] > .sf-category-row > .sf-category-caret { transform: rotate(-90deg); color: #0E8E88; }
        [dir="ltr"] .sf-category-branch[data-open="true"] > .sf-category-row > .sf-category-caret { transform: rotate(90deg); }
        .sf-range-wrap { position: relative; height: 22px; margin: 22px 0 4px; }
        .sf-range-track { position: absolute; inset-inline: 0; top: 8px; height: 6px; border-radius: 999px; background: #EDF5F6; }
        .sf-range-fill { position: absolute; top: 8px; height: 6px; border-radius: 999px; background: #17B0A7; }
        .sf-range { position: absolute; inset-inline: 0; top: 0; width: 100%; height: 22px; margin: 0; padding: 0; background: transparent; outline: none; pointer-events: none; -webkit-appearance: none; appearance: none; }
        .sf-range::-webkit-slider-runnable-track { height: 22px; background: transparent; border: none; }
        .sf-range::-moz-range-track { height: 22px; background: transparent; border: none; }
        .sf-range::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; pointer-events: auto; width: 22px; height: 22px; border-radius: 50%; background: #FFFFFF; border: 2px solid #17B0A7; cursor: pointer; }
        .sf-range::-moz-range-thumb { pointer-events: auto; width: 22px; height: 22px; border-radius: 50%; background: #FFFFFF; border: 2px solid #17B0A7; cursor: pointer; }
        .sf-range:focus-visible::-webkit-slider-thumb { box-shadow: 0 0 0 3px rgba(23,176,167,.25); }
        .sf-page-link:hover { border-color: #17B0A7 !important; background: #ECF8F8 !important; color: #0B2C3F !important; }

        .sf-icon-button { width: 44px; height: 44px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; display: grid; place-items: center; cursor: pointer; transition: background .2s, border-color .2s; }
        .sf-icon-button:hover { background: #ECF8F8; border-color: #BEE6E4; }
        .sf-filter-toggle { display: none; }
        .sf-filter-sheet-head { display: none; }
        .sf-filter-backdrop { display: none; position: fixed; inset: 0; z-index: 90; background: rgba(6,42,62,.55); }
        .sf-filter-backdrop[data-open="true"] { display: block; }
        .sf-skeleton-line { border-radius: 6px; background: linear-gradient(90deg, #EDF5F6 25%, #F8FCFC 50%, #EDF5F6 75%); background-size: 200% 100%; animation: shimmer 1.4s linear infinite; display: block; }
        .sf-skeleton-card { display: flex; flex-direction: column; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; overflow: hidden; }
        .sf-skeleton-media { aspect-ratio: 1 / 1; background: #EAF4F5; display: block; }
        .sf-skeleton-body { padding: 16px 18px 18px; display: flex; flex-direction: column; gap: 11px; }
        .sf-skeleton-action { height: 44px; border-radius: 13px; background: #F4FAFB; display: block; }
        @keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

        /* The old price widens the price group; above the mobile breakpoint the
           card is wide enough to keep it on one row with the CTA, as on the home page. */
        @media (min-width: 760px) {
            .sf-product-actions { flex-wrap: nowrap !important; gap: 8px !important; }
            .sf-product-actions > span:first-child { flex: 1 1 auto; min-width: 0; }
            .sf-product-price, .sf-product-old-price { white-space: nowrap; }
            .sf-product-actions .seafood-cart-toggle, .sf-product-actions .sf-product-action { flex-shrink: 0; padding: 0 13px; gap: 6px; }
        }

        @media (min-width: 980px) {
            .sf-filter-panel { position: sticky; top: 92px; max-height: calc(100vh - 112px); overflow-y: auto; }
        }

        @media (max-width: 979px) {
            .sf-listing-layout { grid-template-columns: 1fr !important; }
            .sf-filter-toggle { display: inline-flex; }
            .sf-filter-panel {
                position: fixed; inset-inline: 0; bottom: 0; top: auto; z-index: 100;
                max-height: 86vh; overflow-y: auto;
                border-radius: 24px 24px 0 0; background: #FFFFFF;
                box-shadow: 0 -22px 40px -18px rgba(0,0,0,.55);
                padding: 0 20px calc(20px + env(safe-area-inset-bottom));
                transform: translateY(100%); transition: transform .3s ease;
                overscroll-behavior: contain;
            }
            /* The sheet covers the listing; the page behind it must not scroll away. */
            body.sf-filter-sheet-open { position: fixed; inset-inline: 0; width: 100%; overflow: hidden; }
            .sf-category-caret { width: 44px; height: 44px; }
            .sf-category-link--sub { min-height: 44px; }
            .sf-filter-panel[data-open="true"] { transform: translateY(0); }
            .sf-filter-sheet-head { display: block; position: sticky; top: 0; background: #FFFFFF; padding: 14px 0 12px; z-index: 2; }
            .sf-filter-card { border: none; border-radius: 0; padding: 18px 0; border-bottom: 1px solid #EDF5F6; }
            .sf-filter-stack { gap: 0 !important; }
        }

        @media (max-width: 759px) {
            .sf-listing-toolbar { flex-direction: column !important; align-items: stretch !important; }
            .sf-listing-toolbar-actions { width: 100%; }
            .sf-sort-field, .sf-data-from-field { flex: 1 1 auto; min-width: 0; }
            .sf-sort-field select, .sf-data-from-field select { min-width: 0 !important; flex: 1; }
            .sf-filter-toggle { flex: 0 0 auto; }
        }
    </style>
