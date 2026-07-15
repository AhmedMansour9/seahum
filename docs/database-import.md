# Database structure import

This tooling imports a structure-only MySQL or MariaDB dump, then restores the Laravel `migrations` table rows. It does not create either SQL export; review the exports before committing them. Never export or commit application data, `.env` files, passwords, SSH keys, or production backups.

## Export database structure

In DBeaver:

1. Right-click the local MySQL database.
2. Select `Tools` then `Dump Database` or `Backup`.
3. Select all required tables and objects.
4. Enable structure-only or no-data mode.
5. Save the result as `database/sql/database-structure.sql`.

The structure export must include tables, foreign keys, indexes, and any views, triggers, or stored procedures. It must not contain `INSERT INTO` or `REPLACE INTO` statements.

## Export Laravel migration records

1. Right-click the `migrations` table.
2. Select `Export Data`.
3. Select SQL format.
4. Export its rows as `INSERT` statements.
5. Save the result as `database/sql/migrations-data.sql`.

This file must only insert into `migrations`.

## Verify exports locally

```bash
grep -nEi "INSERT INTO|REPLACE INTO" database/sql/database-structure.sql
```

```bash
grep -nEi "CREATE USER|GRANT |IDENTIFIED BY|PASSWORD|DEFINER=" database/sql/database-structure.sql
```

```bash
grep -nEi "INSERT INTO" database/sql/migrations-data.sql
```

Then run the application-level validation. It reports file sizes, data statements, unsafe statements, the configured connection/database (never the password), and whether the `mysql` client is available:

```bash
php artisan db:validate-structure
```

Custom paths are supported for both commands:

```bash
php artisan db:import-structure \
  --structure=database/sql/database-structure.sql \
  --migrations=database/sql/migrations-data.sql \
  --force
```

## Import safeguards

`db:import-structure` reads the selected Laravel connection from `database.default` and supports only MySQL/MariaDB. It invokes the MySQL client with argument arrays and a temporary, permission-restricted credentials file; the password is never printed. It imports the structure first and the migrations data second, stopping immediately on a failure.

Production imports require `--force`:

```bash
php artisan db:import-structure --force
```

`--wipe` permanently deletes the target database and recreates it before import, which deletes all of its tables, views, triggers, routines, events, and data. It always requires `--force` and an interactive confirmation. In CI or other non-interactive contexts, add the explicit acknowledgement `--confirm-wipe`:

```bash
php artisan db:import-structure --wipe --force --confirm-wipe
```

Only use `--wipe` when the target database is disposable or its full destruction has been approved.

## Deployment

Commit only structure-only SQL and the migration records; do not add these files to `.gitignore`.

```bash
git add database/sql/database-structure.sql
git add database/sql/migrations-data.sql
git add app/Console/Commands
git add app/Services/DatabaseSqlValidator.php
git add docs/database-import.md
git commit -m "Add database structure import tooling"
git push origin main
```

On the server:

```bash
cd /var/www/delta
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan db:validate-structure
php artisan db:import-structure --wipe --force
php artisan migrate:status
php artisan migrate --force
php artisan optimize
```

The wipe command is interactive on a normal terminal. For a non-interactive deployment, include `--confirm-wipe` only after explicitly approving destruction of the target database.
