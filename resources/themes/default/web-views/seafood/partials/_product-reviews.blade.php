@foreach($productReviews as $productReview)
    @php($reviewerName = $productReview->user ? trim($productReview->user->f_name . ' ' . $productReview->user->l_name) : translate('not_exist'))
    @php($reviewComment = $productReview?->comment ?? '')

    <article style="background: #FFFFFF; border: 1px solid var(--sh-line); border-radius: 18px; padding: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                <img src="{{ $productReview->user ? getStorageImages(path: $productReview->user->image_full_url, type: 'avatar') : theme_asset(path: 'public/assets/front-end/img/image-place-holder.png') }}"
                     alt="{{ $reviewerName }}" loading="lazy"
                     style="width: 46px; height: 46px; border-radius: 50%; object-fit: cover; background: var(--sh-navy-50); flex-shrink: 0;">
                <span style="display: flex; flex-direction: column; gap: 3px; min-width: 0;">
                    <strong style="font-family: var(--sh-font-display); font-size: 15px; font-weight: 700; color: var(--sh-navy); overflow-wrap: anywhere;">{{ $reviewerName }}</strong>
                    <span style="display: flex; align-items: center; gap: 3px;">
                        @for($star = 1; $star <= 5; $star++)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="{{ $star <= (int) ($productReview->rating ?? 0) ? '#F2A43A' : '#E2E8F0' }}"><path d="m12 3.6 2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.5 9.8l5.9-.9z"></path></svg>
                        @endfor
                    </span>
                </span>
            </div>
            <span style="font-size: 12.5px; color: var(--sh-muted-2);">{{ $productReview->updated_at?->format('Y-m-d') }}</span>
        </div>

        @if(filled($reviewComment))
            <p style="font-size: 14.5px; line-height: 1.9; color: var(--sh-ink-2); margin: 14px 0 0; overflow-wrap: anywhere;">{{ $reviewComment }}</p>
        @endif

        @if(!empty($productReview->attachment_full_url))
            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px;">
                @foreach($productReview->attachment_full_url as $attachment)
                    <img src="{{ getStorageImages(path: $attachment, type: 'product') }}" alt="{{ translate('product') }}" loading="lazy"
                         style="width: 72px; height: 72px; border-radius: 12px; border: 1px solid var(--sh-line); object-fit: cover;">
                @endforeach
            </div>
        @endif

        @if($productReview->reply)
            <div style="margin-top: 16px; padding: 16px; border-radius: 14px; background: var(--sh-mist); border: 1px solid var(--sh-line);">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 8px;">
                    <strong style="font-family: var(--sh-font-display); font-size: 14px; font-weight: 700; color: var(--sh-sea-700);">{{ translate('Reply_by_Seller') }}</strong>
                    <span style="font-size: 12px; color: var(--sh-muted-2);">{{ $productReview->reply->created_at?->format('Y-m-d') }}</span>
                </div>
                <p style="font-size: 14px; line-height: 1.85; color: var(--sh-ink-2); margin: 0; overflow-wrap: anywhere;">{{ strip_tags($productReview->reply->reply_text ?? '') }}</p>
            </div>
        @endif
    </article>
@endforeach
