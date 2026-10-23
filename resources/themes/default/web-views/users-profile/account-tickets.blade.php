@extends('layouts.front-end.seafood-account')

@section('title', translate('My_Support_Tickets'))

@section('page_title', translate('support_ticket'))

@if($supportTickets->total() > 0)
    @section('page_count', $supportTickets->total())
@endif

@section('page_actions')
    <button type="button" class="sf-btn-sm sf-btn-sm-primary" data-sf-modal-open="open-ticket">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
        {{ translate('add_new_ticket') }}
    </button>
@endsection

@section('account_content')
    @php
        $ticketPriorityBadges = ['Urgent' => 'sf-badge-danger', 'High' => 'sf-badge-warning', 'Medium' => 'sf-badge-info', 'Low' => 'sf-badge-neutral'];
    @endphp

    @if($supportTickets->count() > 0)
        <div class="sf-stack">
            @foreach($supportTickets as $ticket)
                <article class="sf-row-card">
                    <span class="sf-row-media" style="display: grid; place-items: center; color: #0E8E88; background: #ECF8F8;" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 9V6.5A1.5 1.5 0 0 1 5 5h14a1.5 1.5 0 0 1 1.5 1.5V9a3 3 0 0 0 0 6v2.5A1.5 1.5 0 0 1 19 19H5a1.5 1.5 0 0 1-1.5-1.5V15a3 3 0 0 0 0-6z"></path><path d="M14 5v14" stroke-dasharray="2 2.5"></path></svg>
                    </span>

                    <div class="sf-row-body">
                        <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                            <h2 class="sf-row-title" style="flex: 0 1 auto;">
                                <a href="{{ route('support-ticket.index', $ticket['id']) }}" style="color: inherit;">{{ $ticket['subject'] }}</a>
                            </h2>
                            <span class="sf-badge {{ $ticket['status'] == 'open' ? 'sf-badge-success' : 'sf-badge-neutral' }}">{{ translate($ticket['status']) }}</span>
                        </div>
                        <div class="sf-row-meta">
                            <span>{{ translate($ticket['type']) }}</span>
                            @if($ticket['priority'])
                                <span class="sf-badge {{ $ticketPriorityBadges[$ticket['priority']] ?? 'sf-badge-neutral' }}">{{ translate($ticket['priority']) }}</span>
                            @endif
                            <span><bdi dir="ltr">{{ \Carbon\Carbon::parse($ticket['created_at'])->format('d M, Y h:i A') }}</bdi></span>
                        </div>
                    </div>

                    <div class="sf-row-actions">
                        <a href="{{ route('support-ticket.index', $ticket['id']) }}" class="sf-btn-sm">{{ translate('view') }}</a>
                        <button type="button" class="sf-icon-btn sf-icon-btn-danger call-route-alert"
                                data-route="{{ route('support-ticket.delete', ['id' => $ticket->id]) }}"
                                data-message="{{ translate('This_ticket_will_be_removed_from_this_list') }}"
                                aria-label="{{ translate('Delete_this_ticket') }}">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14M10 7V5h4v2M7 7l.8 12.2A1.8 1.8 0 0 0 9.6 21h4.8a1.8 1.8 0 0 0 1.8-1.8L17 7"></path></svg>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>

        {!! $supportTickets->links('web-views.seafood.partials._pagination') !!}
    @else
        @include('web-views.seafood.partials._empty-state', [
            'emptyStateIcon' => '<path d="M3.5 9V6.5A1.5 1.5 0 0 1 5 5h14a1.5 1.5 0 0 1 1.5 1.5V9a3 3 0 0 0 0 6v2.5A1.5 1.5 0 0 1 19 19H5a1.5 1.5 0 0 1-1.5-1.5V15a3 3 0 0 0 0-6z"></path>',
            'emptyStateTitle' => translate('no_ticket_found').'!',
            'emptyStateText' => translate('you_will_get_response'),
        ])
    @endif

    <div class="sf-modal" id="open-ticket" role="dialog" aria-modal="true" aria-labelledby="open-ticket-title" hidden>
        <div class="sf-modal-dialog">
            <div class="sf-modal-head">
                <div style="min-width: 0;">
                    <h2 class="sf-modal-title" id="open-ticket-title">{{ translate('submit_new_ticket') }}</h2>
                    <p class="sf-field-hint" style="margin: 4px 0 0;">{{ translate('you_will_get_response') }}.</p>
                </div>
                <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                </button>
            </div>

            <form class="sf-modal-body" method="post" action="{{ route('ticket-submit') }}" enctype="multipart/form-data">
                @csrf
                <div class="sf-form-grid">
                    <div class="sf-form-full">
                        <label class="sf-form-label" for="ticket-subject">{{ translate('subject') }} <span class="sf-required">*</span></label>
                        <input type="text" class="sf-filter-field" id="ticket-subject" name="ticket_subject" required>
                    </div>
                    <div>
                        <label class="sf-form-label" for="ticket-type">{{ translate('type') }} <span class="sf-required">*</span></label>
                        <select class="sf-filter-field" id="ticket-type" name="ticket_type" required>
                            <option value="Website problem">{{ translate('website_problem') }}</option>
                            <option value="Partner request">{{ translate('partner_request') }}</option>
                            <option value="Complaint">{{ translate('complaint') }}</option>
                            <option value="Info inquiry">{{ translate('info_inquiry') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="sf-form-label" for="ticket-priority">{{ translate('priority') }} <span class="sf-required">*</span></label>
                        <select class="sf-filter-field" id="ticket-priority" name="ticket_priority" required>
                            <option value="">{{ translate('choose_priority') }}</option>
                            <option value="Urgent">{{ translate('urgent') }}</option>
                            <option value="High">{{ translate('high') }}</option>
                            <option value="Medium">{{ translate('medium') }}</option>
                            <option value="Low">{{ translate('low') }}</option>
                        </select>
                    </div>
                    <div class="sf-form-full">
                        <label class="sf-form-label" for="ticket-description">{{ translate('describe_your_issue') }}</label>
                        <textarea class="sf-filter-field" id="ticket-description" name="ticket_description" style="min-height: 130px;"></textarea>
                    </div>
                    <div class="sf-form-full">
                        <span class="sf-form-label">{{ translate('Attachment') }}</span>
                        @include('web-views.seafood.partials._image-picker', ['pickerName' => 'image[]', 'pickerId' => 'ticket-images'])
                    </div>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 22px;">
                    <button type="submit" class="sf-btn-block" style="flex: 1 1 200px; width: auto;">{{ translate('submit_a_ticket') }}</button>
                    <button type="button" class="sf-btn-outline" data-sf-modal-close style="flex: 1 1 140px; width: auto;">{{ translate('close') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
