<?php

namespace App\Http\Requests\Api\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cart_token' => ['required', 'string', 'max:64'],
            'shipping_address_id' => ['nullable', 'integer', 'required_without:country_code'],
            'country_code' => ['nullable', 'string', 'size:2', 'required_without:shipping_address_id'],
            'locale' => ['sometimes', Rule::in(['fr', 'en'])],
            'delivery_method' => ['nullable', Rule::in(['standard', 'relay'])],
            'carrier' => ['nullable', 'string', 'max:64'],
        ];
    }
}
