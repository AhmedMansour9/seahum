@php
    $checkoutStep = $checkoutStep ?? 1;
    $checkoutSteps = [
        1 => ['label' => translate('cart'), 'url' => route('shop-cart')],
        2 => [
            'label' => getWebConfig(name: 'billing_input_by_customer') == 1 ? translate('shipping_and_billing') : translate('shipping'),
            'url' => $checkoutStep >= 2 ? route('checkout-details') : null,
        ],
        3 => ['label' => translate('payment'), 'url' => null],
    ];
@endphp

<ol class="sf-steps" aria-label="{{ translate('checkout_steps') }}">
    @foreach($checkoutSteps as $stepNumber => $stepItem)
        @php $stepState = $stepNumber < $checkoutStep ? 'done' : ($stepNumber === $checkoutStep ? 'current' : 'upcoming'); @endphp
        <li class="sf-steps-item is-{{ $stepState }}" @if($stepState === 'current') aria-current="step" @endif>
            @if($stepItem['url'] && $stepState === 'done')
                <a href="{{ $stepItem['url'] }}" class="sf-steps-link">
            @else
                <span class="sf-steps-link">
            @endif
                <span class="sf-steps-dot" aria-hidden="true">
                    @if($stepState === 'done')
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg>
                    @else
                        <bdi dir="ltr">{{ $stepNumber }}</bdi>
                    @endif
                </span>
                <span class="sf-steps-label">{{ $stepItem['label'] }}</span>
            @if($stepItem['url'] && $stepState === 'done')
                </a>
            @else
                </span>
            @endif
        </li>
    @endforeach
</ol>
