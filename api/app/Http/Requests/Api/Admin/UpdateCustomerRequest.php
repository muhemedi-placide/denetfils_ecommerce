<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('customers.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['active', 'suspended', 'deleted_pending'])],
        ];
    }
}
