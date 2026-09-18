<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * The seafood storefront is plain Blade with no compiled asset step, so an
 * unbalanced directive or a partial that moved only surfaces as a 500 in the
 * browser. These checks run without the database the storefront needs.
 */
class SeafoodCategoryViewTest extends TestCase
{
    private const THEME_PATH = 'resources/themes/default';

    public function test_category_listing_view_names_resolve_to_existing_templates(): void
    {
        $viewFileNames = include base_path(self::THEME_PATH.'/file_names.php');

        foreach (['all_categories_page', 'category_products_view_page', 'category_products_ajax_partials', 'products_details', 'product_reviews_partials'] as $viewKey) {
            $this->assertArrayHasKey($viewKey, $viewFileNames, "file_names.php is missing the '{$viewKey}' entry.");
            $this->assertFileExists($this->templatePath($viewFileNames[$viewKey]));
        }
    }

    public function test_seafood_templates_compile_to_valid_php(): void
    {
        foreach ($this->seafoodTemplates() as $templatePath) {
            $compiledFile = tempnam(sys_get_temp_dir(), 'blade').'.php';
            file_put_contents($compiledFile, Blade::compileString(file_get_contents($templatePath)));

            exec('php -l '.escapeshellarg($compiledFile).' 2>&1', $output, $exitCode);
            unlink($compiledFile);

            $this->assertSame(0, $exitCode, $templatePath.' does not compile: '.implode("\n", $output));
        }
    }

    public function test_seafood_chrome_partials_are_shared_by_the_home_and_category_pages(): void
    {
        $sharedPartials = ['_styles', '_topbar', '_header', '_footer', '_mobile-bar', '_scripts'];

        foreach (['home', 'category', 'categories', 'product'] as $page) {
            $pageContents = file_get_contents($this->templatePath('web-views.seafood.'.$page));

            foreach ($sharedPartials as $partial) {
                /* The product page carries its own sticky buy bar, so a second fixed bottom bar would overlap it. */
                if ($page === 'product' && $partial === '_mobile-bar') {
                    $this->assertStringNotContainsString('web-views.seafood.partials._mobile-bar', $pageContents);
                    $this->assertStringContainsString('sf-sticky-buy', $pageContents);

                    continue;
                }

                $this->assertStringContainsString(
                    "web-views.seafood.partials.{$partial}",
                    $pageContents,
                    "The seafood {$page} page no longer includes the shared '{$partial}' partial."
                );
            }
        }
    }

    public function test_product_page_keeps_the_cart_javascript_hooks(): void
    {
        $productPage = file_get_contents($this->templatePath('web-views.seafood.product'))
            .file_get_contents($this->templatePath('web-views.seafood.partials._product-buy-box'));

        foreach ([
            'add-to-cart-details-form',
            'product-details-cart-qty',
            'product-generated-variation-code',
            'product-exist-in-cart-list',
            'product-add-to-cart-button',
            'product-buy-now-button',
            'product-restock-request-button',
            'product-add-and-buy-section',
            'discounted-unit-price',
            'product-details-chosen-price-amount',
            'route-cart-variant-price',
            'route-cart-updateQuantity-guest',
            'route-product-restock-request',
        ] as $hook) {
            $this->assertStringContainsString($hook, $productPage, "The product page lost the '{$hook}' cart hook.");
        }
    }

    public function test_product_gallery_leads_with_the_main_image_and_always_shows_the_thumbnail_strip(): void
    {
        $gallery = file_get_contents($this->templatePath('web-views.seafood.partials._product-gallery'));

        $this->assertStringContainsString("collect([['url' => getStorageImages(path: \$product->thumbnail_full_url", $gallery);
        $this->assertStringContainsString("src=\"{{ \$gallerySlides[0]['url'] }}\"", $gallery);
        $this->assertStringNotContainsString('@if(count($galleryImages)', $gallery);
    }

    public function test_product_buy_box_price_uses_the_home_card_price_markup_and_keeps_it_on_variant_change(): void
    {
        $buyBox = file_get_contents($this->templatePath('web-views.seafood.partials._product-buy-box'));
        $productScripts = file_get_contents($this->templatePath('web-views.seafood.partials._product-scripts'));
        $cartController = file_get_contents(app_path('Http/Controllers/Web/CartController.php'));

        $productPage = file_get_contents($this->templatePath('web-views.seafood.product'));

        $this->assertSame(3, substr_count($buyBox, "@include('web-views.seafood.partials._price'"));
        $this->assertStringContainsString("@include('web-views.seafood.partials._price', ['amount' => \$initialProductConfig['total_quantity_price']", $productPage);

        foreach ([$buyBox, $productPage] as $template) {
            $this->assertStringNotContainsString('webCurrencyConverter(', $template, 'Prices must use the localized _price partial, not the unlocalized currency string.');
        }
        $this->assertStringContainsString('class="sf-price-amount"', file_get_contents($this->templatePath('web-views.seafood.partials._price')));

        $productCard = file_get_contents($this->templatePath('web-views.seafood.partials._product-card'));
        $this->assertMatchesRegularExpression('/class="sf-product-price" style="[^"]*color: #0E8E88;/', $productCard);
        $this->assertSame(2, preg_match_all('/class="(?:discounted-unit-price|product-details-chosen-price-amount)" style="[^"]*color: #0E8E88;/', $buyBox));
        $this->assertMatchesRegularExpression('/class="product-details-chosen-price-amount" style="[^"]*color: #0E8E88;/', $productPage);

        foreach (['price_digits', 'discounted_unit_price_digits', 'total_unit_price_digits'] as $responseKey) {
            $this->assertStringContainsString("response?.{$responseKey}", $productScripts);
            $this->assertStringContainsString("'{$responseKey}' =>", $cartController);
        }
    }

    public function test_product_page_omits_the_share_links_reliability_cards_and_more_from_seller_section(): void
    {
        $productPage = file_get_contents($this->templatePath('web-views.seafood.product'))
            .file_get_contents($this->templatePath('web-views.seafood.partials._product-buy-box'));

        foreach (['sf-social-share', 'company_reliability', 'more_from_this_seller'] as $removedMarker) {
            $this->assertStringNotContainsString($removedMarker, $productPage);
        }
    }

    public function test_category_filter_sheet_locks_the_page_scroll_while_it_is_open(): void
    {
        $categoryPage = file_get_contents($this->templatePath('web-views.seafood.category'));

        $this->assertStringContainsString('body.sf-filter-sheet-open', $categoryPage);
        $this->assertStringContainsString('overscroll-behavior: contain', $categoryPage);

        foreach (['lockSeafoodPageScroll();', 'unlockSeafoodPageScroll();'] as $scrollCall) {
            $this->assertStringContainsString($scrollCall, $categoryPage);
        }
    }

    public function test_shared_styles_keep_the_header_brand_and_card_titles_readable_when_space_runs_out(): void
    {
        $sharedStyles = file_get_contents($this->templatePath('web-views.seafood.partials._styles'));

        foreach ([
            '.sf-header-tools { flex-shrink: 1 !important; min-width: 0; }',
            '.sf-header-tools > *:not(.sf-header-search) { flex-shrink: 0; }',
            '.sf-header-search { flex: 0 1 auto; min-width: 0; }',
            '.sf-header-search button { flex-shrink: 0; }',
            '.sf-header-tagline { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }',
            '.sf-product-title { overflow-wrap: anywhere; }',
        ] as $rule) {
            $this->assertStringContainsString($rule, $sharedStyles);
        }
    }

    public function test_touch_target_sizing_covers_the_whole_touch_layout_range(): void
    {
        $sharedStyles = file_get_contents($this->templatePath('web-views.seafood.partials._styles'));
        $touchBlock = $this->mediaBlock($sharedStyles, 'max-width: 979px');

        foreach ([
            '.sf-footer-links a { display: flex; align-items: center; min-height: 44px; }',
            '.sf-social { width: 44px !important; height: 44px !important; }',
            '.sf-topbar-whatsapp { min-height: 44px; }',
        ] as $rule) {
            $this->assertStringContainsString($rule, $touchBlock, 'Tap-target rules must cover tablet widths, not only phones.');
        }

        $categoryPage = file_get_contents($this->templatePath('web-views.seafood.category'));
        $categoryTouchBlock = $this->mediaBlock($categoryPage, 'max-width: 979px');

        $this->assertStringContainsString('.sf-search-submit { width: 44px !important; height: 44px !important;', $touchBlock);
        $this->assertStringContainsString('.sf-category-link--sub { min-height: 44px; }', $categoryTouchBlock);
    }

    private function mediaBlock(string $stylesheet, string $condition): string
    {
        $start = strpos($stylesheet, '@media ('.$condition.')');
        $this->assertNotFalse($start, "The '{$condition}' media block is missing.");

        $end = strpos($stylesheet, '@media', $start + 1) ?: strlen($stylesheet);

        return substr($stylesheet, $start, $end - $start);
    }

    /**
     * @return array<int, string>
     */
    private function seafoodTemplates(): array
    {
        return array_merge(
            glob(base_path(self::THEME_PATH.'/web-views/seafood/*.blade.php')),
            glob(base_path(self::THEME_PATH.'/web-views/seafood/partials/*.blade.php')),
            [base_path(self::THEME_PATH.'/layouts/front-end/seafood.blade.php')]
        );
    }

    private function templatePath(string $viewName): string
    {
        return base_path(self::THEME_PATH.'/'.str_replace('.', '/', $viewName).'.blade.php');
    }
}
