<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'company' => $this->company,
            'street_line_1' => $this->street_line_1,
            'street_line_2' => $this->street_line_2,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'region' => $this->region,
            'country_code' => $this->country_code,
            'phone' => $this->phone,
            'is_default' => $this->is_default,
        ];
    }
}
