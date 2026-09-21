@php
    $refundSummary = \App\Utils\OrderManager::getRefundDetailsForSingleOrderDetails(orderDetailsId: $order_details['id']);
    $refundStatusBadges = ['pending' => 'sf-badge-warning', 'approved' => 'sf-badge-info', 'refunded' => 'sf-badge-success', 'rejected' => 'sf-badge-danger'];
@endphp

<div class="sf-stack">
    @if(isset($refund->product))
        <article class="sf-row-card">
            <a class="sf-row-media" href="{{ route('product', $refund->product['slug']) }}">
                <img src="{{ getStorageImages(path: $refund->product->thumbnail_full_url, type: 'product') }}" alt="">
            </a>
            <div class="sf-row-body">
                <h3 class="sf-row-title"><a href="{{ route('product', $refund->product['slug']) }}" style="color: inherit;">{{ $refund->product['name'] }}</a></h3>
                <div class="sf-row-meta">
                    @if($order_details->variant)
                        <span>{{ translate('variant') }}: <bdi>{{ $order_details->variant }}</bdi></span>
                    @endif
                    <span>{{ translate('qty') }}: <bdi dir="ltr">{{ $order_details->qty }}</bdi></span>
                    <span>{{ translate('price') }}: @include('web-views.seafood.partials._price', ['amount' => $order_details->price])</span>
                    <span><bdi dir="ltr">{{ $order_details->created_at->format('d M Y, h:i a') }}</bdi></span>
                </div>
            </div>
        </article>
    @else
        <div class="sf-panel"><p class="sf-row-title" style="margin: 0;">{{ translate('product_not_found') }}</p></div>
    @endif

    <div class="sf-panel">
        <dl class="sf-summary-rows">
            <div class="sf-summary-row"><dt>{{ translate('total_price') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $refundSummary['product_price']])</dd></div>
            <div class="sf-summary-row"><dt>{{ translate('product_discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $refundSummary['product_discount']])</dd></div>
            <div class="sf-summary-row"><dt>{{ translate('vat') }}/{{ translate('tax') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $refundSummary['tax']])</dd></div>
            <div class="sf-summary-row"><dt>{{ translate('sub_total') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $refundSummary['sub_total']])</dd></div>
            <div class="sf-summary-row"><dt>{{ translate('coupon_discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $refundSummary['coupon_discount']])</dd></div>
            <div class="sf-summary-row"><dt>{{ translate('referral_discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $refundSummary['referral_discount']])</dd></div>
            <div class="sf-summary-row is-total">
                <dt>{{ translate(($refund->status ?? '') == 'refunded' ? 'total_refunded_amount' : 'total_refundable_amount') }}</dt>
                <dd class="sf-summary-total">@include('web-views.seafood.partials._price', ['amount' => $refundSummary['total_refundable_amount'], 'symbolSize' => '14px'])</dd>
            </div>
        </dl>
    </div>

    @if($refund)
        <div class="sf-panel">
            <div class="sf-meta-grid">
                <span class="sf-meta-item">
                    <span class="sf-meta-label">{{ translate('refund_status') }}</span>
                    <span><span class="sf-badge {{ $refundStatusBadges[$refund->status] ?? 'sf-badge-neutral' }}">{{ translate($refund->status) }}</span></span>
                </span>
                <span class="sf-meta-item" style="grid-column: 1 / -1;">
                    <span class="sf-meta-label">{{ translate('refund_reason') }}</span>
                    <span class="sf-meta-value" style="font-weight: 400; font-family: Tajawal, sans-serif; line-height: 1.8; color: #34586B;">{{ $refund->refund_reason }}</span>
                </span>
            </div>

            @if($refund->images != null && count($refund->images_full_url) > 0)
                <span class="sf-form-label" style="margin-top: 16px;">{{ translate('upload_images') }} (<bdi dir="ltr">{{ count($refund->images_full_url) }}</bdi>)</span>
                <div class="sf-bubble-media">
                    @foreach($refund->images_full_url as $photo)
                        <a href="{{ $photo['path'] }}" target="_blank" rel="noopener">
                            <img src="{{ getStorageImages(path: $photo, type: 'product') }}" alt="{{ translate('refund') }}" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
