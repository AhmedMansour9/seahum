<?php

namespace Tests\Feature;

use App\Http\Requests\Web\CustomerProfileUpdateRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CustomerProfilePasswordRuleTest extends TestCase
{
    public function test_new_password_shorter_than_six_characters_is_rejected(): void
    {
        $validator = $this->validatePassword(password: '12345');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Min', $validator->failed()['password']);
    }

    public function test_new_password_of_six_characters_is_accepted(): void
    {
        $this->assertFalse($this->validatePassword(password: '123456')->fails());
    }

    public function test_password_stays_optional_when_profile_is_updated_without_it(): void
    {
        $this->assertFalse($this->validatePassword(password: null)->fails());
    }

    private function validatePassword(?string $password): \Illuminate\Validation\Validator
    {
        $rules = (new CustomerProfileUpdateRequest)->rules();

        return Validator::make(
            data: ['password' => $password],
            rules: ['password' => $rules['password']],
        );
    }
}
