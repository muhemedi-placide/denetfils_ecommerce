<?php

namespace App\Http\Requests\Api\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => ['sometimes', 'array'],
            'name.fr' => ['required_with:name', 'string', 'max:160'],
            'name.en' => ['required_with:name', 'string', 'max:160'],
            'slug' => [
                'sometimes',
                'string',
                'max:180',
                'alpha_dash:ascii',
                Rule::unique('categories', 'slug')->ignore($category?->id),
            ],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
