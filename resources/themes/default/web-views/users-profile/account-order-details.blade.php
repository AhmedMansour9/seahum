@extends('web-views.seafood.order-details-layout')

@section('order_content')
    @php
        $latestEditHistory = $order?->latestEditHistory;
        $isEditedOrder = $order->edited_status == 1;
        $isDefaultOrder = $order['order_type'] === 'default_type';
        $isPayOnDelivery = in_array($order['payment_method'], \App\Enums\PaymentMethod::PAY_ON_DELIVERY);
        $showVerificationCode = $order->order_status != 'delivered' && $isDefaultOrder && getWebConfig(name: 'order_verification');
        $hasDigitalProduct = $order->details->contains(fn ($detail) => ($detail->product->product_type ?? null) === 'digital');
        $shippingAddressData = is_string($order->shipping_address_data ?? null) ? json_decode($order->shipping_address_data) : ($order->shipping_address_data ?? null);
        $billingAddressData = is_string($order->billing_address_data ?? null) ? json_decode($order->billing_address_data) : ($order->billing_address_data ?? null);
        $hasShippingAddress = $shippingAddressData && !empty((array) $shippingAddressData);
        $hasBillingAddress = $billingAddressData && !empty((array) $billingAddressData);
        $isBillingSameAsShipping = $hasShippingAddress && $hasBillingAddress
            && $billingAddressData->address == $shippingAddressData->address
            && $billingAddressData->city == $shippingAddressData->city
            && $billingAddressData->zip == $shippingAddressData->zip;
        $isDuePaymentPending = $isEditedOrder && $order->edit_due_amount > 0 && !$isPayOnDelivery
            && $latestEditHistory?->order_due_payment_method != 'cash_on_delivery' && $latestEditHistory?->order_due_payment_status == 'unpaid';
        $refundStatusLabels = [1 => 'refund_pending', 2 => 'refund_approved', 3 => 'refund_rejected', 4 => 'refund_refunded'];
        $refundStatusBadges = [1 => 'sf-badge-warning', 2 => 'sf-badge-info', 3 => 'sf-badge-danger', 4 => 'sf-badge-success'];
    @endphp

    @if(($isPayOnDelivery || $latestEditHistory?->order_due_payment_method == 'cash_on_delivery') && $order['bring_change_amount'] > 0)
        <p class="sf-summary-saved" style="margin-bottom: 16px;">
            {{ translate('Please bring') }}
            <strong><bdi dir="ltr">{{ $order['bring_change_amount'] }} {{ $order['bring_change_amount_currency'] ?? '' }}</bdi></strong>
            {{ translate('in change when making the delivery') }}
        </p>
    @endif

    <div class="sf-order-grid">
        <section class="sf-panel">
            <h2 class="sf-panel-title">{{ translate('Order_info') }}</h2>
            <div class="sf-meta-grid">
                <span class="sf-meta-item">
                    <span class="sf-meta-label">{{ translate('Order_Type') }}</span>
                    <span class="sf-meta-value">{{ translate($order['order_type'] == 'POS' ? 'POS' : $order['order_type']) }}</span>
                </span>
                <span class="sf-meta-item">
                    <span class="sf-meta-label">{{ translate('payment_method') }}</span>
                    <span class="sf-meta-value">{{ translate($order['payment_method']) }}</span>
                </span>
                <span class="sf-meta-item">
                    <span class="sf-meta-label">{{ translate('payment_status') }}</span>
                    <span>
                        @if($isDuePaymentPending)
                            <span class="sf-badge sf-badge-warning">{{ translate('Partially_Paid') }}</span>
                        @else
                            <span class="sf-badge {{ $order['payment_status'] == 'paid' ? 'sf-badge-success' : 'sf-badge-danger' }}">{{ translate($order['payment_status']) }}</span>
                        @endif
                    </span>
                </span>
                @unless($isPayOnDelivery)
                    <span class="sf-meta-item">
                        <span class="sf-meta-label">{{ translate('Amount') }}</span>
                        <span class="sf-meta-value">@include('web-views.seafood.partials._price', ['amount' => ($order['total_order_amount'] ?? 0) > 0 ? $order['total_order_amount'] : $order['init_order_amount']])</span>
                    </span>
                @endunless
                @if($showVerificationCode)
                    <span class="sf-meta-item">
                        <span class="sf-meta-label">{{ translate('Order verification code') }}</span>
                        <span class="sf-meta-value" style="font-size: 18px; letter-spacing: 2px;"><bdi dir="ltr">{{ $order['verification_code'] }}</bdi></span>
                    </span>
                @endif
            </div>
            @if($order->payment_method == 'offline_payment' && isset($order->offlinePayments))
                <button type="button" class="sf-btn-sm" data-sf-modal-open="verifyViewModal" style="margin-top: 14px;">{{ translate('see_payment_details') }}</button>
            @endif
        </section>

        @if($isEditedOrder && !$isPayOnDelivery)
            <section class="sf-panel">
                @if($latestEditHistory?->order_due_payment_method == 'offline_payment' || $latestEditHistory?->order_due_payment_method == 'cash_on_delivery' || $latestEditHistory?->order_due_payment_status == 'paid')
                    <h2 class="sf-panel-title">{{ translate('Another Payment Info') }}</h2>
                    <div class="sf-meta-grid">
                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('payment_status') }}</span><span><span class="sf-badge {{ $latestEditHistory?->order_due_payment_status == 'paid' ? 'sf-badge-success' : 'sf-badge-danger' }}">{{ translate($latestEditHistory?->order_due_payment_status) }}</span></span></span>
                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('payment_method') }}</span><span class="sf-meta-value">{{ translate($latestEditHistory?->order_due_payment_method) }}</span></span>
                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('Due amount') }}</span><span class="sf-meta-value">@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory?->order_due_amount ?? 0])</span></span>
                    </div>
                    @if($latestEditHistory?->order_due_payment_method == 'offline_payment' && $latestEditHistory?->order_due_payment_info)
                        <button type="button" class="sf-btn-sm" data-sf-modal-open="orderDuePaymentInfoModal" style="margin-top: 14px;">{{ translate('see_payment_details') }}</button>
                    @endif
                @elseif($order->edit_due_amount > 0)
                    <h2 class="sf-panel-title" style="color: #C63F38;">{{ translate('Pay Due Bill') }}</h2>
                    <p class="sf-step-text" style="margin: 0 0 14px;">
                        {{ translate('After editing your product list, you need to pay an additional') }}
                        <strong style="color: #0B2C3F;">@include('web-views.seafood.partials._price', ['amount' => $order['edit_due_amount']])</strong>
                        {{ translate('to continue processing this order') }}
                    </p>
                    <button type="button" class="sf-btn-sm sf-btn-sm-primary" data-sf-modal-open="choosePaymentMethodModal-{{ $order['id'] }}">{{ translate('Pay Now') }}</button>
                @elseif($latestEditHistory?->order_return_payment_status == 'pending')
                    <h2 class="sf-panel-title" style="color: #0E8E88;">{{ translate('Amount to Be Returned') }}</h2>
                    <p class="sf-step-text" style="margin: 0;">
                        {{ translate('After editing your product list, you will receive') }}
                        <strong style="color: #0B2C3F;">@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory?->order_return_amount ?? 0])</strong>
                        {{ translate('Please wait for the admin to process the returned amount') }}
                    </p>
                @elseif($latestEditHistory?->order_return_payment_status == 'returned')
                    <h2 class="sf-panel-title">{{ translate('Another Payment Info') }}</h2>
                    <div class="sf-meta-grid">
                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('Payment_status') }}</span><span><span class="sf-badge sf-badge-success">{{ translate($latestEditHistory?->order_return_payment_status) }}</span></span></span>
                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('Return_Amount') }}</span><span class="sf-meta-value">@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory?->order_return_amount])</span></span>
                        <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('Return Payment method') }}</span><span class="sf-meta-value">{{ translate($latestEditHistory?->order_return_payment_method) }}</span></span>
                    </div>
                    @if($latestEditHistory?->order_return_payment_note)
                        <p class="sf-field-hint">{{ translate('note') }}: {{ translate($latestEditHistory?->order_return_payment_note) }}</p>
                    @endif
                @endif
            </section>
        @endif

        @if($order->order_status == 'delivered')
            <section class="sf-panel" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <p class="sf-row-title" style="margin: 0;">{{ translate('Want to order the same items again') }}?</p>
                <button type="button" class="sf-btn-sm sf-btn-sm-primary sf-order-again" data-id="{{ $order->id }}">{{ translate('reorder') }}</button>
            </section>
        @endif

        @if($isDefaultOrder && $hasShippingAddress)
            <section class="sf-panel">
                <h2 class="sf-panel-title">{{ translate('shipping_address') }}</h2>
                @include('web-views.seafood.partials._order-address', ['orderAddress' => $shippingAddressData])
            </section>
        @endif

        @if($isDefaultOrder && $hasBillingAddress)
            <section class="sf-panel">
                <h2 class="sf-panel-title">{{ translate('billing_address') }}</h2>
                @if($isBillingSameAsShipping)
                    <p class="sf-step-text" style="margin: 0;">{{ translate('Same as shipping address') }}</p>
                @else
                    @include('web-views.seafood.partials._order-address', ['orderAddress' => $billingAddressData])
                @endif
            </section>
        @endif
    </div>

    <section class="sf-panel" style="margin-top: 16px;">
        <h2 class="sf-panel-title">{{ translate('Item List') }}</h2>
        <ul class="sf-order-items">
            @foreach($order->details as $detail)
                @php
                    $detailProduct = $detail?->productAllStatus ?? json_decode($detail->product_details, true);
                    $canReview = $isDefaultOrder && $order->order_status == 'delivered' && isset($detail->product);
                    $isPaidDigital = $detailProduct && $order->payment_status == 'paid' && isset($detailProduct['digital_product_type']);
                @endphp
                @continue(!$detailProduct)
                <li class="sf-order-item">
                    <a class="sf-row-media" href="{{ route('product', $detailProduct['slug']) }}">
                        <img src="{{ getStorageImages(path: $detail?->productAllStatus?->thumbnail_full_url, type: 'product') }}" alt="" loading="lazy">
                    </a>
                    <div class="sf-row-body">
                        <h3 class="sf-row-title"><a href="{{ route('product', $detailProduct['slug']) }}" style="color: inherit;">{{ Str::limit($detailProduct['name'] ?? '', 60) }}</a></h3>
                        <div class="sf-row-meta">
                            @if($detail->variant)
                                <span>{{ translate('variant') }}: <bdi>{{ $detail->variant }}</bdi></span>
                            @endif
                            <span>{{ translate('unit_price') }}: @include('web-views.seafood.partials._price', ['amount' => $detail->price])</span>
                            <span>{{ translate('qty') }}: <bdi dir="ltr">{{ $detail->qty }}</bdi></span>
                            @if($detail->discount > 0)
                                <span>{{ translate('discount') }}: − @include('web-views.seafood.partials._price', ['amount' => $detail->discount])</span>
                            @endif
                        </div>
                        @if(isset($refundStatusLabels[$detail->refund_request]))
                            <span class="sf-badge {{ $refundStatusBadges[$detail->refund_request] }}" style="align-self: flex-start;">{{ translate($refundStatusLabels[$detail->refund_request]) }}</span>
                        @endif
                    </div>
                    <div class="sf-order-item-side">
                        <strong class="sf-cart-item-total">@include('web-views.seafood.partials._price', ['amount' => ($detail->qty * $detail->price) - $detail->discount])</strong>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end;">
                            @if($canReview)
                                <button type="button" class="sf-btn-sm" data-sf-modal-open="submitReviewModal{{ $detail->id }}">
                                    {{ isset($detail->reviewData) ? translate('Update_Review') : translate('Give Review') }}
                                </button>
                            @endif
                            @if($isPaidDigital && $detailProduct['digital_product_type'] == 'ready_product')
                                <button type="button" class="sf-btn-sm sf-digital-download" data-link="{{ route('digital-product-download', $detail->id) }}">{{ translate('download') }}</button>
                            @elseif($isPaidDigital && $detailProduct['digital_product_type'] == 'ready_after_sell')
                                @if($detail->digital_file_after_sell)
                                    <button type="button" class="sf-btn-sm sf-digital-download" data-link="{{ route('digital-product-download', $detail->id) }}">{{ translate('download') }}</button>
                                @else
                                    <button type="button" class="sf-btn-sm" disabled title="{{ translate('Admin hasn’t uploaded it yet') }}">{{ translate('download') }}</button>
                                @endif
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="sf-panel sf-order-totals">
        @include('web-views.seafood.partials._order-totals', ['order' => $order])

        @if($order['order_status'] == 'pending')
            <button type="button" class="sf-btn-sm sf-btn-sm-danger call-route-alert" style="width: 100%; margin-top: 18px; min-height: 46px;"
                    data-route="{{ route('order-cancel', [$order->id]) }}" data-message="{{ translate('want_to_cancel_this_order?') }}">
                {{ translate('cancel_order') }}
            </button>
        @endif
    </section>

    @if($order->payment_method == 'offline_payment' && isset($order->offlinePayments))
        @include('web-views.seafood.partials._info-modal', [
            'infoModalId' => 'verifyViewModal',
            'infoModalTitle' => translate('payment_verification'),
            'infoModalRows' => array_merge([
                translate('name') => trim(($order->customer->f_name ?? translate('name_not_found')) . ' ' . ($order->customer->l_name ?? '')),
                translate('phone') => $order->customer->phone ?? translate('number_not_found'),
            ], collect($order->offlinePayments->payment_info)->except('method_id')->mapWithKeys(fn ($value, $key) => [translate($key) => $value ?? 'N/a'])->all(),
                $order->payment_note ? [translate('payment_note') => $order->payment_note] : []),
        ])
    @endif

    @if($latestEditHistory?->order_due_payment_method === 'offline_payment' && !empty($latestEditHistory?->order_due_payment_info))
        @include('web-views.seafood.partials._info-modal', [
            'infoModalId' => 'orderDuePaymentInfoModal',
            'infoModalTitle' => translate('payment_information'),
            'infoModalRows' => collect($latestEditHistory->order_due_payment_info)->except('method_id')->mapWithKeys(fn ($value, $key) => [translate(str_replace('_', ' ', $key)) => $value ?? 'N/A'])->all(),
        ])
    @endif

    @if($isEditedOrder && $order->edit_due_amount > 0 && !$isPayOnDelivery)
        @include('web-views.seafood.partials._order-due-payment-modal', ['order' => $order, 'paymentGatewayList' => $paymentGatewayList])
    @endif
@endsection
