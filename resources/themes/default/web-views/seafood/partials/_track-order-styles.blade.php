<style>
    .sf-track-form { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto; gap: 14px; align-items: end; }
    .sf-track-form .sf-btn-block { width: auto; min-height: 48px; }
    .sf-track-form input[dir="ltr"]::placeholder { text-align: start; }
    [dir="rtl"] .sf-track-form input[dir="ltr"] { text-align: right; }
    .sf-track-result-head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px; }
    .sf-track-result-actions { display: flex; flex-wrap: wrap; gap: 10px; }

    @media (max-width: 759px) {
        .sf-track-form { grid-template-columns: minmax(0, 1fr); }
        .sf-track-form .sf-btn-block { width: 100%; }
        .sf-track-result-actions, .sf-track-result-actions > * { width: 100%; }
    }
</style>
