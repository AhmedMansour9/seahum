{{-- Five native radios styled as stars; the label beside them names the chosen score. --}}
<fieldset class="sf-star-input" data-labels="{{ json_encode($ratingLabels) }}">
    <legend class="sf-form-label" style="text-align: center; width: 100%;">{{ $ratingTitle }}</legend>
    <div class="sf-star-input-row">
        <div class="sf-star-input-stars">
            {{-- Highest first in the DOM: row-reverse shows 1→5 in reading order and lets "checked ~ label" fill the lower stars. --}}
            @for($starValue = 5; $starValue >= 1; $starValue--)
                <input type="radio" class="sr-only" name="{{ $ratingName }}" id="{{ $ratingIdPrefix }}-{{ $starValue }}" value="{{ $starValue }}"
                       @checked($ratingValue == $starValue) @required($starValue === 5)>
                <label for="{{ $ratingIdPrefix }}-{{ $starValue }}" aria-label="{{ $ratingLabels[$starValue] }}">
                    <svg width="30" height="30" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="m12 3.6 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.6 9.7l5.8-.8z"></path></svg>
                </label>
            @endfor
        </div>
        <span class="sf-star-input-label" aria-live="polite">{{ $ratingLabels[$ratingValue] ?? '' }}</span>
    </div>
</fieldset>
