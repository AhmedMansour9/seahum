@extends('layouts.front-end.seafood-account')

@section('title', translate('refund_request'))

@section('page_title', translate('refund_request'))

@section('page_actions')
    <a href="{{ route('account-order-details', ['id' => $order_details->order_id]) }}" class="sf-btn-sm">{{ translate('back_to_order') }}</a>
@endsection

@section('account_content')
    @php
        $refundProduct = \App\Models\Product::find($order_details->product_id);
        $refundSummary = \App\Utils\OrderManager::getRefundDetailsForSingleOrderDetails(orderDetailsId: $order_details['id']);
    @endphp

    <article class="sf-row-card">
        <span class="sf-row-media">
            <img src="{{ getStorageImages(path: $refundProduct?->thumbnail_full_url, type: 'product') }}" alt="">
        </span>
        <div class="sf-row-body">
            <h2 class="sf-row-title">{{ $refundProduct['name'] ?? translate('product_not_found') }}</h2>
            <div class="sf-row-meta">
                @if($order_details->variant)
                    <span>{{ translate('variant') }}: <bdi>{{ $order_details->variant }}</bdi></span>
                @endif
                <span>{{ translate('QTY') }}: <bdi dir="ltr">{{ $order_details->qty }}</bdi></span>
                <span>{{ translate('price') }}: @include('web-views.seafood.partials._price', ['amount' => $order_details->price])</span>
                <span>{{ translate('discount') }}: @include('web-views.seafood.partials._price', ['amount' => $order_details->discount])</span>
                <span>{{ translate('tax') }}: @include('web-views.seafood.partials._price', ['amount' => $order_details->tax])</span>
            </div>
        </div>
    </article>

    <div class="sf-panel" style="margin-top: 16px;">
        <h2 class="sf-panel-title">{{ translate('refund_summary') }}</h2>
        <dl class="sf-summary-rows">
            <div class="sf-summary-row"><dt>{{ translate('subtotal') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $refundSummary['sub_total']])</dd></div>
            <div class="sf-summary-row"><dt>{{ translate('Coupon_Discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $refundSummary['coupon_discount']])</dd></div>
            <div class="sf-summary-row"><dt>{{ translate('Referral_Discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $refundSummary['referral_discount']])</dd></div>
            <div class="sf-summary-row is-total"><dt>{{ translate('Total_Refundable_Amount') }}</dt><dd class="sf-summary-total">@include('web-views.seafood.partials._price', ['amount' => $refundSummary['total_refundable_amount'], 'symbolSize' => '14px'])</dd></div>
        </dl>
    </div>

    <form class="sf-panel" action="{{ route('refund-store') }}" method="post" enctype="multipart/form-data" style="margin-top: 16px;">
        @csrf
        <input type="hidden" name="order_details_id" value="{{ $order_details->id }}">
        <input type="hidden" name="amount" value="{{ $refundSummary['total_refundable_amount'] }}">

        <label class="sf-form-label" for="refund_reason">{{ translate('refund_reason') }} <span class="sf-required">*</span></label>
        <textarea class="sf-filter-field" id="refund_reason" name="refund_reason" required style="min-height: 130px;">{{ old('refund_reason') }}</textarea>

        <span class="sf-form-label" style="margin-top: 18px;">{{ translate('attachment') }}</span>
        @include('web-views.seafood.partials._image-picker', ['pickerName' => 'images[]', 'pickerId' => 'refund-images'])

        <button type="submit" class="sf-btn-block" style="margin-top: 22px;">{{ translate('submit') }}</button>
    </form>
@endsection
