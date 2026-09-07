@php($isRtl = (session('direction') ?? 'rtl') === 'rtl')
@php($previousArrowPath = $isRtl ? 'm10 6 6 6-6 6' : 'M14 6 8 12l6 6')
@php($nextArrowPath = $isRtl ? 'M14 6 8 12l6 6' : 'm10 6 6 6-6 6')
@php($itemStyle = 'min-width: 44px; min-height: 44px; padding: 0 10px; border-radius: 12px; font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #E4EFF1; background: #FFFFFF; color: #45677A;')
@php($activeItemStyle = 'min-width: 44px; min-height: 44px; padding: 0 10px; border-radius: 12px; font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #17B0A7; background: #17B0A7; color: #FFFFFF;')
@php($disabledItemStyle = 'min-width: 44px; min-height: 44px; padding: 0 10px; border-radius: 12px; font-family: Cairo, sans-serif; font-size: 14.5px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #E4EFF1; background: #F4FAFB; color: #A8B9C1;')

@if($paginator->hasPages())
    <nav class="sf-pagination" aria-label="{{ translate('pagination') }}"
         style="display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 32px;">

        @if($paginator->onFirstPage())
            <span style="{{ $disabledItemStyle }}" aria-disabled="true" aria-label="{{ translate('previous') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $previousArrowPath }}"></path></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="sf-page-link" rel="prev" aria-label="{{ translate('previous') }}" style="{{ $itemStyle }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $previousArrowPath }}"></path></svg>
            </a>
        @endif

        @foreach($elements as $element)
            @if(is_string($element))
                <span style="{{ $disabledItemStyle }}" aria-hidden="true">{{ $element }}</span>
            @endif

            @if(is_array($element))
                @foreach($element as $page => $url)
                    @if($page == $paginator->currentPage())
                        <span style="{{ $activeItemStyle }}" aria-current="page"><bdi dir="ltr">{{ $page }}</bdi></span>
                    @else
                        <a href="{{ $url }}" class="sf-page-link" style="{{ $itemStyle }}" aria-label="{{ translate('page') }} {{ $page }}"><bdi dir="ltr">{{ $page }}</bdi></a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="sf-page-link" rel="next" aria-label="{{ translate('next') }}" style="{{ $itemStyle }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $nextArrowPath }}"></path></svg>
            </a>
        @else
            <span style="{{ $disabledItemStyle }}" aria-disabled="true" aria-label="{{ translate('next') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $nextArrowPath }}"></path></svg>
            </span>
        @endif
    </nav>
@endif
