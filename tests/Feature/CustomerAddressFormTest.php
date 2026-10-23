<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Traits\CommonTrait;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The customer address form no longer asks for a country: the server stores
 * the store's own country, and the zip code is optional unless the admin
 * restricts delivery by zip code.
 */
class CustomerAddressFormTest extends TestCase
{
    private const ADDRESS_VIEWS = [
        'resources/themes/default/web-views/users-profile/account-address.blade.php',
        'resources/themes/default/web-views/users-profile/account-address-edit.blade.php',
    ];

    public function test_default_country_follows_the_system_country_code(): void
    {
        $this->assertSame('Egypt', $this->defaultCountryNameFor(systemCountryCode: 'EG'));
        $this->assertSame('Saudi Arabia', $this->defaultCountryNameFor(systemCountryCode: 'SA'));
    }

    public function test_default_country_falls_back_to_egypt_when_the_code_is_unknown(): void
    {
        $this->assertSame('Egypt', $this->defaultCountryNameFor(systemCountryCode: 'XX'));
    }

    public function test_address_forms_send_no_country_and_do_not_require_a_free_text_zip(): void
    {
        foreach (self::ADDRESS_VIEWS as $viewPath) {
            $viewContents = file_get_contents(base_path($viewPath));

            $this->assertStringNotContainsString('name="country"', $viewContents, "{$viewPath} still asks for a country.");
            $this->assertDoesNotMatchRegularExpression('/<input[^>]*name="zip"[^>]*required/', $viewContents, "{$viewPath} still requires the zip code.");
        }
    }

    public function test_address_actions_store_the_default_country_and_make_zip_optional(): void
    {
        $controller = file_get_contents(base_path('app/Http/Controllers/Web/UserProfileController.php'));

        $this->assertSame(2, substr_count($controller, "'zip' => \$zip_restrict_status ? 'required' : 'nullable|max:10'"));
        $this->assertSame(2, substr_count($controller, "\$request->merge(['country' => \$this->getDefaultCountryName()]);"));
        $this->assertStringNotContainsString("'country' => 'required'", $controller);
    }

    public function test_checkout_address_form_has_no_country_and_checkout_stores_the_default_country(): void
    {
        $checkoutFields = file_get_contents(base_path('resources/themes/default/web-views/seafood/partials/_checkout-address-fields.blade.php'));
        $checkoutController = file_get_contents(base_path('app/Http/Controllers/Customer/SystemController.php'));

        $this->assertStringNotContainsString("translate('country')", $checkoutFields);
        $this->assertStringNotContainsString("'country' =>", $checkoutFields);
        $this->assertStringContainsString("\$shipping['country'] = empty(\$shipping['country']) ? \$this->getDefaultCountryName() : \$shipping['country'];", $checkoutController);
        $this->assertStringContainsString("\$billing['billing_country'] = empty(\$billing['billing_country']) ? \$this->getDefaultCountryName() : \$billing['billing_country'];", $checkoutController);
    }

    public function test_checkout_bills_physical_orders_to_the_shipping_address_without_asking(): void
    {
        $checkoutPage = file_get_contents(base_path('resources/themes/default/web-views/seafood/checkout-shipping.blade.php'));
        $checkoutScripts = file_get_contents(base_path('resources/themes/default/web-views/seafood/partials/_checkout-scripts.blade.php'));

        $this->assertStringNotContainsString('same_as_shipping_address', $checkoutPage);
        $this->assertStringContainsString('@if($billingInputByCustomer && !$physical_product_view)', $checkoutPage);
        $this->assertStringContainsString('const sameAsShipping = hasPhysicalProduct;', $checkoutScripts);
    }

    public function test_checkout_saves_a_new_address_by_default_but_not_a_chosen_saved_one(): void
    {
        $checkoutFields = file_get_contents(base_path('resources/themes/default/web-views/seafood/partials/_checkout-address-fields.blade.php'));
        $checkoutPage = file_get_contents(base_path('resources/themes/default/web-views/seafood/checkout-shipping.blade.php'));

        $this->assertMatchesRegularExpression('/<input type="checkbox" class="sf-address-save"[^>]*\schecked>/', $checkoutFields);
        $this->assertStringContainsString("saveToggle.prop('checked', !address)", $checkoutPage);
    }

    public function test_checkout_payment_does_not_ask_for_a_change_amount(): void
    {
        $paymentPage = file_get_contents(base_path('resources/themes/default/web-views/seafood/checkout-payment.blade.php'));

        $this->assertStringNotContainsString('bring_change_amount', $paymentPage);
    }

    public function test_checkout_terms_are_accepted_by_default(): void
    {
        $orderSummary = file_get_contents(base_path('resources/themes/default/web-views/seafood/partials/_order-summary.blade.php'));

        $this->assertStringContainsString('<input type="checkbox" class="sf-payment-terms" checked>', $orderSummary);
    }

    private function defaultCountryNameFor(string $systemCountryCode): string
    {
        Cache::flush();
        Cache::put(CACHE_BUSINESS_SETTINGS_TABLE, collect([
            new BusinessSetting(['type' => 'country_code', 'value' => $systemCountryCode]),
        ]));

        $countryResolver = new class {
            use CommonTrait;
        };

        return $countryResolver->getDefaultCountryName();
    }
}
