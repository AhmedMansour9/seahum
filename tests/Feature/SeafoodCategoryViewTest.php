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

        foreach (['all_categories_page', 'category_products_view_page', 'category_products_ajax_partials'] as $viewKey) {
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

        foreach (['home', 'category', 'categories'] as $page) {
            $pageContents = file_get_contents($this->templatePath('web-views.seafood.'.$page));

            foreach ($sharedPartials as $partial) {
                $this->assertStringContainsString(
                    "web-views.seafood.partials.{$partial}",
                    $pageContents,
                    "The seafood {$page} page no longer includes the shared '{$partial}' partial."
                );
            }
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
