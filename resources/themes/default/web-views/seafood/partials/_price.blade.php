@php($decimalPointSettings = getWebConfig('decimal_point_settings') ?? 2)

<bdi dir="ltr" style="display: inline-flex; align-items: baseline; gap: 5px;">
    <span style="font-size: {{ $symbolSize ?? '14px' }}; font-weight: 700; color: {{ $symbolColor ?? 'var(--sh-muted-2)' }};">{{ getCurrencySymbol(currencyCode: getCurrencyCode(type: 'web'), type: 'web') }}</span>
    <span class="sf-price-amount">{{ number_format(webCurrencyConverterOnlyDigit(amount: $amount), $decimalPointSettings) }}</span>
</bdi>
