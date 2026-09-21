<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Blog, order tracking, digital download and the OTP / reset-password steps
 * moved onto the seafood design system. These checks pin the contracts the
 * backend relies on, which break silently when markup is rewritten.
 */
class SeafoodBlogTrackingAndOtpViewTest extends TestCase
{
    private const THEME_PATH = 'resources/themes/default';

    private const MIGRATED_TEMPLATES = [
        'web-views.customer-views.auth.verify-otp',
        'web-views.customer-views.auth._otp-login-verify',
        'web-views.customer-views.auth.verify',
        'web-views.customer-views.auth._otp-update-info',
        'web-views.customer-views.auth.reset-password',
        'web-views.seafood.blogs',
        'web-views.seafood.popular-blogs',
        'web-views.seafood.blog-details',
        'web-views.order.tracking-page',
        'web-views.order.tracking',
        'web-views.order.digital-product-download',
        'web-views.users-profile.account-order-details',
    ];

    public function test_migrated_templates_compile_to_valid_php(): void
    {
        foreach (self::MIGRATED_TEMPLATES as $template) {
            $compiledFile = tempnam(sys_get_temp_dir(), 'blade').'.php';
            file_put_contents($compiledFile, Blade::compileString(file_get_contents($this->templatePath($template))));

            exec('php -l '.escapeshellarg($compiledFile).' 2>&1', $output, $exitCode);
            unlink($compiledFile);

            $this->assertSame(0, $exitCode, $template.' does not compile: '.implode("\n", $output));
        }
    }

    /**
     * ForgotPasswordController reads "otp"; the login, social and sign-up verifiers read "token".
     */
    public function test_otp_pages_submit_the_field_name_their_controller_reads(): void
    {
        $expectedOtpFieldNames = [
            'web-views.customer-views.auth.verify-otp' => 'otp',
            'web-views.customer-views.auth._otp-login-verify' => 'token',
            'web-views.customer-views.auth.verify' => 'token',
        ];

        foreach ($expectedOtpFieldNames as $template => $otpFieldName) {
            $pageContents = file_get_contents($this->templatePath($template));

            $this->assertStringContainsString("'otpFieldName' => '{$otpFieldName}'", $pageContents, "{$template} posts the wrong OTP field.");
            $this->assertStringContainsString('@csrf', $pageContents);
        }

        $otpInput = file_get_contents($this->templatePath('web-views.seafood.partials._otp-input'));
        $this->assertStringContainsString('name="{{ $otpFieldName }}"', $otpInput);
    }

    public function test_login_otp_page_keeps_every_verification_route(): void
    {
        $loginOtpPage = file_get_contents($this->templatePath('web-views.customer-views.auth._otp-login-verify'));

        foreach (['customer.auth.login.social.verify-account', 'customer.auth.verify-recover-password', 'customer.auth.login.verify-account.submit', 'customer.auth.resend_otp', 'customer.auth.resend-otp-reset-password'] as $routeName) {
            $this->assertStringContainsString("route('{$routeName}')", $loginOtpPage);
        }

        foreach (['name="identity"', 'name="type"'] as $hiddenField) {
            $this->assertStringContainsString($hiddenField, $loginOtpPage);
        }
    }

    /**
     * The seafood layout has no Bootstrap JS, so the legacy "Is it you?" modal would never open.
     */
    public function test_social_profile_completion_uses_the_seafood_modal(): void
    {
        $updateInfoPage = file_get_contents($this->templatePath('web-views.customer-views.auth._otp-update-info'));

        $this->assertStringContainsString('web-views.seafood.partials._social-user-confirm-modal', $updateInfoPage);
        $this->assertStringNotContainsString('modal_for_social_media_user_view', $updateInfoPage);
    }

    /**
     * Web blog pages use the seafood views; the in-app webview (/app/...) keeps the legacy ones.
     */
    public function test_blog_views_are_split_between_web_and_app(): void
    {
        $viewFileNames = require base_path(self::THEME_PATH.'/file_names.php');

        $this->assertSame('web-views.seafood.blogs', $viewFileNames['frontend_blog_list']);
        $this->assertSame('web-views.seafood.blog-details', $viewFileNames['frontend_blog_details_view']);
        $this->assertSame('web-views.seafood.popular-blogs', $viewFileNames['frontend_popular_blogs_view']);
        $this->assertSame('web-views.blogs.index', $viewFileNames['frontend_app_blog_list']);
        $this->assertSame('web-views.blogs.details', $viewFileNames['frontend_app_blog_details_view']);
        $this->assertSame('web-views.blogs.index-popular', $viewFileNames['frontend_app_popular_blogs_view']);

        foreach (['frontend_blog_list', 'frontend_blog_details_view', 'frontend_popular_blogs_view', 'frontend_app_blog_list', 'frontend_app_blog_details_view', 'frontend_app_popular_blogs_view'] as $viewKey) {
            $this->assertFileExists($this->templatePath($viewFileNames[$viewKey]), "{$viewKey} points to a missing view.");
        }

        $blogController = file_get_contents(base_path('Modules/Blog/app/Http/Controllers/Web/FrontendBlogController.php'));
        $this->assertSame(3, substr_count($blogController, 'view($this->getPlatformViewName('));
    }

    public function test_track_order_form_sends_the_lookup_fields_the_controller_reads(): void
    {
        $trackOrderForm = file_get_contents($this->templatePath('web-views.seafood.partials._track-order-form'));

        $this->assertStringContainsString("route('track-order.result')", $trackOrderForm);
        $this->assertStringContainsString('method="get"', $trackOrderForm);
        $this->assertStringContainsString('name="order_id"', $trackOrderForm);
        $this->assertStringContainsString('name="phone_number"', $trackOrderForm);

        foreach (['web-views.order.tracking-page', 'web-views.order.tracking'] as $template) {
            $this->assertStringContainsString('web-views.seafood.partials._track-order-form', file_get_contents($this->templatePath($template)));
        }
    }

    public function test_order_totals_partial_is_shared_by_account_and_public_tracking(): void
    {
        foreach (['web-views.users-profile.account-order-details', 'web-views.order.tracking'] as $template) {
            $this->assertStringContainsString('web-views.seafood.partials._order-totals', file_get_contents($this->templatePath($template)));
        }
    }

    private function templatePath(string $viewName): string
    {
        return base_path(self::THEME_PATH.'/'.str_replace('.', '/', $viewName).'.blade.php');
    }
}
