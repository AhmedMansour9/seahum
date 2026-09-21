{{-- $transactionRows: list of ['amount' => number, 'isDebit' => bool, 'label' => string, 'date' => Carbon|string, 'isMoney' => bool]. --}}
@if(count($transactionRows) > 0)
    <ul class="sf-transaction-list">
        @foreach($transactionRows as $transactionRow)
            <li class="sf-transaction {{ $transactionRow['isDebit'] ? 'is-debit' : 'is-credit' }}">
                <span class="sf-transaction-icon" aria-hidden="true">
                    @if($transactionRow['isDebit'])
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M6 13l6 6 6-6"></path></svg>
                    @else
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M6 11l6-6 6 6"></path></svg>
                    @endif
                </span>
                <span class="sf-transaction-body">
                    <strong class="sf-transaction-amount">
                        <bdi dir="ltr">{{ $transactionRow['isDebit'] ? '−' : '+' }}</bdi>
                        @if($transactionRow['isMoney'])
                            @include('web-views.seafood.partials._price', ['amount' => $transactionRow['amount'], 'symbolColor' => 'currentColor'])
                        @else
                            <bdi dir="ltr">{{ $transactionRow['amount'] }}</bdi> {{ translate('points') }}
                        @endif
                    </strong>
                    <span class="sf-row-meta" style="text-transform: capitalize;">{{ $transactionRow['label'] }}</span>
                </span>
                <span class="sf-transaction-side">
                    <span class="sf-row-meta"><bdi dir="ltr">{{ \Carbon\Carbon::parse($transactionRow['date'])->format('d M, Y h:i A') }}</bdi></span>
                    <span class="sf-badge {{ $transactionRow['isDebit'] ? 'sf-badge-danger' : 'sf-badge-success' }}">{{ $transactionRow['isDebit'] ? translate('debit') : translate('credit') }}</span>
                </span>
            </li>
        @endforeach
    </ul>
@else
    @include('web-views.seafood.partials._empty-state', [
        'emptyStateIcon' => '<path d="M4 7h16v12H4z"></path><path d="M8 7V5h8v2M8 12h8M8 15.5h5"></path>',
        'emptyStateTitle' => translate('no_transactions_yet'),
    ])
@endif
