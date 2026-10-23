@extends('web-views.seafood.order-details-layout')

@section('order_content')
    <section class="sf-panel">
        @if($orderDetails->order_type == 'default_type' && getWebConfig(name: 'order_verification'))
            <p class="sf-summary-saved" style="margin-bottom: 18px;">
                {{ translate('order_verification_code') }}: <strong><bdi dir="ltr">{{ $orderDetails['verification_code'] }}</bdi></strong>
            </p>
        @endif
        @if($orderDetails->order_type == 'POS')
            <span class="sf-badge sf-badge-neutral" style="margin-bottom: 18px;">{{ translate('POS_Order') }}</span>
        @endif

        @include('web-views.seafood.partials._order-timeline', [
            'trackOrderArray' => \App\Utils\OrderManager::getTrackOrderStatusHistory(orderId: $orderDetails['id'], isOrderOnlyDigital: $isOrderOnlyDigital),
        ])
    </section>
@endsection
