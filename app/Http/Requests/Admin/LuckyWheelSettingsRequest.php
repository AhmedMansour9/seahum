<?php

namespace App\Http\Requests\Admin;

use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int|null $is_enabled
 * @property string $spin_frequency
 * @property string $minimum_order_amount
 */
class LuckyWheelSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_enabled' => 'nullable|boolean',
            'spin_frequency' => ['required', Rule::enum(LuckyWheelSpinFrequency::class)],
            'minimum_order_amount' => 'required|numeric|min:0|max:999999999',
        ];
    }
}
