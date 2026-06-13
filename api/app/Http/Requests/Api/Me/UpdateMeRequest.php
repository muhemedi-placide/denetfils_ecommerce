<?php

namespace App\Http\Requests\Api\Me;

use App\Support\CoreDefaults;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:120'],
            'last_name' => ['sometimes', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'preferred_locale' => ['sometimes', Rule::in(CoreDefaults::LOCALES)],
            'country_code' => ['sometimes', 'string', 'size:2', Rule::exists('supported_countries', 'code')->where('is_active', true)],
            'timezone' => ['sometimes', 'string', 'max:64'],
        ];
    }
}
