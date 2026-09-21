{{-- Admin-configured "download our app" promo (Blog settings). Renders nothing when the feature is off or empty. --}}
@php
    $appTitleData = getWebConfig(name: 'blog_feature_download_app_title') ?? [];
    $appSubTitleData = getWebConfig(name: 'blog_feature_download_app_subtitle') ?? [];
    $googleStoreLink = getWebConfig(name: 'download_app_google_store') ?? [];
    $appleStoreLink = getWebConfig(name: 'download_app_apple_store') ?? [];
    $appCardTitle = $appTitleData[getDefaultLanguage()] ?? ($appTitleData['en'] ?? '');
    $appCardSubtitle = $appSubTitleData[getDefaultLanguage()] ?? ($appSubTitleData['en'] ?? '');
    $googlePlayUrl = getWebConfig(name: 'blog_feature_download_google_app_button_status') && !empty($googleStoreLink['status']) ? ($googleStoreLink['link'] ?? '') : '';
    $appStoreUrl = getWebConfig(name: 'blog_feature_download_apple_app_button_status') && !empty($appleStoreLink['status']) ? ($appleStoreLink['link'] ?? '') : '';
    $appCardIcon = getWebConfig(name: 'blog_feature_download_app_icon');
    $appCardBackground = getWebConfig(name: 'blog_feature_download_app_background');
    $isAppCardVisible = getWebConfig(name: 'blog_feature_download_app_status')
        && (isset($appCardIcon['path']) || $appCardTitle || $appCardSubtitle || $googlePlayUrl || $appStoreUrl);
@endphp

@if($isAppCardVisible)
    <div class="sf-app-card" @isset($appCardBackground['path']) style="background-image: linear-gradient(rgba(11,44,63,.72), rgba(11,44,63,.72)), url('{{ $appCardBackground['path'] }}');" @endisset>
        @isset($appCardIcon['path'])
            <img src="{{ $appCardIcon['path'] }}" alt="" width="56" height="56" style="border-radius: 14px; object-fit: cover; margin-bottom: 14px;">
        @endisset
        @if($appCardTitle)
            <h2 style="font-family: Cairo, sans-serif; font-size: 19px; font-weight: 800; margin: 0 0 8px; color: #FFFFFF;">{{ $appCardTitle }}</h2>
        @endif
        @if($appCardSubtitle)
            <p style="font-size: 14px; line-height: 1.8; margin: 0 0 18px; color: rgba(255,255,255,.85);">{!! $appCardSubtitle !!}</p>
        @endif
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            @if($googlePlayUrl)
                <a href="{{ $googlePlayUrl }}" target="_blank" rel="noopener noreferrer">
                    <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/play-store.svg') }}" alt="">{{ translate('Google_Play') }}
                </a>
            @endif
            @if($appStoreUrl)
                <a href="{{ $appStoreUrl }}" target="_blank" rel="noopener noreferrer">
                    <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/apple-store.svg') }}" alt="">{{ translate('App_Store') }}
                </a>
            @endif
        </div>
    </div>
@endif
