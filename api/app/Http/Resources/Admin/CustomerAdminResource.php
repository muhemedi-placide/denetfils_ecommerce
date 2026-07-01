<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role_id' => $this->role_id,
            'role' => $this->role?->name ?? 'customer',
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'preferred_locale' => $this->preferred_locale,
            'country_code' => $this->country_code,
            'timezone' => $this->timezone,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'summary' => [
                'orders_count' => (int) ($this->orders_count ?? 0),
                'addresses_count' => (int) ($this->addresses_count ?? 0),
                'total_spent_cents' => (int) ($this->total_spent_cents ?? 0),
                'open_conversations_count' => (int) ($this->open_conversations_count ?? 0),
            ],
            'profile' => $this->whenLoaded('customerProfile'),
            'addresses' => $this->whenLoaded('addresses', fn () => $this->addresses->map(fn ($address) => [
                'id' => $address->id,
                'type' => $address->type,
                'label' => $address->label,
                'recipient_name' => $address->recipient_name,
                'company' => $address->company,
                'street_line_1' => $address->street_line_1,
                'street_line_2' => $address->street_line_2,
                'postal_code' => $address->postal_code,
                'city' => $address->city,
                'region' => $address->region,
                'country_code' => $address->country_code,
                'phone' => $address->phone,
                'is_default' => $address->is_default,
            ])->values()),
            'orders' => $this->whenLoaded('orders', fn () => OrderAdminResource::collection($this->orders)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
