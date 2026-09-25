@extends('layouts.front-end.seafood')

@section('title', translate('track_Order'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')
    @include('web-views.seafood.partials._track-order-styles')

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ $web_config['company_name'] }} "/>
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ config('app.url') }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    @php
        $order = $orderDetails;
        $latestEditHistory = $order?->latestEditHistory;
        $isEditedOrder = $order->edited_status == 1;
        $isPayOnDelivery = in_array($order['payment_method'], \App\Enums\PaymentMethod::PAY_ON_DELIVERY);
        $isDuePaymentPending = $isEditedOrder && $latestEditHistory?->order_due_payment_status == 'unpaid'
            && !in_array($latestEditHistory?->order_due_payment_method, ['offline_payment', 'cash_on_delivery'])
            && $latestEditHistory?->order_due_amount > 0;
        $showVerificationCode = ($order_verification_status ?? getWebConfig(name: 'order_verification')) && $order->order_type == 'default_type';
        $orderStatusBadges = [
            'pending' => 'sf-badge-info', 'confirmed' => 'sf-badge-success', 'processing' => 'sf-badge-success',
            'out_for_delivery' => 'sf-badge-info', 'delivered' => 'sf-badge-success',
            'failed' => 'sf-badge-danger', 'canceled' => 'sf-badge-danger', 'returned' => 'sf-badge-danger',
        ];
        $orderStatusLabels = ['processing' => 'packaging', 'failed' => 'Failed_To_Delivery', 'out_for_delivery' => 'Out_For_Delivery'];
    @endphp

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'track_order'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [
                        ['label' => translate('track_order'), 'url' => route('track-order.index')],
                        ['label' => translate('order') . " \u{2066}#{$order->id}\u{2069}"],
                    ],
                ])

                <section class="sf-panel" style="margin-bottom: 16px;" aria-label="{{ translate('track_order') }}">
                    @include('web-views.seafood.partials._track-order-form', ['trackOrderId' => $order->id, 'trackOrderPhone' => $user_phone ?? ''])
                </section>

                <div class="sf-order-grid">
                    <section class="sf-panel">
                        <div class="sf-track-result-head">
                            <div style="display: flex; flex-direction: column; gap: 8px; min-width: 0;">
                                <h1 style="font-family: Cairo, sans-serif; font-size: clamp(20px, 2.6vw, 24px); font-weight: 800; margin: 0; color: #0B2C3F;">
                                    {{ translate('your_order') }} <bdi dir="ltr">#{{ $order->id }}</bdi>
                                </h1>
                                <div class="sf-order-meta" style="margin: 0;">
                                    <span class="sf-badge {{ $orderStatusBadges[$order['order_status']] ?? 'sf-badge-info' }}">{{ translate($orderStatusLabels[$order['order_status']] ?? $order['order_status']) }}</span>
                                    @if($isEditedOrder)
                                        <span class="sf-badge sf-badge-neutral">{{ translate('Edited') }}</span>
                                    @endif
                                    <span class="sf-row-meta"><bdi dir="ltr">{{ date('d M, Y h:i A', strtotime($order['created_at'])) }}</bdi></span>
                                </div>
                            </div>
                        </div>

                        @if($showVerificationCode)
                            <p class="sf-summary-saved" style="margin: 0 0 18px;">
                                {{ translate('order_verification_code') }}: <strong><bdi dir="ltr">{{ $order['verification_code'] }}</bdi></strong>
                            </p>
                        @endif

                        @include('web-views.seafood.partials._order-timeline', [
                            'trackOrderArray' => \App\Utils\OrderManager::getTrackOrderStatusHistory(orderId: $order['id'], isOrderOnlyDigital: $isOrderOnlyDigital),
                        ])
                    </section>

                    <div>
                        <section class="sf-panel">
                            <h2 class="sf-panel-title">{{ translate('Payment_info') }}</h2>
                            <div class="sf-meta-grid">
                                <span class="sf-meta-item">
                                    <span class="sf-meta-label">{{ translate('payment_method') }}</span>
                                    <span class="sf-meta-value">{{ translate($order['payment_method']) }}</span>
                                </span>
                                <span class="sf-meta-item">
                                    <span class="sf-meta-label">{{ translate('payment_status') }}</span>
                                    <span>
                                        @if($isEditedOrder && $order->edit_due_amount > 0 && $latestEditHistory?->order_due_payment_method != 'cash_on_delivery' && $latestEditHistory?->order_due_payment_status == 'unpaid')
                                            <span class="sf-badge sf-badge-warning">{{ translate('Partially_Paid') }}</span>
                                        @else
                                            <span class="sf-badge {{ $order['payment_status'] == 'paid' ? 'sf-badge-success' : 'sf-badge-danger' }}">{{ translate($order['payment_status']) }}</span>
                                        @endif
                                    </span>
                                </span>
                                <span class="sf-meta-item">
                                    <span class="sf-meta-label">{{ translate('Amount') }}</span>
                                    <span class="sf-meta-value">@include('web-views.seafood.partials._price', ['amount' => $order['init_order_amount']])</span>
                                </span>
                            </div>

                            @if(($isPayOnDelivery || $latestEditHistory?->order_due_payment_method == 'cash_on_delivery') && $order['bring_change_amount'] > 0)
                                <p class="sf-summary-saved" style="margin: 16px 0 0;">
                                    {{ translate('Please bring') }}
                                    <strong><bdi dir="ltr">{{ $order['bring_change_amount'] }} {{ $order['bring_change_amount_currency'] ?? '' }}</bdi></strong>
                                    {{ translate('in change when making the delivery') }}
                                </p>
                            @endif
                        </section>

                        @if($isEditedOrder)
                            <section class="sf-panel">
                                @if(in_array($latestEditHistory?->order_due_payment_method, ['offline_payment', 'cash_on_delivery']) || $latestEditHistory?->order_due_payment_status == 'paid')
                                    <h2 class="sf-panel-title">{{ translate('Another Payment Info') }}</h2>
                                    <div class="sf-meta-grid">
                                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('payment_status') }}</span><span><span class="sf-badge {{ $latestEditHistory?->order_due_payment_status == 'paid' ? 'sf-badge-success' : 'sf-badge-danger' }}">{{ translate($latestEditHistory?->order_due_payment_status) }}</span></span></span>
                                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('payment_method') }}</span><span class="sf-meta-value">{{ translate($latestEditHistory?->order_due_payment_method) }}</span></span>
                                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('Due_amount') }}</span><span class="sf-meta-value">@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory?->order_due_amount ?? 0])</span></span>
                                    </div>
                                @elseif($isDuePaymentPending)
                                    <h2 class="sf-panel-title" style="color: #C63F38;">{{ translate('Pay Due Bill') }}</h2>
                                    <p class="sf-step-text" style="margin: 0 0 14px;">
                                        {{ translate('after_editing_your_product_list,_the_order_total_has_increased._please_pay_the_amount_to_continue_processing_the_order.') }}
                                        <strong style="color: #0B2C3F;">@include('web-views.seafood.partials._price', ['amount' => $order['edit_due_amount']])</strong>
                                    </p>
                                    <button type="button" class="sf-btn-sm sf-btn-sm-primary" data-sf-modal-open="choosePaymentMethodModal-{{ $order['id'] }}">{{ translate('Pay_Now') }}</button>
                                @elseif($latestEditHistory?->order_return_payment_status === 'pending')
                                    <h2 class="sf-panel-title" style="color: #0E8E88;">{{ translate('Amount_to_Be_Returned') }}</h2>
                                    <p class="sf-step-text" style="margin: 0;">
                                        <strong style="color: #0B2C3F;">@include('web-views.seafood.partials._price', ['amount' => $order['edit_return_amount']])</strong>
                                        {{ translate('after_editing_your_product_list,_you_will_receive_this_amount._please_wait_for_the_admin_to_process_the_returned.') }}
                                    </p>
                                @elseif($latestEditHistory?->order_return_payment_status == 'returned')
                                    <h2 class="sf-panel-title">{{ translate('Return_Payment_info') }}</h2>
                                    <div class="sf-meta-grid">
                                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('Return_Payment_method') }}</span><span class="sf-meta-value">{{ translate($latestEditHistory?->order_return_payment_method) }}</span></span>
                                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('Return_Amount') }}</span><span class="sf-meta-value">@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory?->order_return_amount])</span></span>
                                    </div>
                                    @if($latestEditHistory?->order_return_payment_note)
                                        <p class="sf-field-hint">{{ translate('Note') }}: {{ $latestEditHistory->order_return_payment_note }}</p>
                                    @endif
                                @endif
                            </section>
                        @endif
                    </div>
                </div>

                <section class="sf-panel" style="margin-top: 16px;">
                    <h2 class="sf-panel-title">{{ translate('Item_List') }}</h2>
                    <ul class="sf-order-items">
                        @foreach($order->details as $detail)
                            @php($detailProduct = $detail?->product ?? json_decode($detail->product_details))
                            <li class="sf-order-item">
                                <span class="sf-row-media">
                                    <img src="{{ getStorageImages(path: $detail?->product?->thumbnail_full_url, type: 'product') }}" alt="" loading="lazy">
                                </span>
                                <div class="sf-row-body">
                                    <h3 class="sf-row-title">{{ Str::limit($detailProduct->name ?? '', 60) }}</h3>
                                    <div class="sf-row-meta">
                                        @if($detail->variant)
                                            <span>{{ translate('variation') }}: <bdi>{{ $detail->variant }}</bdi></span>
                                        @endif
                                        <span>{{ translate('unit_price') }}: @include('web-views.seafood.partials._price', ['amount' => $detail->price])</span>
                                        <span>{{ translate('qty') }}: <bdi dir="ltr">{{ $detail->qty }}</bdi></span>
                                        @if($detail->discount > 0)
                                            <span>{{ translate('discount') }}: − @include('web-views.seafood.partials._price', ['amount' => $detail->discount])</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="sf-order-item-side">
                                    <strong class="sf-cart-item-total">@include('web-views.seafood.partials._price', ['amount' => ($detail->qty * $detail->price) - $detail->discount])</strong>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="sf-panel sf-order-totals">
                    @include('web-views.seafood.partials._order-totals', ['order' => $order])
                </section>
            </section>
        </main>

        @if($isDuePaymentPending)
            @include('web-views.seafood.partials._order-due-payment-modal', ['order' => $order])
        @endif

        <div style="background: #F4FAFB; border-top: 1px solid #E4EFF1; padding-top: clamp(44px, 7vw, 76px);">
            @include('web-views.seafood.partials._help-links')
        </div>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')
    </div>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')
@endpush
