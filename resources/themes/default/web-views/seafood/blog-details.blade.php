@extends('layouts.front-end.seafood')

@section('title', $blogData['title'] ?? translate('Blog_Details'))

@push('css_or_js')
    @include(VIEW_FILE_NAMES['blog_seo_meta_content_partials'], ['metaContentData' => $blogData?->seoInfo, 'blogDetails' => $blogData])
    @include('web-views.seafood.partials._styles')
    @include('web-views.seafood.partials._blog-styles')
@endpush

@section('content')
    @php
        $articleNavLinks = collect($articleLinks)->filter(fn ($articleLink) => !empty($articleLink['text']));
        $blogShareUrl = route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]);
    @endphp

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'blog'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [
                        ['label' => translate('Blog'), 'url' => route('frontend.blog.index')],
                        ['label' => Str::limit($blogData['title'] ?? '', 60)],
                    ],
                ])

                <div class="sf-blog-article {{ $articleNavLinks->isEmpty() ? 'sf-blog-article-single' : '' }}">
                    @if($articleNavLinks->isNotEmpty())
                        <nav class="sf-card sf-blog-toc" aria-label="{{ translate('In_this_article') }}">
                            <details class="sf-blog-toc-details" open>
                                <summary>
                                    {{ translate('In_this_article') }}
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                                </summary>
                                <ul>
                                    @foreach($articleNavLinks as $articleLink)
                                        <li><a href="#{{ $articleLink['id'] }}">{{ $articleLink['text'] }}</a></li>
                                    @endforeach
                                </ul>
                            </details>
                        </nav>
                    @endif

                    <article style="min-width: 0;">
                        <header style="text-align: center; margin-bottom: 26px;">
                            @if(request('source') == 'draft')
                                <p class="sf-badge sf-badge-danger" style="margin: 0 0 14px; white-space: normal;">
                                    {{ translate('This_is_a_draft_copy.') }} {{ translate('It_has_not_been_published_yet.') }}
                                </p>
                            @endif

                            @if($blogData?->category?->name)
                                <div style="margin-bottom: 12px;">
                                    <a href="{{ route('frontend.blog.index', ['category' => $blogData->category->name]) }}" class="sf-badge sf-badge-info">{{ Str::limit($blogData->category->name, 25) }}</a>
                                </div>
                            @endif

                            <h1 style="font-family: Cairo, sans-serif; font-size: clamp(24px, 3.4vw, 34px); font-weight: 800; line-height: 1.4; margin: 0 0 14px; color: #0B2C3F; text-wrap: pretty; overflow-wrap: anywhere;">
                                {{ $blogData['title'] ?? null }}
                            </h1>

                            <div class="sf-blog-meta">
                                @if($blogData->writer)
                                    <span>{{ translate('By') }} <a href="{{ route('frontend.blog.index', ['writer' => $blogData['writer']]) }}">{{ Str::limit($blogData['writer'], 30) }}</a></span>
                                @endif
                                <span><bdi dir="ltr">{{ $blogData['click_count'] ?? 0 }}</bdi> {{ translate('views') }}</span>
                                @if(!empty($blogData['publish_date']))
                                    <time datetime="{{ date('Y-m-d', strtotime($blogData['publish_date'])) }}">{{ date('M d, Y', strtotime($blogData['publish_date'])) }}</time>
                                @endif
                            </div>
                        </header>

                        <img class="sf-blog-hero-image" src="{{ getStorageImages(path: $blogData['thumbnail_full_url'] ?? null, type: 'wide-banner') }}" alt="{{ $blogData['title'] ?? '' }}">

                        <div class="sf-prose sf-blog-prose">
                            {!! $updatedDescription !!}
                        </div>

                        <footer style="margin-top: 36px; padding-top: 24px; border-top: 1px solid #E4EFF1; text-align: center;">
                            <h2 style="font-family: Cairo, sans-serif; font-size: 16px; font-weight: 800; margin: 0 0 14px; color: #0B2C3F;">{{ translate('Share_this_article') }}</h2>
                            @include('web-views.seafood.partials._blog-share', ['blogShareUrl' => $blogShareUrl])
                        </footer>

                        <div style="margin-top: 28px;">
                            @include('web-views.seafood.partials._blog-download-app-card')
                        </div>
                    </article>
                </div>

                @if(count($popularBlogList) > 0)
                    <section style="margin-top: clamp(44px, 6vw, 72px);" aria-labelledby="sf-popular-articles-title">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px;">
                            <h2 id="sf-popular-articles-title" style="font-family: Cairo, sans-serif; font-size: clamp(19px, 2.4vw, 24px); font-weight: 800; margin: 0; color: #0B2C3F;">{{ translate('Popular_articles') }}</h2>
                            <a href="{{ route('frontend.blog.popular-blog') }}" class="sf-text-link" style="display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                {{ translate('See_more') }}
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="{{ (session()->get('direction') ?? 'rtl') === 'rtl' ? 'M14 6 8 12l6 6' : 'M10 6l6 6-6 6' }}"></path>
                                </svg>
                            </a>
                        </div>
                        <div class="sf-blog-grid sf-blog-grid-3">
                            @foreach($popularBlogList as $blogItem)
                                @include('web-views.seafood.partials._blog-card', ['blogItem' => $blogItem])
                            @endforeach
                        </div>
                    </section>
                @endif
            </section>
        </main>

        <div style="background: #F4FAFB; border-top: 1px solid #E4EFF1; padding-top: clamp(44px, 7vw, 76px);">
            @include('web-views.seafood.partials._help-links')
        </div>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')
    </div>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')
    <script>
        $(function () {
            const tableOfContents = document.querySelector('.sf-blog-toc-details');
            if (!tableOfContents) {
                return;
            }

            const compactLayoutQuery = window.matchMedia('(max-width: 979px)');
            tableOfContents.open = !compactLayoutQuery.matches;

            const tocLinks = Array.from(tableOfContents.querySelectorAll('a[href^="#"]'));
            const tocLinkByHeadingId = new Map(tocLinks.map((link) => [link.getAttribute('href').slice(1), link]));

            $(tocLinks).on('click', function () {
                if (compactLayoutQuery.matches) {
                    tableOfContents.open = false;
                }
            });

            const headingObserver = new IntersectionObserver((entries) => {
                entries.filter((entry) => entry.isIntersecting).forEach((entry) => {
                    tocLinks.forEach((link) => link.removeAttribute('aria-current'));
                    tocLinkByHeadingId.get(entry.target.id)?.setAttribute('aria-current', 'true');
                });
            }, {rootMargin: '-96px 0px -65% 0px'});

            tocLinkByHeadingId.forEach((link, headingId) => {
                const heading = document.getElementById(headingId);
                if (heading) {
                    headingObserver.observe(heading);
                }
            });
        });
    </script>
@endpush
