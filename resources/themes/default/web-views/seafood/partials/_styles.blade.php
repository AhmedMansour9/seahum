    <style>
        .sf-filter-field { width: 100%; height: 48px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; padding: 0 14px; font-family: Tajawal, sans-serif; font-size: 15px; outline: none; transition: border-color .2s, box-shadow .2s; }
        .sf-filter-field:focus { border: 1.5px solid #17B0A7; box-shadow: 0 0 0 3px rgba(23,176,167,.22); }
        .sf-filter-field::placeholder { color: #7B98A4; }
        .sf-search-wrap { position: relative; display: flex; align-items: center; }
        .sf-search-submit { position: absolute; inset-inline-end: 6px; width: 36px; height: 36px; border-radius: 10px; border: none; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; cursor: pointer; transition: background .2s, color .2s; }
        .sf-search-submit:hover { background: #17B0A7; color: #FFFFFF; }
        .sf-cat-card-title { overflow-wrap: anywhere; }

        .sf-card { background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; box-shadow: 0 10px 26px -24px rgba(11,44,63,.9); }
        .sf-form-label { display: block; font-family: Cairo, sans-serif; font-size: 13.5px; font-weight: 700; color: #0B2C3F; margin-bottom: 8px; }
        .sf-required { color: #E4574F; margin-inline-start: 2px; }
        .sf-field-hint { display: block; font-size: 12.5px; line-height: 1.7; color: #6E8C99; margin-top: 8px; }
        .sf-field-error { color: #E4574F; }
        .sf-filter-field.is-invalid { border: 1.5px solid #E4574F; box-shadow: 0 0 0 3px rgba(228,87,79,.16); }
        .sf-input-group { position: relative; display: flex; align-items: center; }
        .sf-input-group > .sf-filter-field { padding-inline-end: 48px; }
        .sf-input-action { position: absolute; inset-inline-end: 6px; width: 36px; height: 36px; border-radius: 10px; border: none; background: transparent; color: #7B98A4; display: grid; place-items: center; cursor: pointer; transition: background .2s, color .2s; }
        .sf-input-action:hover { background: #ECF8F8; color: #0E8E88; }
        .sf-input-action svg[hidden] { display: none; }
        .sf-check { display: inline-flex; align-items: center; gap: 11px; min-height: 44px; cursor: pointer; position: relative; }
        .sf-check input { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; border: 0; opacity: 0; clip-path: inset(50%); }
        .sf-check-box { width: 22px; height: 22px; border-radius: 7px; border: 1.5px solid #E4EFF1; background: #FFFFFF; display: grid; place-items: center; flex-shrink: 0; transition: background .2s, border-color .2s, box-shadow .2s; }
        .sf-check-box svg { stroke: transparent; }
        .sf-check input:checked + .sf-check-box { background: #17B0A7; border-color: #17B0A7; }
        .sf-check input:checked + .sf-check-box svg { stroke: #FFFFFF; }
        .sf-check input:focus-visible + .sf-check-box { box-shadow: 0 0 0 3px rgba(23,176,167,.25); }
        .sf-check-label { font-size: 14.5px; color: #34586B; }
        .sf-btn-block { width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 52px; padding: 0 30px; border: none; border-radius: 14px; background: #17B0A7; color: #FFFFFF; font-family: Cairo, sans-serif; font-size: 16px; font-weight: 700; cursor: pointer; box-shadow: 0 16px 32px -14px rgba(23,176,167,1); transition: background .22s, color .22s, transform .22s, box-shadow .22s; }
        .sf-btn-block:hover { background: #14C6BB; color: #062A3E; transform: translateY(-2px); box-shadow: 0 20px 36px -14px rgba(23,176,167,1); }
        .sf-btn-block:active { background: #0E8E88; color: #FFFFFF; transform: none; box-shadow: none; }
        .sf-btn-block:focus-visible { outline: none; box-shadow: 0 0 0 3px #FFFFFF, 0 0 0 5px #0E8E88; }
        .sf-btn-block:disabled { background: #BEE6E4; color: #FFFFFF; opacity: .7; box-shadow: none; transform: none; cursor: not-allowed; }
        .sf-btn-outline { display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%; min-height: 52px; padding: 0 20px; border: 1.5px solid #CFE9EA; border-radius: 14px; background: #FFFFFF; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 15px; font-weight: 700; cursor: pointer; transition: background .22s, border-color .22s, transform .22s; }
        .sf-btn-outline:hover { background: #ECF8F8; border-color: #17B0A7; color: #0B2C3F; transform: translateY(-2px); }
        .sf-btn-outline:focus-visible { outline: none; border-color: #17B0A7; box-shadow: 0 0 0 3px rgba(23,176,167,.25); }
        .sf-text-link { color: #0E8E88; font-weight: 700; }
        .sf-text-link:hover { color: #0B2C3F; }
        .sf-crumb-link { color: #6E8C99; }
        .sf-crumb-link:hover { color: #0E8E88; }

        .sf-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 20px; }
        .sf-form-grid > .sf-form-full { grid-column: 1 / -1; }
        .sf-filter-field.is-valid { border: 1.5px solid #CFE9EA; }
        textarea.sf-filter-field { height: auto; min-height: 150px; padding: 12px 14px; line-height: 1.85; resize: vertical; }
        .sf-field-note { display: block; font-size: 12.5px; line-height: 1.7; color: #E4574F; margin-top: 8px; min-height: 0; }
        .sf-step { display: flex; align-items: flex-start; gap: 12px; }
        .sf-step-num { width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; border: 2px solid #BEE6E4; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; font-family: Cairo, sans-serif; font-size: 14px; font-weight: 800; }
        .sf-step-text { font-size: 14.5px; line-height: 1.85; color: #55747F; overflow-wrap: anywhere; }
        .sf-divider-text { display: flex; align-items: center; gap: 14px; color: #6E8C99; font-size: 13px; }
        .sf-divider-text::before, .sf-divider-text::after { content: ""; flex: 1 1 auto; height: 1px; background: #E4EFF1; }
        .sf-social-btn { width: 52px; height: 52px; border-radius: 14px; border: 1px solid #E4EFF1; background: #FFFFFF; display: grid; place-items: center; transition: background .22s, border-color .22s, transform .22s; }
        .sf-social-btn:hover { background: #ECF8F8; border-color: #BEE6E4; transform: translateY(-2px); }
        .sf-social-btn img { width: 24px; height: 24px; object-fit: contain; }
        .sf-password-toggle .sf-eye-off { display: none; }
        .sf-password-toggle[aria-pressed="true"] .sf-eye { display: none; }
        .sf-password-toggle[aria-pressed="true"] .sf-eye-off { display: block; }
        /* The captcha widget is rendered by google-recaptcha-init.js, so it is styled from the outside. */
        .default-captcha-container { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .default-captcha-container input { flex: 1 1 180px; min-width: 0; height: 48px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; padding: 0 14px; font-family: Tajawal, sans-serif; font-size: 15px; outline: none; transition: border-color .2s, box-shadow .2s; }
        .default-captcha-container input:focus { border: 1.5px solid #17B0A7; box-shadow: 0 0 0 3px rgba(23,176,167,.22); }
        .default-captcha-container input::placeholder { color: #7B98A4; }
        .default-captcha-container .captcha-image-container { display: flex; align-items: center; gap: 8px; height: 48px; padding: 0 10px; border-radius: 12px; border: 1px solid #E4EFF1; background: #F4FAFB; cursor: pointer; }
        .default-captcha-container .captcha-image-container img { max-height: 34px; width: auto; }
        .default-captcha-container .refresh-icon { display: grid; place-items: center; color: #0E8E88; }

        /* intl-tel-input paints its own wrapper around the field; keep it on the design-system grid. */
        .iti { width: 100%; display: block; }
        .iti__country-list { font-family: Tajawal, sans-serif; text-align: start; }
        .iti__flag-container:focus-within + .sf-filter-field { border: 1.5px solid #17B0A7; box-shadow: 0 0 0 3px rgba(23,176,167,.22); }

        .sf-page-hero { position: relative; border-radius: 26px; overflow: hidden; background-color: #062A3E; background-size: cover; background-position: center; }
        .sf-page-hero-body { background: linear-gradient(200deg, rgba(6,42,62,.88) 0%, rgba(13,59,88,.8) 55%, rgba(14,107,114,.74) 100%); padding: clamp(36px, 6vw, 68px) clamp(20px, 4vw, 44px); }

        .sf-prose { color: #34586B; font-size: 15.5px; line-height: 1.9; overflow-wrap: anywhere; }
        .sf-prose > :first-child { margin-top: 0; }
        .sf-prose > :last-child { margin-bottom: 0; }
        .sf-prose h1, .sf-prose h2, .sf-prose h3, .sf-prose h4, .sf-prose h5, .sf-prose h6 { font-family: Cairo, sans-serif; color: #0B2C3F; line-height: 1.4; margin: 28px 0 12px; text-wrap: pretty; }
        .sf-prose h1 { font-size: clamp(24px, 3.2vw, 32px); font-weight: 800; }
        .sf-prose h2 { font-size: clamp(21px, 2.6vw, 26px); font-weight: 800; }
        .sf-prose h3 { font-size: 20px; font-weight: 800; }
        .sf-prose h4, .sf-prose h5, .sf-prose h6 { font-size: 17px; font-weight: 700; }
        .sf-prose p { margin: 0 0 16px; }
        .sf-prose a { color: #0E8E88; text-decoration: underline; }
        .sf-prose a:hover { color: #0B2C3F; }
        .sf-prose strong, .sf-prose b { color: #0B2C3F; font-weight: 700; }
        .sf-prose ul, .sf-prose ol { margin: 0 0 16px; padding-inline-start: 22px; }
        .sf-prose li { margin-bottom: 8px; }
        .sf-prose img { max-width: 100%; height: auto; border-radius: 14px; }
        .sf-prose iframe, .sf-prose video, .sf-prose embed { max-width: 100%; }
        .sf-prose hr { border: none; border-top: 1px solid #E4EFF1; margin: 26px 0; }
        .sf-prose blockquote { margin: 20px 0; padding: 14px 18px; border-inline-start: 3px solid #17B0A7; background: #F4FAFB; border-radius: 12px; }
        .sf-prose pre { overflow-x: auto; max-width: 100%; background: #F4FAFB; border: 1px solid #E4EFF1; border-radius: 12px; padding: 14px; }
        /* Admin-authored tables have no wrapper to scroll, so the table itself becomes the scroller. */
        .sf-prose table { display: block; width: max-content; max-width: 100%; overflow-x: auto; border-collapse: collapse; margin: 0 0 16px; }
        .sf-prose th, .sf-prose td { border: 1px solid #E4EFF1; padding: 10px 12px; text-align: start; min-width: 120px; }
        .sf-prose th { background: #F4FAFB; font-family: Cairo, sans-serif; font-weight: 700; color: #0B2C3F; }
        .sf-help-card { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; padding: 28px 20px; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; box-shadow: 0 10px 26px -24px rgba(11,44,63,.9); }
        .sf-help-icon { width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; }
        .sf-help-title { font-family: Cairo, sans-serif; font-size: 17px; font-weight: 700; color: #0B2C3F; margin-top: 6px; }
        .sf-help-text { font-size: 14px; line-height: 1.8; color: #6E8C99; }


        .sr-only { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; border: 0; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
        .sf-avatar-upload { position: relative; width: 108px; height: 108px; border-radius: 50%; cursor: pointer; display: block; }
        .sf-avatar-upload img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid #ECF8F8; display: block; }
        .sf-avatar-upload-badge { position: absolute; inset-block-end: 2px; inset-inline-end: 2px; width: 34px; height: 34px; border-radius: 50%; background: #17B0A7; color: #FFFFFF; display: grid; place-items: center; border: 2px solid #FFFFFF; transition: background .22s; }
        .sf-avatar-upload:hover .sf-avatar-upload-badge { background: #0E8E88; }
        .sf-avatar-upload input:focus-visible + .sr-only, .sf-avatar-upload:focus-within .sf-avatar-upload-badge { box-shadow: 0 0 0 3px rgba(23,176,167,.35); }

        .sf-segment { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; padding: 0; list-style: none; }
        .sf-segment input { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; border: 0; opacity: 0; clip-path: inset(50%); }
        .sf-segment label { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 18px; border-radius: 999px; border: 1.5px solid #E4EFF1; background: #FFFFFF; color: #45677A; font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .22s, color .22s, border-color .22s; }
        .sf-segment label:hover { border-color: #17B0A7; color: #0B2C3F; }
        .sf-segment input:checked + label { background: #0B2C3F; border-color: #17B0A7; color: #FFFFFF; }
        .sf-segment input:focus-visible + label { box-shadow: 0 0 0 3px rgba(23,176,167,.25); }

        .sf-map-canvas { height: 240px; width: 100%; border-radius: 14px; overflow: hidden; border: 1px solid #E4EFF1; background: #F4FAFB; }
        .sf-map-alert { display: block; font-size: 12.5px; line-height: 1.7; color: #E4574F; margin-top: 8px; opacity: 0; transition: opacity .2s; }
        .sf-map-alert.is-visible { opacity: 1; }
        .location-map-address-canvas-area.map-area-alert-danger .sf-map-canvas { border-color: #E4574F; }

        /* ---- Chat ----
           The message bubbles are re-rendered by chatting.js from the server partial, so the legacy
           class names stay and are restyled here rather than replaced. */
        .sf-chat { display: grid; grid-template-columns: minmax(0, 320px) minmax(0, 1fr); gap: 16px; align-items: stretch; }
        .sf-chat-aside { display: flex; flex-direction: column; min-width: 0; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; overflow: hidden; }
        .sf-chat-aside-head { padding: 14px; border-bottom: 1px solid #EDF5F6; display: flex; flex-direction: column; gap: 12px; }
        .sf-chat-tabs { display: flex; gap: 8px; }
        .sf-chat-tabs > a { flex: 1 1 0; display: inline-flex; align-items: center; justify-content: center; min-height: 44px; border-radius: 12px; border: 1.5px solid #E4EFF1; background: #FFFFFF; color: #45677A; font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; transition: background .22s, color .22s, border-color .22s; }
        .sf-chat-tabs > a:hover { border-color: #17B0A7; color: #0B2C3F; }
        .sf-chat-tabs > a.active { background: #0B2C3F; border-color: #17B0A7; color: #FFFFFF; }
        .sf-chat-list { flex: 1 1 auto; overflow-y: auto; max-height: 560px; padding: 8px; display: flex; flex-direction: column; gap: 4px; }
        .sf-chat-item { display: flex; align-items: center; gap: 11px; padding: 10px; border-radius: 14px; cursor: pointer; min-width: 0; transition: background .2s; }
        .sf-chat-item:hover { background: #F1F7F8; }
        .sf-chat-item.active { background: #ECF8F8; }
        .sf-chat-item-avatar { width: 46px; height: 46px; flex-shrink: 0; border-radius: 14px; overflow: hidden; background: #F4FAFB; border: 1px solid #E4EFF1; }
        .sf-chat-item-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .sf-chat-item-body { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
        .sf-chat-item-top { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; }
        .sf-chat-item-name { font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; color: #0B2C3F; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
        .sf-chat-item-date { font-size: 11.5px; color: #A8B9C1; flex-shrink: 0; white-space: nowrap; }
        .sf-chat-item-bottom { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .sf-chat-item-preview { font-size: 13px; color: #6E8C99; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
        .sf-chat-unread { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px; background: #17B0A7; color: #FFFFFF; font-family: Cairo, sans-serif; font-size: 11.5px; font-weight: 800; flex-shrink: 0; }

        .sf-chat-main { display: flex; flex-direction: column; min-width: 0; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; overflow: hidden; }
        .sf-chat-main-head { display: flex; align-items: center; gap: 11px; padding: 14px 18px; border-bottom: 1px solid #EDF5F6; background: #F4FAFB; }
        .sf-chat-main-head img { width: 44px; height: 44px; border-radius: 14px; object-fit: cover; border: 1px solid #E4EFF1; flex-shrink: 0; }
        .sf-chat-main-head h2 { font-family: Cairo, sans-serif; font-size: 16px; font-weight: 700; color: #0B2C3F; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
        .sf-chat-thread { display: flex; flex-direction: column-reverse; gap: 14px; padding: 20px 18px; overflow-y: auto; height: 460px; background: #FFFFFF; }
        .sf-chat-composer { border-top: 1px solid #EDF5F6; padding: 12px 14px; background: #F4FAFB; }
        .sf-chat-composer form { display: flex; align-items: flex-end; gap: 10px; }
        .sf-chat-composer textarea { flex: 1 1 auto; min-width: 0; min-height: 52px; max-height: 140px; border-radius: 14px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #0B2C3F; padding: 14px; font-family: Tajawal, sans-serif; font-size: 15px; line-height: 1.6; outline: none; resize: vertical; transition: border-color .2s, box-shadow .2s; }
        .sf-chat-composer textarea:focus { border: 1.5px solid #17B0A7; box-shadow: 0 0 0 3px rgba(23,176,167,.22); }
        .sf-chat-tools { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .sf-chat-tool { display: grid; place-items: center; width: 44px; height: 44px; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #45677A; cursor: pointer; transition: background .2s, border-color .2s, color .2s; }
        .sf-chat-tool:hover { background: #ECF8F8; border-color: #BEE6E4; color: #0E8E88; }
        .sf-chat-send { display: grid; place-items: center; width: 48px; height: 48px; flex-shrink: 0; border: none; border-radius: 14px; background: #17B0A7; color: #FFFFFF; cursor: pointer; transition: background .22s; }
        .sf-chat-send:hover { background: #0E8E88; }
        .sf-chat-attachments { display: flex; flex-wrap: wrap; gap: 8px; }
        .sf-chat-attachments:empty { display: none; }
        .sf-chat-alert { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #FDF4E6; border-top: 1px solid #F1DDB4; color: #A9720B; font-size: 13.5px; line-height: 1.7; }
        .sf-chat-alert.d-none { display: none; }

        /* Legacy message-bubble class names, restyled onto the design system. */
        #chatting-messages-section .incoming_msg, #chatting-messages-section .outgoing_msg { display: flex; align-items: flex-end; gap: 9px; max-width: 100%; }
        #chatting-messages-section .outgoing_msg { justify-content: flex-end; }
        #chatting-messages-section .incoming_msg_img img { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid #E4EFF1; }
        #chatting-messages-section .received_msg { display: flex; flex-direction: column; align-items: flex-start; min-width: 0; max-width: min(78%, 520px); }
        #chatting-messages-section .received_withdraw_msg, #chatting-messages-section .send_msg { display: flex; flex-direction: column; gap: 8px; padding: 12px 15px; border-radius: 16px; font-size: 14.5px; line-height: 1.8; overflow-wrap: anywhere; }
        #chatting-messages-section .received_withdraw_msg { background: #F1F7F8; color: #34586B; border: 1px solid #E4EFF1; border-start-start-radius: 6px; }
        #chatting-messages-section .send_msg { background: #ECF8F8; color: #0B2C3F; border: 1px solid #BEE6E4; border-start-end-radius: 6px; align-items: flex-end; max-width: min(78%, 520px); margin-inline-start: auto; }
        #chatting-messages-section p { margin: 0; }
        #chatting-messages-section .uploaded-file-item { display: flex; align-items: center; gap: 10px; padding: 9px 11px; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 12px; max-width: 260px; }
        #chatting-messages-section .file-icon { width: 28px; height: 28px; object-fit: contain; flex-shrink: 0; }
        #chatting-messages-section .pdf-file-name { font-size: 13px; font-weight: 700; color: #0B2C3F; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px; }
        #chatting-messages-section .upload-file-item-content small { font-size: 11.5px; color: #7B98A4; }
        #chatting-messages-section .btn--download { display: inline-grid; place-items: center; width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; background: #ECF8F8; color: #0E8E88; }
        #chatting-messages-section .btn--download:hover { background: #17B0A7; color: #FFFFFF; }
        #chatting-messages-section .zip-images { display: flex; flex-wrap: wrap; gap: 6px; max-width: 200px; }
        #chatting-messages-section .zip-images img, #chatting-messages-section .zip-images video { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; border: 1px solid #E4EFF1; display: block; }
        #chatting-messages-section .extra-images { position: absolute; inset: 0; border-radius: 10px; background: rgba(11,44,63,.62); display: grid; place-items: center; }
        #chatting-messages-section .extra-image-count { color: #FFFFFF; font-family: Cairo, sans-serif; font-size: 13px; font-weight: 800; }
        #chatting-messages-section .video-play-btn { position: absolute; inset-block-start: 50%; inset-inline-start: 50%; transform: translate(-50%, -50%); width: 26px; height: 26px; border: none; border-radius: 50%; background: rgba(255,255,255,.92); color: #0E8E88; display: grid; place-items: center; cursor: pointer; padding: 0; }
        .sf-lightbox-strip { display: flex; gap: 14px; overflow-x: auto; padding-bottom: 8px; }
        .sf-lightbox-item { flex: 0 0 auto; max-width: 100%; display: flex; flex-direction: column; gap: 10px; }
        .sf-lightbox-item img, .sf-lightbox-item video { max-width: min(560px, 78vw); max-height: 60vh; border-radius: 14px; display: block; }

        /* The chat message partial is server-rendered legacy markup re-injected by chatting.js;
           these scoped utilities keep its layout intact without the legacy stylesheet. */
        #chatting-messages-section .d-flex, .imgViewModal .d-flex { display: flex; }
        #chatting-messages-section .d-inline-block { display: inline-block; }
        #chatting-messages-section .d-none { display: none !important; }
        #chatting-messages-section .flex-column { flex-direction: column; }
        #chatting-messages-section .flex-wrap, .imgViewModal .flex-wrap { flex-wrap: wrap; }
        #chatting-messages-section .align-items-center, .imgViewModal .align-items-center { align-items: center; }
        #chatting-messages-section .align-items-start { align-items: flex-start; }
        #chatting-messages-section .align-items-end, .imgViewModal .align-items-end { align-items: center; }
        #chatting-messages-section .justify-content-start { justify-content: flex-start; }
        #chatting-messages-section .justify-content-end { justify-content: flex-end; }
        #chatting-messages-section .justify-content-between, .imgViewModal .justify-content-between { justify-content: space-between; }
        #chatting-messages-section .justify-content-center, .imgViewModal .justify-content-center { justify-content: center; }
        #chatting-messages-section .gap-2, .imgViewModal .gap-2 { gap: 8px; }
        #chatting-messages-section .gap-3 { gap: 12px; }
        #chatting-messages-section .row { display: flex; }
        #chatting-messages-section .position-relative, .imgViewModal .position-relative { position: relative; }
        #chatting-messages-section .flex-shrink-0 { flex-shrink: 0; }
        #chatting-messages-section .max-w-150px { max-width: 150px; }
        #chatting-messages-section .aspect-1 { aspect-ratio: 1 / 1; }
        #chatting-messages-section .overflow-hidden { overflow: hidden; }
        #chatting-messages-section .w-60px { width: 60px; }
        #chatting-messages-section .img-fit { object-fit: cover; }
        #chatting-messages-section .rounded { border-radius: 10px; }
        #chatting-messages-section .border { border: 1px solid #E4EFF1; }
        .imgViewModal .btn--download { display: inline-grid; place-items: center; width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,.16); color: #FFFFFF; }
        .imgViewModal .btn--download:hover { background: #17B0A7; }
        .imgViewModal .btn-close { width: 36px; height: 36px; border: none; border-radius: 10px; background: rgba(255,255,255,.16); color: #FFFFFF; display: grid; place-items: center; cursor: pointer; }
        .imgViewModal .btn-close:hover { background: rgba(255,255,255,.3); }
        .imgViewModal .text-white { color: #FFFFFF; }
        .imgViewModal h6 { font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; margin: 0; color: #FFFFFF; }
        .imgViewModal .mb-10px { margin-bottom: 10px; }
        .imgViewModal .video-play-btn { position: absolute; inset-block-start: 50%; inset-inline-start: 50%; transform: translate(-50%, -50%); width: 44px; height: 44px; border: none; border-radius: 50%; background: rgba(255,255,255,.9); display: grid; place-items: center; cursor: pointer; }

        /* ---- Account shell ---- */
        .sf-account-layout { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 24px; align-items: start; }
        .sf-account-main { min-width: 0; }
        .sf-account-nav { background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; box-shadow: 0 10px 26px -24px rgba(11,44,63,.9); padding: 14px; position: sticky; top: 18px; }
        .sf-account-nav-head { display: flex; align-items: center; gap: 11px; padding: 8px 8px 16px; border-bottom: 1px solid #EDF5F6; margin-bottom: 10px; }
        .sf-account-avatar { width: 44px; height: 44px; flex-shrink: 0; border-radius: 14px; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; }
        .sf-account-nav-identity { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .sf-account-nav-identity > strong { font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; color: #0B2C3F; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sf-account-nav-identity > span { font-size: 12.5px; color: #6E8C99; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sf-account-nav-close { display: none; margin-inline-start: auto; width: 36px; height: 36px; flex-shrink: 0; border: none; border-radius: 10px; background: #F4FAFB; color: #45677A; place-items: center; cursor: pointer; }
        .sf-account-nav-list { display: flex; flex-direction: column; gap: 2px; }
        .sf-account-link { display: flex; align-items: center; gap: 11px; min-height: 44px; padding: 0 12px; border-radius: 12px; color: #34586B; font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; transition: background .2s, color .2s; }
        .sf-account-link > svg { flex-shrink: 0; color: #6E8C99; transition: color .2s; }
        .sf-account-link-label { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sf-account-link:hover { background: #F1F7F8; color: #0B2C3F; }
        .sf-account-link:hover > svg { color: #0E8E88; }
        .sf-account-link.active { background: #ECF8F8; color: #0E8E88; }
        .sf-account-link.active > svg { color: #0E8E88; }
        .sf-account-link:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(23,176,167,.25); }
        .sf-account-link-danger { color: #E4574F; }
        .sf-account-link-danger > svg { color: #E4574F; }
        .sf-account-link-danger:hover { background: #FDF0EF; color: #C63F38; }
        .sf-account-link-danger:hover > svg { color: #C63F38; }
        .sf-account-nav-group { margin-top: 14px; padding-top: 12px; border-top: 1px solid #EDF5F6; }
        .sf-account-nav-group-title { display: block; font-family: Cairo, sans-serif; font-size: 12.5px; font-weight: 800; letter-spacing: .5px; color: #6E8C99; padding: 0 12px 8px; }
        .sf-account-nav-backdrop { display: none; position: fixed; inset: 0; background: rgba(11,44,63,.45); z-index: 1040; }

        .sf-page-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
        .sf-page-head-title { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .sf-page-title { font-family: Cairo, sans-serif; font-size: clamp(20px, 2.6vw, 26px); font-weight: 800; color: #0B2C3F; margin: 0; overflow-wrap: anywhere; text-wrap: pretty; }
        .sf-page-count { display: inline-flex; align-items: center; height: 26px; padding: 0 10px; border-radius: 999px; background: #ECF8F8; color: #0E8E88; font-family: Cairo, sans-serif; font-size: 13px; font-weight: 800; flex-shrink: 0; }
        .sf-page-head-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
        .sf-account-nav-toggle { display: none; width: 44px; height: 44px; flex-shrink: 0; border: 1px solid #E4EFF1; border-radius: 12px; background: #FFFFFF; color: #0B2C3F; place-items: center; cursor: pointer; }
        .sf-account-nav-toggle:hover { background: #ECF8F8; border-color: #BEE6E4; }

        /* ---- Shared content blocks ---- */
        .sf-empty { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; padding: 44px 22px; }
        .sf-empty-icon { width: 60px; height: 60px; border-radius: 20px; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; margin-bottom: 8px; }
        .sf-empty-title { font-family: Cairo, sans-serif; font-size: 17px; font-weight: 700; color: #0B2C3F; margin: 0; overflow-wrap: anywhere; }
        .sf-empty-text { font-size: 14px; color: #6E8C99; line-height: 1.8; margin: 0 auto 10px; max-width: 420px; overflow-wrap: anywhere; }

        .sf-panel { background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 20px; box-shadow: 0 10px 26px -24px rgba(11,44,63,.9); padding: clamp(16px, 2.6vw, 24px); }
        .sf-panel + .sf-panel { margin-top: 16px; }
        .sf-panel-title { font-family: Cairo, sans-serif; font-size: 17px; font-weight: 700; color: #0B2C3F; margin: 0 0 16px; }
        .sf-stack { display: flex; flex-direction: column; gap: 12px; }

        .sf-row-card { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 18px; padding: 16px; transition: border-color .22s, box-shadow .22s; }
        .sf-row-card:hover { border-color: #BEE6E4; box-shadow: 0 16px 32px -28px rgba(11,44,63,.9); }
        .sf-row-media { width: 76px; height: 76px; flex-shrink: 0; border-radius: 14px; overflow: hidden; background: #F4FAFB; border: 1px solid #E4EFF1; }
        .sf-row-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .sf-row-body { flex: 1 1 220px; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
        .sf-row-title { font-family: Cairo, sans-serif; font-size: 15.5px; font-weight: 700; color: #0B2C3F; margin: 0; overflow-wrap: anywhere; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .sf-row-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 6px 14px; font-size: 13.5px; color: #6E8C99; overflow-wrap: anywhere; }
        .sf-row-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-inline-start: auto; }

        .sf-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px 18px; }
        .sf-meta-item { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
        .sf-meta-label { font-size: 12.5px; color: #6E8C99; }
        .sf-meta-value { font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; color: #0B2C3F; overflow-wrap: anywhere; }

        .sf-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border-radius: 999px; font-family: Cairo, sans-serif; font-size: 12.5px; font-weight: 700; white-space: nowrap; background: #ECF8F8; color: #0E8E88; border: 1px solid #BEE6E4; }
        .sf-badge-neutral { background: #F4FAFB; color: #45677A; border-color: #E4EFF1; }
        .sf-badge-success { background: #EAF7F0; color: #1E8A5B; border-color: #BFE6D2; }
        .sf-badge-warning { background: #FDF4E6; color: #A9720B; border-color: #F1DDB4; }
        .sf-badge-danger { background: #FDF0EF; color: #C63F38; border-color: #F4CFCC; }
        .sf-badge-info { background: #EDF3FB; color: #2F6BB0; border-color: #CCDEF2; }

        .sf-btn-sm { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 14px; border-radius: 12px; border: 1.5px solid #CFE9EA; background: #FFFFFF; color: #0B2C3F; font-family: Cairo, sans-serif; font-size: 13.5px; font-weight: 700; white-space: nowrap; cursor: pointer; transition: background .22s, border-color .22s, color .22s; }
        .sf-btn-sm:hover { background: #ECF8F8; border-color: #17B0A7; color: #0B2C3F; }
        .sf-btn-sm:focus-visible { outline: none; border-color: #17B0A7; box-shadow: 0 0 0 3px rgba(23,176,167,.25); }
        .sf-btn-sm-primary { background: #17B0A7; border-color: #17B0A7; color: #FFFFFF; }
        .sf-btn-sm-primary:hover { background: #14C6BB; border-color: #14C6BB; color: #062A3E; }
        .sf-btn-sm-danger { border-color: #F4CFCC; color: #C63F38; }
        .sf-btn-sm-danger:hover { background: #FDF0EF; border-color: #E4574F; color: #C63F38; }
        .sf-icon-btn { display: inline-grid; place-items: center; width: 40px; height: 40px; flex-shrink: 0; border-radius: 12px; border: 1px solid #E4EFF1; background: #FFFFFF; color: #45677A; cursor: pointer; transition: background .22s, border-color .22s, color .22s; }
        .sf-icon-btn:hover { background: #ECF8F8; border-color: #BEE6E4; color: #0E8E88; }
        .sf-icon-btn-danger:hover { background: #FDF0EF; border-color: #F4CFCC; color: #C63F38; }

        select.sf-filter-field { appearance: none; -webkit-appearance: none; padding-inline-end: 38px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236E8C99' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 14px center; cursor: pointer; }
        [dir="ltr"] select.sf-filter-field { background-position: right 14px center; }

        .sf-table-wrap { width: 100%; overflow-x: auto; border: 1px solid #E4EFF1; border-radius: 18px; background: #FFFFFF; }
        .sf-table { width: 100%; min-width: 620px; border-collapse: collapse; }
        .sf-table th, .sf-table td { padding: 14px 16px; text-align: start; border-bottom: 1px solid #EDF5F6; font-size: 14px; color: #34586B; vertical-align: middle; }
        .sf-table th { background: #F4FAFB; font-family: Cairo, sans-serif; font-size: 13px; font-weight: 800; color: #0B2C3F; white-space: nowrap; }
        .sf-table tr:last-child td { border-bottom: none; }

        .sf-accordion { display: flex; flex-direction: column; gap: 12px; }
        .sf-accordion-item { background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 18px; overflow: hidden; transition: border-color .22s, box-shadow .22s; }
        .sf-accordion-item[open] { border-color: #BEE6E4; box-shadow: 0 16px 32px -28px rgba(11,44,63,.9); }
        .sf-accordion-item > summary { display: flex; align-items: center; gap: 12px; padding: 16px 18px; cursor: pointer; list-style: none; font-family: Cairo, sans-serif; font-size: 15.5px; font-weight: 700; color: #0B2C3F; min-height: 44px; }
        .sf-accordion-item > summary::-webkit-details-marker { display: none; }
        .sf-accordion-item > summary:focus-visible { outline: none; box-shadow: inset 0 0 0 2px #17B0A7; }
        .sf-accordion-q { flex: 1 1 auto; min-width: 0; overflow-wrap: anywhere; text-wrap: pretty; }
        .sf-accordion-caret { width: 30px; height: 30px; flex-shrink: 0; border-radius: 10px; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; transition: transform .22s, background .22s; }
        .sf-accordion-item[open] .sf-accordion-caret { background: #17B0A7; color: #FFFFFF; transform: rotate(180deg); }
        .sf-accordion-body { padding: 0 18px 18px; font-size: 14.5px; line-height: 1.9; color: #55747F; overflow-wrap: anywhere; }

        .sf-dropdown { position: relative; }
        .sf-dropdown-menu { position: absolute; inset-inline-end: 0; top: calc(100% + 6px); min-width: 190px; background: #FFFFFF; border: 1px solid #E4EFF1; border-radius: 14px; box-shadow: 0 24px 42px -26px rgba(11,44,63,.85); padding: 6px; z-index: 30; }
        .sf-dropdown-menu[hidden] { display: none; }
        .sf-dropdown-item { display: flex; align-items: center; gap: 9px; min-height: 44px; padding: 0 12px; border-radius: 10px; font-family: Cairo, sans-serif; font-size: 14px; font-weight: 700; color: #34586B; }
        .sf-dropdown-item:hover { background: #F1F7F8; color: #0B2C3F; }
        .sf-dropdown-item-danger { color: #E4574F; }
        .sf-dropdown-item-danger:hover { background: #FDF0EF; color: #C63F38; }

        .sf-modal { position: fixed; inset: 0; z-index: 1050; display: flex; align-items: center; justify-content: center; padding: 20px; background: rgba(11,44,63,.5); overflow-y: auto; }
        .sf-modal[hidden] { display: none; }
        .sf-modal-dialog { background: #FFFFFF; border-radius: 22px; width: 100%; max-width: 620px; max-height: calc(100vh - 40px); overflow-y: auto; box-shadow: 0 30px 60px -30px rgba(11,44,63,.9); }
        .sf-modal-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 18px 22px; border-bottom: 1px solid #EDF5F6; position: sticky; top: 0; background: #FFFFFF; border-radius: 22px 22px 0 0; }
        .sf-modal-title { font-family: Cairo, sans-serif; font-size: 17px; font-weight: 700; color: #0B2C3F; margin: 0; }
        .sf-modal-body { padding: 22px; }

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
            .sf-search-submit { width: 44px !important; height: 44px !important; inset-inline-end: 2px !important; }
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

        @media (max-width: 979px) {
            .sf-account-layout { grid-template-columns: minmax(0, 1fr) !important; }
            .sf-account-nav { position: fixed; inset-block: 0; inset-inline-start: 0; width: min(320px, 88vw); border-radius: 0; border: none; border-inline-end: 1px solid #E4EFF1; z-index: 1045; overflow-y: auto; transform: translateX(var(--sf-account-nav-offset, -100%)); transition: transform .28s; }
            [dir="rtl"] .sf-account-nav { transform: translateX(calc(var(--sf-account-nav-offset, -100%) * -1)); }
            .sf-account-nav[data-open="true"] { --sf-account-nav-offset: 0%; }
            .sf-account-nav-backdrop[data-open="true"] { display: block; }
            .sf-account-nav-close { display: grid; }
            .sf-account-nav-toggle { display: grid; }
        }

        @media (max-width: 899px) {
            .sf-topbar-info { display: none !important; }
        }

        @media (max-width: 759px) {
            .sf-help-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 12px !important; }
            .sf-help-card { padding: 20px 14px; }
            .sf-help-text { display: none; }
            .sf-search-wrap { flex: 1 1 100%; }
            .sf-search-field { width: 100% !important; }
            .sf-product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 12px !important; }
            .sf-product-title { font-size: 15px !important; line-height: 1.45 !important; }
            .sf-product-rule { margin: 10px 0 !important; }
            .sf-product-price { font-size: 19px !important; }
            .sf-product-badge { font-size: 10.5px !important; }
            .sf-product-actions { flex-direction: column !important; align-items: stretch !important; }
            .sf-product-actions .seafood-cart-toggle, .sf-product-actions .sf-product-action { width: 100%; padding: 0 14px; font-size: 13px; }
            .sf-tabs { flex-wrap: nowrap !important; }
            .sf-form-grid { grid-template-columns: 1fr !important; }
            .sf-chat { grid-template-columns: minmax(0, 1fr) !important; }
            .sf-chat-list { max-height: 260px; }
            .sf-chat-thread { height: 380px; }
            .sf-row-card { padding: 12px; gap: 12px; }
            .sf-row-media { width: 64px; height: 64px; }
            .sf-row-actions { margin-inline-start: 0; width: 100%; }
            .sf-row-actions > .sf-btn-sm { flex: 1 1 auto; }
            .sf-page-head-actions { width: 100%; }
            .sf-page-head-actions > .sf-btn-sm, .sf-page-head-actions > form { flex: 1 1 auto; }
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
            .sf-header-logout { display: none !important; }
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
