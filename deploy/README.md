# Automatic deployment

Every push (or merge) to a configured branch triggers `.github/workflows/deploy.yml`, which
SSHes into that branch's server and updates it to the exact pushed commit. No manual
`git pull`.

## Pieces

| File | Role |
| --- | --- |
| `.github/workflows/deploy.yml` | Trigger, SSH setup, git fetch/reset/checkout/clean, status summary |
| `deploy/deploy.sh` | Server-side framework steps (composer, migrations, caches, queue, permissions) — branch-agnostic |

The git operations live in the workflow, not in `deploy/deploy.sh`: that script is part of
the working tree the reset rewrites, and bash reads a script incrementally — resetting from
inside it would make bash execute the wrong bytes. The script is `exec`'d once the tree is
already at the deployed commit.

## Deployment branches

One workflow serves every branch, with one shared set of secrets. The branch is read from
`github.ref_name` and deployed to the same server and path as every other branch.

Configured branches: `dev`, `staging`, `main`.

### Adding or removing a branch

Edit only the `DEPLOYMENT BRANCHES` section at the top of `.github/workflows/deploy.yml`:

```yaml
on:
  push:
    branches:
      - dev
      - staging
      - main          # <- add or remove here
```

Nothing else changes — the secrets are shared, so a new branch needs no new configuration.

**All branches deploy to the same `DEPLOY_PATH`.** Whichever branch was pushed last is what
the server runs; pushing `dev` after `main` replaces production code with `dev` code. That is
the intended setup here — if that ever stops being true, the branches need separate servers,
which means separate secrets.

`concurrency` is keyed on the branch, so the same branch never deploys twice at once. Two
different branches pushed at the same time can overlap; the second one aborts on the
server-side `flock` with `Another deployment is already running`, and re-running the job
deploys it.

## Required GitHub Secrets

Repository → Settings → Secrets and variables → Actions → *New repository secret*.

| Secret | Required | Example | Notes |
| --- | --- | --- | --- |
| `DEPLOY_SSH_HOST` | yes | `dev.example.com` | Hostname or IP of the server |
| `DEPLOY_SSH_USER` | yes | `deploy` | SSH user; must own the deployment path |
| `DEPLOY_SSH_KEY` | yes | `-----BEGIN OPENSSH PRIVATE KEY-----…` | **Private** key, full PEM including header/footer, no passphrase |
| `DEPLOY_PATH` | yes | `/var/www/delta2` | Absolute path of the existing checkout on the server |
| `DEPLOY_SSH_PORT` | no | `22` | Defaults to `22` when unset |
| `DEPLOY_SSH_KNOWN_HOSTS` | no | output of `ssh-keyscan -H dev.example.com` | Pins the host key. Without it the workflow keyscans at run time |

Generate a dedicated key pair (never reuse a personal one):

```bash
ssh-keygen -t ed25519 -N '' -C 'github-actions-deploy' -f ~/.ssh/delta2_deploy
# public key → server
ssh-copy-id -i ~/.ssh/delta2_deploy.pub deploy@dev.example.com
# private key → DEPLOY_SSH_KEY secret
cat ~/.ssh/delta2_deploy
```

## One-time server preparation

The workflow updates an existing checkout; it does not provision one.

```bash
cd /var/www/delta2
git remote -v                 # must point at this repository
```

The checkout does not need to be on the right branch beforehand — the workflow runs
`git checkout -B <branch> <sha>`, which also recovers a checkout left detached or on another
branch.

Requirements on the server, reachable from a **non-interactive** SSH login:

- `git`, `php` (8.2+), `composer`, `flock` (util-linux)
- a working `.env` and an already-imported database
- the deploy user **owning** the checkout (git refuses to operate on a directory owned by
  someone else) and able to write `storage/` and `bootstrap/cache/`

Verify PATH exactly as the workflow will see it:

```bash
ssh deploy@dev.example.com 'bash -lc "command -v git php composer flock"'
```

If `php` or `composer` are missing there, set absolute paths in `deploy/deploy.sh` via the
`PHP_BIN` / `COMPOSER_BIN` overrides it already reads.

## What the deployment does

1. **Lock** — `flock` on `.deploy.lock`; a second deployment aborts instead of interleaving.
   The workflow also uses `concurrency: deploy-${{ github.ref_name }}` with
   `cancel-in-progress: false`, so a branch never races itself; the `flock` is what stops two
   different branches from interleaving on the shared server.
2. **Fetch + reset + checkout** — `git fetch origin <branch>`, then
   `git reset --hard <pushed sha>` followed by `git checkout -B <branch> <pushed sha>` (the
   pushed commit, not `origin/<branch>`, so two rapid pushes each deploy their own commit),
   then verifies `HEAD` matches. Reset runs first on purpose: `checkout -B` refuses to switch
   when a tracked file is locally modified, which a previous failed deploy can leave behind.
3. **Clean** — `git clean -fd`, without `-x`. Gitignored server-only paths survive: `.env`,
   `vendor/`, `node_modules/`, `storage/app/`, `public/storage`, `public/themes`,
   `Modules/Gateways`. `public/json` and `resources/lang` are excluded explicitly because the
   admin panel writes language files there that are not gitignored.
4. **Database guard** — aborts if `migrate:status` fails, *before* maintenance mode and
   composer, so an unreachable database reports that instead of an opaque composer error.
   Per `CLAUDE.md`, a fresh server must have `installation/backup/database.sql` imported first;
   a deployment must never bootstrap a schema.
5. **Maintenance mode** — `artisan down --retry=15`, always lifted again by an `EXIT` trap,
   including on failure. If the framework is too broken to run `artisan up`, the trap deletes
   the `storage/framework/down` flag directly.
6. **Dependencies** — `composer install --no-dev --optimize-autoloader --no-interaction`.
7. **Migrations** — `artisan migrate --force`.
8. **Storage symlink** — `artisan storage:link` only when `public/storage` is missing.
9. **Caches** — `artisan optimize:clear`, then `artisan config:cache` and `artisan view:cache`.
10. **Queue** — `artisan queue:restart` so workers pick up the new code (no-op without workers).
11. **Permissions** — `chmod -R ug+rwX storage/framework storage/logs bootstrap/cache`
    (narrow on purpose: recursing through `storage/app` uploads would add minutes per deploy).

Any failing step aborts the deployment immediately (`set -Eeuo pipefail`, no `|| true` on
deployment steps) and fails the GitHub Actions job. The job summary reports commit, branch,
actor, and target.

## Deliberate omissions

- **No `route:cache` / `optimize`.** `routes/web/routes.php`, `routes/admin/routes.php` and
  others define closure routes, which cannot be serialised.
- **No `npm` build.** Compiled assets (`public/js/app.js`, `public/css/app.css`,
  `public/mix-manifest.json`) are committed. **Build locally and commit before pushing:**
  ```bash
  npm run prod
  ```
  Laravel Mix 5 needs a legacy Node toolchain; running it on the server only adds failure modes.
- **No `artisan file:permission`.** It `chmod 777`s `sitemap.xml` / `robots.txt` and swallows
  errors; the narrow `chmod` in step 11 covers what the deployment actually needs.

## Config caching

`config:cache` skips loading `.env` entirely, so a runtime `env()` call returns `null`.
Application code therefore reads every value through the config repository —
`config/software.php`, `config/payment_overrides.php` and `config('app.mode')` back the keys
that used to be read from `.env` directly. `tests/Feature/ConfigCacheSafetyTest.php` fails the
build if an `env()` call reappears in `app/`, `Modules/`, `resources/` or `routes/`.

Everything that writes `.env` at runtime — `App\Utils\EnvironmentManager` (used by
`Helpers::setEnvironmentValue` and `SettingsTrait::setEnvironmentValue`), the installer, and the
activation cleanup — applies the new value to the config repository and deletes
`bootstrap/cache/config.php`.

Consequence: **saving the admin "Environment settings" page leaves the site running without a
cached config until the next deployment re-runs `config:cache`.** That is intentional; a web
request must not write `bootstrap/cache/` as the web user.

## Running it manually

```bash
ssh deploy@dev.example.com
cd /var/www/delta2
branch=dev    # the branch to deploy
git fetch origin "$branch"
git reset --hard "origin/$branch"
git checkout -B "$branch" "origin/$branch"
DEPLOY_BRANCH="$branch" bash deploy/deploy.sh
```

## Troubleshooting

| Symptom | Cause |
| --- | --- |
| `composer: not found` | Non-interactive PATH; set `COMPOSER_BIN` / `PHP_BIN` in `deploy/deploy.sh` |
| `Another deployment is already running` | A previous run still holds the lock; re-run after it finishes |
| `Import installation/backup/database.sql…` | The server DB is unreachable or was never initialised |
| `Could not resolve host key` | Host unreachable from the runner, or set `DEPLOY_SSH_KNOWN_HOSTS` |
| `DEPLOY_PATH secret is empty` | The secret was never created, or was created at environment scope instead of repository scope |
| Site stuck in maintenance mode | `php artisan up`, or delete `storage/framework/down`; the trap only misses on `kill -9` |
