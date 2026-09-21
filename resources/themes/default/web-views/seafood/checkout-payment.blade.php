@extends('layouts.front-end.seafood')

@section('title', translate('choose_Payment_Method'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    @include('web-views.seafood.partials._checkout-styles')
@endpush

@section('content')
    @php
        $isCashOnDeliveryAvailable = $cashOnDeliveryBtnShow && $cash_on_delivery['status'];
        $isWalletAvailable = auth('customer')->check() && $wallet_status == 1;
        $isDigitalPaymentAvailable = $digital_payment['status'] == 1 && count($payment_gateways_list) > 0;
        $isOfflinePaymentAvailable = isset($offline_payment) && $offline_payment['status'] && count($offline_payment_methods) > 0;
        $walletBalance = $isWalletAvailable ? (auth('customer')->user()->wallet_balance ?? 0) : 0;
        $walletRemainingBalance = $walletBalance - $amount;
        $isFirstChoice = true;
    @endphp

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'products'])

        <main>
            <section class="sf-gutter sf-checkout-page" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [
                        ['label' => translate('shopping_cart'), 'url' => route('shop-cart')],
                        ['label' => translate('shipping_Address'), 'url' => route('checkout-details')],
                        ['label' => translate('payment')],
                    ],
                ])

                @include('web-views.seafood.partials._checkout-steps', ['checkoutStep' => 3])

                <div class="sf-checkout-layout">
                    <div class="sf-checkout-main">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <h1 class="sf-page-title">{{ translate('choose_Payment_Method') }}</h1>
                            <a href="{{ route('checkout-details') }}" class="sf-btn-sm">{{ translate('go_back') }}</a>
                        </div>

                        @if(!$activeMinimumMethods)
                            @include('web-views.seafood.partials._empty-state', [
                                'emptyStateTitle' => translate('payment_methods_are_not_available_at_this_time.'),
                                'emptyStateIcon' => '<rect x="3" y="6" width="18" height="12" rx="3"></rect><path d="M3 10h18"></path>',
                            ])
                        @else
                            <div class="sf-panel">
                                <h2 class="sf-panel-title">{{ translate('select_a_payment_method_to_proceed') }}</h2>

                                <ul class="sf-choice-list" role="radiogroup" aria-label="{{ translate('payment_method') }}" style="margin-bottom: 0;">
                                    @if($isCashOnDeliveryAvailable)
                                        <li>
                                            <label class="sf-choice">
                                                <input type="radio" name="sf_payment_method" value="cash_on_delivery" @checked($isFirstChoice)>
                                                <span class="sf-choice-dot" aria-hidden="true"></span>
                                                <span class="sf-choice-body">
                                                    <span class="sf-choice-title">{{ translate('cash_on_Delivery') }}</span>
                                                    <span class="sf-choice-text">{{ translate('pay_when_your_order_arrives') }}</span>
                                                </span>
                                            </label>
                                        </li>
                                        @php $isFirstChoice = false; @endphp
                                    @endif

                                    @if($isWalletAvailable)
                                        <li>
                                            <label class="sf-choice">
                                                <input type="radio" name="sf_payment_method" value="wallet" @checked($isFirstChoice && $walletRemainingBalance >= 0) @disabled($walletRemainingBalance < 0)>
                                                <span class="sf-choice-dot" aria-hidden="true"></span>
                                                <span class="sf-choice-body">
                                                    <span class="sf-choice-title">{{ translate('pay_via_Wallet') }}</span>
                                                    <span class="sf-choice-text">{{ translate('your_current_balance') }}: @include('web-views.seafood.partials._price', ['amount' => $walletBalance])</span>
                                                    @if($walletRemainingBalance < 0)
                                                        <span class="sf-choice-text" style="color: #E4574F;">{{ translate('you_do_not_have_sufficient_balance_for_pay_this_order!!') }}</span>
                                                    @else
                                                        <span class="sf-choice-text">{{ translate('remaining_balance') }}: @include('web-views.seafood.partials._price', ['amount' => $walletRemainingBalance])</span>
                                                    @endif
                                                </span>
                                            </label>
                                        </li>
                                        @php $isFirstChoice = $isFirstChoice && $walletRemainingBalance < 0; @endphp
                                    @endif

                                    @if($isDigitalPaymentAvailable)
                                        @foreach($payment_gateways_list as $paymentGateway)
                                            @php
                                                $gatewayAdditionalData = $paymentGateway['additional_data'] != null ? json_decode($paymentGateway['additional_data']) : null;
                                                $gatewayImagePath = dynamicAsset(path: 'public/assets/back-end/img/modal/payment-methods/' . $paymentGateway->key_name . '.png');
                                                if ($gatewayAdditionalData?->gateway_image && file_exists(base_path('storage/app/public/payment_modules/gateway_image/' . $gatewayAdditionalData->gateway_image))) {
                                                    $gatewayImagePath = dynamicStorage(path: 'storage/app/public/payment_modules/gateway_image/' . $gatewayAdditionalData->gateway_image);
                                                }
                                                $gatewayTitle = $gatewayAdditionalData?->gateway_title ?: str_replace('_', ' ', $paymentGateway->key_name);
                                            @endphp
                                            <li>
                                                <label class="sf-choice">
                                                    <input type="radio" name="sf_payment_method" value="{{ $paymentGateway->key_name }}" @checked($isFirstChoice)>
                                                    <span class="sf-choice-dot" aria-hidden="true"></span>
                                                    <img class="sf-choice-media" src="{{ $gatewayImagePath }}" alt="">
                                                    <span class="sf-choice-body">
                                                        <span class="sf-choice-title">{{ $gatewayTitle }}</span>
                                                        <span class="sf-choice-text">{{ translate('faster_&_secure_way_to_pay') }}</span>
                                                    </span>
                                                </label>
                                            </li>
                                            @php $isFirstChoice = false; @endphp
                                        @endforeach
                                    @endif

                                    @if($isOfflinePaymentAvailable)
                                        <li>
                                            <label class="sf-choice">
                                                <input type="radio" name="sf_payment_method" value="pay_offline" @checked($isFirstChoice)>
                                                <span class="sf-choice-dot" aria-hidden="true"></span>
                                                <span class="sf-choice-body">
                                                    <span class="sf-choice-title">{{ translate('pay_offline') }}</span>
                                                    <span class="sf-choice-text">{{ translate('for_offline_payment_options,_please_follow_the_steps_below') }}</span>
                                                </span>
                                            </label>
                                        </li>
                                    @endif
                                </ul>

                                @if($isCashOnDeliveryAvailable)
                                    <div class="sf-payment-extra" data-payment-extra="cash_on_delivery" style="margin-top: 18px;">
                                        <label class="sf-form-label" for="bring_change_amount_input">
                                            {{ translate('Change_Amount') }} ({{ getCurrencySymbol(type: 'web') }})
                                            <span style="font-weight: 500; color: #6E8C99;">({{ translate('optional') }})</span>
                                        </label>
                                        <input type="text" inputmode="numeric" class="sf-filter-field" id="bring_change_amount_input" style="max-width: 260px;"
                                               placeholder="{{ translate('Amount') }}" dir="ltr">
                                        <span class="sf-field-hint">{{ translate('Insert_amount_if_you_need_deliveryman_to_bring') }}</span>
                                    </div>
                                @endif

                                @if($isOfflinePaymentAvailable)
                                    <div class="sf-payment-extra" data-payment-extra="pay_offline" style="margin-top: 18px;" hidden>
                                        <span class="sf-form-label">{{ translate('select_Payment_Method') }}</span>
                                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                            @foreach($offline_payment_methods as $offlinePaymentMethod)
                                                <button type="button" class="sf-btn-sm sf-offline-method" data-method-id="{{ $offlinePaymentMethod->id }}">{{ $offlinePaymentMethod->method_name }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Each payment choice submits its own form, so the gateways keep receiving the fields they expect. --}}
                        @if($isCashOnDeliveryAvailable)
                            <form action="{{ route('checkout-complete') }}" method="get" id="cash_on_delivery_form" hidden>
                                <input type="hidden" name="payment_method" value="cash_on_delivery">
                                <input type="hidden" name="bring_change_amount" id="bring_change_amount_value">
                            </form>
                        @endif

                        @if($isWalletAvailable)
                            <form action="{{ route('checkout-complete-wallet') }}" method="get" id="wallet_form" hidden>
                                @csrf
                            </form>
                        @endif

                        @if($isDigitalPaymentAvailable)
                            @foreach($payment_gateways_list as $paymentGateway)
                                <form method="post" class="digital_payment" id="{{ $paymentGateway->key_name }}_form" action="{{ route('customer.web-payment-request') }}" hidden>
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ auth('customer')->check() ? auth('customer')->user()->id : session('guest_id') }}">
                                    <input type="hidden" name="customer_id" value="{{ auth('customer')->check() ? auth('customer')->user()->id : session('guest_id') }}">
                                    <input type="hidden" name="payment_method" value="{{ $paymentGateway->key_name }}">
                                    <input type="hidden" name="payment_platform" value="web">
                                    @if($paymentGateway->mode == 'live' && isset($paymentGateway->live_values['callback_url']))
                                        <input type="hidden" name="callback" value="{{ $paymentGateway->live_values['callback_url'] }}">
                                    @elseif($paymentGateway->mode == 'test' && isset($paymentGateway->test_values['callback_url']))
                                        <input type="hidden" name="callback" value="{{ $paymentGateway->test_values['callback_url'] }}">
                                    @else
                                        <input type="hidden" name="callback" value="">
                                    @endif
                                    <input type="hidden" name="external_redirect_link" value="{{ route('web-payment-success') }}">
                                </form>
                            @endforeach
                        @endif
                    </div>

                    @include('web-views.seafood.partials._order-summary', ['summaryStep' => 'payment'])
                </div>
            </section>
        </main>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')
    </div>

    @if($isOfflinePaymentAvailable)
        <div class="sf-modal" id="sf-offline-payment-modal" role="dialog" aria-modal="true" aria-labelledby="sf-offline-payment-title" hidden>
            <div class="sf-modal-dialog">
                <div class="sf-modal-head">
                    <h2 class="sf-modal-title" id="sf-offline-payment-title">{{ translate('pay_offline') }}</h2>
                    <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <form action="{{ route('offline-payment-checkout-complete') }}" method="post" class="sf-modal-body sf-offline-form">
                    @csrf
                    <p class="sf-field-hint" style="margin-top: 0;">{{ translate('pay_your_bill_using_any_of_the_payment_method_below_and_input_the_required_information_in_the_form') }}</p>
                    <label class="sf-form-label" for="pay_offline_method">{{ translate('select_Payment_Method') }}</label>
                    <select class="sf-filter-field" id="pay_offline_method" name="payment_by" required>
                        @foreach($offline_payment_methods as $offlinePaymentMethod)
                            <option value="{{ $offlinePaymentMethod->id }}">{{ $offlinePaymentMethod->method_name }}</option>
                        @endforeach
                    </select>
                    <div id="payment_method_field" style="margin-top: 16px;"></div>
                </form>
            </div>
        </div>
    @endif

    <span id="route-pay-offline-method-list" data-url="{{ route('pay-offline-method-list') }}"></span>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')

    @include('web-views.seafood.partials._checkout-scripts')

    <script>
        "use strict";

        function updateSeafoodPaymentState() {
            const selectedMethod = $('input[name="sf_payment_method"]:checked').val();
            const hasAcceptedTerms = $('.sf-payment-terms').length === $('.sf-payment-terms:checked').length;

            $('.sf-payment-extra').each(function () {
                $(this).prop('hidden', $(this).data('payment-extra') !== selectedMethod);
            });
            // Offline orders are placed from the offline form itself, never from the summary button.
            $('.sf-checkout-proceed').prop('disabled', !(hasAcceptedTerms && selectedMethod && selectedMethod !== 'pay_offline'));
        }

        $(document).on('change', 'input[name="sf_payment_method"]', updateSeafoodPaymentState);

        $(document).on('change', '.sf-payment-terms', function () {
            $('.sf-payment-terms').prop('checked', this.checked);
            updateSeafoodPaymentState();
        });

        $(document).on('input', '#bring_change_amount_input', function () {
            this.value = this.value.replace(/[^0-9.]/g, '');
            $('#bring_change_amount_value').val(this.value);
        });

        function loadSeafoodOfflineMethodFields(methodId) {
            $('#pay_offline_method').val(methodId);
            $.get({
                url: $('#route-pay-offline-method-list').data('url') + '?method_id=' + encodeURIComponent(methodId),
                success: function (response) {
                    $('#payment_method_field').html(response.methodHtml);
                    openSeafoodModal('sf-offline-payment-modal');
                },
                error: function () {
                    toastr.error(@json(translate('something_went_wrong')));
                },
            });
        }

        $(document).on('click', '.sf-offline-method', function () {
            loadSeafoodOfflineMethodFields($(this).data('method-id'));
        });

        $(document).on('change', '#pay_offline_method', function () {
            loadSeafoodOfflineMethodFields(this.value);
        });

        updateSeafoodPaymentState();
    </script>
@endpush
