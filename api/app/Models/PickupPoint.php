<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickupPoint extends Model
{
    protected $fillable = ['carrier_code', 'external_id', 'type', 'country', 'name', 'address_line1', 'address_line2', 'postal_code', 'city', 'latitude', 'longitude', 'opening_hours', 'raw_payload', 'last_seen_at'];
    protected $casts = ['latitude' => 'float', 'longitude' => 'float', 'opening_hours' => 'array', 'raw_payload' => 'array', 'last_seen_at' => 'datetime'];

    public function cartShippingSelections(): HasMany
    {
        return $this->hasMany(CartShippingSelection::class);
    }

    public function orderShipments(): HasMany
    {
        return $this->hasMany(OrderShipment::class);
    }
}
