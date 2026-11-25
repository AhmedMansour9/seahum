{{-- Store badges stay in English in every locale, per the store brand guidelines. A store without a link shows a "coming soon" badge only when $showComingSoon is set. --}}
@php($showComingSoon = $showComingSoon ?? false)
@php($storeBadges = [
    ['url' => $googlePlayUrl, 'store' => 'Google Play', 'caption' => 'GET IT ON', 'name' => 'Google Play',
     'icon' => '<svg width="24" height="26" viewBox="0 0 24 26" aria-hidden="true"><path d="M1.2 0.6 13.4 12.9 1.2 25.2c-.4-.3-.7-.8-.7-1.5V2.1c0-.7.3-1.2.7-1.5Z" fill="#00D7FE"></path><path d="m17.5 8.8-4.1 4.1 4.1 4.1 4.7-2.7c1.3-.8 1.3-2 0-2.8l-4.7-2.7Z" fill="#FFCE00"></path><path d="M17.5 17 13.4 12.9 1.2 25.2c.5.4 1.2.4 2 0L17.5 17Z" fill="#FF3A44"></path><path d="M17.5 8.8 3.2.6c-.8-.4-1.5-.4-2 0l12.2 12.3 4.1-4.1Z" fill="#00F076"></path></svg>'],
    ['url' => $appStoreUrl, 'store' => 'App Store', 'caption' => 'Download on the', 'name' => 'App Store',
     'icon' => '<svg width="22" height="26" viewBox="0 0 22 26" fill="#FFFFFF" aria-hidden="true"><path d="M18.2 13.8c0-3.3 2.7-4.9 2.8-5-1.5-2.2-3.9-2.5-4.7-2.6-2-.2-3.9 1.2-4.9 1.2-1 0-2.6-1.2-4.2-1.1-2.2 0-4.2 1.3-5.3 3.2-2.3 3.9-.6 9.8 1.6 13 1.1 1.6 2.4 3.3 4.1 3.3 1.6-.1 2.3-1.1 4.2-1.1 2 0 2.5 1.1 4.2 1 1.8 0 2.9-1.6 3.9-3.2 1.2-1.8 1.7-3.6 1.8-3.7-.1 0-3.5-1.3-3.5-5Z"></path><path d="M15 4.1c.9-1.1 1.5-2.6 1.3-4.1-1.3.1-2.8.9-3.7 1.9-.8.9-1.5 2.4-1.3 3.9 1.4.1 2.8-.7 3.7-1.7Z"></path></svg>'],
])

<div class="sf-store-badges">
    @foreach($storeBadges as $storeBadge)
        @if($storeBadge['url'])
            <a href="{{ $storeBadge['url'] }}" target="_blank" rel="noopener noreferrer" dir="ltr" class="sf-store-badge"
               aria-label="{{ translate('download_the_app_from') }} {{ $storeBadge['store'] }}">
                {!! $storeBadge['icon'] !!}
                <span class="sf-store-badge-text">
                    <span class="sf-store-badge-caption">{{ $storeBadge['caption'] }}</span>
                    <span class="sf-store-badge-name">{{ $storeBadge['name'] }}</span>
                </span>
            </a>
        @elseif($showComingSoon)
            <span class="sf-store-badge is-coming-soon" dir="ltr" aria-label="{{ $storeBadge['store'] }} - {{ translate('coming_soon') }}">
                {!! $storeBadge['icon'] !!}
                <span class="sf-store-badge-text">
                    <span class="sf-store-badge-caption">{{ $storeBadge['caption'] }}</span>
                    <span class="sf-store-badge-name">{{ $storeBadge['name'] }}</span>
                </span>
                <span class="sf-store-badge-soon" dir="auto">{{ translate('coming_soon') }}</span>
            </span>
        @endif
    @endforeach
</div>
