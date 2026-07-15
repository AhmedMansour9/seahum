<?php

namespace Tests\Unit;

use App\Services\DatabaseSqlValidator;
use PHPUnit\Framework\TestCase;

class DatabaseSqlValidatorTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/database-sql-validator-'.uniqid();
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->directory.'/*') ?: []);
        rmdir($this->directory);
        parent::tearDown();
    }

    public function test_it_reports_a_missing_structure_file(): void
    {
        $report = (new DatabaseSqlValidator)->validate($this->file('missing.sql'), $this->write('migrations.sql', 'INSERT INTO migrations VALUES (1, "a", 1);'));

        $this->assertNotEmpty($report['structure']['errors']);
    }

    public function test_it_reports_a_missing_migrations_file(): void
    {
        $report = (new DatabaseSqlValidator)->validate($this->write('structure.sql', 'CREATE TABLE users (id INT);'), $this->file('missing.sql'));

        $this->assertNotEmpty($report['migrations']['errors']);
    }

    public function test_it_rejects_structure_data_and_user_creation(): void
    {
        $validator = new DatabaseSqlValidator;
        $migrations = $this->write('migrations.sql', 'INSERT INTO migrations VALUES (1, "a", 1);');

        $insertReport = $validator->validate($this->write('structure.sql', 'INSERT INTO users VALUES (1);'), $migrations);
        $userReport = $validator->validate($this->write('users.sql', 'CREATE USER "bad"@"%";'), $migrations);

        $this->assertNotEmpty($insertReport['structure']['errors']);
        $this->assertNotEmpty($userReport['structure']['warnings']);
    }

    public function test_it_rejects_migrations_data_for_another_table(): void
    {
        $report = (new DatabaseSqlValidator)->validate($this->write('structure.sql', 'CREATE TABLE migrations (id INT);'), $this->write('migrations.sql', 'INSERT INTO users VALUES (1);'));

        $this->assertNotEmpty($report['migrations']['errors']);
    }

    public function test_it_accepts_valid_structure_and_migrations_sql(): void
    {
        $report = (new DatabaseSqlValidator)->validate($this->write('structure.sql', 'CREATE TABLE migrations (id INT);'), $this->write('migrations.sql', 'INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1, "create_users", 1);'));

        $this->assertTrue((new DatabaseSqlValidator)->isValid($report));
    }

    public function test_it_accepts_a_full_dump_with_test_data_but_rejects_database_deletion(): void
    {
        $validator = new DatabaseSqlValidator;

        $validReport = $validator->validateFullDump($this->write('full.sql', 'CREATE TABLE users (id INT); INSERT INTO users VALUES (1);'));
        $unsafeReport = $validator->validateFullDump($this->write('unsafe.sql', 'DROP DATABASE demo;'));

        $this->assertTrue($validator->isValidFullDump($validReport));
        $this->assertNotEmpty($validReport['warnings']);
        $this->assertFalse($validator->isValidFullDump($unsafeReport));
    }

    private function file(string $name): string
    {
        return $this->directory.'/'.$name;
    }

    private function write(string $name, string $contents): string
    {
        $path = $this->file($name);
        file_put_contents($path, $contents);

        return $path;
    }
}
