{{-- Shared shell for the order-details tabs: title, status, actions, tab bar and the order-level modals. --}}
@extends('layouts.front-end.seafood-account')

@php
    $order = $order ?? $orderDetails;
    $orderStatusBadges = [
        'pending' => 'sf-badge-info', 'confirmed' => 'sf-badge-success', 'processing' => 'sf-badge-success',
        'out_for_delivery' => 'sf-badge-info', 'delivered' => 'sf-badge-success',
        'failed' => 'sf-badge-danger', 'canceled' => 'sf-badge-danger', 'returned' => 'sf-badge-danger',
    ];
    $orderStatusLabels = ['processing' => 'packaging', 'failed' => 'Failed_To_Delivery', 'out_for_delivery' => 'Out_For_Delivery'];

    $isOrderOnlyDigital = $order->details->every(fn ($detail) => (json_decode($detail->product_details)->product_type ?? null) !== 'physical');
    $refundDayLimit = getWebConfig(name: 'refund_day_limit');
    $orderRefundRows = $order->details->map(function ($detail) use ($order, $refundDayLimit) {
        $refundAgeInDays = $detail?->refund_started_at?->diffInDays(now());
        $isDeliveredDefaultOrder = $order->order_type == 'default_type' && $order->order_status == 'delivered';

        return [
            'detail' => $detail,
            'product' => $detail?->productAllStatus ?? json_decode($detail->product_details, true),
            'hasRefund' => $isDeliveredDefaultOrder && $detail->refund_request != 0,
            'canRequestRefund' => $isDeliveredDefaultOrder && $refundDayLimit > 0 && !is_null($refundAgeInDays) && $refundAgeInDays <= $refundDayLimit && $detail->refund_request == 0,
        ];
    })->filter(fn ($row) => $row['product']);
    $isRefundAvailable = $orderRefundRows->contains(fn ($row) => $row['hasRefund'] || $row['canRequestRefund']);

    $orderTabs = [
        ['label' => translate('order_summary'), 'url' => route('account-order-details', ['id' => $order->id]), 'active' => Request::is('account-order-details')],
        ['label' => translate('vendor_info'), 'url' => route('account-order-details-vendor-info', ['id' => $order->id]), 'active' => Request::is('account-order-details-vendor-info')],
    ];
    if ($order->order_type != 'POS') {
        if (!$isOrderOnlyDigital) {
            $orderTabs[] = ['label' => translate('delivery_man_info'), 'url' => route('account-order-details-delivery-man-info', ['id' => $order->id]), 'active' => Request::is('account-order-details-delivery-man-info')];
        }
        $orderTabs[] = ['label' => translate('reviews'), 'url' => route('account-order-details-reviews', ['id' => $order->id]), 'active' => Request::is('account-order-details-reviews')];
        $orderTabs[] = ['label' => translate('track_order'), 'url' => route('track-order.order-wise-result-view', ['order_id' => $order['id']]), 'active' => Request::is('track-order/order-wise-result-view*')];
    }
@endphp

@section('title', translate('order_Details'))

{{-- LRI…PDI keeps "#100002" in one piece inside right-to-left text. --}}
@section('page_title', translate('order') . " \u{2066}#{$order->id}\u{2069}")

@section('page_actions')
    @if($isRefundAvailable)
        <button type="button" class="sf-btn-sm" data-sf-modal-open="refund_request">{{ translate('refund') }}</button>
    @endif
    <a href="{{ route('generate-invoice', [$order->id]) }}" class="sf-btn-sm">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11m0 0-3.6-3.6M12 15l3.6-3.6"></path><path d="M5 19h14"></path></svg>
        {{ translate('invoice') }}
    </a>
@endsection

@section('account_content')
    <div class="sf-order-meta">
        <span class="sf-badge {{ $orderStatusBadges[$order['order_status']] ?? 'sf-badge-info' }}">{{ translate($orderStatusLabels[$order['order_status']] ?? $order['order_status']) }}</span>
        @if($order['edited_status'] == 1)
            <span class="sf-badge sf-badge-neutral">{{ translate('Edited') }}</span>
        @endif
        <span class="sf-row-meta"><bdi dir="ltr">{{ date('d M, Y h:i A', strtotime($order['created_at'])) }}</bdi></span>
    </div>

    <nav class="sf-tabs sf-order-tabs" aria-label="{{ translate('order_Details') }}">
        @foreach($orderTabs as $orderTab)
            <a href="{{ $orderTab['url'] }}" class="sf-tab{{ $orderTab['active'] ? ' active' : '' }}" @if($orderTab['active']) aria-current="page" @endif>{{ $orderTab['label'] }}</a>
        @endforeach
    </nav>

    @yield('order_content')

    @if($isRefundAvailable)
        <div class="sf-modal" id="refund_request" role="dialog" aria-modal="true" aria-labelledby="refund-request-title" hidden>
            <div class="sf-modal-dialog">
                <div class="sf-modal-head">
                    <div style="min-width: 0;">
                        <h2 class="sf-modal-title" id="refund-request-title">{{ translate('refund_request') }}</h2>
                        <p class="sf-field-hint" style="margin: 4px 0 0;">{{ translate('Choose the item you’d like to refund.') }}</p>
                    </div>
                    <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <div class="sf-modal-body sf-stack">
                    @foreach($orderRefundRows->filter(fn ($row) => $row['hasRefund'] || $row['canRequestRefund']) as $refundRow)
                        @php $refundDetail = $refundRow['detail']; @endphp
                        <article class="sf-row-card">
                            <span class="sf-row-media"><img src="{{ getStorageImages(path: $refundDetail?->productAllStatus?->thumbnail_full_url, type: 'product') }}" alt=""></span>
                            <div class="sf-row-body">
                                <h3 class="sf-row-title">{{ Str::limit($refundRow['product']['name'] ?? '', 60) }}</h3>
                                <div class="sf-row-meta">
                                    <span>{{ translate('qty') }}: <bdi dir="ltr">{{ $refundDetail->qty }}</bdi></span>
                                    <span>{{ translate('Total') }}: @include('web-views.seafood.partials._price', ['amount' => ($refundDetail->qty * $refundDetail->price) + $refundDetail->tax - $refundDetail->discount])</span>
                                </div>
                            </div>
                            <div class="sf-row-actions">
                                @if($refundRow['hasRefund'])
                                    <button type="button" class="sf-btn-sm sf-refund-details-trigger" data-route="{{ route('refund-details', ['id' => $refundDetail->id]) }}">{{ translate('refund_details') }}</button>
                                @endif
                                @if($refundRow['canRequestRefund'])
                                    <button type="button" class="sf-btn-sm sf-btn-sm-primary" data-sf-modal-open="refundModal{{ $refundDetail->id }}">{{ translate('Req._Refund') }}</button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="sf-modal" id="refund_details_modal" role="dialog" aria-modal="true" aria-labelledby="refund-details-title" hidden>
            <div class="sf-modal-dialog">
                <div class="sf-modal-head">
                    <h2 class="sf-modal-title" id="refund-details-title">{{ translate('refund_details') }}</h2>
                    <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <div class="sf-modal-body" id="refund_details_field"></div>
            </div>
        </div>

        @foreach($orderRefundRows->filter(fn ($row) => $row['canRequestRefund']) as $refundRow)
            @include('web-views.seafood.partials._order-refund-modal', ['refundDetail' => $refundRow['detail'], 'refundProduct' => $refundRow['product']])
        @endforeach
    @endif

    @foreach($order->details as $reviewableDetail)
        @if($order->order_type == 'default_type' && $order->order_status == 'delivered' && isset($reviewableDetail->product))
            @include('web-views.seafood.partials._order-review-modal', ['reviewDetail' => $reviewableDetail])
        @endif
    @endforeach
@endsection

@push('script')
    @include('web-views.seafood.partials._order-details-scripts')
@endpush
