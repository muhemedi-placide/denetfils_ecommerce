<?php

namespace App\Http\Requests\Api\Auth;

use App\Support\CoreDefaults;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
            'preferred_locale' => ['nullable', Rule::in(CoreDefaults::LOCALES)],
            'country_code' => ['required', 'string', 'size:2', Rule::exists('supported_countries', 'code')->where('is_active', true)],
            'timezone' => ['nullable', 'string', 'max:64'],
            'privacy_policy_consent' => ['accepted'],
            'terms_consent' => ['accepted'],
            'marketing_consent' => ['sometimes', 'boolean'],
        ];
    }
}
