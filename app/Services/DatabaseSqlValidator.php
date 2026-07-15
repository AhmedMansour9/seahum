<?php

namespace App\Services;

class DatabaseSqlValidator
{
    public function validate(string $structurePath, string $migrationsPath): array
    {
        return [
            'structure' => $this->validateStructure($structurePath),
            'migrations' => $this->validateMigrations($migrationsPath),
        ];
    }

    public function validateFullDump(string $path): array
    {
        $report = $this->fileReport($path);

        if (! $report['readable']) {
            return $report;
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            $report['errors'][] = 'The file could not be read.';

            return $report;
        }

        foreach ([
            '/\bDROP\s+DATABASE\b/i' => 'DROP DATABASE statement detected.',
            '/\bCREATE\s+DATABASE\b/i' => 'CREATE DATABASE statement detected. Export tables and data without database-creation statements.',
            '/\bUSE\s+`?[A-Za-z0-9_]+`?\s*;/i' => 'USE statement detected. Export without database-selection statements so the configured Laravel database remains the target.',
            '/\bCREATE\s+USER\b/i' => 'CREATE USER statement detected.',
            '/\bGRANT\b/i' => 'GRANT statement detected.',
            '/\bIDENTIFIED\s+BY\b/i' => 'IDENTIFIED BY statement detected.',
        ] as $pattern => $message) {
            if ($this->contains($sql, $pattern)) {
                $report['errors'][] = $message;
            }
        }

        $this->addWarningsFor($report, $sql, [
            '/\bINSERT\s+INTO\b/i' => 'Application data statements detected. Commit this file only when its data is intentionally safe to share.',
            '/\bPASSWORD\b/i' => 'A password-related SQL statement was detected.',
            '/\bDEFINER\s*=/i' => 'DEFINER= detected. This may cause permission errors on the target server.',
            '/\b(?:INSTALL|UNINSTALL)\s+(?:PLUGIN|COMPONENT)\b|\b(?:SHUTDOWN|FLUSH\s+(?:PRIVILEGES|TABLES)|SET\s+GLOBAL|CHANGE\s+(?:MASTER|REPLICATION\s+SOURCE)|RESET\s+(?:MASTER|REPLICA))\b/i' => 'Unexpected server-level command detected.',
        ]);

        return $report;
    }

    public function isValidFullDump(array $report): bool
    {
        return empty($report['errors']);
    }

    public function validateStructure(string $path): array
    {
        $report = $this->fileReport($path);

        if (! $report['readable']) {
            return $report;
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            $report['errors'][] = 'The file could not be read.';

            return $report;
        }

        if ($this->contains($sql, '/\b(?:INSERT|REPLACE)\s+INTO\b/i')) {
            $report['errors'][] = 'The structure file contains INSERT INTO or REPLACE INTO statements.';
        }

        $this->addWarningsFor($report, $sql, [
            '/\bCREATE\s+USER\b/i' => 'CREATE USER statement detected.',
            '/\bGRANT\b/i' => 'GRANT statement detected.',
            '/\bIDENTIFIED\s+BY\b/i' => 'IDENTIFIED BY statement detected.',
            '/\bPASSWORD\b/i' => 'A password-related SQL statement was detected.',
            '/\bDEFINER\s*=/i' => 'DEFINER= detected. This may cause permission errors on the target server.',
            '/\b(?:INSTALL|UNINSTALL)\s+(?:PLUGIN|COMPONENT)\b|\b(?:SHUTDOWN|FLUSH\s+(?:PRIVILEGES|TABLES)|CREATE\s+DATABASE|DROP\s+DATABASE|SET\s+GLOBAL|CHANGE\s+(?:MASTER|REPLICATION\s+SOURCE)|RESET\s+(?:MASTER|REPLICA))\b/i' => 'Unexpected server-level command detected.',
        ]);

        return $report;
    }

    public function validateMigrations(string $path): array
    {
        $report = $this->fileReport($path);

        if (! $report['readable']) {
            return $report;
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            $report['errors'][] = 'The file could not be read.';

            return $report;
        }

        foreach ([
            '/\bDROP\s+DATABASE\b/i' => 'DROP DATABASE statement detected.',
            '/\bCREATE\s+USER\b/i' => 'CREATE USER statement detected.',
            '/\bGRANT\b/i' => 'GRANT statement detected.',
        ] as $pattern => $message) {
            if ($this->contains($sql, $pattern)) {
                $report['errors'][] = $message;
            }
        }

        if ($this->contains($sql, '/\bTRUNCATE\s+(?:TABLE\s+)?(?!`?migrations`?\b)/i')) {
            $report['errors'][] = 'TRUNCATE is allowed only for the migrations table.';
        }

        if ($this->contains($sql, '/\bREPLACE\s+INTO\b/i')) {
            $report['errors'][] = 'The migrations file must use INSERT statements only.';
        }

        if (preg_match_all('/\bINSERT\s+INTO\s+((?:`[^`]+`|[A-Za-z0-9_]+)(?:\.(?:`[^`]+`|[A-Za-z0-9_]+))?)/i', $sql, $matches)) {
            foreach ($matches[1] as $table) {
                if ($this->tableName($table) !== 'migrations') {
                    $report['errors'][] = "INSERT statement targets '{$table}', not migrations.";
                }
            }
        }

        foreach ([
            '/\b(?:DELETE\s+FROM|UPDATE|ALTER\s+TABLE|DROP\s+TABLE|CREATE\s+TABLE)\s+((?:`[^`]+`|[A-Za-z0-9_]+)(?:\.(?:`[^`]+`|[A-Za-z0-9_]+))?)/i',
        ] as $pattern) {
            if (preg_match_all($pattern, $sql, $matches)) {
                foreach ($matches[1] as $table) {
                    if ($this->tableName($table) !== 'migrations') {
                        $report['errors'][] = "Statement affects '{$table}', not migrations.";
                    }
                }
            }
        }

        if (! $this->contains($sql, '/\bINSERT\s+INTO\s+(?:(?:`?[^`.]+`?)\.)?`?migrations`?(?:\s|\()/i')) {
            $report['errors'][] = 'The migrations file does not contain an INSERT INTO migrations statement.';
        }

        return $report;
    }

    public function isValid(array $report): bool
    {
        return empty($report['structure']['errors']) && empty($report['migrations']['errors']);
    }

    private function fileReport(string $path): array
    {
        $exists = is_file($path);
        $readable = $exists && is_readable($path);

        return [
            'path' => $path,
            'exists' => $exists,
            'readable' => $readable,
            'size' => $exists ? filesize($path) : null,
            'errors' => $readable ? [] : ['File is missing or not readable.'],
            'warnings' => [],
        ];
    }

    private function contains(string $sql, string $pattern): bool
    {
        return preg_match($pattern, $sql) === 1;
    }

    private function addWarningsFor(array &$report, string $sql, array $checks): void
    {
        foreach ($checks as $pattern => $message) {
            if ($this->contains($sql, $pattern)) {
                $report['warnings'][] = $message;
            }
        }
    }

    private function tableName(string $table): string
    {
        $parts = explode('.', str_replace('`', '', $table));

        return strtolower(end($parts));
    }
}
