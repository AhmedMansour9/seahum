{{--
    Filter dialog for wallet / loyalty history. The controllers expect transaction_range as "DD/MM/YYYY - DD/MM/YYYY",
    so the two native date inputs are folded into that hidden field on submit.
--}}
@php
    $rangeDates = array_pad(explode(' - ', $transactionRange ?? ''), 2, '');
    $toIsoDate = function (string $date): string {
        $parts = explode('/', trim($date));
        return count($parts) === 3 ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : '';
    };
@endphp

<button type="button" class="sf-btn-sm" data-sf-modal-open="sf-transaction-filter">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M7 12h10M10 18h4"></path></svg>
    {{ translate('filter') }}
    @if($filterCount > 0)
        <span class="sf-page-count" style="height: 22px; padding: 0 8px;"><bdi dir="ltr">{{ $filterCount }}</bdi></span>
    @endif
</button>

<div class="sf-modal" id="sf-transaction-filter" role="dialog" aria-modal="true" aria-labelledby="sf-transaction-filter-title" hidden>
    <div class="sf-modal-dialog" style="max-width: 480px;">
        <div class="sf-modal-head">
            <h2 class="sf-modal-title" id="sf-transaction-filter-title">{{ translate('filter_data') }}</h2>
            <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </div>
        <form class="sf-modal-body sf-transaction-filter-form" action="{{ $filterRoute }}" method="get">
            <span class="sf-form-label">{{ translate('filter_by') }}</span>
            <ul class="sf-segment">
                @foreach(['all' => translate('all'), 'debit' => translate('debit'), 'credit' => translate('credit')] as $filterValue => $filterLabel)
                    <li>
                        <input type="radio" id="sf-filter-by-{{ $filterValue }}" name="filter_by" value="{{ $filterValue }}" @checked(($filterBy ?: 'all') == $filterValue)>
                        <label for="sf-filter-by-{{ $filterValue }}">{{ $filterLabel }}</label>
                    </li>
                @endforeach
            </ul>

            <span class="sf-form-label" style="margin-top: 18px;">{{ translate('date_range') }}</span>
            <div class="sf-form-grid" style="gap: 10px;">
                <label>
                    <span class="sf-field-hint" style="margin: 0 0 6px;">{{ translate('from') }}</span>
                    <input type="date" class="sf-filter-field sf-range-start" value="{{ $toIsoDate($rangeDates[0]) }}">
                </label>
                <label>
                    <span class="sf-field-hint" style="margin: 0 0 6px;">{{ translate('to') }}</span>
                    <input type="date" class="sf-filter-field sf-range-end" value="{{ $toIsoDate($rangeDates[1]) }}">
                </label>
            </div>
            <input type="hidden" name="transaction_range" class="sf-range-value" value="{{ $transactionRange }}">

            <span class="sf-form-label" style="margin-top: 18px;">{{ translate('earn_by') }}</span>
            <div style="display: flex; flex-direction: column;">
                @foreach($filterTypeOptions as $typeValue => $typeLabel)
                    <label class="sf-check" style="justify-content: space-between;">
                        <span class="sf-check-label">{{ $typeLabel }}</span>
                        <input type="checkbox" name="types[]" value="{{ $typeValue }}" @checked(in_array($typeValue, $transactionTypes))>
                        <span class="sf-check-box" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg></span>
                    </label>
                @endforeach
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 22px;">
                <a href="{{ $filterRoute }}" class="sf-btn-outline" style="flex: 1 1 140px; width: auto; min-height: 48px;">{{ translate('clear_filter') }}</a>
                <button type="submit" class="sf-btn-block" style="flex: 1 1 140px; width: auto; min-height: 48px;">{{ translate('filter') }}</button>
            </div>
        </form>
    </div>
</div>

@push('script')
    <script>
        "use strict";

        $(document).on('submit', '.sf-transaction-filter-form', function () {
            const toDisplayDate = (isoDate) => isoDate ? isoDate.split('-').reverse().join('/') : '';
            const start = toDisplayDate($(this).find('.sf-range-start').val());
            const end = toDisplayDate($(this).find('.sf-range-end').val() || $(this).find('.sf-range-start').val());
            const rangeField = $(this).find('.sf-range-value');
            rangeField.val(start ? start + ' - ' + end : '');
            if (!rangeField.val()) {
                rangeField.prop('disabled', true);
            }
        });
    </script>
@endpush
