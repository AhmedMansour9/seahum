@php
    $reviewData = $reviewDetail->reviewData ?? null;
    $reviewRating = (int) ($reviewData?->rating ?? 0);
    $reviewRatingLabels = [1 => translate('poor'), 2 => translate('average'), 3 => translate('good'), 4 => translate('very_good'), 5 => translate('excellent')];
@endphp

<div class="sf-modal" id="submitReviewModal{{ $reviewDetail->id }}" role="dialog" aria-modal="true" aria-labelledby="review-title-{{ $reviewDetail->id }}" hidden>
    <div class="sf-modal-dialog">
        <div class="sf-modal-head">
            <h2 class="sf-modal-title" id="review-title-{{ $reviewDetail->id }}">{{ $reviewData ? translate('Update_Review') : translate('submit_a_review') }}</h2>
            <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </div>

        <form class="sf-modal-body" action="{{ route('review.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="product_id" value="{{ $reviewDetail->product_id }}">
            <input type="hidden" name="order_id" value="{{ $reviewDetail->order_id }}">
            <input type="hidden" name="review_id" value="{{ $reviewData?->id }}">

            <article class="sf-row-card" style="margin-bottom: 18px;">
                <span class="sf-row-media"><img src="{{ getStorageImages(path: $reviewDetail->product->thumbnail_full_url, type: 'product') }}" alt=""></span>
                <div class="sf-row-body">
                    <h3 class="sf-row-title">{{ Str::limit($reviewDetail->product['name'], 60) }}</h3>
                    <div class="sf-row-meta">
                        @if($reviewDetail->variant)
                            <span>{{ translate('variant') }}: <bdi>{{ $reviewDetail->variant }}</bdi></span>
                        @endif
                        <span>{{ translate('qty') }}: <bdi dir="ltr">{{ $reviewDetail->qty }}</bdi></span>
                        <span>{{ translate('price') }}: @include('web-views.seafood.partials._price', ['amount' => $reviewDetail->price])</span>
                    </div>
                </div>
            </article>

            @include('web-views.seafood.partials._star-rating-input', [
                'ratingName' => 'rating',
                'ratingIdPrefix' => 'review-' . $reviewDetail->id,
                'ratingValue' => $reviewRating,
                'ratingTitle' => translate('rate_the_quality'),
                'ratingLabels' => $reviewRatingLabels,
            ])

            <label class="sf-form-label" for="review-comment-{{ $reviewDetail->id }}" style="margin-top: 18px;">{{ translate('have_thoughts_to_share') }}? <span class="sf-required">*</span></label>
            <textarea class="sf-filter-field" id="review-comment-{{ $reviewDetail->id }}" name="comment" required
                      placeholder="{{ translate('best_product,_highly_recommended') }}.">{{ $reviewData?->comment ?? '' }}</textarea>

            <span class="sf-form-label" style="margin-top: 18px;">{{ translate('upload_images') }}</span>
            @if($reviewData && $reviewData->attachment_full_url && count($reviewData->attachment_full_url) > 0)
                <ul class="sf-image-picker-list" style="display: flex; flex-wrap: wrap; gap: 10px; margin: 0 0 10px; padding: 0;">
                    @foreach($reviewData->attachment_full_url as $attachmentKey => $attachment)
                        <li class="sf-image-picker-item">
                            <img src="{{ getStorageImages(path: $attachment, type: 'product') }}" alt="{{ translate('review') }}">
                            <button type="button" class="sf-image-picker-remove sf-review-image-delete" aria-label="{{ translate('remove') }}"
                                    data-review-id="{{ $reviewData->id }}" data-photo="{{ $attachment['key'] }}" data-route="{{ route('delete-review-image') }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
            @include('web-views.seafood.partials._image-picker', ['pickerName' => 'fileUpload[]', 'pickerId' => 'review-images-' . $reviewDetail->id])

            <button type="submit" class="sf-btn-block" style="margin-top: 22px;">{{ translate('submit') }}</button>
        </form>
    </div>
</div>
