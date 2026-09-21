@extends('layouts.front-end.seafood-account')

@section('title', translate('my_loyalty_point'))

@section('page_title', translate('loyalty_point'))

@section('page_actions')
    @include('web-views.seafood.partials._transaction-filter', [
        'filterRoute' => route('loyalty'),
        'filterTypeOptions' => [
            'order_place' => translate('Order_Transactions'),
            'refund_order' => translate('Refund_Order'),
            'point_to_wallet' => translate('Point_to_wallet'),
        ],
    ])
@endsection

@section('account_content')
    @php
        $canConvertPoints = $walletStatus == 1 && $loyaltyPointStatus == 1;
        $hasEnoughPoints = $loyaltyPointMinimumPoint <= $totalLoyaltyPoint;
        $loyaltyTransactionRows = collect($loyaltyPointList->items())->map(fn ($loyaltyTransaction) => [
            'amount' => $loyaltyTransaction['debit'] != 0 ? $loyaltyTransaction['debit'] : $loyaltyTransaction['credit'],
            'isDebit' => $loyaltyTransaction['debit'] != 0,
            'isMoney' => false,
            'label' => translate(str_replace(' ', '_', $loyaltyTransaction['transaction_type'])),
            'date' => $loyaltyTransaction['created_at'],
        ])->all();
    @endphp

    <div class="sf-balance-grid">
        <section class="sf-balance-card">
            <span class="sf-balance-label">{{ translate('Total_Loyalty_Point') }}</span>
            <strong class="sf-balance-value"><bdi dir="ltr">{{ $totalLoyaltyPoint ?? 0 }}</bdi></strong>
            @if($canConvertPoints)
                @if($hasEnoughPoints)
                    <button type="button" class="sf-btn-sm" data-sf-modal-open="convertToCurrency" style="align-self: flex-start;">{{ translate('convert_to_currency') }}</button>
                @else
                    <button type="button" class="sf-btn-sm sf-insufficient-points" data-message="{{ translate('insufficient_loyalty_point') }}" style="align-self: flex-start;" aria-disabled="true">{{ translate('convert_to_currency') }}</button>
                @endif
            @endif
        </section>

        <section class="sf-panel">
            <h2 class="sf-panel-title">{{ translate('how_to_use') }}</h2>
            <ul class="sf-stack" style="margin: 0; padding-inline-start: 18px;">
                <li class="sf-step-text">{{ translate('convert_your_loyalty_point_to_wallet_money') }}.</li>
                <li class="sf-step-text">{{ translate('minimum') }} <bdi dir="ltr">{{ $loyaltyPointMinimumPoint }}</bdi> {{ translate('points_required_to_convert_into_currency') }}</li>
            </ul>
        </section>
    </div>

    <section class="sf-panel" style="margin-top: 16px;">
        <h2 class="sf-panel-title">{{ translate('Transaction_History') }}</h2>
        @include('web-views.seafood.partials._transaction-list', ['transactionRows' => $loyaltyTransactionRows])
        {!! $loyaltyPointList->appends(request()->only(['filter_by', 'transaction_range', 'types']))->links('web-views.seafood.partials._pagination') !!}
    </section>

    @if($canConvertPoints)
        <div class="sf-modal" id="convertToCurrency" role="dialog" aria-modal="true" aria-labelledby="convert-points-title" hidden>
            <div class="sf-modal-dialog" style="max-width: 480px;">
                <div class="sf-modal-head">
                    <h2 class="sf-modal-title" id="convert-points-title">{{ translate('Convert_point_to_wallet_money') }}</h2>
                    <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <form class="sf-modal-body" action="{{ route('loyalty-exchange-currency') }}" method="post">
                    @csrf
                    <label class="sf-form-label" for="convert-points-input">{{ translate('Enters_Point_Amount') }} <span class="sf-required">*</span></label>
                    <input type="number" class="sf-filter-field" id="convert-points-input" name="point" required
                           min="{{ $loyaltyPointMinimumPoint }}" max="{{ $totalLoyaltyPoint }}" dir="ltr" style="height: 60px; font-size: 22px; text-align: center;">
                    <p class="sf-summary-saved" style="margin: 14px 0;">
                        <bdi dir="ltr">{{ $loyaltyPointExchangeRate }}</bdi> {{ translate('point') }} = {{ loyaltyPointToLocalCurrency(amount: $loyaltyPointExchangeRate, type: 'web') }}
                    </p>
                    <ul class="sf-stack" style="margin: 0 0 18px; padding-inline-start: 18px;">
                        <li class="sf-field-hint" style="margin: 0;">{{ translate('convert_your_loyalty_point_to_wallet_money') }}.</li>
                        <li class="sf-field-hint" style="margin: 0;">{{ translate('minimum') }} <bdi dir="ltr">{{ $loyaltyPointMinimumPoint }}</bdi> {{ translate('points_required_to_convert_into_currency') }}</li>
                    </ul>
                    <button type="submit" class="sf-btn-block">{{ translate('submit') }}</button>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('script')
    <script>
        "use strict";

        $(document).on('click', '.sf-insufficient-points', function () {
            toastr.warning($(this).data('message'));
        });
    </script>
@endpush
