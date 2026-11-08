#!/usr/bin/env bash
#
# Server-side deployment steps. Branch-agnostic: the CI job decides which branch
# and commit a server gets, this script only brings the checkout it finds into a
# working state.
#
# Runs INSIDE the deployment path, AFTER the CI job has already fetched and
# reset the working tree to the deployed commit. Keeping the git operations in
# the CI job matters: this script is itself part of the working tree, so a
# `git reset` executed from here would rewrite the file bash is still reading.
#
# Can also be run by hand on the server: `bash deploy/deploy.sh`
#
# Optional environment overrides (useful when a non-interactive shell has a
# different PATH than the login shell):
#   PHP_BIN=/usr/bin/php8.4 COMPOSER_BIN=/usr/local/bin/composer bash deploy/deploy.sh

set -Eeuo pipefail

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_ROOT"

MAINTENANCE_MODE_ENABLED=0

log() {
    printf '\n==> %s\n' "$1"
}

fail() {
    printf '\nDeployment failed at line %s: %s\n' "$1" "$2" >&2
}

trap 'fail "$LINENO" "$BASH_COMMAND"' ERR

bring_application_up() {
    if [ "$MAINTENANCE_MODE_ENABLED" -eq 1 ]; then
        # A failed step can leave the framework unbootable (a half-written
        # vendor/, for instance), which would make `artisan up` throw and strand
        # the site in maintenance mode. storage/framework/down is the flag file
        # itself, so removing it is a fallback that needs no working framework.
        "$PHP_BIN" artisan up --no-interaction || rm -f storage/framework/down
        MAINTENANCE_MODE_ENABLED=0
    fi
}

trap bring_application_up EXIT

log "Verifying environment"
command -v "$PHP_BIN" >/dev/null || { echo "php not found (set PHP_BIN)" >&2; exit 1; }
command -v "$COMPOSER_BIN" >/dev/null || { echo "composer not found (set COMPOSER_BIN)" >&2; exit 1; }
test -f artisan || { echo "artisan not found in $APP_ROOT" >&2; exit 1; }
test -f .env || { echo ".env not found in $APP_ROOT — server configuration is missing" >&2; exit 1; }

echo "PHP:      $("$PHP_BIN" -r 'echo PHP_VERSION;')"
echo "Branch:   ${DEPLOY_BRANCH:-$(git rev-parse --abbrev-ref HEAD)}"
echo "Commit:   $(git rev-parse HEAD)"

# The application must never bootstrap a database from a deployment. A fresh
# server has to import installation/backup/database.sql first (see CLAUDE.md),
# otherwise `migrate` would build a schema the product does not ship with.
# Checked before maintenance mode and composer so an unreachable database fails
# with this message instead of an opaque composer/package:discover error.
# A deploy resets and cleans the working tree, so make sure the runtime folders
# Laravel needs exist before anything boots the framework (composer's
# package:discover included). A missing views folder takes the whole site down.
log "Ensuring runtime folders"
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

log "Checking database state"
if ! "$PHP_BIN" artisan migrate:status --no-interaction >/dev/null 2>&1; then
    echo "Database is unreachable or has never been initialised." >&2
    echo "Import installation/backup/database.sql on the server before deploying." >&2
    exit 1
fi

log "Enabling maintenance mode"
"$PHP_BIN" artisan down --retry=15 --no-interaction
MAINTENANCE_MODE_ENABLED=1

log "Installing PHP dependencies"
COMPOSER_ALLOW_SUPERUSER=1 "$COMPOSER_BIN" install \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-dev \
    --optimize-autoloader

log "Running migrations"
"$PHP_BIN" artisan migrate --force --no-interaction

log "Ensuring public storage symlink"
if [ ! -e public/storage ]; then
    "$PHP_BIN" artisan storage:link --no-interaction
fi

# route:cache stays out: routes/*.php define closure-based routes, which cannot
# be serialised. config:cache is safe because application code reads everything
# through the config repository (guarded by ConfigCacheSafetyTest); the admin
# "Environment settings" page drops the cached config file when it writes .env.
log "Rebuilding caches"
"$PHP_BIN" artisan optimize:clear --no-interaction
"$PHP_BIN" artisan config:cache --no-interaction
"$PHP_BIN" artisan view:cache --no-interaction

log "Restarting queue workers"
"$PHP_BIN" artisan queue:restart --no-interaction

log "Fixing writable paths"
# Only files this user owns: sessions, logs and compiled views written by the web
# server belong to it, and chmod on them fails with "Operation not permitted".
find storage/framework storage/logs bootstrap/cache -user "$(id -u)" -exec chmod ug+rwX {} +

log "Disabling maintenance mode"
bring_application_up

log "Deployment complete: ${DEPLOY_BRANCH:-$(git rev-parse --abbrev-ref HEAD)} at $(git rev-parse HEAD)"
