<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = ['shipping_method_id', 'shipping_zone_id', 'min_weight_grams', 'max_weight_grams', 'price_cents', 'currency', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function method(): BelongsTo { return $this->belongsTo(ShippingMethod::class, 'shipping_method_id'); }
    public function zone(): BelongsTo { return $this->belongsTo(ShippingZone::class, 'shipping_zone_id'); }
}
