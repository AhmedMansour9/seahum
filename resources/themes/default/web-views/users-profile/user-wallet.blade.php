@extends('layouts.front-end.seafood-account')

@section('title', translate('my_Wallet'))

@section('page_title', translate('wallet'))

@section('page_actions')
    @include('web-views.seafood.partials._transaction-filter', [
        'filterRoute' => route('wallet'),
        'filterTypeOptions' => [
            'order_place' => translate('Order_Transactions'),
            'order_refund' => translate('Order_Refund'),
            'loyalty_point' => translate('Converted_from_Loyalty_Point'),
            'add_fund' => translate('Added_via_Payment_Method'),
            'add_fund_by_admin' => translate('add_fund_by_admin'),
            'earned_by_referral' => translate('earned_by_referral'),
            'due_payment_for_order' => translate('due_payment_for_order'),
            'return_order_amount_by_admin' => translate('Return_order_amount_by_admin'),
        ],
    ])
@endsection

@section('account_content')
    @php
        $walletTransactionLabels = [
            'order_place' => translate('order_place'),
            'loyalty_point' => translate('converted_from_loyalty_point'),
            'add_fund' => translate('added_via_payment_method'),
        ];
        $walletTransactionRows = [];
        foreach ($walletTransactionList as $walletTransaction) {
            if ($walletTransaction['admin_bonus'] > 0) {
                $walletTransactionRows[] = [
                    'amount' => $walletTransaction['admin_bonus'], 'isDebit' => false, 'isMoney' => true,
                    'label' => translate('admin_bonus'), 'date' => $walletTransaction['created_at'],
                ];
            }
            $walletTransactionLabel = $walletTransaction['transaction_type'] == 'add_fund_by_admin'
                ? translate('add_fund_by_admin') . ($walletTransaction['reference'] == 'earned_by_referral' ? ' (' . translate('earned_by_referral') . ')' : '')
                : ($walletTransactionLabels[$walletTransaction['transaction_type']] ?? str_replace('_', ' ', translate($walletTransaction['transaction_type'])));
            $walletTransactionRows[] = [
                'amount' => $walletTransaction['debit'] != 0 ? $walletTransaction['debit'] : $walletTransaction['credit'],
                'isDebit' => $walletTransaction['debit'] != 0, 'isMoney' => true,
                'label' => $walletTransactionLabel, 'date' => $walletTransaction['created_at'],
            ];
        }
    @endphp

    <div class="sf-balance-grid">
        <section class="sf-balance-card">
            <span class="sf-balance-label">{{ translate('wallet') }} {{ translate('amount') }}</span>
            <strong class="sf-balance-value">@include('web-views.seafood.partials._price', ['amount' => $totalWalletBalance ?? 0, 'symbolColor' => '#7FE3DC', 'symbolSize' => '.5em'])</strong>
            @if($addFundsToWalletStatus)
                <p class="sf-balance-hint">{{ translate('If_you_want_to_add_fund_to_your_wallet_then_click_add_fund_button') }}</p>
                <button type="button" class="sf-btn-sm" data-sf-modal-open="addFundToWallet" style="align-self: flex-start;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"></path></svg>
                    {{ translate('add_Fund') }}
                </button>
            @endif
        </section>

        @if($addFundsToWalletStatus && count($addFundBonusList) > 0)
            <div class="sf-bonus-list">
                @foreach($addFundBonusList as $addFundBonus)
                    <article class="sf-panel sf-bonus-card">
                        <h2 class="sf-panel-title" style="margin-bottom: 6px; color: #0E8E88;">{{ $addFundBonus->title }}</h2>
                        <p class="sf-step-text" style="margin: 0;">
                            {{ translate('add_fund_to_wallet') }} @include('web-views.seafood.partials._price', ['amount' => $addFundBonus->min_add_money_amount])
                            {{ translate('and_enjoy') }}
                            @if($addFundBonus->bonus_type == 'percentage')
                                <bdi dir="ltr">{{ $addFundBonus->bonus_amount }}%</bdi>
                            @else
                                @include('web-views.seafood.partials._price', ['amount' => $addFundBonus->bonus_amount])
                            @endif
                            {{ translate('bonus') }}
                        </p>
                        @if($addFundBonus->description)
                            <p class="sf-field-hint">{{ Str::limit($addFundBonus->description, 80) }}</p>
                        @endif
                        <span class="sf-row-meta">{{ translate('valid_till') }} <bdi dir="ltr">{{ date('d M, Y', strtotime($addFundBonus->end_date_time)) }}</bdi></span>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <section class="sf-panel" style="margin-top: 16px;">
        <h2 class="sf-panel-title">{{ translate('Transaction_History') }}</h2>
        @include('web-views.seafood.partials._transaction-list', ['transactionRows' => $walletTransactionRows])
        {!! $walletTransactionList->appends(request()->only(['type', 'filter_by', 'transaction_range', 'types']))->links('web-views.seafood.partials._pagination') !!}
    </section>

    @if($addFundsToWalletStatus)
        <div class="sf-modal" id="addFundToWallet" role="dialog" aria-modal="true" aria-labelledby="add-fund-title" hidden>
            <div class="sf-modal-dialog" style="max-width: 480px;">
                <div class="sf-modal-head">
                    <div style="min-width: 0;">
                        <h2 class="sf-modal-title" id="add-fund-title">{{ translate('add_Fund_to_Wallet') }}</h2>
                        <p class="sf-field-hint" style="margin: 4px 0 0;">{{ translate('add_fund_by_from_secured_digital_payment_gateways') }}</p>
                    </div>
                    <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <form class="sf-modal-body" action="{{ route('customer.add-fund-request') }}" method="post">
                    @csrf
                    <input type="hidden" name="payment_platform" value="web">
                    <input type="hidden" name="external_redirect_link" value="{{ request()->url() }}">
                    <label class="sf-form-label" for="add-fund-amount-input">{{ translate('amount') }} <span class="sf-required">*</span></label>
                    <input type="number" class="sf-filter-field" id="add-fund-amount-input" name="amount" required step="any" min="0" dir="ltr"
                           style="height: 60px; font-size: 22px; text-align: center;" placeholder="{{ translate('ex') }}: 500">

                    @if(count($paymentGatewayList) > 0)
                        <span class="sf-form-label" style="margin-top: 18px;">{{ translate('payment_Methods') }}</span>
                        <ul class="sf-choice-list" role="radiogroup">
                            @foreach($paymentGatewayList as $paymentGateway)
                                @php($gatewayAdditionalData = !empty($paymentGateway->additional_data) ? json_decode($paymentGateway->additional_data) : null)
                                <li>
                                    <label class="sf-choice">
                                        <input type="radio" name="payment_method" value="{{ $paymentGateway->key_name }}" required>
                                        <span class="sf-choice-dot" aria-hidden="true"></span>
                                        <img class="sf-choice-media" alt="" src="{{ getValidImage(path: 'storage/app/public/payment_modules/gateway_image/' . ($gatewayAdditionalData->gateway_image ?? ''), type: 'banner') }}">
                                        <span class="sf-choice-body"><span class="sf-choice-title">{{ $gatewayAdditionalData->gateway_title ?? ucwords(str_replace('_', ' ', $paymentGateway->key_name)) }}</span></span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                        <button type="submit" class="sf-btn-block">{{ translate('add_Fund') }}</button>
                    @else
                        <p class="sf-empty-text" style="margin-top: 18px;">{{ translate('no_Payment_Methods_Gateway_found') }}</p>
                    @endif
                </form>
            </div>
        </div>
    @endif
@endsection
