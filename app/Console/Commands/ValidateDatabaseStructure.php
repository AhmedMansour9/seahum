<?php

namespace App\Console\Commands;

use App\Services\DatabaseSqlValidator;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ValidateDatabaseStructure extends Command
{
    protected $signature = 'db:validate-structure
        {--structure=database/sql/database-structure.sql : Structure-only SQL file}
        {--migrations=database/sql/migrations-data.sql : Laravel migrations SQL file}
        {--dump= : Full SQL dump containing both structure and test data}';

    protected $description = 'Validate SQL files intended for database structure import';

    public function handle(DatabaseSqlValidator $validator): int
    {
        if ($this->option('dump')) {
            $report = $validator->validateFullDump($this->path('dump'));
            $this->displayFileReport('Full dump', $report);

            return $this->displayEnvironment() && $validator->isValidFullDump($report) ? self::SUCCESS : self::FAILURE;
        }

        $report = $validator->validate($this->path('structure'), $this->path('migrations'));

        foreach ($report as $name => $file) {
            $this->displayFileReport(ucfirst($name), $file);
        }

        $this->displayEnvironment();

        return $validator->isValid($report) ? self::SUCCESS : self::FAILURE;
    }

    private function displayFileReport(string $name, array $file): void
    {
        $this->line($name.': '.$file['path']);
        $this->line('  Exists: '.($file['exists'] ? 'yes' : 'no'));
        $this->line('  Readable: '.($file['readable'] ? 'yes' : 'no'));
        $this->line('  Size: '.($file['size'] === null ? 'n/a' : $file['size'].' bytes'));
        foreach ($file['errors'] as $error) {
            $this->error('  Error: '.$error);
        }
        foreach ($file['warnings'] as $warning) {
            $this->warn('  Warning: '.$warning);
        }
    }

    private function displayEnvironment(): bool
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $this->line("Connection: {$connection}");
        $this->line('Target database: '.($database ?: '(not configured)'));
        $this->line('MySQL client: '.($this->mysqlAvailable() ? 'available' : 'not found or not executable'));

        return true;
    }

    private function path(string $option): string
    {
        $path = (string) $this->option($option);

        return str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path);
    }

    private function mysqlAvailable(): bool
    {
        $process = new Process(['mysql', '--version']);
        $process->setTimeout(5);
        $process->run();

        return $process->isSuccessful();
    }
}
