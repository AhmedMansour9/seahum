@php($showGiftStatus = $showGiftStatus ?? false)
<ul class="sf-order-items">
    @foreach($luckyWheelGifts as $luckyWheelGift)
        <li class="sf-order-item">
            <span class="sf-row-media">
                <img src="{{ getStorageImages(path: $luckyWheelGift->image_full_url, type: 'product') }}" alt="" loading="lazy">
            </span>
            <div class="sf-row-body">
                <h3 class="sf-row-title">{{ $luckyWheelGift->getLocalizedName() }}</h3>
                <div class="sf-row-meta">
                    <span>{{ translate('won_on') }} <bdi dir="ltr">{{ $luckyWheelGift->awarded_at->format('d M, Y') }}</bdi></span>
                    @if($showGiftStatus && $luckyWheelGift->order_id)
                        <a href="{{ route('account-order-details', ['id' => $luckyWheelGift->order_id]) }}" class="sf-text-link">
                            {{ translate('added_to_order') }} <bdi dir="ltr">#{{ $luckyWheelGift->order_id }}</bdi>
                        </a>
                    @elseif($showGiftStatus)
                        <span>{{ translate('will_be_added_to_your_next_order') }}</span>
                    @endif
                </div>
            </div>
            <div class="sf-order-item-side">
                <span class="sf-badge sf-badge-success">{{ translate('free_gift') }}</span>
            </div>
        </li>
    @endforeach
</ul>
