{{-- Pays the extra amount left after an order is edited: cash on delivery, wallet, a gateway, or offline. --}}
@php
    $dueAmount = $order['edit_due_amount'] ?? 0;
    $walletBalance = auth('customer')->user()?->wallet_balance ?? 0;
    $isWalletEnabled = getWebConfig(name: 'wallet_status') == 1;
    $isWalletInsufficient = $walletBalance < $dueAmount;
    $isPhysicalOrder = $order->details()->whereHas('product', fn ($query) => $query->where('product_type', 'physical'))->exists();
    $isDigitalPaymentEnabled = getWebConfig(name: 'digital_payment')['status'] ?? false;
    $isOfflinePaymentEnabled = isset($offlinePaymentMethods) && ($offlinePaymentStatus['status'] ?? false) && count($offlinePaymentMethods) > 0;
@endphp

<div class="sf-modal" id="choosePaymentMethodModal-{{ $order['id'] }}" role="dialog" aria-modal="true" aria-labelledby="due-payment-title-{{ $order['id'] }}" hidden>
    <div class="sf-modal-dialog">
        <div class="sf-modal-head">
            <div style="min-width: 0;">
                <h2 class="sf-modal-title" id="due-payment-title-{{ $order['id'] }}">{{ translate('Choose_Payment_Method') }}</h2>
                <p class="sf-field-hint" style="margin: 4px 0 0;">{{ translate('Due Bill') }}: <strong style="color: #0B2C3F;">@include('web-views.seafood.partials._price', ['amount' => $dueAmount])</strong></p>
            </div>
            <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </div>

        <form class="sf-modal-body sf-due-payment-form" action="{{ route('customer.customer-order-edit-pay-amount') }}" method="post">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order['id'] }}">
            <input type="hidden" name="payment_platform" value="web">
            <input type="hidden" name="external_redirect_link" value="{{ route('web-payment-success') }}">

            <ul class="sf-choice-list" role="radiogroup" aria-label="{{ translate('payment_method') }}">
                @if($isPhysicalOrder && ($cashOnDeliveryStatus ?? false))
                    <li>
                        <label class="sf-choice">
                            <input type="radio" name="payment_method" value="cash_on_delivery" checked>
                            <span class="sf-choice-dot" aria-hidden="true"></span>
                            <span class="sf-choice-body"><span class="sf-choice-title">{{ translate('Cash_on_Delivery') }}</span></span>
                        </label>
                    </li>
                @endif
                @if($isWalletEnabled)
                    <li>
                        <label class="sf-choice">
                            <input type="radio" name="payment_method" value="wallet" @disabled($isWalletInsufficient)>
                            <span class="sf-choice-dot" aria-hidden="true"></span>
                            <span class="sf-choice-body">
                                <span class="sf-choice-title">{{ translate('Pay_via_Wallet') }}</span>
                                <span class="sf-choice-text" @if($isWalletInsufficient) style="color: #E4574F;" @endif>
                                    {{ $isWalletInsufficient ? translate('Insufficient wallet balance') : translate('Remaining wallet balance') }}:
                                    @include('web-views.seafood.partials._price', ['amount' => $walletBalance - $dueAmount])
                                </span>
                            </span>
                        </label>
                    </li>
                @endif
                @if($isDigitalPaymentEnabled)
                    @foreach($paymentGatewayList as $paymentGateway)
                        @php($gatewayAdditionalData = $paymentGateway['additional_data'] != null ? json_decode($paymentGateway['additional_data']) : null)
                        <li>
                            <label class="sf-choice">
                                <input type="radio" name="payment_method" value="{{ $paymentGateway->key_name }}">
                                <span class="sf-choice-dot" aria-hidden="true"></span>
                                <span class="sf-choice-body"><span class="sf-choice-title">{{ $gatewayAdditionalData?->gateway_title ?: str_replace('_', ' ', $paymentGateway->key_name) }}</span></span>
                            </label>
                        </li>
                    @endforeach
                @endif
            </ul>

            <div class="sf-due-change-amount">
                <label class="sf-form-label" for="due-bring-change-{{ $order['id'] }}">{{ translate('Change Amount') }} <span style="font-weight: 500; color: #6E8C99;">({{ translate('optional') }})</span></label>
                <input type="text" inputmode="numeric" class="sf-filter-field" id="due-bring-change-{{ $order['id'] }}" name="bring_change_amount_input" dir="ltr" style="max-width: 260px;">
                <span class="sf-field-hint">{{ translate('insert_amount_of_you_need_deliveryman_to_bring') }}</span>
            </div>

            <button type="submit" class="sf-btn-block" style="margin-top: 20px;">{{ translate('Proceed') }}</button>
        </form>

        @if($isOfflinePaymentEnabled)
            <form class="sf-modal-body" action="{{ route('customer.customer-order-edit-pay-amount') }}" method="post" style="border-top: 1px solid #EDF5F6;">
                @csrf
                <input type="hidden" name="payment_method" value="offline">
                <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                <input type="hidden" name="payment_platform" value="web">
                <label class="sf-form-label" for="pay_offline_method_{{ $order['id'] }}">{{ translate('Pay_offline') }}</label>
                <select class="sf-filter-field sf-due-offline-method" id="pay_offline_method_{{ $order['id'] }}" name="payment_by" required
                        data-edit-due="{{ $dueAmount }}" data-target="#payment_method_field_{{ $order['id'] }}" data-url="{{ route('pay-offline-method-list') }}">
                    <option value="" disabled selected>{{ translate('select_Payment_Method') }}</option>
                    @foreach($offlinePaymentMethods as $offlinePaymentMethod)
                        <option value="{{ $offlinePaymentMethod->id }}">{{ $offlinePaymentMethod->method_name }}</option>
                    @endforeach
                </select>
                <div id="payment_method_field_{{ $order['id'] }}" style="margin-top: 14px;"></div>
            </form>
        @endif
    </div>
</div>

@push('script')
    <script>
        "use strict";

        $(document).on('change', '.sf-due-payment-form input[name="payment_method"]', function () {
            $(this).closest('form').find('.sf-due-change-amount').prop('hidden', this.value !== 'cash_on_delivery');
        });
        $('.sf-due-payment-form input[name="payment_method"]:checked').trigger('change');

        $(document).on('change', '.sf-due-offline-method', function () {
            const select = $(this);
            $.get(select.data('url') + '?method_id=' + encodeURIComponent(this.value) + '&edit_due_amount=' + encodeURIComponent(select.data('edit-due')), function (response) {
                $(select.data('target')).html(response.methodHtml);
            });
        });
    </script>
@endpush
