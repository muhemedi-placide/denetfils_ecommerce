<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role_id' => $this->role_id,
            'role' => $this->role?->name,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'preferred_locale' => $this->preferred_locale,
            'country_code' => $this->country_code,
            'timezone' => $this->timezone,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at,
            'roles' => [$this->role?->name ?? 'customer'],
            'permissions' => [],
            'customer_profile' => $this->whenLoaded('customerProfile'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
