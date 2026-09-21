{{-- Blog post card; the title link stretches over the whole card while category and writer links stay clickable. --}}
<article class="sf-card sf-lift sf-blog-card {{ !empty($isFeaturedBlogCard) ? 'sf-blog-card-featured' : '' }}">
    <span class="sf-blog-card-media">
        <img src="{{ getStorageImages(path: $blogItem?->thumbnail_full_url, type: 'wide-banner') }}" alt="" loading="lazy">
    </span>
    <div class="sf-blog-card-body">
        @if($blogItem?->category?->name)
            <a href="{{ route('frontend.blog.index', ['category' => $blogItem->category->name]) }}" class="sf-badge sf-badge-info sf-blog-card-category"
               title="{{ $blogItem->category->name }}">{{ $blogItem->category->name }}</a>
        @endif

        <h3 class="sf-blog-card-title">
            <a href="{{ route('frontend.blog.details', ['slug' => $blogItem?->slug]) }}">{{ $blogItem?->title }}</a>
        </h3>

        <div class="sf-blog-card-meta">
            @if($blogItem?->writer)
                <span style="min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ translate('By') }}
                    <a href="{{ route('frontend.blog.index', ['writer' => $blogItem->writer]) }}" title="{{ $blogItem->writer }}">{{ $blogItem->writer }}</a>
                </span>
            @endif
            <time datetime="{{ $blogItem->publish_date?->toDateString() }}" style="flex-shrink: 0; margin-inline-start: auto;">{{ $blogItem->publish_date?->diffForHumans() }}</time>
        </div>
    </div>
</article>
