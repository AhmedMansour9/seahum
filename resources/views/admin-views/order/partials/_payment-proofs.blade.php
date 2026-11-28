{{-- Transfer screenshots uploaded when the order was marked paid online, newest first. --}}
@php($orderPaymentProofs = $order->paymentProofs)
@if($orderPaymentProofs->isNotEmpty())
    <div class="bg-section p-2 mt-3 rounded" id="order-payment-proofs">
        <h5 class="fs-12 mb-2">{{ translate('transfer_screenshot') }}</h5>
        <div class="d-flex flex-wrap gap-2">
            @foreach($orderPaymentProofs as $paymentProof)
                @php($paymentProofUrl = is_array($paymentProof->image_full_url) ? ($paymentProof->image_full_url['path'] ?? null) : $paymentProof->image_full_url)
                <a href="{{ $paymentProofUrl }}" target="_blank" rel="noopener" class="d-flex flex-column gap-1 text-decoration-none" style="width: 96px;">
                    <img src="{{ $paymentProofUrl }}" alt="{{ translate('transfer_screenshot') }}"
                         style="width: 96px; height: 96px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;">
                    <span class="fs-10 text-dark text-truncate" title="{{ $paymentProof->uploaded_by_name }}">{{ $paymentProof->uploaded_by_name }}</span>
                    <span class="fs-10 text-muted">{{ $paymentProof->created_at?->format('d M, h:i A') }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
