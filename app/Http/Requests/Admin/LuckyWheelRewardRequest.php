<?php

namespace App\Http\Requests\Admin;

use App\Enums\LuckyWheel\LuckyWheelRewardType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property array $name
 * @property array $lang
 * @property string $type
 * @property int $display_order
 * @property int|null $is_active
 */
class LuckyWheelRewardRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lang' => 'required|array',
            'lang.*' => 'required|string',
            'name' => 'required|array',
            'name.'.(int)array_search('en', (array)$this->input('lang')) => 'required|string|max:100',
            'name.*' => 'nullable|string|max:100',
            'type' => ['required', Rule::enum(LuckyWheelRewardType::class)],
            'display_order' => 'required|integer|min:0|max:100000',
            'is_active' => 'nullable|boolean',
            'image' => getRulesStringForImageValidation(
                rules: ['nullable', 'image'],
                skipMimes: ['.svg', '.gif'],
            ),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => translate('the_name_field_is_required'),
            'name.*.required' => translate('the_name_field_is_required'),
        ];
    }
}
