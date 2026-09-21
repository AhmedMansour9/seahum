{{-- Title + search box + category chips shared by the blog list and popular-blogs pages. $blogPageRouteName is the page's own route. --}}
<form method="GET" action="{{ route($blogPageRouteName) }}" class="sf-page-head" role="search"
      style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding: 16px 18px; background: #F4FAFB; border: 1px solid #E4EFF1; border-radius: 16px; margin-bottom: 18px;">
    <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0; flex: 1 1 260px;">
        <h1 style="font-family: Cairo, sans-serif; font-size: 20px; font-weight: 800; line-height: 1.4; margin: 0; color: #0B2C3F; text-wrap: pretty; overflow-wrap: anywhere;">{{ $blogPageTitle }}</h1>
        @if(!empty($blogPageSubtitle))
            <span style="font-size: 14px; color: #45677A; overflow-wrap: anywhere;">{{ $blogPageSubtitle }}</span>
        @endif
    </div>

    @if(request('category'))
        <input type="hidden" name="category" value="{{ request('category') }}">
    @endif
    <span class="sf-search-wrap sf-page-head-search" style="width: 300px;">
        <input type="search" class="sf-filter-field sf-search-field" name="search" autocomplete="off" required
               value="{{ request('search') }}" placeholder="{{ translate('Search_Blog') }}" aria-label="{{ translate('Search_Blog') }}"
               style="padding-inline-end: 46px;">
        <button type="submit" class="sf-search-submit" aria-label="{{ translate('search') }}">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.2-3.2"></path></svg>
        </button>
    </span>
</form>

@if(count($blogCategoryList) > 0)
    <nav aria-label="{{ translate('categories') }}">
        <ul class="sf-blog-chips">
            <li><a href="{{ route($blogPageRouteName) }}" class="sf-blog-chip" @if(!request('category')) aria-current="page" @endif>{{ translate('all') }}</a></li>
            @foreach($blogCategoryList as $blogCategory)
                <li>
                    <a href="{{ route($blogPageRouteName, ['category' => $blogCategory->name]) }}" class="sf-blog-chip" title="{{ $blogCategory->name }}"
                       @if(request('category') == $blogCategory->name) aria-current="page" @endif>{{ Str::limit($blogCategory->name, 25) }}</a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif

@if(request('search') || request('writer'))
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin: -6px 0 20px; font-size: 14px; color: #45677A;">
        <span>
            <strong style="color: #0B2C3F;"><bdi dir="ltr">{{ $blogResultTotal }}</bdi></strong> {{ translate('Search_Result_Found') }}
            @if(request('writer'))
                · {{ translate('By') }} <strong style="color: #0B2C3F;">{{ request('writer') }}</strong>
            @endif
        </span>
        <a href="{{ route($blogPageRouteName, array_filter(['category' => request('category')])) }}" class="sf-text-link">{{ translate('Clear_Search') }}</a>
    </div>
@endif
