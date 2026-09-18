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
