{{-- Vertical order-status timeline built from OrderManager::getTrackOrderStatusHistory(). --}}
@php
    $terminalStatuses = ['order_canceled', 'order_returned', 'order_failed'];
    $activeTerminalStatus = collect($terminalStatuses)->first(fn ($status) => $trackOrderArray['history'][$status]['status'] ?? false);
    $statusesToShow = $trackOrderArray['is_digital_order']
        ? ['order_placed', 'order_confirmed', 'order_delivered']
        : ['order_placed', 'order_confirmed', 'preparing_for_shipment', 'order_is_on_the_way', 'order_delivered'];
    if ($activeTerminalStatus) {
        $statusesToShow[] = $activeTerminalStatus;
    }
    $timelineSteps = collect($trackOrderArray['history'])->only($statusesToShow);
    $terminalMessages = [
        'order_canceled' => translate('Order has been canceled'),
        'order_returned' => translate('Order has been returned'),
        'order_failed' => translate('Order processing failed'),
    ];
@endphp

<ol class="sf-timeline">
    @foreach($timelineSteps as $statusKey => $statusData)
        @php($isTerminalStep = in_array($statusKey, $terminalStatuses))
        <li class="sf-timeline-step {{ $statusData['status'] ? ($isTerminalStep ? 'is-failed' : 'is-done') : '' }}">
            <span class="sf-timeline-dot" aria-hidden="true">
                @if($statusData['status'] && $isTerminalStep)
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                @elseif($statusData['status'])
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg>
                @endif
            </span>
            <div class="sf-timeline-body">
                <strong class="sf-timeline-title">{{ translate($statusData['label']) }}</strong>
                @if($statusData['date_time'])
                    <span class="sf-row-meta"><bdi dir="ltr">{{ $statusData['date_time']->format('h:i A, d M Y') }}</bdi></span>
                @endif
                @if($statusKey === 'order_is_on_the_way' && $statusData['status'] && !$trackOrderArray['is_digital_order'])
                    <span class="sf-row-meta">{{ translate('Your deliveryman is coming') }}</span>
                @endif
                @if($isTerminalStep && $statusData['status'])
                    <span class="sf-row-meta" style="color: #C63F38;">{{ $terminalMessages[$statusKey] }}</span>
                @endif
            </div>
        </li>
    @endforeach
</ol>
