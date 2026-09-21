@extends('web-views.seafood.order-details-layout')

@section('order_content')
    @php
        $reviewedDetails = $order->details->filter(fn ($detail) => isset($detail->reviewData));
    @endphp

    @forelse($reviewedDetails as $reviewedDetail)
        @php
            $reviewedProduct = $reviewedDetail?->product;
            $productReview = $reviewedDetail->reviewData;
            $reviewReply = $productReview->reply;
        @endphp

        <section class="sf-panel">
            <article class="sf-row-card" style="border: none; padding: 0;">
                <a class="sf-row-media" @if($reviewedProduct?->slug) href="{{ route('product', $reviewedProduct->slug) }}" @endif>
                    <img src="{{ getStorageImages(path: $reviewedDetail?->productAllStatus?->thumbnail_full_url, type: 'product') }}" alt="" loading="lazy">
                </a>
                <div class="sf-row-body">
                    <h2 class="sf-row-title">{{ $reviewedProduct?->name ? Str::limit($reviewedProduct->name, 60) : translate('Product_not_found') }}</h2>
                    <div class="sf-row-meta">
                        @if($reviewedDetail->variant)
                            <span>{{ translate('variant') }}: <bdi>{{ $reviewedDetail->variant }}</bdi></span>
                        @endif
                        @include('web-views.seafood.partials._star-rating-display', ['displayRating' => $productReview->rating])
                        <span><bdi dir="ltr">{{ ($productReview->created_at ?? $productReview->updated_at)->format('d M Y, h:i A') }}</bdi></span>
                    </div>
                </div>
                @if(isset($reviewedDetail->product) && $order->order_status == 'delivered')
                    <div class="sf-row-actions">
                        <button type="button" class="sf-btn-sm" data-sf-modal-open="submitReviewModal{{ $reviewedDetail->id }}">{{ translate('update_review') }}</button>
                    </div>
                @endif
            </article>

            @if($productReview->comment)
                <p class="sf-step-text" style="margin: 14px 0 0;">{{ $productReview->comment }}</p>
            @endif

            @if(!empty($productReview->attachment) && count($productReview->attachment_full_url ?? []) > 0)
                <div class="sf-bubble-media" style="margin-top: 12px;">
                    @foreach($productReview->attachment_full_url as $reviewPhoto)
                        <a href="{{ getStorageImages(path: $reviewPhoto, type: 'product') }}" target="_blank" rel="noopener">
                            <img src="{{ getStorageImages(path: $reviewPhoto, type: 'product') }}" alt="{{ translate('review') }}" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @endif

            @if($reviewReply)
                <div class="sf-review-reply">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                        <strong style="font-family: Cairo, sans-serif; color: #0B2C3F;">{{ translate('Reply_by_Seller') }}</strong>
                        <span class="sf-row-meta"><bdi dir="ltr">{{ $reviewReply->created_at?->format('M d, Y') }}</bdi></span>
                    </div>
                    <div class="sf-step-text" style="margin-top: 8px;">{!! $reviewReply->reply_text !!}</div>
                    @if(!empty(json_decode($reviewReply->attachment ?? '[]')))
                        <div class="sf-bubble-media" style="margin-top: 10px;">
                            @foreach($reviewReply->attachment_full_url as $replyPhoto)
                                <a href="{{ getStorageImages(path: $replyPhoto, type: 'product') }}" target="_blank" rel="noopener">
                                    <img src="{{ getStorageImages(path: $replyPhoto, type: 'product') }}" alt="{{ translate('reply') }}" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </section>
    @empty
        @include('web-views.seafood.partials._empty-state', [
            'emptyStateIcon' => '<path d="m12 3.6 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.6 9.7l5.8-.8z"></path>',
            'emptyStateTitle' => translate('no_review_found').'!',
        ])
    @endforelse
@endsection
