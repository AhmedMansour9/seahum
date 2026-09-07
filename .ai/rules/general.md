---
paths:
  - '**/*.php'
---

# General

## Never call env() outside config/; do not run Pint
Deployments run `php artisan config:cache`, which skips loading .env — a runtime env() call then returns null. Read settings via config(): `config/software.php` (purchase code, buyer username, admin name/identifier, software version, development_environment, web theme, force https), `config/payment_overrides.php` (optional .env overrides of DB-stored gateway credentials; null means "use the DB value"), and `config('app.mode')` for APP_MODE. `tests/Feature/ConfigCacheSafetyTest.php` fails the build if env() reappears in app/, Modules/, resources/ or routes/.

Write .env only through `App\Utils\EnvironmentManager` (Helpers::setEnvironmentValue and SettingsTrait::setEnvironmentValue delegate to it) — it syncs the config repository and deletes bootstrap/cache/config.php. Any other .env writer must call EnvironmentManager::synchronizeConfig()/forgetCachedConfig().

`route:cache`/`optimize` stay off: routes/web/routes.php and routes/admin/routes.php define closure routes.

Do NOT run `vendor/bin/pint`, including `--dirty`. This codebase predates Pint; a single run reformatted 55 whole legacy files. Match surrounding style by hand.
