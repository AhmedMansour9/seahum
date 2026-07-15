<?php

namespace App\Console\Commands;

use App\Services\DatabaseSqlValidator;
use Illuminate\Console\Command;
use RuntimeException;
use Symfony\Component\Process\Process;

class ImportDatabaseStructure extends Command
{
    protected $signature = 'db:import-structure
        {--structure=database/sql/database-structure.sql : Structure-only SQL file}
        {--migrations=database/sql/migrations-data.sql : Laravel migrations SQL file}
        {--dump= : Full SQL dump containing both structure and test data}
        {--force : Required in production}
        {--wipe : Permanently delete the target database before importing}
        {--confirm-wipe : Required with --wipe when running non-interactively}';

    protected $description = 'Import a validated MySQL or MariaDB structure, migration records, or full test-data dump';

    public function handle(DatabaseSqlValidator $validator): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to run in production without --force.');

            return self::FAILURE;
        }

        if ($this->option('wipe') && ! $this->confirmWipe()) {
            return self::FAILURE;
        }

        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}");
        if (! is_array($connection) || ! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            $this->error('This command supports MySQL and MariaDB connections only.');

            return self::FAILURE;
        }

        $dump = $this->option('dump') ? $this->path('dump') : null;
        if ($dump) {
            $report = $validator->validateFullDump($dump);
            if (! $validator->isValidFullDump($report)) {
                $this->error('Full dump validation failed. Run db:validate-structure --dump=... for details.');

                return self::FAILURE;
            }
            foreach ($report['warnings'] as $warning) {
                $this->warn($warning);
            }
        } else {
            $structure = $this->path('structure');
            $migrations = $this->path('migrations');
            $report = $validator->validate($structure, $migrations);
            if (! $validator->isValid($report)) {
                $this->error('SQL validation failed. Run db:validate-structure for details.');

                return self::FAILURE;
            }
            foreach ($report['structure']['warnings'] as $warning) {
                $this->warn($warning);
            }
        }

        try {
            $defaultsFile = $this->createDefaultsFile($connection);
            if ($this->option('wipe')) {
                $this->warn('Wiping permanently deletes all contents and objects in the target database.');
                $this->runMysql($connection, $defaultsFile, $this->wipeSql($connection));
            }

            if ($dump) {
                $this->info('Importing full database dump, including test data...');
                $this->runMysql($connection, $defaultsFile, fopen($dump, 'rb'));
            } else {
                $this->info('Importing database structure...');
                $this->runMysql($connection, $defaultsFile, fopen($structure, 'rb'));
                $this->info('Importing Laravel migration records...');
                $this->runMysql($connection, $defaultsFile, fopen($migrations, 'rb'));
            }
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            if (isset($defaultsFile) && is_file($defaultsFile)) {
                @unlink($defaultsFile);
            }
        }

        $this->info($dump ? 'Full database dump was imported successfully.' : 'Database structure and Laravel migration records were imported successfully.');

        return self::SUCCESS;
    }

    private function confirmWipe(): bool
    {
        if (! $this->option('force')) {
            $this->error('--wipe requires --force.');

            return false;
        }

        if (! $this->input->isInteractive()) {
            if (! $this->option('confirm-wipe')) {
                $this->error('Non-interactive --wipe requires --confirm-wipe as well as --force.');

                return false;
            }

            return true;
        }

        return $this->confirm('WARNING: This permanently deletes all contents and objects in the target database. Continue?', false);
    }

    private function createDefaultsFile(array $connection): string
    {
        $path = tempnam(sys_get_temp_dir(), 'laravel-mysql-');
        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary MySQL configuration file.');
        }

        $content = "[client]\nuser=".($connection['username'] ?? '')."\npassword=".($connection['password'] ?? '')."\n";
        if (file_put_contents($path, $content) === false || ! chmod($path, 0600)) {
            @unlink($path);
            throw new RuntimeException('Unable to secure the temporary MySQL configuration file.');
        }

        return $path;
    }

    private function runMysql(array $connection, string $defaultsFile, mixed $input): void
    {
        $database = (string) ($connection['database'] ?? '');
        if ($database === '') {
            throw new RuntimeException('The target database is not configured.');
        }

        $process = new Process([
            'mysql',
            '--defaults-extra-file='.$defaultsFile,
            '--host='.(string) ($connection['host'] ?? '127.0.0.1'),
            '--port='.(string) ($connection['port'] ?? 3306),
            '--protocol=TCP',
            $database,
        ]);
        $process->setInput($input);
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('MySQL import failed: '.trim($process->getErrorOutput() ?: $process->getOutput()));
        }
    }

    private function wipeSql(array $connection): string
    {
        $database = str_replace('`', '``', (string) ($connection['database'] ?? ''));

        return "DROP DATABASE IF EXISTS `{$database}`; CREATE DATABASE `{$database}`;";
    }

    private function path(string $option): string
    {
        $path = (string) $this->option($option);

        return str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path);
    }
}
