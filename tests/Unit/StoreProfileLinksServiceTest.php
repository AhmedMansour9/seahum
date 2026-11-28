<?php

namespace Tests\Unit;

use App\Services\StoreProfileLinksService;
use PHPUnit\Framework\TestCase;

class StoreProfileLinksServiceTest extends TestCase
{
    public function test_only_real_links_on_the_matching_platform_are_kept(): void
    {
        $profiles = (new StoreProfileLinksService)->getSocialProfiles([
            ['name' => 'facebook', 'link' => 'https://www.facebook.com/seahum.eg'],
            ['name' => 'instagram', 'link' => 'https://instagram.com/seahum'],
            ['name' => 'tiktok', 'link' => 'https://www.tiktok.com/@seahum'],
            /* Template placeholders seen on the live site. */
            ['name' => 'facebook', 'link' => 'facebook.com'],
            ['name' => 'twitter', 'link' => 'https://www.w3schools.com/howto/howto_css_table_responsive.asp'],
            ['name' => 'instagram', 'link' => 'https://dev.6amtech.com/'],
            ['name' => 'facebook', 'link' => 'https://www.facebook.com/'],
        ]);

        $this->assertSame([
            'https://www.facebook.com/seahum.eg',
            'https://instagram.com/seahum',
            'https://www.tiktok.com/@seahum',
        ], $profiles->pluck('link')->all());
    }

    public function test_no_profiles_gives_an_empty_list(): void
    {
        $this->assertTrue((new StoreProfileLinksService)->getSocialProfiles(null)->isEmpty());
    }
}
