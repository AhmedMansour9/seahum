<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppDownloadLinkTest extends TestCase
{
    public function test_android_visitors_are_sent_to_the_play_store(): void
    {
        $response = $this->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 14; Pixel 8)')->get('/app');

        $response->assertRedirect(config('app_download.android_url'));
    }

    public function test_iphone_visitors_are_sent_to_the_app_store(): void
    {
        $response = $this->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)')->get('/app');

        $response->assertRedirect(config('app_download.ios_url'));
    }
}
