@php($categoryTitleLimit = $categoryTitleLimit ?? 20)
@php($categoryProductCount = $categoryProductCount ?? ($category->product_count ?? $category->product?->count() ?? 0))

<a href="{{ route('category-products', ['slug' => $category['slug']]) }}" class="sf-lift sf-cat-card"
   style="display: block; border-radius: 20px; overflow: hidden; background: #FFFFFF; border: 1px solid #E4EFF1; box-shadow: 0 10px 26px -22px rgba(11,44,63,.9);">
    <div style="position: relative; height: 116px; background: #EAF4F5;">
        <img src="{{ getStorageImages(path: $category->icon_full_url, type: 'category') }}"
             alt="{{ $category['name'] }}" loading="lazy"
             style="width: 100%; height: 100%; object-fit: cover; display: block;">
    </div>
    <div style="padding: 11px 14px 12px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
        <span style="display: flex; flex-direction: column; line-height: 1.4; min-width: 0;">
            <strong class="sf-cat-card-title" style="font-family: Cairo, sans-serif; font-size: 15.5px; color: #0B2C3F; text-wrap: pretty;">{{ Str::limit($category['name'], $categoryTitleLimit) }}</strong>
            <span style="font-size: 12px; color: #6E8C99;">{{ $categoryProductCount }} {{ translate('products') }}</span>
        </span>
        <span style="width: 30px; height: 30px; border-radius: 10px; background: #ECF8F8; color: #0E8E88; display: grid; place-items: center; flex-shrink: 0;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                {{-- The arrow is swapped, not mirrored: "next" points left in RTL and right in LTR. --}}
                <path d="{{ (session()->get('direction') ?? 'rtl') === 'rtl' ? 'M14 6 8 12l6 6' : 'M10 6l6 6-6 6' }}"></path>
            </svg>
        </span>
    </div>
</a>
