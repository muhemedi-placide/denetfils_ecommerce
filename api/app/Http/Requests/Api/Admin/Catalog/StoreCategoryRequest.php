<?php

namespace App\Http\Requests\Api\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.fr' => ['required', 'string', 'max:160'],
            'name.en' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('categories', 'slug')],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
