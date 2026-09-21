{{-- Quick message to a vendor or delivery man; posted by the shared .sf-chat-form handler in _scripts. --}}
@php
    $chatModalId = $chatModalId ?? 'sf-chat-modal';
    $chatRecipientField = $chatRecipientField ?? 'vendor_id';
    $chatInboxType = $chatInboxType ?? 'vendor';
    $isChatSendable = $isChatSendable ?? true;
@endphp

<div class="sf-modal" id="{{ $chatModalId }}" role="dialog" aria-modal="true" aria-labelledby="{{ $chatModalId }}-title" hidden>
    <div class="sf-modal-dialog" style="max-width: 520px;">
        <div class="sf-modal-head">
            <h2 class="sf-modal-title" id="{{ $chatModalId }}-title">{{ $chatModalTitle }}</h2>
            <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </div>
        <form class="sf-modal-body sf-chat-form" action="{{ route('messages') }}" method="post">
            @csrf
            <input type="hidden" name="{{ $chatRecipientField }}" value="{{ $chatRecipientId }}">
            <label class="sf-form-label" for="{{ $chatModalId }}-message">{{ translate('message') }}</label>
            <textarea id="{{ $chatModalId }}-message" name="message" class="sf-filter-field" required
                      placeholder="{{ translate('Write_here') }}..." style="min-height: 130px;"></textarea>
            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px;">
                <a href="{{ route('chat', ['type' => $chatInboxType]) }}" class="sf-btn-sm" style="flex: 1 1 140px;">{{ translate('go_to_chatbox') }}</a>
                <button type="submit" class="sf-btn-sm sf-btn-sm-primary" style="flex: 1 1 140px;" @disabled(!$isChatSendable)>{{ translate('send') }}</button>
            </div>
        </form>
    </div>
</div>
