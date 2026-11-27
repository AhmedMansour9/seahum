<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Search engine basics for the storefront: a real sitemap, a robots file pointing at it,
 * keyword titles and descriptions, one canonical address per page and product structured data.
 */
class StorefrontSeoTest extends TestCase
{
    private const THEME_PATH = 'resources/themes/default/';

    public function test_robots_file_points_search_engines_to_the_live_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Sitemap: https://shop.sea-hum.store/sitemap.xml', $robots);
        $this->assertStringNotContainsString('.test/', $robots, 'The sitemap must not point at a development domain.');
        foreach (['Disallow: /checkout', 'Disallow: /shop-cart', 'Disallow: /customer/'] as $rule) {
            $this->assertStringContainsString($rule, $robots);
        }
    }

    public function test_sitemap_is_built_from_the_database_on_the_main_domain(): void
    {
        $routes = file_get_contents(base_path('routes/web/routes.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Web/SitemapController.php'));

        $this->assertFileDoesNotExist(public_path('sitemap.xml'), 'A static file would be served instead of the generated sitemap.');
        $this->assertStringContainsString("Route::get('sitemap.xml', [SitemapController::class, 'index'])", $routes);
        foreach (["'category-products'", "'product'", "'business-page.view'", 'Product::active()'] as $source) {
            $this->assertStringContainsString($source, $controller);
        }
        $this->assertStringContainsString("rtrim(config('app.url'), '/').route(\$routeName, \$parameters, false)", $controller);
    }

    public function test_home_and_category_pages_get_keyword_titles_and_descriptions(): void
    {
        $migration = file_get_contents(database_path('migrations/2026_11_27_232746_seed_storefront_seo_meta_defaults.php'));
        $listController = file_get_contents(app_path('Http/Controllers/Web/ProductListController.php'));

        $this->assertStringContainsString("'page_name' => 'default'", $migration);
        $this->assertMatchesRegularExpression('/\'meta_title\' => \'Sea Hum \| سمك طازج وسالمون وكافيار وسوشي/u', $migration);
        $this->assertStringContainsString("->where('page_name', \$page['page_name'])->exists()", $migration, 'An SEO text edited in admin must never be overwritten.');

        $this->assertStringContainsString('metaData: $category?->seo ?? $this->getCategoryFallbackMeta(category: $category)', $listController);
        foreach (['en', 'eg'] as $locale) {
            $messages = include base_path("resources/lang/{$locale}/new-messages.php");
            foreach (['seo_category_meta_title', 'seo_category_meta_description'] as $key) {
                $this->assertArrayHasKey($key, $messages);
                $this->assertStringContainsString(':category', $messages[$key]);
            }
        }

        foreach (['home', 'categories', 'category', 'products'] as $page) {
            $this->assertStringContainsString("@section('title', (\$robotsMetaContentData?->meta_title ?? \$robotsMetaContentData?->title ?? null) ?:", $this->template("web-views/seafood/{$page}"));
        }
    }

    public function test_every_page_has_one_title_and_a_canonical_address(): void
    {
        $layout = $this->template('layouts/front-end/seafood');
        $metaPartial = $this->template('web-views/partials/_robotsMetaContentData');

        $this->assertStringContainsString('<link rel="canonical" href="{{ $seoCanonicalUrl }}">', $layout);
        $this->assertStringContainsString("rtrim(config('app.url'), '/')", $layout);
        $this->assertSame(2, substr_count($metaPartial, "@unless(View::hasSection('title'))<title>"), 'The layout already prints the page title.');
        $this->assertStringContainsString('<meta property="og:url" content="{{ $seoCanonicalUrl ?? config(\'app.url\') }}">', $metaPartial);
    }

    public function test_product_and_store_pages_carry_structured_data(): void
    {
        $productData = $this->template('web-views/seafood/partials/_product-structured-data');
        $storeData = $this->template('web-views/seafood/partials/_store-structured-data');

        foreach (["'@type' => 'Product'", "'@type' => 'Offer'", "'priceCurrency'", "'availability'", "'@type' => 'AggregateRating'"] as $field) {
            $this->assertStringContainsString($field, $productData);
        }
        foreach ([$productData, $storeData] as $template) {
            $this->assertStringContainsString('JSON_HEX_TAG', $template, 'Product text must not be able to close the script tag.');
        }
        $this->assertStringContainsString("'@type' => 'OnlineStore'", $storeData);
        $this->assertStringContainsString("'?name={search_term_string}'", $storeData);

        $this->assertStringContainsString("@include('web-views.seafood.partials._product-structured-data'", $this->template('web-views/seafood/product'));
        $this->assertStringContainsString("@include('web-views.seafood.partials._store-structured-data')", $this->template('web-views/seafood/home'));
    }

    public function test_changed_templates_compile_to_valid_php(): void
    {
        foreach ([
            'web-views/seafood/partials/_product-structured-data',
            'web-views/seafood/partials/_store-structured-data',
            'web-views/partials/_robotsMetaContentData',
            'layouts/front-end/seafood',
        ] as $template) {
            $compiledFile = tempnam(sys_get_temp_dir(), 'blade').'.php';
            file_put_contents($compiledFile, Blade::compileString($this->template($template)));
            exec('"'.PHP_BINARY.'" -l '.escapeshellarg($compiledFile).' 2>&1', $output, $exitCode);
            unlink($compiledFile);

            $this->assertSame(0, $exitCode, "{$template} does not compile: ".implode("\n", $output));
        }
    }

    private function template(string $path): string
    {
        return file_get_contents(base_path(self::THEME_PATH.$path.'.blade.php'));
    }
}
