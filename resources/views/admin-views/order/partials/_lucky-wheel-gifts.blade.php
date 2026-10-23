@if($luckyWheelGifts->isNotEmpty())
    <div class="p-12 p-sm-20 bg-section rounded mt-3">
        <h4 class="mb-1">{{ translate('lucky_wheel_gifts') }}</h4>
        <p class="fs-12 mb-3">{{ translate('pack_these_free_gifts_with_the_order_they_are_not_charged') }}</p>
        <div class="d-flex flex-column gap-3">
            @foreach($luckyWheelGifts as $luckyWheelGift)
                <div class="d-flex align-items-center gap-3">
                    <img class="rounded object-fit-cover" width="50" height="50" alt=""
                         src="{{ getStorageImages(path: $luckyWheelGift->image_full_url, type: 'backend-basic') }}">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark text-truncate">{{ $luckyWheelGift->name }}</div>
                        @foreach($luckyWheelGift->name_translations ?? [] as $locale => $translatedName)
                            @if($locale !== 'en')
                                <div class="fs-12 text-truncate">{{ $translatedName }}</div>
                            @endif
                        @endforeach
                    </div>
                    <span class="badge badge-success text-bg-success">{{ translate('free_gift') }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif
