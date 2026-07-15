<?php

namespace Tests\Feature;

use Tests\TestCase;

class DatabaseStructureCommandTest extends TestCase
{
    public function test_production_import_requires_force_before_any_database_work(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('db:import-structure')
            ->expectsOutputToContain('Refusing to run in production without --force.')
            ->assertExitCode(1);
    }

    public function test_wipe_does_not_run_when_confirmation_is_declined(): void
    {
        $this->app['env'] = 'testing';

        $this->artisan('db:import-structure', ['--wipe' => true, '--force' => true])
            ->expectsConfirmation('WARNING: This permanently deletes all contents and objects in the target database. Continue?', 'no')
            ->assertExitCode(1);
    }
}
