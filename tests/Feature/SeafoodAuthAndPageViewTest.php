<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * The customer auth pages and the business page were migrated off the legacy
 * Bootstrap layout onto the seafood design system. These checks pin the parts
 * of that migration that fail silently in the browser rather than loudly.
 */
class SeafoodAuthAndPageViewTest extends TestCase
{
    private const THEME_PATH = 'resources/themes/default';

    private const MIGRATED_TEMPLATES = [
        'web-views.customer-views.auth.register',
        'web-views.customer-views.auth.recover-password',
        'web-views.pages.business_page',
        'web-views.pages.contact-us',
    ];

    public function test_migrated_templates_compile_to_valid_php(): void
    {
        foreach (self::MIGRATED_TEMPLATES as $template) {
            $templatePath = $this->templatePath($template);
            $compiledFile = tempnam(sys_get_temp_dir(), 'blade').'.php';
            file_put_contents($compiledFile, Blade::compileString(file_get_contents($templatePath)));

            exec('php -l '.escapeshellarg($compiledFile).' 2>&1', $output, $exitCode);
            unlink($compiledFile);

            $this->assertSame(0, $exitCode, $templatePath.' does not compile: '.implode("\n", $output));
        }
    }

    public function test_migrated_pages_use_the_seafood_layout_and_shared_chrome(): void
    {
        $sharedPartials = ['_styles', '_topbar', '_header', '_breadcrumb', '_footer', '_mobile-bar', '_scripts'];

        foreach (self::MIGRATED_TEMPLATES as $template) {
            $pageContents = file_get_contents($this->templatePath($template));

            $this->assertStringContainsString("@extends('layouts.front-end.seafood')", $pageContents, $template.' left the seafood layout.');

            foreach ($sharedPartials as $partial) {
                $this->assertStringContainsString(
                    "web-views.seafood.partials.{$partial}",
                    $pageContents,
                    "{$template} no longer includes the shared '{$partial}' partial."
                );
            }
        }
    }

    public function test_registration_form_keeps_every_field_the_backend_validates(): void
    {
        $registerPage = file_get_contents($this->templatePath('web-views.customer-views.auth.register'));

        foreach (['f_name', 'l_name', 'email', 'phone', 'password', 'con_password', 'remember'] as $fieldName) {
            $this->assertStringContainsString('name="'.$fieldName.'"', $registerPage);
        }

        $this->assertStringContainsString('@csrf', $registerPage);
        $this->assertStringContainsString('type="tel"', $registerPage, 'intl-tel-input only claims tel inputs.');
        $this->assertStringContainsString('_firebase-recaptcha-container', $registerPage);
    }

    /**
     * A label that both wraps the checkbox and points at it with for= fires the
     * toggle twice, so the terms box never changes and the submit button stays
     * disabled forever.
     */
    public function test_terms_checkbox_label_is_associated_only_once(): void
    {
        $registerPage = file_get_contents($this->templatePath('web-views.customer-views.auth.register'));

        $this->assertStringContainsString('<label class="sf-check">', $registerPage);
        $this->assertStringNotContainsString('for="inputChecked"', $registerPage);
        $this->assertStringContainsString('id="inputChecked"', $registerPage);
    }

    public function test_recover_password_keeps_the_server_verified_captcha_branches(): void
    {
        $recoverPage = file_get_contents($this->templatePath('web-views.customer-views.auth.recover-password'));

        $this->assertStringContainsString('name="identity"', $recoverPage);
        $this->assertStringContainsString('generate-firebase-auth-recaptcha', $recoverPage);
        $this->assertStringContainsString('render-grecaptcha-response', $recoverPage);
        $this->assertStringContainsString('default-captcha-container', $recoverPage);
    }

    /**
     * The banner used to be painted by a custom.js data-bg-img convention that
     * no longer runs under the seafood layout.
     */
    public function test_business_page_paints_its_banner_without_legacy_javascript(): void
    {
        $businessPage = file_get_contents($this->templatePath('web-views.pages.business_page'));

        $this->assertStringNotContainsString('data-bg-img', $businessPage);
        $this->assertStringContainsString('background-image: url(', $businessPage);
    }

    /**
     * Admin-authored HTML has no wrapper to scroll, so wide content has to be
     * contained by the prose rules themselves.
     */
    public function test_shared_prose_rules_contain_admin_authored_content(): void
    {
        $sharedStyles = file_get_contents($this->templatePath('web-views.seafood.partials._styles'));

        foreach ([
            '.sf-prose { color: #34586B; font-size: 15.5px; line-height: 1.9; overflow-wrap: anywhere; }',
            '.sf-prose img { max-width: 100%; height: auto; border-radius: 14px; }',
            '.sf-prose pre { overflow-x: auto; max-width: 100%;',
            '.sf-prose table { display: block; width: max-content; max-width: 100%; overflow-x: auto;',
        ] as $rule) {
            $this->assertStringContainsString($rule, $sharedStyles);
        }
    }

    /**
     * The contact form no longer ships a captcha, so the server-side check has
     * to go with it: RecaptchaService compares the posted value against the
     * session and would reject every submission once the input disappears.
     */
    public function test_contact_page_and_controller_drop_the_captcha_together(): void
    {
        $contactPage = file_get_contents($this->templatePath('web-views.pages.contact-us'));
        $webController = file_get_contents(base_path('app/Http/Controllers/Web/WebController.php'));

        foreach (['default-captcha-container', 'render-grecaptcha-response', 'g-recaptcha-response'] as $captchaMarker) {
            $this->assertStringNotContainsString($captchaMarker, $contactPage);
        }

        $this->assertStringNotContainsString('default_captcha_value_contact', $webController);
        $this->assertStringNotContainsString('RecaptchaService', $webController);
    }

    public function test_contact_form_keeps_every_field_the_backend_stores(): void
    {
        $contactPage = file_get_contents($this->templatePath('web-views.pages.contact-us'));

        foreach (['name', 'email', 'mobile_number', 'subject', 'message'] as $fieldName) {
            $this->assertStringContainsString('name="'.$fieldName.'"', $contactPage);
        }

        $this->assertStringContainsString('@csrf', $contactPage);
        $this->assertStringContainsString('type="tel"', $contactPage, 'intl-tel-input only claims tel inputs.');
        $this->assertStringContainsString("route('contact.store')", $contactPage);
    }

    /**
     * The legacy template repopulated the message box from old('subject'), so a
     * failed submission silently replaced what the customer had written.
     */
    public function test_contact_message_box_repopulates_from_its_own_old_input(): void
    {
        $contactPage = file_get_contents($this->templatePath('web-views.pages.contact-us'));

        $this->assertMatchesRegularExpression('/<textarea[^>]*name="message".*?old\(\x27message\x27\)/s', $contactPage);
    }

    /**
     * The seafood layout ships neither the legacy front-end bundle nor its
     * intl-tel-input assets, so a migrated page carrying a phone field has to
     * pull the shared phone partials itself.
     */
    public function test_pages_with_a_phone_field_load_the_shared_phone_input_assets(): void
    {
        $contactPage = file_get_contents($this->templatePath('web-views.pages.contact-us'));

        $this->assertStringContainsString('web-views.seafood.partials._phone-input-styles', $contactPage);
        $this->assertStringContainsString('web-views.seafood.partials._phone-input-scripts', $contactPage);
    }

    private function templatePath(string $viewName): string
    {
        return base_path(self::THEME_PATH.'/'.str_replace('.', '/', $viewName).'.blade.php');
    }
}
