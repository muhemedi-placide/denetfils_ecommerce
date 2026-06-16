<?php

namespace App\Http\Requests\Api\Admin\Orders;

use App\Support\OrderStatusCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('orders.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(OrderStatusCatalog::ORDER_STATUSES)],
            'payment_status' => ['sometimes', 'string', Rule::in(OrderStatusCatalog::PAYMENT_STATUSES)],
            'fulfillment_status' => ['sometimes', 'string', Rule::in(OrderStatusCatalog::FULFILLMENT_STATUSES)],
            'carrier' => ['nullable', 'string', 'max:64'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'tracking_url' => ['nullable', 'url', 'max:2048'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'order_state' => ['nullable', 'string', 'max:64'],
            'notify_customer' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $keys = [
                    'status',
                    'payment_status',
                    'fulfillment_status',
                    'carrier',
                    'tracking_number',
                    'tracking_url',
                    'admin_note',
                    'order_state',
                ];

                $hasAction = collect($keys)->contains(fn (string $key) => $this->has($key));

                if (! $hasAction) {
                    $validator->errors()->add('order', 'At least one order update field is required.');
                }
            },
        ];
    }
}
