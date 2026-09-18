@extends('layouts.front-end.seafood-account')

@section('title', translate('my_Order_List'))

@section('page_title', translate('My_Order'))

@if($orders->count() > 0)
    @section('page_count', $orders->total())
@endif

@section('page_actions')
    <label class="sr-only" for="orderFilter">{{ translate('sort_by') }}</label>
    <select name="filter" id="orderFilter" class="sf-filter-field" style="width: auto; min-width: 190px; height: 40px;">
        <option value="{{ route('account-oder', ['order_by' => 'desc']) }}" {{ request('order_by') === 'desc' ? 'selected' : '' }}>
            {{ translate('sort_by_latest') }}
        </option>
        <option value="{{ route('account-oder', ['order_by' => 'asc']) }}" {{ request('order_by') === 'asc' ? 'selected' : '' }}>
            {{ translate('sort_by_oldest') }}
        </option>
    </select>
@endsection

@section('account_content')
    @php
        $orderStatusBadgeClasses = [
            'failed' => 'sf-badge-danger',
            'canceled' => 'sf-badge-danger',
            'returned' => 'sf-badge-danger',
            'confirmed' => 'sf-badge-success',
            'processing' => 'sf-badge-success',
            'delivered' => 'sf-badge-success',
        ];
    @endphp

    @if($orders->count() > 0)
        <div class="sf-stack">
            @foreach($orders as $order)
                @php
                    $orderTotalPriceSummary = \App\Utils\OrderManager::getOrderTotalPriceSummary(order: $order);
                    $orderStatusLabel = $order['order_status'] === 'failed'
                        ? 'failed_to_deliver'
                        : ($order['order_status'] === 'processing' ? 'packaging' : $order['order_status']);

                    $hasDuePayment = $order->edited_status == 1
                        && $order?->latestEditHistory?->order_due_payment_status == 'unpaid'
                        && $order?->latestEditHistory?->order_due_payment_method != 'offline_payment'
                        && $order?->latestEditHistory?->order_due_payment_method != 'cash_on_delivery'
                        && $order?->latestEditHistory?->order_due_amount > 0;
                @endphp

                <article class="sf-row-card">
                    <a class="sf-row-media" href="{{ route('account-order-details', ['id' => $order->id]) }}">
                        <img alt="{{ translate('shop') }}" loading="lazy"
                             src="{{ getStorageImages(path: $order->seller_is == 'seller' ? $order?->seller?->shop?->image_full_url : getInHouseShopConfig(key: 'image_full_url'), type: 'shop') }}">
                    </a>

                    <div class="sf-row-body">
                        <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                            <h2 class="sf-row-title" style="flex: 0 1 auto;">
                                <a href="{{ route('account-order-details', ['id' => $order->id]) }}" style="color: inherit;">
                                    {{ translate('order') }} <bdi dir="ltr">#{{ $order['id'] }}</bdi>
                                </a>
                            </h2>
                            <span class="sf-badge {{ $orderStatusBadgeClasses[$order['order_status']] ?? 'sf-badge-info' }}">
                                {{ translate($orderStatusLabel) }}
                            </span>
                            @if($order->edited_status == 1)
                                <span class="sf-badge sf-badge-neutral">{{ translate('Edited') }}</span>
                            @endif
                            @if($hasDuePayment)
                                <span class="sf-badge sf-badge-warning">{{ translate('Pay Due') }}</span>
                            @endif
                        </div>

                        <div class="sf-row-meta">
                            <span><strong style="color: #0B2C3F;"><bdi dir="ltr">{{ $order->order_details_sum_qty }}</bdi></strong> {{ translate('Products') }}</span>
                            <span><bdi dir="ltr">{{ date('d M, Y h:i A', strtotime($order['created_at'])) }}</bdi></span>
                        </div>

                        <strong style="font-family: Cairo, sans-serif; font-size: 18px; font-weight: 800; color: #0E8E88;">
                            {{ webCurrencyConverter(amount: $orderTotalPriceSummary['totalAmount']) }}
                        </strong>
                    </div>

                    <div class="sf-row-actions">
                        <a href="{{ route('account-order-details', ['id' => $order->id]) }}" class="sf-btn-sm">
                            {{ translate('View Order Details') }}
                        </a>

                        <div class="sf-dropdown">
                            <button type="button" class="sf-icon-btn" data-sf-dropdown-toggle="order-menu-{{ $order->id }}"
                                    aria-expanded="false" aria-haspopup="true" aria-label="{{ translate('more_options') }}">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.2h.01M12 12h.01M12 17.8h.01"></path></svg>
                            </button>
                            <div class="sf-dropdown-menu" id="order-menu-{{ $order->id }}" hidden>
                                <a class="sf-dropdown-item" href="{{ route('generate-invoice', [$order->id]) }}">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11m0 0-3.6-3.6M12 15l3.6-3.6"></path><path d="M5 19h14"></path></svg>
                                    {{ translate('Download Invoice') }}
                                </a>
                                @if($hasDuePayment)
                                    <a class="sf-dropdown-item" href="{{ route('account-order-details', ['id' => $order->id]) }}">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="12" rx="3"></rect><path d="M3 10h18"></path></svg>
                                        {{ translate('Pay Due') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {!! $orders->links('web-views.seafood.partials._pagination') !!}
    @else
        @include('web-views.seafood.partials._empty-state', [
            'emptyStateIcon' => '<path d="M6 3.6h12v16.8l-3-2.2-3 2.2-3-2.2-3 2.2z"></path><path d="M9 8h6M9 12h4"></path>',
            'emptyStateTitle' => translate('You_have_not_any_order_yet').'!',
            'emptyStateText' => translate('your_placed_orders_will_appear_here_with_their_status_and_invoices'),
            'emptyStateActionUrl' => route('products'),
            'emptyStateActionLabel' => translate('explore_more'),
        ])
    @endif

    @php
        $orderSuccessIds = session('order_success_ids') ?? [];
        if (!is_array($orderSuccessIds)) {
            $orderSuccessIds = [];
        }
        $isPlural = count($orderSuccessIds) > 1;
        session()->forget('order_success_ids');
    @endphp

    @if($orderSuccessIds && auth('customer')->check())
        <div class="sf-modal" id="order_successfully" role="dialog" aria-modal="true" aria-labelledby="order-success-title" hidden>
            <div class="sf-modal-dialog" style="max-width: 480px;">
                <div class="sf-modal-head">
                    <h2 class="sf-modal-title" id="order-success-title">{{ translate('Thank You For Your Purchase!') }}</h2>
                    <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <div class="sf-modal-body" style="text-align: center;">
                    <span class="sf-empty-icon" style="display: inline-grid; margin-bottom: 16px;">
                        <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="m8.2 12.4 2.6 2.6 5-5.4"></path></svg>
                    </span>
                    <p style="font-size: 14.5px; line-height: 1.9; color: #55747F; margin: 0 0 22px; overflow-wrap: anywhere;">
                        {{ translate('We have received your order and will ship it shortly.') }}
                        {{ translate('Your Order ID' . ($isPlural ? 's' : '')) }}
                        <bdi dir="ltr">{{ implode(', ', $orderSuccessIds) }}</bdi>
                        {{ translate('keep it handy for tracking.') }}
                    </p>
                    <a href="{{ route('home') }}" class="sf-btn-block">{{ translate('Explore More Items') }}</a>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script')
    <script>
        "use strict";

        $(document).on('change', '#orderFilter', function () {
            if (this.value) {
                location.href = this.value;
            }
        });

        @if($orderSuccessIds && auth('customer')->check())
            /* The shared seafood script stack is pushed by the layout, so wait for it. */
            $(function () {
                openSeafoodModal('order_successfully');
            });
        @endif
    </script>
@endpush
