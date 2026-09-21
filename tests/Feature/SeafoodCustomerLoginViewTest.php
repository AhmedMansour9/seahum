<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * The customer login page was migrated from the legacy Bootstrap layout to the
 * seafood design system. Its submit, OTP toggle and password reveal now come
 * from the seafood auth scripts partial instead of the legacy custom.js, so a
 * dropped hook or partial only surfaces as a dead form in the browser.
 */
class SeafoodCustomerLoginViewTest extends TestCase
{
    private const LOGIN_TEMPLATE = 'resources/themes/default/web-views/customer-views/auth/login.blade.php';

    private const AUTH_SCRIPTS_TEMPLATE = 'resources/themes/default/web-views/seafood/partials/_auth-scripts.blade.php';

    private const SHARED_SCRIPTS_TEMPLATE = 'resources/themes/default/web-views/seafood/partials/_scripts.blade.php';

    public function test_login_page_renders_through_the_seafood_layout_and_its_shared_partials(): void
    {
        $loginPage = file_get_contents(base_path(self::LOGIN_TEMPLATE));

        $this->assertStringContainsString("@extends('layouts.front-end.seafood')", $loginPage);

        foreach (['_styles', '_topbar', '_header', '_footer', '_mobile-bar', '_scripts', '_breadcrumb', '_auth-scripts'] as $partial) {
            $this->assertStringContainsString(
                "web-views.seafood.partials.{$partial}",
                $loginPage,
                "The login page no longer includes the shared '{$partial}' partial."
            );
        }
    }

    public function test_login_form_keeps_the_hooks_the_submit_handler_depends_on(): void
    {
        $loginPage = file_get_contents(base_path(self::LOGIN_TEMPLATE));

        foreach ([
            'customer-centralize-login-form',
            'auth-login-type-input',
            'auth-email-input',
            'auth-password-input',
            'name="user_identity"',
            'name="password"',
            'name="remember"',
            'name="keep_customer_login_redirect_url"',
            '@csrf',
        ] as $hook) {
            $this->assertStringContainsString($hook, $loginPage, "The login form lost the '{$hook}' hook.");
        }
    }

    public function test_seafood_auth_scripts_replace_the_legacy_custom_js_behaviour(): void
    {
        $authScripts = file_get_contents(base_path(self::AUTH_SCRIPTS_TEMPLATE));

        foreach ([
            '.customer-centralize-login-form',
            'seafoodAuthResponseManager',
            'setSeafoodLoginMethod',
            '.otp-login-btn',
            '.manual-login-btn',
        ] as $behaviour) {
            $this->assertStringContainsString($behaviour, $authScripts, "The seafood auth scripts lost '{$behaviour}'.");
        }

        $sharedScripts = file_get_contents(base_path(self::SHARED_SCRIPTS_TEMPLATE));

        $this->assertStringContainsString('.sf-password-toggle', $sharedScripts, 'The password reveal handler lives in the shared seafood scripts partial.');
    }

    public function test_login_template_compiles_to_valid_php(): void
    {
        foreach ([self::LOGIN_TEMPLATE, self::AUTH_SCRIPTS_TEMPLATE] as $template) {
            $compiledFile = tempnam(sys_get_temp_dir(), 'blade').'.php';
            file_put_contents($compiledFile, Blade::compileString(file_get_contents(base_path($template))));

            exec('php -l '.escapeshellarg($compiledFile).' 2>&1', $output, $exitCode);
            unlink($compiledFile);

            $this->assertSame(0, $exitCode, $template.' does not compile: '.implode("\n", $output));
        }
    }
}
