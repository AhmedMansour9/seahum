<span class="sf-stars" role="img" aria-label="{{ translate('rating') }} {{ number_format((float) $displayRating, 1) }}/5">
    @for($starIndex = 1; $starIndex <= 5; $starIndex++)
        <svg width="15" height="15" viewBox="0 0 24 24" fill="{{ $starIndex <= round($displayRating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="m12 3.6 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.6 9.7l5.8-.8z"></path></svg>
    @endfor
</span>
