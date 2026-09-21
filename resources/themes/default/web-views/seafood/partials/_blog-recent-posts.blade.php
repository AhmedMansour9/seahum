@if(count($recentBlogList) > 0)
    <div class="sf-card" style="padding: 20px;">
        <h2 style="font-family: Cairo, sans-serif; font-size: 17px; font-weight: 800; margin: 0 0 16px; color: #0B2C3F;">{{ translate('Recent_Posts') }}</h2>
        <ul class="sf-blog-recent">
            @foreach($recentBlogList->take(6) as $recentBlogItem)
                <li class="sf-blog-recent-item">
                    <img src="{{ getStorageImages(path: $recentBlogItem?->thumbnail_full_url, type: 'wide-banner') }}" alt="" loading="lazy">
                    <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                        <a href="{{ route('frontend.blog.details', ['slug' => $recentBlogItem?->slug]) }}">{{ $recentBlogItem?->title }}</a>
                        <time datetime="{{ $recentBlogItem->publish_date?->toDateString() }}" style="font-size: 12.5px; color: #6E8C99;">{{ $recentBlogItem->publish_date?->diffForHumans() }}</time>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
