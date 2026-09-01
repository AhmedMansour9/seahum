<?php

namespace Tests\Unit;

use Tests\TestCase;

class LocalizedCurrencySymbolTest extends TestCase
{
    public function test_arabic_locale_returns_arabic_symbol(): void
    {
        $this->app->setLocale('eg');

        $this->assertSame('جنيه', getLocalizedCurrencySymbol(currencyCode: 'EGP', defaultSymbol: 'جنيه'));
    }

    public function test_non_arabic_locale_returns_default_mapped_symbol(): void
    {
        $this->app->setLocale('en');

        $this->assertSame('EGP', getLocalizedCurrencySymbol(currencyCode: 'EGP', defaultSymbol: 'جنيه'));
    }

    public function test_unmapped_currency_falls_back_to_stored_symbol(): void
    {
        $this->app->setLocale('en');

        $this->assertSame('$', getLocalizedCurrencySymbol(currencyCode: 'USD', defaultSymbol: '$'));
    }
}
