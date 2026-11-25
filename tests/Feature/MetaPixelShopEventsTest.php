<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * The storefront sends the Meta Pixel shopping events next to the admin-managed base pixel.
 * These checks run without the database the storefront needs.
 */
class MetaPixelShopEventsTest extends TestCase
{
    private const THEME_PATH = 'resources/themes/default/web-views/';

    public function test_events_only_fire_when_the_pixel_is_installed(): void
    {
        $eventPartial = $this->template('seafood/partials/_meta-pixel-event');
        $sharedScripts = $this->template('seafood/partials/_scripts');

        $this->assertStringContainsString("if (typeof window.fbq === 'function') {", $eventPartial);
        $this->assertStringContainsString("if (typeof window.fbq !== 'function' || !productId) {", $sharedScripts);
    }

    public function test_each_shopping_step_sends_its_event(): void
    {
        $this->assertStringContainsString("'pixelEvent' => 'ViewContent'", $this->template('seafood/product'));
        $this->assertStringContainsString("'pixelEvent' => 'InitiateCheckout'", $this->template('seafood/checkout-shipping'));
        $this->assertStringContainsString("trackSeafoodAddToCart(button.data('product-id'), button.data('quantity'));", $this->template('seafood/partials/_scripts'));
        $this->assertStringContainsString('trackSeafoodAddToCart(detailsForm.find(', $this->template('seafood/partials/_product-scripts'));

        /* Every page that confirms a placed order reports the purchase once, with the order ids as the event id. */
        foreach (['seafood/checkout-complete', 'seafood/home', 'users-profile/account-orders'] as $page) {
            $template = $this->template($page);
            $this->assertStringContainsString('MetaPixelService::class)->getPurchaseEvent(', $template, "{$page} does not report the purchase.");
            $this->assertStringContainsString("'pixelEventId' => \$pixelPurchase['event_id']", $template);
        }
    }

    public function test_purchase_reports_only_recent_orders(): void
    {
        $service = file_get_contents(app_path('Services/MetaPixelService.php'));

        $this->assertStringContainsString("->where('created_at', '>=', now()->subHours(self::PURCHASE_REPORT_WINDOW_HOURS))", $service);
        $this->assertStringContainsString("array_filter((array) \$orderIds, 'is_numeric')", $service);
    }

    public function test_changed_templates_compile_to_valid_php(): void
    {
        foreach (['seafood/partials/_meta-pixel-event', 'users-profile/account-orders', 'seafood/checkout-complete', 'seafood/checkout-shipping', 'seafood/product', 'seafood/home'] as $page) {
            $compiledFile = tempnam(sys_get_temp_dir(), 'blade').'.php';
            file_put_contents($compiledFile, Blade::compileString($this->template($page)));
            exec('"'.PHP_BINARY.'" -l '.escapeshellarg($compiledFile).' 2>&1', $output, $exitCode);
            unlink($compiledFile);

            $this->assertSame(0, $exitCode, "{$page} does not compile: ".implode("\n", $output));
        }
    }

    private function template(string $path): string
    {
        return file_get_contents(base_path(self::THEME_PATH.$path.'.blade.php'));
    }
}
