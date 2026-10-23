@extends('web-views.seafood.order-details-layout')

@section('order_content')
    @php
        $deliveryMan = $order->deliveryMan;
        $deliveryManReview = $order->deliveryManReview;
        $deliveryManAverageRating = (float) ($deliveryMan?->rating[0]->average ?? 0);
        $canReviewDeliveryMan = $order->payment_status == 'paid' && $order->order_type == 'default_type' && $order->order_status == 'delivered' && $order->delivery_man_id;
        $deliveryRatingLabels = [1 => translate('poor'), 2 => translate('average'), 3 => translate('good'), 4 => translate('very_good'), 5 => translate('excellent')];
    @endphp

    @if($order->delivery_type == 'self_delivery' && isset($deliveryMan))
        <section class="sf-panel">
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <img src="{{ getStorageImages(path: $deliveryMan->image_full_url, type: 'avatar') }}" alt=""
                     style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 1px solid #E4EFF1; flex-shrink: 0;">
                <div class="sf-shop-body">
                    <h2 class="sf-shop-name" style="font-size: 19px;">{{ $deliveryMan->f_name }} {{ $deliveryMan->l_name }}</h2>
                    <div class="sf-row-meta">
                        <span style="display: inline-flex; align-items: center; gap: 6px;">
                            @include('web-views.seafood.partials._star-rating-display', ['displayRating' => $deliveryManAverageRating])
                            <bdi dir="ltr">({{ number_format($deliveryManAverageRating, 1) }})</bdi>
                        </span>
                        <span><bdi dir="ltr">{{ $delivered_count }}</bdi> {{ translate('completed_deliveries') }}</span>
                    </div>
                </div>
                <div class="sf-shop-actions">
                    <button type="button" class="sf-btn-sm" data-sf-modal-open="chatting_modal">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12.5a7.5 7.5 0 0 1-11 6.6L4 20.5l1.4-4.6A7.5 7.5 0 1 1 20 12.5z"></path></svg>
                        {{ translate('chat_with_delivery_man') }}
                    </button>
                    @if($canReviewDeliveryMan)
                        <button type="button" class="sf-btn-sm sf-btn-sm-primary" data-sf-modal-open="submitReviewModal">
                            {{ ($deliveryManReview?->comment || $deliveryManReview?->rating) ? translate('Update_Review') : translate('review') }}
                        </button>
                    @endif
                </div>
            </div>
        </section>

        @isset($deliveryManReview)
            <section class="sf-panel">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <h2 class="sf-panel-title" style="margin: 0; display: inline-flex; align-items: center; gap: 8px;">
                        {{ translate('delivery_man_review') }}
                        @include('web-views.seafood.partials._star-rating-display', ['displayRating' => $deliveryManReview->rating])
                    </h2>
                    <span class="sf-row-meta"><bdi dir="ltr">{{ date('M d, Y h:i A', strtotime($deliveryManReview->updated_at)) }}</bdi></span>
                </div>
                @if($deliveryManReview->comment)
                    <p class="sf-step-text" style="margin: 12px 0 0;">{{ $deliveryManReview->comment }}</p>
                @endif
            </section>
        @endisset

        @if($order->verificationImages->count() > 0)
            <section class="sf-panel">
                <h2 class="sf-panel-title">{{ translate('picture_upload_by') }} {{ $deliveryMan->f_name }} {{ $deliveryMan->l_name }}</h2>
                <div class="sf-bubble-media">
                    @foreach($order->verificationImages as $verificationImage)
                        <a href="{{ getStorageImages(path: $verificationImage->image_full_url, type: 'product') }}" target="_blank" rel="noopener">
                            <img src="{{ getStorageImages(path: $verificationImage->image_full_url, type: 'product') }}" alt="{{ translate('verification') }}" loading="lazy">
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($canReviewDeliveryMan)
            <div class="sf-modal" id="submitReviewModal" role="dialog" aria-modal="true" aria-labelledby="delivery-review-title" hidden>
                <div class="sf-modal-dialog">
                    <div class="sf-modal-head">
                        <h2 class="sf-modal-title" id="delivery-review-title">{{ translate('submit_a_review') }}</h2>
                        <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                        </button>
                    </div>
                    <form class="sf-modal-body" action="{{ route('submit-deliveryman-review') }}" method="post">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        @include('web-views.seafood.partials._star-rating-input', [
                            'ratingName' => 'rating',
                            'ratingIdPrefix' => 'delivery-rating',
                            'ratingValue' => (int) ($deliveryManReview?->rating ?? 0),
                            'ratingTitle' => translate('rate_the_delivery_quality'),
                            'ratingLabels' => $deliveryRatingLabels,
                        ])
                        <label class="sf-form-label" for="delivery-review-comment" style="margin-top: 18px;">{{ translate('have_thoughts_to_share') }}?</label>
                        <textarea class="sf-filter-field" id="delivery-review-comment" name="comment"
                                  placeholder="{{ translate('best_delivery_service,_highly_recommended') }}">{{ $deliveryManReview->comment ?? '' }}</textarea>
                        <button type="submit" class="sf-btn-block" style="margin-top: 22px;">
                            {{ ($deliveryManReview?->comment || $deliveryManReview?->rating) ? translate('update') : translate('submit') }}
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @include('web-views.seafood.partials._chat-modal', [
            'chatModalId' => 'chatting_modal',
            'chatModalTitle' => translate('Send_Message_to_Deliveryman'),
            'chatRecipientField' => 'delivery_man_id',
            'chatRecipientId' => $deliveryMan->id,
            'chatInboxType' => 'delivery-man',
            'isChatSendable' => $deliveryMan->id != 0,
        ])
    @elseif($order->delivery_type == 'third_party_delivery')
        <section class="sf-panel">
            <div class="sf-meta-grid">
                <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('delivery_service_name') }}</span><span class="sf-meta-value">{{ $order->delivery_service_name }}</span></span>
                <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('tracking_ID') }}</span><span class="sf-meta-value"><bdi dir="ltr">{{ $order->third_party_delivery_tracking_id ?? '' }}</bdi></span></span>
            </div>
        </section>
    @else
        @include('web-views.seafood.partials._empty-state', [
            'emptyStateIcon' => '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"></path><circle cx="7" cy="17.5" r="1.8"></circle><circle cx="17" cy="17.5" r="1.8"></circle>',
            'emptyStateTitle' => $order->order_type == 'POS'
                ? translate('this_order_is_a_POS_order.delivery_man_is_not_assigned_to_POS_orders')
                : (($order->product_type_check ?? null) == 'digital'
                    ? translate('this_order_contains_one_or_more_digital_products.') . ' ' . translate('delivery_man_is_not_assigned_for_digital_products')
                    : translate('no_delivery_man_assigned_yet')),
        ])
    @endif
@endsection
