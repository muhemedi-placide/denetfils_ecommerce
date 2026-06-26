<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingCarrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'provider',
        'display_name',
        'description',
        'environment',
        'status',
        'is_enabled',
        'sort_order',
        'delivery_modes',
        'countries',
        'max_weight_grams',
        'supports_relay_points',
        'supports_home_delivery',
        'public_config',
        'credentials',
        'last_tested_at',
        'last_test_status',
        'last_test_message',
    ];

    protected $casts = [
        'display_name' => 'array',
        'description' => 'array',
        'is_enabled' => 'boolean',
        'delivery_modes' => 'array',
        'countries' => 'array',
        'supports_relay_points' => 'boolean',
        'supports_home_delivery' => 'boolean',
        'public_config' => 'array',
        'credentials' => 'encrypted:array',
        'last_tested_at' => 'datetime',
    ];

    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class);
    }
}
