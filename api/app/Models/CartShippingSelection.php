<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartShippingSelection extends Model
{
    protected $fillable = ['cart_id', 'shipping_method_id', 'pickup_point_id', 'shipping_price_cents', 'currency', 'country', 'postal_code', 'city', 'address_snapshot'];
    protected $casts = ['address_snapshot' => 'array'];
    public function cart(): BelongsTo { return $this->belongsTo(Cart::class); }
    public function method(): BelongsTo { return $this->belongsTo(ShippingMethod::class, 'shipping_method_id'); }
    public function pickupPoint(): BelongsTo { return $this->belongsTo(PickupPoint::class); }
}
