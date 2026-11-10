@php
    $direction = \Illuminate\Support\Facades\Session::get('direction') ?? 'ltr';
    $shippingAddress = json_decode(json_encode($order['shipping_address_data']), true) ?: [];
    $customerName = $shippingAddress['contact_person_name'] ?? trim(($order->customer?->f_name ?? '') . ' ' . ($order->customer?->l_name ?? ''));
    $customerPhone = $shippingAddress['phone'] ?? $order->customer?->phone;
    $orderNumber = $order->order_source === \App\Enums\OrderSource::EasyOrders ? $order->external_order_id : $order['id'];
    $formatPrice = fn ($amount) => setCurrencySymbol(amount: usdToDefaultCurrency(amount: $amount), currencyCode: getCurrencyCode());
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ translate('receipt') }} #{{ $orderNumber }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { width: 72mm; margin: 0 auto; padding: 4mm 0; font-family: Tahoma, Arial, sans-serif; font-size: 12px; line-height: 1.5; color: #000; background: #fff; }
        .center { text-align: center; }
        .title { font-size: 16px; font-weight: 700; }
        .big { font-size: 15px; font-weight: 700; }
        .muted { font-size: 11px; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .row > span:last-child { text-align: end; white-space: nowrap; }
        .block { word-break: break-word; overflow-wrap: anywhere; }
        .item { margin-bottom: 5px; }
        .item-name { font-weight: 700; word-break: break-word; overflow-wrap: anywhere; }
        .total { font-size: 16px; font-weight: 700; }
        .no-print { text-align: center; margin: 8px 0; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="center">
        <div class="title">{{ $companyName }}</div>
        @if($companyPhone)
            <div class="muted" dir="ltr">{{ $companyPhone }}</div>
        @endif
    </div>
    <div class="divider"></div>

    <div class="row"><span class="big">{{ translate('receipt_order') }} #{{ $orderNumber }}</span><span class="muted">{{ $order['created_at']->format('d/m/Y h:i A') }}</span></div>
    @if($order->order_source === \App\Enums\OrderSource::EasyOrders)
        <div class="muted">{{ translate('EasyOrders') }}</div>
    @endif
    <div class="divider"></div>

    <div class="block">
        <div class="big">{{ $customerName }}</div>
        @if($customerPhone)
            <div dir="ltr" style="text-align: start">{{ $customerPhone }}</div>
        @endif
        @if(!empty($shippingAddress['city']))
            <div>{{ $shippingAddress['city'] }}</div>
        @endif
        @if(!empty($shippingAddress['address']))
            <div>{{ $shippingAddress['address'] }}</div>
        @endif
    </div>
    <div class="divider"></div>

    @foreach($order->details as $detail)
        <div class="item">
            <div class="item-name">{{ $detail->getInvoiceProductName() }}</div>
            @if($detail['variant'])
                <div class="muted">{{ $detail['variant'] }}</div>
            @endif
            <div class="row"><span>{{ $detail['qty'] }} × {{ $formatPrice($detail['price']) }}</span><span>{{ $formatPrice($detail['price'] * $detail['qty']) }}</span></div>
        </div>
    @endforeach
    <div class="divider"></div>

    <div class="row"><span>{{ translate('receipt_subtotal') }}</span><span>{{ $formatPrice($priceSummary['itemPrice'] - $priceSummary['itemDiscount']) }}</span></div>
    @if($priceSummary['extraDiscount'] > 0 || $priceSummary['couponDiscount'] > 0)
        <div class="row"><span>{{ translate('receipt_discount') }}</span><span>- {{ $formatPrice($priceSummary['extraDiscount'] + $priceSummary['couponDiscount']) }}</span></div>
    @endif
    <div class="row"><span>{{ translate('receipt_shipping') }}</span><span>{{ $formatPrice($priceSummary['shippingTotal']) }}</span></div>
    <div class="divider"></div>
    <div class="row total"><span>{{ translate('receipt_total') }}</span><span>{{ $formatPrice($priceSummary['totalAmount']) }}</span></div>
    <div class="row muted"><span>{{ translate('receipt_payment') }}</span><span>{{ translate($order['payment_method']) }} - {{ translate($order['payment_status']) }}</span></div>

    @if($order['order_note'])
        <div class="divider"></div>
        <div class="block"><b>{{ translate('receipt_note') }}:</b> {{ $order['order_note'] }}</div>
    @endif

    <div class="no-print"><button type="button" onclick="window.print()">{{ translate('receipt_print') }}</button></div>
    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
