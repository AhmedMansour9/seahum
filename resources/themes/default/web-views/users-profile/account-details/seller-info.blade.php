@extends('web-views.seafood.order-details-layout')

@section('order_content')
    @php
        $isVendorOrder = $order->seller_is == 'seller';
        $vendorShop = $isVendorOrder ? $order?->seller?->shop : null;
        $vendorShopName = $isVendorOrder ? ($vendorShop->name ?? translate('shop_not_found')) : getInHouseShopConfig(key: 'name');
        $vendorShopImage = $isVendorOrder ? $vendorShop?->image_full_url : getInHouseShopConfig(key: 'image_full_url');
        $vendorAbilityType = $isVendorOrder ? 'vendor' : 'inhouse';
        $isVendorTemporarilyClosed = checkVendorAbility(type: $vendorAbilityType, status: 'temporary_close', vendor: $vendorShop);
        $isVendorOnVacation = !$isVendorTemporarilyClosed && checkVendorAbility(type: $vendorAbilityType, status: 'vacation_status', vendor: $vendorShop);
        $vendorAverageRating = round((float) $avg_rating, 1);
    @endphp

    <section class="sf-panel">
        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <span class="sf-shop-logo" style="width: 76px; height: 76px; border-width: 1px; border-color: #E4EFF1; box-shadow: none;">
                <img src="{{ getStorageImages(path: $vendorShopImage, type: 'shop') }}" alt="{{ $vendorShopName }}">
            </span>
            <div class="sf-shop-body">
                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <h2 class="sf-shop-name" style="font-size: 19px;">{{ $vendorShopName }}</h2>
                    @if($isVendorTemporarilyClosed)
                        <span class="sf-badge sf-badge-danger">{{ translate('Temporary_OFF') }}</span>
                    @elseif($isVendorOnVacation)
                        <span class="sf-badge sf-badge-warning">{{ translate('closed_now') }}</span>
                    @endif
                </div>
                <div class="sf-row-meta">
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        @include('web-views.seafood.partials._star-rating-display', ['displayRating' => $vendorAverageRating])
                        <bdi dir="ltr">({{ number_format($vendorAverageRating, 1) }})</bdi>
                    </span>
                    <span><bdi dir="ltr">{{ $rating_count }}</bdi> {{ translate('reviews') }}</span>
                    <span><bdi dir="ltr">{{ $product_count }}</bdi> {{ translate('products') }}</span>
                    <span><bdi dir="ltr">{{ round($rating_percentage) }}%</bdi> {{ translate('positive_review') }}</span>
                </div>
            </div>
            <div class="sf-shop-actions">
                @if($isVendorOrder && $vendorShop)
                    <a href="{{ route('vendor-shop', ['slug' => $vendorShop->slug]) }}" class="sf-btn-sm">{{ translate('visit_store') }}</a>
                @elseif(!$isVendorOrder)
                    <a href="{{ route('vendor-shop', ['slug' => getInHouseShopConfig(key: 'slug')]) }}" class="sf-btn-sm">{{ translate('visit_store') }}</a>
                @endif
                <button type="button" class="sf-btn-sm sf-btn-sm-primary" data-sf-modal-open="chatting_modal" @disabled($isVendorTemporarilyClosed)>
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12.5a7.5 7.5 0 0 1-11 6.6L4 20.5l1.4-4.6A7.5 7.5 0 1 1 20 12.5z"></path></svg>
                    {{ translate('chat_with_vendor') }}
                </button>
            </div>
        </div>
    </section>

    @include('web-views.seafood.partials._chat-modal', [
        'chatModalId' => 'chatting_modal',
        'chatModalTitle' => translate('send_message_to') . ' ' . $vendorShopName,
        'chatRecipientField' => 'vendor_id',
        'chatRecipientId' => $isVendorOrder ? $order->seller?->id : 0,
        'chatInboxType' => 'vendor',
    ])
@endsection
