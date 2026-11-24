<style>
    .sf-blog-layout { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 28px; align-items: start; }
    .sf-blog-layout-full { grid-template-columns: minmax(0, 1fr); }
    .sf-blog-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
    .sf-blog-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .sf-blog-grid > .sf-blog-card-featured { grid-column: 1 / -1; }

    .sf-blog-card { position: relative; display: flex; flex-direction: column; min-width: 0; overflow: hidden; }
    .sf-blog-card-media { display: block; aspect-ratio: 2 / 1; background: var(--sh-mist); overflow: hidden; }
    .sf-blog-card-featured .sf-blog-card-media { aspect-ratio: 21 / 9; }
    .sf-blog-card-media img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
    .sf-blog-card:hover .sf-blog-card-media img { transform: scale(1.04); }
    .sf-blog-card-body { display: flex; flex-direction: column; gap: 10px; padding: 16px 18px 18px; flex: 1; min-width: 0; }
    .sf-blog-card-title { font-family: var(--sh-font-display); font-size: 17px; font-weight: 800; line-height: 1.5; margin: 0; color: var(--sh-navy); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; overflow-wrap: anywhere; }
    .sf-blog-card-featured .sf-blog-card-title { font-size: clamp(18px, 2.2vw, 22px); }
    .sf-blog-card-title a { color: inherit; }
    .sf-blog-card-title a::after { content: ""; position: absolute; inset: 0; }
    .sf-blog-card-title a:focus-visible { outline: none; }
    .sf-blog-card:has(.sf-blog-card-title a:focus-visible) { box-shadow: 0 0 0 3px #FFFFFF, 0 0 0 5px var(--sh-sea-700); }
    .sf-blog-card-meta { margin-top: auto; display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: 13px; color: var(--sh-muted); min-width: 0; }
    .sf-blog-card-meta a { position: relative; z-index: 1; color: var(--sh-sea-700); font-weight: 700; }
    .sf-blog-card-category { position: relative; z-index: 1; align-self: flex-start; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .sf-blog-chips { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; margin: 0 0 22px; list-style: none; padding-inline: 0; scrollbar-width: thin; }
    .sf-blog-chip { display: inline-flex; align-items: center; min-height: 40px; padding: 0 16px; border-radius: 999px; border: 1.5px solid var(--sh-line); background: #FFFFFF; color: var(--sh-ink-2); font-family: var(--sh-font-display); font-size: 14px; font-weight: 700; white-space: nowrap; transition: background .22s, color .22s, border-color .22s; }
    .sf-blog-chip:hover { border-color: var(--sh-sea); color: var(--sh-navy); }
    .sf-blog-chip[aria-current="page"] { background: var(--sh-navy); border-color: var(--sh-navy); color: #FFFFFF; }

    .sf-blog-side { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 96px; }
    .sf-blog-recent { display: flex; flex-direction: column; gap: 14px; margin: 0; padding: 0; list-style: none; }
    .sf-blog-recent-item { display: flex; gap: 12px; align-items: center; min-width: 0; }
    .sf-blog-recent-item img { width: 72px; height: 72px; border-radius: 12px; object-fit: cover; flex-shrink: 0; background: var(--sh-mist); }
    .sf-blog-recent-item a { font-family: var(--sh-font-display); font-size: 14.5px; font-weight: 700; line-height: 1.5; color: var(--sh-navy); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; overflow-wrap: anywhere; }
    .sf-blog-recent-item a:hover { color: var(--sh-sea-700); }

    .sf-blog-share { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 10px; }
    .sf-blog-share a { width: 44px; height: 44px; border-radius: 12px; border: 1px solid var(--sh-line); background: #FFFFFF; color: var(--sh-ink-2); display: grid; place-items: center; transition: background .22s, color .22s, border-color .22s; }
    .sf-blog-share a:hover { background: var(--sh-sea-50); border-color: var(--sh-sea); color: var(--sh-sea-700); }

    .sf-blog-article { display: grid; grid-template-columns: 240px minmax(0, 760px); justify-content: center; gap: 32px; align-items: start; }
    .sf-blog-article-single { grid-template-columns: minmax(0, 820px); }
    .sf-blog-toc { position: sticky; top: 96px; padding: 18px; }
    .sf-blog-toc ul { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 4px; }
    .sf-blog-toc a { display: block; padding: 8px 12px; border-radius: 10px; color: var(--sh-ink-2); font-size: 14px; line-height: 1.5; border-inline-start: 2px solid transparent; overflow-wrap: anywhere; }
    .sf-blog-toc a:hover, .sf-blog-toc a[aria-current="true"] { background: var(--sh-sea-50); color: var(--sh-navy); border-inline-start-color: var(--sh-sea); }
    .sf-blog-toc summary { font-family: var(--sh-font-display); font-size: 15px; font-weight: 800; color: var(--sh-navy); cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 28px; }
    .sf-blog-toc summary::-webkit-details-marker { display: none; }
    .sf-blog-toc summary svg { transition: transform .22s; }
    .sf-blog-toc details[open] summary svg { transform: rotate(180deg); }
    .sf-blog-toc details[open] summary { margin-bottom: 12px; }
    .sf-blog-hero-image { width: 100%; aspect-ratio: 2 / 1; object-fit: cover; border-radius: 20px; margin: 0 0 28px; background: var(--sh-mist); }
    .sf-blog-meta { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 6px 16px; font-size: 14px; color: var(--sh-muted); }
    .sf-blog-meta a { color: var(--sh-sea-700); font-weight: 700; }
    .sf-blog-prose [id] { scroll-margin-top: 96px; }

    .sf-app-card { border-radius: 20px; padding: 24px 20px; text-align: center; color: #FFFFFF; background: var(--sh-navy) center / cover no-repeat; }
    .sf-app-card a { display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 44px; padding: 0 16px; border-radius: 12px; background: #FFFFFF; color: var(--sh-navy); font-family: var(--sh-font-display); font-weight: 700; font-size: 14px; flex: 1 1 130px; }

    @media (max-width: 979px) {
        .sf-blog-layout { grid-template-columns: minmax(0, 1fr); }
        .sf-blog-side { position: static; }
        .sf-blog-grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sf-blog-article { grid-template-columns: minmax(0, 1fr); gap: 18px; }
        .sf-blog-toc { position: static; }
    }

    @media (max-width: 759px) {
        .sf-page-head { flex-direction: column !important; align-items: stretch !important; }
        .sf-page-head-search { width: 100% !important; }
    }

    @media (max-width: 599px) {
        .sf-blog-grid, .sf-blog-grid-3 { grid-template-columns: minmax(0, 1fr); gap: 16px; }
        .sf-blog-card-featured .sf-blog-card-media { aspect-ratio: 2 / 1; }
        .sf-blog-hero-image { border-radius: 14px; margin-bottom: 20px; }
    }
</style>
