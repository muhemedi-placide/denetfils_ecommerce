<?php

namespace App\Http\Requests\Api\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PickupPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => ['required', 'integer'],
            'locale' => ['sometimes', Rule::in(['fr', 'en'])],
            'carrier' => ['nullable', 'string', 'max:64'],
            'query' => ['nullable', 'string', 'max:120'],
        ];
    }
}
