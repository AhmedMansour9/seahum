<?php

namespace Tests\Feature;

use App\Http\Requests\Web\CustomerRegistrationRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CustomerRegistrationPasswordRuleTest extends TestCase
{
    public function test_password_shorter_than_six_characters_is_rejected(): void
    {
        $validator = $this->validatePassword(password: '12345');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->failed());
        $this->assertArrayHasKey('Min', $validator->failed()['password']);
    }

    public function test_password_of_six_characters_is_accepted(): void
    {
        $this->assertFalse($this->validatePassword(password: '123456')->fails());
    }

    private function validatePassword(string $password): \Illuminate\Validation\Validator
    {
        $rules = (new CustomerRegistrationRequest)->rules();

        return Validator::make(
            data: ['password' => $password, 'con_password' => $password],
            rules: ['password' => $rules['password']],
        );
    }
}
