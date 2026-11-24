@extends('layouts.front-end.seafood')

@section('title', translate('choose_Payment_Method'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    @include('web-views.seafood.partials._checkout-styles')
@endpush

@section('content')

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'products'])

        <main>
            <section class="sf-gutter sf-checkout-page" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [
                        ['label' => translate('shopping_cart'), 'url' => route('shop-cart')],
                        ['label' => translate('checkout'), 'url' => route('checkout-details')],
                        ['label' => translate('payment')],
                    ],
                ])

                @include('web-views.seafood.partials._checkout-steps', ['checkoutStep' => 2])

                <div class="sf-checkout-layout">
                    <div class="sf-checkout-main">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <h1 class="sf-page-title">{{ translate('choose_Payment_Method') }}</h1>
                            <a href="{{ route('checkout-details') }}" class="sf-btn-sm">{{ translate('go_back') }}</a>
                        </div>

                        @include('web-views.seafood.partials._checkout-payment-methods')
                    </div>

                    @include('web-views.seafood.partials._order-summary', ['summaryStep' => 'payment'])
                </div>
            </section>
        </main>

        @include('web-views.seafood.partials._footer')

    </div>

@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')

    @include('web-views.seafood.partials._checkout-scripts')
@endpush
