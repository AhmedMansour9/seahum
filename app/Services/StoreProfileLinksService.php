<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * The store's official profiles elsewhere (social pages, app store listings). Search engines use
 * them to connect the website, the social pages and the app as one business, so only real links
 * on the matching platform are returned; template placeholders are skipped.
 */
class StoreProfileLinksService
{
    /** @var array<string, array<int, string>> platform name => accepted hosts */
    private const SOCIAL_HOSTS = [
        'facebook' => ['facebook.com', 'fb.com', 'm.facebook.com'],
        'instagram' => ['instagram.com'],
        'tiktok' => ['tiktok.com'],
        'twitter' => ['twitter.com', 'x.com'],
        'youtube' => ['youtube.com', 'youtu.be'],
        'linkedin' => ['linkedin.com'],
        'snapchat' => ['snapchat.com'],
        'pinterest' => ['pinterest.com'],
        'whatsapp' => ['wa.me', 'whatsapp.com'],
    ];

    /**
     * Active social links that really point at that platform.
     *
     * @param  iterable<int, mixed>|null  $socialMedia
     * @return Collection<int, array{name: string, link: string}>
     */
    public function getSocialProfiles(?iterable $socialMedia): Collection
    {
        return collect($socialMedia ?? [])
            ->map(fn ($social) => ['name' => strtolower((string) data_get($social, 'name')), 'link' => trim((string) data_get($social, 'link'))])
            ->filter(fn (array $social) => $this->isPlatformUrl(url: $social['link'], platform: $social['name']))
            ->values();
    }

    /**
     * App store listings set in admin (download app links), even while their badges are switched off.
     *
     * @return array{android: ?string, ios: ?string}
     */
    public function getAppStoreLinks(): array
    {
        $googlePlayLink = trim((string) data_get(getWebConfig(name: 'download_app_google_store'), 'link'));
        $appStoreLink = trim((string) data_get(getWebConfig(name: 'download_app_apple_store'), 'link'));

        return [
            'android' => $this->hostEndsWith(url: $googlePlayLink, hosts: ['play.google.com']) && str_contains($googlePlayLink, 'id=') ? $googlePlayLink : null,
            'ios' => $this->hostEndsWith(url: $appStoreLink, hosts: ['apps.apple.com']) ? $appStoreLink : null,
        ];
    }

    private function isPlatformUrl(string $url, string $platform): bool
    {
        $hosts = self::SOCIAL_HOSTS[$platform] ?? array_merge(...array_values(self::SOCIAL_HOSTS));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        return $this->hostEndsWith(url: $url, hosts: $hosts) && $path !== '';
    }

    /**
     * @param  array<int, string>  $hosts
     */
    private function hostEndsWith(string $url, array $hosts): bool
    {
        if (! preg_match('#^https?://#i', $url)) {
            return false;
        }
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        foreach ($hosts as $acceptedHost) {
            if ($host === $acceptedHost || str_ends_with($host, '.'.$acceptedHost)) {
                return true;
            }
        }

        return false;
    }
}
