# CLAUDE.md

## Project Rules
@.claude/rules/translate.md
@.claude/rules/response-style.md
@.claude/rules/coding-standards.md
@.claude/rules/auction-ui.md

## Commands

### Artisan
- `php artisan serve` — dev server
- `php artisan migrate` — apply pending migrations (see Database note below)
- `php artisan optimize:clear` — clear all caches
- `php artisan generate:entity {name}` — scaffold model + repo interface + impl
- `php artisan file:permission` — fix storage permissions after deployment

### Frontend (Laravel Mix 5)
- `npm run dev` / `npm run watch` / `npm run prod`

## Database
**Critical:** Never run `php artisan migrate` on an empty database. Import `installation/backup/database.sql` first, then migrate.

## Architecture

**Flow:** Controller → Service → Repository → Model. Controllers are thin; logic lives in `app/Services/`. Repos in `app/Contracts/Repositories/`. Helpers in `app/Utils/`.

**Routes** (non-standard, loaded by `app/Providers/RouteServiceProvider.php`):
- Admin: `routes/admin/routes.php`
- Vendor: `routes/vendor/routes.php`
- Web: `routes/web/routes.php`
- API v1/v2: `routes/rest_api/v{1,2}/api.php`
- API v3 seller: `routes/rest_api/v3/seller.php`
- Each `Modules/` dir has its own RouteServiceProvider.

**Modules** (`nwidart/laravel-modules`): `Modules/AI`, `Modules/Auction`, `Modules/Blog`, `Modules/TaxModule` — each is a self-contained mini-app.

**Bootstrap:** `AppServiceProvider` loads themes, settings, payment configs, and add-on states from DB at boot. Many behaviors are DB-driven, not code-driven.

**AI Module:** `Modules/AI/AIProviders/AIProviderManager.php` selects active provider (OpenAI/Claude) from DB config.

**Frontend:** Vue 2 + Bootstrap 4 + Laravel Mix 5 — intentionally legacy, be conservative.

## Design Sync (home page)

The default theme's home page is ported by hand from a Claude Design canvas. There is no
automatic sync — the `claude_design` MCP is read-only (`list_files` / `get_file`).

- Design project: `3a611c36-5e9e-472a-ae27-6365771aba05`, file `Seafood Store Home.dc.html`
- Snapshot of the last ported version: `.design/seafood-store-home.dc.html`
- Rendered by: `resources/themes/default/web-views/seafood/home.blade.php` (+ `partials/_product-card.blade.php`, layout `layouts/front-end/seafood.blade.php`); `file_names.php` maps `'home'` to it

Pull loop: `get_file` the design → diff against the snapshot → port only changed sections
into the Blade views (canvas primitives `<x-dc>`, `<helmet>`, `sc-for`, `sc-if`,
`style-hover`, `{{ props }}` never ship) → add any new copy to `resources/lang/{en,eg}/new-messages.php`
→ overwrite the snapshot.

