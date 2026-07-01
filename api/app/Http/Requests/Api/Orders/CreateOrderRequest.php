<?php

namespace App\Http\Requests\Api\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cart_token' => ['required', 'string', 'max:64'],
            'shipping_address_id' => ['required', 'integer'],
            'billing_address_id' => ['nullable', 'integer'],
            'locale' => ['sometimes', Rule::in(['fr', 'en'])],
            'delivery_method' => ['nullable', Rule::in(['standard', 'relay'])],
            'carrier' => ['nullable', 'string', 'max:64'],
            'shipping_method_id' => ['nullable', 'integer', 'exists:shipping_methods,id'],
            'pickup_point_id' => ['nullable', 'integer', 'exists:pickup_points,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
