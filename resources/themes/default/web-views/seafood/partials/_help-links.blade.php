@php($isBlogActive = Route::has('frontend.blog.index') && getWebConfig(name: 'blog_feature_active_status'))
@php($hasAboutPage = (bool) $web_config['business_pages']?->firstWhere('slug', 'about-us'))

<section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: 0 20px clamp(44px, 7vw, 76px);">
    <div class="sf-help-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr)); gap: 16px;">

        @if($hasAboutPage)
            <a href="{{ route('business-page.view', ['slug' => 'about-us']) }}" class="sf-lift sf-help-card">
                <span class="sf-help-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16"></path>
                        <path d="M15 9h2a2 2 0 0 1 2 2v10"></path><path d="M8 7h3M8 11h3M8 15h3"></path>
                    </svg>
                </span>
                <strong class="sf-help-title">{{ translate('about_us') }}</strong>
                <span class="sf-help-text">{{ translate('Know_about_our_company_more.') }}</span>
            </a>
        @endif

        <a href="{{ route('contacts') }}" class="sf-lift sf-help-card">
            <span class="sf-help-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 12a8 8 0 0 1 16 0"></path>
                    <path d="M4 12v4a2 2 0 0 0 2 2h1v-6H6a2 2 0 0 0-2 2Z"></path>
                    <path d="M20 12v4a2 2 0 0 1-2 2h-1v-6h1a2 2 0 0 1 2 2Z"></path>
                    <path d="M17 18v.5a2.5 2.5 0 0 1-2.5 2.5H12"></path>
                </svg>
            </span>
            <strong class="sf-help-title">{{ translate('contact_Us') }}</strong>
            <span class="sf-help-text">{{ translate('We_are_Here_to_Help') }}</span>
        </a>

        <a href="{{ route('helpTopic') }}" class="sf-lift sf-help-card">
            <span class="sf-help-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.5 11.6a8.2 8.2 0 0 1-8.8 8.2L7 21l1.3-3.4A8.2 8.2 0 1 1 20.5 11.6Z"></path>
                    <path d="M9.9 9.3a2.2 2.2 0 1 1 3.1 2.1c-.7.3-1 .9-1 1.6"></path>
                    <path d="M12 15.7h.01"></path>
                </svg>
            </span>
            <strong class="sf-help-title">{{ translate('FAQ') }}</strong>
            <span class="sf-help-text">{{ translate('Get_all_Answers') }}</span>
        </a>

        @if($isBlogActive)
            <a href="{{ route('frontend.blog.index') }}" class="sf-lift sf-help-card">
                <span class="sf-help-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 4h9l5 5v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"></path>
                        <path d="M14 4v5h5"></path><path d="M8 13h8M8 17h5"></path>
                    </svg>
                </span>
                <strong class="sf-help-title">{{ translate('Blog') }}</strong>
                <span class="sf-help-text">{{ translate('Check_Latest_Blogs') }}</span>
            </a>
        @endif
    </div>
</section>
