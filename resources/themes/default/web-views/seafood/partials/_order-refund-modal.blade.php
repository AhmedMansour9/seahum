@php($refundSummary = \App\Utils\OrderManager::getRefundDetailsForSingleOrderDetails(orderDetailsId: $refundDetail['id']))

<div class="sf-modal" id="refundModal{{ $refundDetail->id }}" role="dialog" aria-modal="true" aria-labelledby="refund-modal-title-{{ $refundDetail->id }}" hidden>
    <div class="sf-modal-dialog">
        <div class="sf-modal-head">
            <h2 class="sf-modal-title" id="refund-modal-title-{{ $refundDetail->id }}">{{ translate('refund_request') }}</h2>
            <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </div>

        <form class="sf-modal-body" action="{{ route('refund-store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="order_details_id" value="{{ $refundDetail->id }}">
            <input type="hidden" name="amount" value="{{ $refundSummary['total_refundable_amount'] }}">

            <article class="sf-row-card">
                <span class="sf-row-media"><img src="{{ getStorageImages(path: $refundDetail?->productAllStatus?->thumbnail_full_url, type: 'product') }}" alt=""></span>
                <div class="sf-row-body">
                    <h3 class="sf-row-title">{{ Str::limit($refundProduct['name'] ?? translate('product_not_found'), 60) }}</h3>
                    <div class="sf-row-meta">
                        @if($refundDetail->variant)
                            <span>{{ translate('variant') }}: <bdi>{{ $refundDetail->variant }}</bdi></span>
                        @endif
                        <span>{{ translate('price') }}: @include('web-views.seafood.partials._price', ['amount' => $refundDetail->price])</span>
                        <span>{{ translate('qty') }}: <bdi dir="ltr">{{ $refundDetail->qty }}</bdi></span>
                    </div>
                </div>
            </article>

            <dl class="sf-summary-rows" style="margin: 18px 0;">
                <div class="sf-summary-row"><dt>{{ translate('total_price') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $refundDetail->price])</dd></div>
                <div class="sf-summary-row"><dt>{{ translate('product_discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $refundDetail->discount])</dd></div>
                <div class="sf-summary-row"><dt>{{ translate('vat') }}/{{ translate('tax') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $refundDetail->tax])</dd></div>
                <div class="sf-summary-row"><dt>{{ translate('sub_total') }}</dt><dd>@include('web-views.seafood.partials._price', ['amount' => $refundSummary['sub_total']])</dd></div>
                <div class="sf-summary-row"><dt>{{ translate('coupon_discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $refundSummary['coupon_discount']])</dd></div>
                <div class="sf-summary-row"><dt>{{ translate('referral_discount') }}</dt><dd class="is-negative">− @include('web-views.seafood.partials._price', ['amount' => $refundSummary['referral_discount']])</dd></div>
                <div class="sf-summary-row is-total"><dt>{{ translate('total_refundable_amount') }}</dt><dd class="sf-summary-total">@include('web-views.seafood.partials._price', ['amount' => $refundSummary['total_refundable_amount'], 'symbolSize' => '14px'])</dd></div>
            </dl>

            <label class="sf-form-label" for="refund-reason-{{ $refundDetail->id }}">{{ translate('give_a_refund_reason') }} <span class="sf-required">*</span></label>
            <textarea class="sf-filter-field" id="refund-reason-{{ $refundDetail->id }}" name="refund_reason" required placeholder="{{ translate('write_here') }}..."></textarea>

            <span class="sf-form-label" style="margin-top: 18px;">{{ translate('upload_images') }}</span>
            @include('web-views.seafood.partials._image-picker', ['pickerName' => 'images[]', 'pickerId' => 'refund-modal-images-' . $refundDetail->id])

            <button type="submit" class="sf-btn-block" style="margin-top: 22px;">{{ translate('send_request') }}</button>
        </form>
    </div>
</div>
