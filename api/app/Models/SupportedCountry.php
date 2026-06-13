<?php

namespace App\Models;

use App\Support\LocalizesJson;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportedCountry extends Model
{
    use HasFactory;
    use LocalizesJson;

    protected $fillable = [
        'code',
        'name',
        'currency',
        'default_locale',
        'timezone',
        'standard_vat_rate_percent',
        'food_vat_rate_percent',
        'is_eu',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'standard_vat_rate_percent' => 'decimal:2',
        'food_vat_rate_percent' => 'decimal:2',
        'is_eu' => 'boolean',
        'is_active' => 'boolean',
    ];
}
