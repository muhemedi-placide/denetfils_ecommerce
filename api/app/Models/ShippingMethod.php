<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = ['shipping_carrier_id', 'code', 'name', 'description', 'delivery_type', 'service_code', 'is_active', 'requires_pickup_point', 'requires_phone', 'max_weight_grams', 'min_delivery_days', 'max_delivery_days', 'sort_order', 'configuration'];
    protected $casts = ['name' => 'array', 'description' => 'array', 'is_active' => 'boolean', 'requires_pickup_point' => 'boolean', 'requires_phone' => 'boolean', 'configuration' => 'array'];

    public function carrier(): BelongsTo { return $this->belongsTo(ShippingCarrier::class, 'shipping_carrier_id'); }
    public function rates(): HasMany { return $this->hasMany(ShippingRate::class); }
    public function localized(string $field, string $locale): ?string { $value = $this->{$field}; return is_array($value) ? ($value[$locale] ?? $value['fr'] ?? $value['en'] ?? null) : $value; }
}
