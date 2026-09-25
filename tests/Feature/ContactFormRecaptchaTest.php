<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\WebController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContactFormRecaptchaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::put(CACHE_BUSINESS_SETTINGS_TABLE, collect());

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function test_enabled_recaptcha_rejects_a_message_without_a_token(): void
    {
        $this->setRecaptchaStatus(status: 1);
        Http::fake();

        $this->submitContactForm();

        $this->assertSame(0, DB::table('contacts')->count());
        Http::assertNothingSent();
    }

    public function test_enabled_recaptcha_stores_a_message_google_accepts(): void
    {
        $this->setRecaptchaStatus(status: 1);
        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true, 'score' => 0.9, 'action' => 'contact']),
        ]);

        $this->submitContactForm(extraFields: ['g-recaptcha-response' => 'valid-token']);

        $this->assertSame(1, DB::table('contacts')->count());
        Http::assertSent(fn ($request) => $request['secret'] === 'test-secret' && $request['response'] === 'valid-token');
    }

    public function test_enabled_recaptcha_rejects_a_token_issued_for_another_action(): void
    {
        $this->setRecaptchaStatus(status: 1);
        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true, 'score' => 0.9, 'action' => 'login']),
        ]);

        $this->submitContactForm(extraFields: ['g-recaptcha-response' => 'login-token']);

        $this->assertSame(0, DB::table('contacts')->count());
    }

    public function test_disabled_recaptcha_stores_a_message_without_a_token(): void
    {
        $this->setRecaptchaStatus(status: 0);
        Http::fake();

        $this->submitContactForm();

        $this->assertSame(1, DB::table('contacts')->count());
        Http::assertNothingSent();
    }

    private function setRecaptchaStatus(int $status): void
    {
        Cache::put('recaptcha', ['status' => $status, 'site_key' => 'test-site', 'secret_key' => 'test-secret']);
    }

    /**
     * @param  array<string, string>  $extraFields
     */
    private function submitContactForm(array $extraFields = []): void
    {
        $request = Request::create('/contact/store', 'POST', array_merge([
            'name' => 'Buyer',
            'email' => 'buyer@gmail.com',
            'mobile_number' => '+971501234567',
            'subject' => 'Order question',
            'message' => 'Is the hammour fresh today?',
        ], $extraFields));
        $request->setLaravelSession($this->app['session.store']);
        $this->app->instance('request', $request);

        $this->app->make(WebController::class)->contact_store(request: $request);
    }
}
