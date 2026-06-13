<?php

namespace App\Http\Requests\Api\Admin;

use App\Support\CoreDefaults;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
            'email' => ['sometimes', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['sometimes', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:32'],
            'preferred_locale' => ['sometimes', Rule::in(CoreDefaults::LOCALES)],
            'country_code' => ['sometimes', 'string', 'size:2', Rule::exists('supported_countries', 'code')->where('is_active', true)],
            'timezone' => ['sometimes', 'string', 'max:64'],
            'status' => ['sometimes', Rule::in(CoreDefaults::USER_STATUSES)],
            'position' => ['nullable', 'string', 'max:120'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
