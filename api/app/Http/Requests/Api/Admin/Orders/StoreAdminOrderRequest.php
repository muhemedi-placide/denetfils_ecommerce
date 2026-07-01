<?php

namespace App\Http\Requests\Api\Admin\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('orders.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'cart_token' => ['required', 'string', 'max:64'],
            'shipping_address_id' => ['required', 'integer'],
            'billing_address_id' => ['nullable', 'integer'],
            'locale' => ['sometimes', Rule::in(['fr', 'en'])],
            'delivery_method' => ['nullable', Rule::in(['standard', 'relay'])],
            'carrier' => ['nullable', 'string', 'max:64'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
