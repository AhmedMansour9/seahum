<?php

namespace App\Http\Requests\Web;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class CustomerAccountDeleteRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    private ?User $customer = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => translate('email_is_required'),
            'email.email' => translate('please_enter_a_valid_email_address'),
            'password.required' => translate('password_is_required'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $customer = User::where('email', trim((string) $this['email']))->first();

                if (!$customer || empty($customer->password) || !Hash::check((string) $this['password'], $customer->password)) {
                    $validator->errors()->add('email', translate('the_email_or_password_is_incorrect'));
                    return;
                }

                $this->customer = $customer;
            }
        ];
    }

    public function customer(): User
    {
        return $this->customer;
    }
}
