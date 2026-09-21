@extends('layouts.front-end.seafood')

@section('title', translate('Blogs'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')
    @include('web-views.seafood.partials._blog-styles')
@endpush

@section('content')
    @php($isFilteredBlogList = request('search') || request('category') || request('writer'))

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'blog'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => translate('Blog')]],
                ])

                @include('web-views.seafood.partials._blog-page-head', [
                    'blogPageRouteName' => 'frontend.blog.index',
                    'blogPageTitle' => $blogTitle != '' ? $blogTitle : translate('Blog'),
                    'blogPageSubtitle' => $blogSubTitle,
                    'blogResultTotal' => $blogList->total(),
                ])

                <div class="sf-blog-layout {{ request('search') ? 'sf-blog-layout-full' : '' }}">
                    <div style="min-width: 0;">
                        @if($blogList->total() > 0)
                            <div class="sf-blog-grid {{ request('search') ? 'sf-blog-grid-3' : '' }}">
                                @foreach($blogList as $blogItem)
                                    @include('web-views.seafood.partials._blog-card', [
                                        'blogItem' => $blogItem,
                                        'isFeaturedBlogCard' => $loop->first && !request('search') && $blogList->onFirstPage(),
                                    ])
                                @endforeach
                            </div>

                            {!! $blogList->links('web-views.seafood.partials._pagination') !!}
                        @else
                            @include('web-views.seafood.partials._empty-state', [
                                'emptyStateIcon' => '<path d="M5 4h11l3 3v13H5z"></path><path d="M9 9h6M9 13h6M9 17h3"></path>',
                                'emptyStateTitle' => $isFilteredBlogList ? translate('No_Result_Found') : translate('No_Blogs_Found'),
                                'emptyStateText' => $isFilteredBlogList ? translate('We_could_not_find_what_you_are_looking_for.') . ' ' . translate('Try_a_different_approach_or_keyword.') : translate('Currently_no_blog_available_in_this_section'),
                                'emptyStateActionUrl' => $isFilteredBlogList ? route('frontend.blog.index') : route('home'),
                                'emptyStateActionLabel' => $isFilteredBlogList ? translate('view_all') : translate('back_to_home'),
                            ])
                        @endif
                    </div>

                    @if(!request('search'))
                        <aside class="sf-blog-side" aria-label="{{ translate('Recent_Posts') }}">
                            @include('web-views.seafood.partials._blog-recent-posts')
                            <a href="{{ route('frontend.blog.popular-blog') }}" class="sf-btn-outline">{{ translate('Popular_Blogs') }}</a>
                            @include('web-views.seafood.partials._blog-download-app-card')
                        </aside>
                    @endif
                </div>
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
@endpush
