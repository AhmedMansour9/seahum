# Database import

This project supports two import formats for MySQL and MariaDB. The import commands use the Laravel database connection, never print the password, and require explicit confirmation before wiping a database.

## Full dump with test data

Use this format when the database contains only intentionally shareable demo, dummy, or test data and you want to version it in GitHub.

In DBeaver:

1. Right-click the local MySQL database.
2. Select `Tools`, then `Dump Database` or `Backup`.
3. Select all required tables and objects.
4. Enable both structure and data. Disable `CREATE DATABASE` and `USE database` statements so the configured Laravel database remains the import target.
5. Save the result as `database/sql/database-full.sql`.

Before committing, inspect the dump and ensure it contains no real customer data, passwords, API keys, access tokens, uploaded-file paths, or production credentials. The validator rejects database creation/selection, `DROP DATABASE`, `CREATE USER`, `GRANT`, and `IDENTIFIED BY` statements. It warns when data, password-related statements, or `DEFINER=` are found.

Validate the full dump:

```bash
php artisan db:validate-structure --dump=database/sql/database-full.sql
```

Import it into the configured database. In production, `--force` is required:

```bash
php artisan db:import-structure --dump=database/sql/database-full.sql --force
```

To replace all database contents, include `--wipe`; it permanently deletes the target database first and always requires confirmation:

```bash
php artisan db:import-structure --dump=database/sql/database-full.sql --wipe --force
```

For non-interactive deployments, add the explicit acknowledgement only after approving the destructive operation:

```bash
php artisan db:import-structure --dump=database/sql/database-full.sql --wipe --force --confirm-wipe
```

## Structure-only import

The original structure-only workflow remains available when application data must not be committed.

1. Export the database structure to `database/sql/database-structure.sql`.
2. Export only the Laravel `migrations` table as SQL `INSERT` statements to `database/sql/migrations-data.sql`.

```bash
php artisan db:validate-structure
php artisan db:import-structure --force
```

The structure file must contain no `INSERT INTO` or `REPLACE INTO` statements. The migrations file may affect only the `migrations` table.

## Local checks

```bash
grep -nEi "CREATE USER|GRANT |IDENTIFIED BY|PASSWORD|DEFINER=|DROP DATABASE" database/sql/database-full.sql
```

```bash
grep -nEi "INSERT INTO|REPLACE INTO" database/sql/database-structure.sql
```

```bash
grep -nEi "INSERT INTO" database/sql/migrations-data.sql
```

## Commit and deploy test data

Keep `database/sql/database-full.sql` version-controlled only while it remains safe test/demo data. Never commit `.env` files, production backups, actual customer data, passwords, API keys, SSH keys, or payment information.

```bash
git add database/sql/database-full.sql
git add app/Console/Commands
git add app/Services/DatabaseSqlValidator.php
git add docs/database-import.md
git commit -m "Add versioned test database dump"
git push origin main
```

On the server:

```bash
cd /var/www/delta
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan db:validate-structure --dump=database/sql/database-full.sql
php artisan db:import-structure --dump=database/sql/database-full.sql --wipe --force
php artisan migrate:status
php artisan migrate --force
php artisan optimize
```

Use a protected repository and restrict access to everyone who can read the dump.
