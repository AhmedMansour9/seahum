{{-- Order price breakdown (items, discounts, tax, shipping, edit-due/return rows). Expects $order. --}}
@php
    $latestEditHistory = $order?->latestEditHistory;
    $isEditedOrder = $order->edited_status == 1;
    $isDefaultOrder = $order['order_type'] === 'default_type';
    $orderTotalPriceSummary = \App\Utils\OrderManager::getOrderTotalPriceSummary(order: $order);
@endphp

@if($isEditedOrder)
    <p class="sf-summary-saved">{{ translate('#Note : Total bill has been updated after the edits.') }}</p>
@endif
<dl class="sf-summary-rows">
    <div class="sf-summary-row"><dt>{{ translate('Total_Item') }}</dt><dd><bdi dir="ltr">{{ $orderTotalPriceSummary['totalItemQuantity'] }}</bdi></dd></div>
    <div class="sf-summary-row"><dt>{{ translate('item_price') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['itemPrice']])</dd></div>
    <div class="sf-summary-row"><dt>{{ translate('item_discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['itemDiscount']])</dd></div>
    @unless($isDefaultOrder)
        <div class="sf-summary-row"><dt>{{ translate('extra_discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['extraDiscount']])</dd></div>
    @endunless
    <div class="sf-summary-row"><dt>{{ translate('subtotal') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['subTotal']])</dd></div>
    <div class="sf-summary-row"><dt>{{ translate('coupon_discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['couponDiscount']])</dd></div>
    @if($orderTotalPriceSummary['referAndEarnDiscount'] > 0)
        <div class="sf-summary-row"><dt>{{ translate('referral_discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['referAndEarnDiscount']])</dd></div>
    @endif
    <div class="sf-summary-row"><dt>{{ translate('tax_fee') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['taxTotal']])</dd></div>
    @if($isDefaultOrder && $order?->is_shipping_free == 0)
        <div class="sf-summary-row"><dt>{{ translate('shipping_Fee') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['shippingTotal']])</dd></div>
    @endif
    <div class="sf-summary-row is-total">
        <dt>{{ translate('total') }} @if($orderTotalPriceSummary['tax_model'] == 'include')<span style="font-size: 12px; color: #6E8C99;">({{ translate('Tax_:_Inc.') }})</span>@endif</dt>
        <dd class="sf-summary-total">@include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['totalAmount'], 'symbolSize' => '14px'])</dd>
    </div>
    @if(($orderTotalPriceSummary['edited_total_paid_amount'] ?? 0) > 0)
        <div class="sf-summary-row"><dt>{{ translate('Paid Amount') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['edited_total_paid_amount']])</dd></div>
    @endif
    @if($isEditedOrder && $latestEditHistory)
        @if($latestEditHistory->order_due_amount > 0)
            @if($latestEditHistory->order_due_payment_status == 'paid')
                <div class="sf-summary-row"><dt>{{ translate('Paid Amount') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory->order_amount - $latestEditHistory->order_due_amount])</dd></div>
                <div class="sf-summary-row"><dt>{{ translate('Due Amount Paid By') }} ({{ ucwords(str_replace('_', ' ', $latestEditHistory->order_due_payment_method)) }})</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory->order_due_amount])</dd></div>
                <div class="sf-summary-row is-total"><dt>{{ translate('Total Paid Amount') }}</dt><dd class="sf-summary-total">@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory->order_amount, 'symbolSize' => '14px'])</dd></div>
            @else
                <div class="sf-summary-row" style="color: #C63F38;"><dt><strong>{{ translate('Due Amount') }}</strong></dt><dd style="color: #C63F38;">@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory->order_due_amount, 'symbolColor' => 'currentColor'])</dd></div>
            @endif
        @endif
        @if($latestEditHistory->order_return_amount > 0)
            @if($latestEditHistory->order_return_payment_status == 'returned')
                <div class="sf-summary-row"><dt>{{ translate('Paid Amount') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory->order_amount + $latestEditHistory->order_return_amount])</dd></div>
                <div class="sf-summary-row"><dt>{{ translate('Returned by') }} ({{ ucwords(str_replace('_', ' ', $latestEditHistory->order_return_payment_method)) }})</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory->order_return_amount])</dd></div>
            @else
                <div class="sf-summary-row" style="color: #C63F38;"><dt><strong>{{ translate('Amount To Return') }}</strong></dt><dd style="color: #C63F38;">@include('web-views.seafood.partials._price', ['amount' => $latestEditHistory->order_return_amount, 'symbolColor' => 'currentColor'])</dd></div>
            @endif
        @endif
    @endif
    @if(in_array($order->order_type, ['POS', 'pos']))
        <div class="sf-summary-row is-total"><dt>{{ translate('paid_amount') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['paidAmount']])</dd></div>
        <div class="sf-summary-row"><dt>{{ translate('change_amount') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $orderTotalPriceSummary['changeAmount']])</dd></div>
    @endif
</dl>
