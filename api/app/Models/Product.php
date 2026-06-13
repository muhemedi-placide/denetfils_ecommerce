<?php

namespace App\Models;

use App\Support\LocalizesJson;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    use LocalizesJson;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'origin',
        'sku',
        'price_cents',
        'currency',
        'weight_grams',
        'stock_quantity',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'origin' => 'array',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id');
    }
}
