@extends('layouts.front-end.seafood')

@section('title', translate('order_Complete'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    @include('web-views.seafood.partials._checkout-styles')
@endpush

@section('content')
    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'products'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => translate('order_Complete')]],
                ])

                @if(auth('customer')->check() || session('guest_id'))
                    <div class="sf-card" style="max-width: 620px; margin: 0 auto; padding: clamp(24px, 4vw, 40px); text-align: center;">
                        <span class="sf-empty-icon" style="display: inline-grid; width: 72px; height: 72px; border-radius: 24px;">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="m8.2 12.4 2.6 2.6 5-5.4"></path></svg>
                        </span>

                        <h1 class="sf-page-title" style="margin-top: 14px;">
                            @if(!empty($isNewCustomerInSession))
                                {{ translate('Order_Placed_&_Account_Created_Successfully') }}!
                            @else
                                {{ translate('Order_Placed_Successfully') }}!
                            @endif
                        </h1>

                        <p style="font-size: 15px; line-height: 1.9; color: #55747F; margin: 12px auto 0; max-width: 460px; overflow-wrap: anywhere;">
                            @if(!empty($order_ids))
                                {{ translate('your_payment_has_been_successfully_processed_and_your_order') }}
                                <strong style="color: #0E8E88;"><bdi dir="ltr">{{ implode(', ', $order_ids) }}</bdi></strong>
                                {{ translate('has_been_placed.') }}
                            @else
                                {{ translate('your_order_is_being_processed_and_will_be_completed.') }}
                                {{ translate('You_will_receive_an_email_confirmation_when_your_order_is_placed.') }}
                            @endif
                        </p>

                        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 26px;">
                            <a href="{{ route('track-order.index') }}" class="sf-btn-block" style="width: auto; flex: 1 1 200px;">{{ translate('track_Order') }}</a>
                            <a href="{{ route('home') }}" class="sf-btn-outline" style="width: auto; flex: 1 1 200px;">{{ translate('Continue_Shopping') }}</a>
                        </div>
                    </div>
                @else
                    @include('web-views.seafood.partials._empty-state', [
                        'emptyStateTitle' => translate('order_Complete'),
                        'emptyStateActionUrl' => route('home'),
                        'emptyStateActionLabel' => translate('Continue_Shopping'),
                    ])
                @endif
            </section>
        </main>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')
    </div>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')
@endpush
