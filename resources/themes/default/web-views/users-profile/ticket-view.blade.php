@extends('layouts.front-end.seafood-account')

@section('title', translate('support_Ticket'))

@section('page_title', translate('support_Ticket'))

@section('page_actions')
    <a href="{{ route('account-tickets') }}" class="sf-btn-sm">{{ translate('back') }}</a>
    @if($ticket->status != 'close')
        <a href="{{ route('support-ticket.close', [$ticket['id']]) }}" class="sf-btn-sm sf-btn-sm-danger">{{ translate('close_this_ticket') }}</a>
    @endif
@endsection

@section('account_content')
    @php
        $ticketCustomer = \App\Utils\customer_info();
        $ticketPriorityBadges = ['Urgent' => 'sf-badge-danger', 'High' => 'sf-badge-warning', 'Medium' => 'sf-badge-info', 'Low' => 'sf-badge-neutral'];
        $ticketMessages = collect([[
            'isMine' => true,
            'text' => $ticket['description'],
            'photos' => $ticket['attachment'] != null ? ($ticket->attachment_full_url ?? []) : [],
            'time' => $ticket->created_at,
            'avatar' => null,
        ]])->merge($ticket->conversations->map(fn ($conversation) => [
            'isMine' => !$conversation['admin_id'],
            'text' => $conversation['admin_id'] ? $conversation['admin_message'] : $conversation['customer_message'],
            'photos' => $conversation['attachment'] != null ? ($conversation->attachment_full_url ?? []) : [],
            'time' => $conversation->created_at,
            'avatar' => $conversation['admin_id'] ? \App\Models\Admin::find($conversation['admin_id'])?->image_full_url : null,
        ]));
    @endphp

    <div class="sf-panel">
        <div style="display: flex; align-items: flex-start; gap: 14px; flex-wrap: wrap;">
            <img src="{{ getStorageImages(path: $ticketCustomer->image_full_url, type: 'avatar') }}" alt=""
                 style="width: 52px; height: 52px; border-radius: 16px; object-fit: cover; border: 1px solid #E4EFF1; flex-shrink: 0;">
            <div style="flex: 1 1 240px; min-width: 0; display: flex; flex-direction: column; gap: 8px;">
                <h2 class="sf-row-title" style="-webkit-line-clamp: unset;">{{ $ticket->subject }}</h2>
                <div class="sf-row-meta">
                    <span>{{ $ticketCustomer->f_name }} {{ $ticketCustomer->l_name }}</span>
                    <span class="sf-badge {{ $ticket->status == 'open' ? 'sf-badge-success' : 'sf-badge-neutral' }}">{{ translate($ticket->status) }}</span>
                    <span class="sf-badge {{ $ticketPriorityBadges[$ticket->priority] ?? 'sf-badge-neutral' }}">{{ translate('priority') }}: {{ translate($ticket->priority) }}</span>
                    <span>{{ translate($ticket->type) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="sf-panel sf-thread" aria-label="{{ translate('conversation') }}">
        @foreach($ticketMessages as $ticketMessage)
            <div class="sf-bubble-row {{ $ticketMessage['isMine'] ? 'is-mine' : 'is-theirs' }}">
                @unless($ticketMessage['isMine'])
                    <img class="sf-bubble-avatar" src="{{ getStorageImages(path: $ticketMessage['avatar'], type: 'avatar') }}" alt="">
                @endunless
                <div class="sf-bubble-stack">
                    @if($ticketMessage['text'])
                        <p class="sf-bubble">{{ $ticketMessage['text'] }}</p>
                    @endif
                    @if(count($ticketMessage['photos']) > 0)
                        <div class="sf-bubble-media">
                            @foreach($ticketMessage['photos'] as $photo)
                                <a href="{{ $photo['path'] }}" target="_blank" rel="noopener">
                                    <img src="{{ getStorageImages(path: $photo, type: 'product') }}" alt="{{ translate('attachment') }}" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <span class="sf-bubble-time">{{ $ticketMessage['time']->diffForHumans() }}</span>
                </div>
            </div>
        @endforeach
    </div>

    @if($ticket->status != 'close')
        <form class="sf-panel" method="post" action="{{ route('support-ticket.comment', [$ticket['id']]) }}" enctype="multipart/form-data">
            @csrf
            <label class="sf-form-label" for="ticket-comment">{{ translate('write_your_message_here') }}</label>
            <textarea class="sf-filter-field" id="ticket-comment" name="comment" style="min-height: 120px;"
                      placeholder="{{ translate('write_your_message_here') }}..."></textarea>
            <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; flex-wrap: wrap; margin-top: 14px;">
                @include('web-views.seafood.partials._image-picker', ['pickerName' => 'image[]', 'pickerId' => 'ticket-reply-images'])
                <button type="submit" class="sf-btn-sm sf-btn-sm-primary" style="min-height: 46px; padding: 0 22px;">
                    {{ translate('send') }}
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transform: scaleX(var(--sf-dir-flip, 1));"><path d="M4 12h15M13 6l6 6-6 6"></path></svg>
                </button>
            </div>
        </form>
    @endif
@endsection
