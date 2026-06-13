<?php

namespace App\Http\Requests\Api\Me;

use App\Support\CoreDefaults;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'type' => [$required, Rule::in(CoreDefaults::ADDRESS_TYPES)],
            'label' => ['nullable', 'string', 'max:120'],
            'recipient_name' => [$required, 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'street_line_1' => [$required, 'string', 'max:255'],
            'street_line_2' => ['nullable', 'string', 'max:255'],
            'postal_code' => [$required, 'string', 'max:32'],
            'city' => [$required, 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'country_code' => [$required, 'string', 'size:2', Rule::exists('supported_countries', 'code')->where('is_active', true)],
            'phone' => ['nullable', 'string', 'max:32'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
