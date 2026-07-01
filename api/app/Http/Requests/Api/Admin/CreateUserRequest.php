<?php

namespace App\Http\Requests\Api\Admin;

use App\Support\CoreDefaults;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
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
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:32'],
            'preferred_locale' => ['nullable', Rule::in(CoreDefaults::LOCALES)],
            'country_code' => ['required', 'string', 'size:2', Rule::exists('supported_countries', 'code')->where('is_active', true)],
            'timezone' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', Rule::in(CoreDefaults::USER_STATUSES)],
            'roles' => ['nullable', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'not_in:customer', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'position' => ['nullable', 'string', 'max:120'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
