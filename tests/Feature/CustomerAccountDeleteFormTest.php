<?php

namespace Tests\Feature;

use App\Http\Requests\Web\CustomerAccountDeleteRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CustomerAccountDeleteFormTest extends TestCase
{
    private const VIEW_PATH = 'resources/themes/default/web-views/customer-views/auth/account-delete.blade.php';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::put(CACHE_BUSINESS_SETTINGS_TABLE, collect());

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
        });

        DB::table('users')->insert([
            ['id' => 1, 'email' => 'buyer@gmail.com', 'password' => Hash::make('secret-password')],
            ['id' => 2, 'email' => 'seller@gmail.com', 'password' => null],
        ]);
    }

    public function test_guest_with_matching_email_and_password_resolves_the_account(): void
    {
        $request = $this->validatedRequest(email: 'buyer@gmail.com', password: 'secret-password');

        $this->assertSame(1, $request->customer()->id);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->assertArrayHasKey('email', $this->validationErrors(email: 'buyer@gmail.com', password: 'wrong-password'));
    }

    public function test_unknown_email_gets_the_same_error_as_a_wrong_password(): void
    {
        $unknownEmailErrors = $this->validationErrors(email: 'nobody@gmail.com', password: 'secret-password');
        $wrongPasswordErrors = $this->validationErrors(email: 'buyer@gmail.com', password: 'wrong-password');

        $this->assertSame($wrongPasswordErrors, $unknownEmailErrors);
    }

    public function test_account_without_password_cannot_be_deleted(): void
    {
        $this->assertArrayHasKey('email', $this->validationErrors(email: 'seller@gmail.com', password: 'anything'));
    }

    public function test_email_and_password_are_required(): void
    {
        $this->assertArrayHasKey('email', $this->validationErrors(email: '', password: 'secret-password'));
        $this->assertArrayHasKey('password', $this->validationErrors(email: 'buyer@gmail.com', password: ''));
    }

    public function test_delete_routes_are_open_to_guests_and_throttle_submissions(): void
    {
        $formRoute = Route::getRoutes()->getByName('account-delete-form');
        $submitRoute = Route::getRoutes()->getByName('account-delete-submit');

        $this->assertContains('GET', $formRoute->methods());
        $this->assertNotContains('customer', $formRoute->gatherMiddleware());
        $this->assertContains('POST', $submitRoute->methods());
        $this->assertNotContains('customer', $submitRoute->gatherMiddleware());
        $this->assertContains('throttle:5,1', $submitRoute->gatherMiddleware());
    }

    public function test_delete_account_view_compiles_and_posts_credentials(): void
    {
        $viewContents = file_get_contents(base_path(self::VIEW_PATH));
        $compiledFile = tempnam(sys_get_temp_dir(), 'blade').'.php';
        file_put_contents($compiledFile, Blade::compileString($viewContents));

        exec('php -l '.escapeshellarg($compiledFile).' 2>&1', $output, $exitCode);
        unlink($compiledFile);

        $this->assertSame(0, $exitCode, implode("\n", $output));
        $this->assertStringContainsString("route('account-delete-submit')", $viewContents);
        $this->assertStringContainsString('@csrf', $viewContents);
        $this->assertStringContainsString('name="email"', $viewContents);
        $this->assertStringContainsString('name="password"', $viewContents);
    }

    private function validatedRequest(string $email, string $password): CustomerAccountDeleteRequest
    {
        $request = CustomerAccountDeleteRequest::create(uri: '/account-delete', method: 'POST', parameters: [
            'email' => $email,
            'password' => $password,
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->validateResolved();

        return $request;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function validationErrors(string $email, string $password): array
    {
        try {
            $this->validatedRequest(email: $email, password: $password);
        } catch (ValidationException $exception) {
            return $exception->errors();
        }

        return [];
    }
}
