<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_basic_test()
    {
        $this->markTestSkipped('The storefront schema is restored from installation/backup/database.sql, so RefreshDatabase cannot bootstrap it on sqlite.');

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
