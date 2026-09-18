@php($emptyStateIcon = $emptyStateIcon ?? '<path d="M4.5 8.5h15l-1.4 11a1.8 1.8 0 0 1-1.8 1.6H7.7a1.8 1.8 0 0 1-1.8-1.6z"></path><path d="M9 8.5V6.6A3 3 0 0 1 15 6.6v1.9"></path>')

<div class="sf-empty">
    <span class="sf-empty-icon" aria-hidden="true">
        <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            {!! $emptyStateIcon !!}
        </svg>
    </span>
    <h2 class="sf-empty-title">{{ $emptyStateTitle }}</h2>
    @if(!empty($emptyStateText))
        <p class="sf-empty-text">{{ $emptyStateText }}</p>
    @endif
    @if(!empty($emptyStateActionUrl))
        <a href="{{ $emptyStateActionUrl }}" class="sf-see-more" style="min-height: 44px; padding: 0 24px; font-size: 14.5px;">
            {{ $emptyStateActionLabel ?? translate('back_to_home') }}
        </a>
    @endif
</div>
