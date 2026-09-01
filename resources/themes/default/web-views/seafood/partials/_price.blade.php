@php($decimalPointSettings = getWebConfig('decimal_point_settings') ?? 2)

<bdi dir="ltr" style="display: inline-flex; align-items: baseline; gap: 5px;">
    <span style="font-size: {{ $symbolSize ?? '14px' }}; font-weight: 700; color: {{ $symbolColor ?? '#7B98A4' }};">{{ getCurrencySymbol(currencyCode: getCurrencyCode(type: 'web'), type: 'web') }}</span>
    <span>{{ number_format(webCurrencyConverterOnlyDigit(amount: $amount), $decimalPointSettings) }}</span>
</bdi>
