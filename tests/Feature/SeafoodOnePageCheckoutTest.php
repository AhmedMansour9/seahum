<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\WebController;
use App\Models\BusinessSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The seafood checkout is one page: the delivery address and the payment method are
 * chosen together and a single "place order" saves the address, then submits the payment.
 */
class SeafoodOnePageCheckoutTest extends TestCase
{
    private const SEAFOOD_VIEWS = 'resources/themes/default/web-views/seafood/';

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('addon_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key_name');
            $table->text('live_values')->nullable();
            $table->text('test_values')->nullable();
            $table->string('settings_type');
            $table->string('mode')->nullable();
            $table->boolean('is_active')->default(0);
            $table->text('additional_data')->nullable();
            $table->timestamps();
        });
        Schema::create('offline_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_name');
            $table->text('method_fields')->nullable();
            $table->text('method_informations')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('symbol')->nullable();
            $table->string('code')->nullable();
            $table->decimal('exchange_rate')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function test_checkout_templates_compile_to_valid_php(): void
    {
        foreach (['checkout-shipping', 'checkout-payment', 'partials/_checkout-payment-methods', 'partials/_checkout-steps', 'partials/_order-summary'] as $template) {
            $templatePath = base_path(self::SEAFOOD_VIEWS.$template.'.blade.php');
            $compiledFile = tempnam(sys_get_temp_dir(), 'blade').'.php';
            file_put_contents($compiledFile, Blade::compileString(file_get_contents($templatePath)));

            exec('"'.PHP_BINARY.'" -l '.escapeshellarg($compiledFile).' 2>&1', $output, $exitCode);
            unlink($compiledFile);

            $this->assertSame(0, $exitCode, $template.' does not compile: '.implode("\n", $output));
        }
    }

    public function test_checkout_page_offers_payment_and_places_the_order_itself(): void
    {
        $checkoutPage = $this->viewSource('checkout-shipping');

        $this->assertStringContainsString("@include('web-views.seafood.partials._checkout-payment-methods')", $checkoutPage);
        $this->assertStringContainsString("['summaryStep' => 'checkout']", $checkoutPage);
        $this->assertStringContainsString("@include('web-views.seafood.partials._checkout-payment-methods')", $this->viewSource('checkout-payment'));
    }

    public function test_checkout_shows_two_steps(): void
    {
        $steps = $this->viewSource('partials/_checkout-steps');

        $this->assertStringContainsString("1 => ['label' => translate('cart')", $steps);
        $this->assertStringContainsString("2 => ['label' => translate('checkout')", $steps);
        $this->assertStringNotContainsString('3 =>', $steps);
    }

    public function test_place_order_saves_the_address_before_submitting_the_payment(): void
    {
        $scripts = $this->viewSource('partials/_checkout-scripts');

        $this->assertStringContainsString('saveSeafoodCheckoutAddress().done(submitSeafoodSelectedPayment);', $scripts);
        $this->assertStringNotContainsString("seafoodCheckoutUrl('checkout-payment-url')", $scripts, 'The address step must not redirect to a separate payment page.');
    }

    public function test_delivery_address_asks_only_for_name_phone_and_a_map_location(): void
    {
        $addressFields = $this->viewSource('partials/_checkout-address-fields');

        $this->assertStringContainsString('<input type="hidden" name="{{ $fieldNames[\'type\'] }}" id="{{ $fieldIds[\'type\'] }}" value="home">', $addressFields);
        $this->assertStringContainsString('<input type="hidden" name="{{ $fieldNames[\'zip\'] }}" id="{{ $fieldIds[\'zip\'] }}" value="">', $addressFields, 'The zip code is only asked for when delivery is restricted by zip.');
        $this->assertStringContainsString('<input type="hidden" name="{{ $fieldNames[\'city\'] }}" id="{{ $fieldIds[\'city\'] }}" value="">', $addressFields, 'The city comes from the map, not a field.');
        $this->assertStringContainsString('$isLocationPicker = !$isBilling && getWebConfig(\'map_api_status\') == 1;', $addressFields);
        $this->assertStringContainsString('data-invalid-target="#sf-location-picker"', $addressFields, 'The map-picked address is required.');
        $this->assertSame(1, substr_count($this->viewSource('checkout-shipping'), '_checkout-guest-account'), 'Only digital-only carts still offer account creation; delivery orders do not ask for an email.');
    }

    public function test_the_address_can_be_typed_when_the_map_is_unavailable(): void
    {
        $checkoutPage = $this->viewSource('checkout-shipping');

        $this->assertStringContainsString('window.gm_authFailure = switchSeafoodToTypedAddress;', $checkoutPage);
        $this->assertStringContainsString('onerror="switchSeafoodToTypedAddress()"', $checkoutPage);
        $this->assertStringContainsString("canvas.querySelector('.gm-err-container, .dismissButton')", $checkoutPage);
        $this->assertStringContainsString('class="sf-filter-field sf-typed-address"', $this->viewSource('partials/_checkout-address-fields'));
    }

    public function test_map_opens_on_the_customers_current_location(): void
    {
        $checkoutPage = $this->viewSource('checkout-shipping');

        $this->assertStringContainsString('navigator.geolocation.getCurrentPosition(', $checkoutPage);
        $this->assertStringContainsString('locateSeafoodCustomer(false);', $checkoutPage);
        $this->assertStringContainsString("\$(document).on('click', '.sf-use-my-location', () => locateSeafoodCustomer(true));", $checkoutPage, 'The button works before the map loads.');
        $this->assertStringContainsString('new google.maps.Marker({position: seafoodDefaultLocation, map: map, draggable: true});', $checkoutPage, 'The pin must be visible without a Map ID.');
        $this->assertStringContainsString("\$('.sf-location-blocked').prop('hidden', !isBlocked);", $checkoutPage, 'A blocked location explains how to allow it.');
        $this->assertStringNotContainsString('v=3.56', $checkoutPage, 'Pinned Maps versions are retired.');
    }

    public function test_guests_can_order_without_an_email_or_a_city(): void
    {
        $checkoutController = file_get_contents(base_path('app/Http/Controllers/Customer/SystemController.php'));
        $orderManager = file_get_contents(base_path('app/Utils/OrderManager.php'));

        $this->assertStringNotContainsString("translate('Shipping email is required.')", $checkoutController);
        $this->assertStringContainsString("\$shipping['address'] == null || (\$zipRestrictStatus && empty(\$shipping['zip']))", $checkoutController, 'The city is not required.');
        $this->assertStringNotContainsString("(\$isGuestCustomer && \$shipping['email'] == null)", $checkoutController);
        $this->assertStringContainsString("'email' => auth('customer')->check() ? null : (\$shipping['email'] ?? null),", $checkoutController);
        $this->assertStringContainsString('if (! empty($email)) {', $orderManager, 'No customer mail is queued without an email.');
    }

    public function test_checkout_requires_a_zip_code_only_when_delivery_is_restricted_by_zip(): void
    {
        $checkoutController = file_get_contents(base_path('app/Http/Controllers/Customer/SystemController.php'));

        $this->assertStringContainsString("|| (\$zipRestrictStatus && empty(\$shipping['zip']))", $checkoutController);
        $this->assertStringContainsString("|| (\$zipRestrictStatus && empty(\$billing['billing_zip']))", $checkoutController);
    }

    public function test_cart_and_checkout_preselect_the_first_shipping_method(): void
    {
        $webController = file_get_contents(base_path('app/Http/Controllers/Web/WebController.php'));
        $cartManager = file_get_contents(base_path('app/Utils/CartManager.php'));

        $this->assertSame(2, substr_count($webController, 'CartManager::assignFirstShippingMethodWhereMissing();'));
        $this->assertStringContainsString('public static function assignFirstShippingMethodWhereMissing(): void', $cartManager);
        $this->assertStringContainsString("if (\$groupShippingType != 'order_wise') {", $cartManager, 'Only order-wise groups have a method to pick.');
    }

    public function test_mobile_bar_offers_only_the_cart_and_stays_off_cart_and_checkout(): void
    {
        $this->assertStringNotContainsString('wa.me', $this->viewSource('partials/_mobile-bar'));

        foreach (['cart', 'checkout-shipping', 'checkout-payment'] as $template) {
            $this->assertStringNotContainsString('_mobile-bar', $this->viewSource($template), "{$template} would stack the mobile bar under the order bar.");
        }
    }

    public function test_home_lists_every_category_that_has_products(): void
    {
        $homePage = $this->viewSource('home');

        $this->assertStringContainsString('@php($shopCategories = $categories->filter(fn ($category) => ($category->product_count ?? $category->product?->count() ?? 0) > 0))', $homePage);
        $this->assertStringNotContainsString('$categories->take(6)', $homePage, 'A fixed count hides categories such as caviar.');
    }

    public function test_product_cards_share_one_image_height(): void
    {
        $productCard = $this->viewSource('partials/_product-card');

        $this->assertStringContainsString('class="sf-product-media" style="width: 100%; aspect-ratio: 4 / 3; height: auto; object-fit: contain;', $productCard, 'Every card image has the same box and shows the whole photo.');
        $this->assertStringNotContainsString('mediaRatio', $productCard);
        $this->assertStringContainsString('-webkit-line-clamp: 2;', $productCard, 'Long names must not stretch one card.');
    }

    public function test_home_hero_is_hidden_on_phones(): void
    {
        $this->assertStringContainsString('<section id="home" class="sf-hero"', $this->viewSource('home'));
        $this->assertStringContainsString('.sf-hero { display: none !important; }', $this->viewSource('partials/_styles'));
    }

    public function test_cash_on_delivery_is_offered_when_every_vendor_group_ships_a_physical_item(): void
    {
        $this->setBusinessSettings(['cash_on_delivery' => ['status' => 1]]);

        $paymentOptions = $this->checkoutPaymentOptions(isPayOnDeliveryAllowed: true);

        $this->assertTrue($paymentOptions['cashOnDeliveryBtnShow']);
        $this->assertTrue($paymentOptions['activeMinimumMethods']);
        $this->assertSame(250, $paymentOptions['amount']);
    }

    public function test_no_payment_method_is_active_when_pay_on_delivery_is_the_only_one_and_not_allowed(): void
    {
        $this->setBusinessSettings(['cash_on_delivery' => ['status' => 1]]);

        $paymentOptions = $this->checkoutPaymentOptions(isPayOnDeliveryAllowed: false);

        $this->assertFalse($paymentOptions['activeMinimumMethods']);
    }

    public function test_offline_payment_keeps_checkout_open_without_pay_on_delivery(): void
    {
        $this->setBusinessSettings(['cash_on_delivery' => ['status' => 0], 'offline_payment' => ['status' => 1]]);
        DB::table('offline_payment_methods')->insert(['method_name' => 'Vodafone Cash', 'status' => 1]);

        $paymentOptions = $this->checkoutPaymentOptions(isPayOnDeliveryAllowed: false);

        $this->assertTrue($paymentOptions['activeMinimumMethods']);
        $this->assertCount(1, $paymentOptions['offline_payment_methods']);
    }

    /**
     * @return array<string, mixed>
     */
    private function checkoutPaymentOptions(bool $isPayOnDeliveryAllowed): array
    {
        $paymentOptionsMethod = new ReflectionMethod(WebController::class, 'getCheckoutPaymentOptions');

        return $paymentOptionsMethod->invoke(app(WebController::class), $isPayOnDeliveryAllowed, 250, 0);
    }

    /**
     * @param  array<string, array<string, int>>  $settings
     */
    private function setBusinessSettings(array $settings): void
    {
        Cache::flush();
        Cache::put(CACHE_BUSINESS_SETTINGS_TABLE, collect($settings)->map(
            fn (array $value, string $type) => new BusinessSetting(['type' => $type, 'value' => json_encode($value)])
        )->values());
    }

    private function viewSource(string $template): string
    {
        return file_get_contents(base_path(self::SEAFOOD_VIEWS.$template.'.blade.php'));
    }
}
