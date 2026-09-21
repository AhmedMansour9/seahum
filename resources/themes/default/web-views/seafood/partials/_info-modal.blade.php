{{-- Read-only label/value dialog, e.g. offline payment details. --}}
<div class="sf-modal" id="{{ $infoModalId }}" role="dialog" aria-modal="true" aria-labelledby="{{ $infoModalId }}-title" hidden>
    <div class="sf-modal-dialog" style="max-width: 520px;">
        <div class="sf-modal-head">
            <h2 class="sf-modal-title" id="{{ $infoModalId }}-title">{{ $infoModalTitle }}</h2>
            <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </div>
        <div class="sf-modal-body">
            <dl class="sf-summary-rows">
                @foreach($infoModalRows as $infoLabel => $infoValue)
                    <div class="sf-summary-row"><dt style="text-transform: capitalize;">{{ $infoLabel }}</dt><dd style="white-space: normal; text-align: end; overflow-wrap: anywhere;">{{ $infoValue }}</dd></div>
                @endforeach
            </dl>
        </div>
    </div>
</div>
