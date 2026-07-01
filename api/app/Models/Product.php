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
        'short_description',
        'origin',
        'highlights',
        'badges',
        'tags',
        'ingredients',
        'allergens',
        'nutrition_facts',
        'certifications',
        'storage_instructions',
        'usage_instructions',
        'shipping_profile',
        'return_policy',
        'guarantee',
        'sku',
        'barcode',
        'brand',
        'supplier_reference',
        'purchase_price_cents',
        'price_cents',
        'compare_at_price_cents',
        'currency',
        'price_includes_tax',
        'tax_class',
        'weight_grams',
        'unit_label',
        'stock_quantity',
        'max_order_quantity',
        'rating_average',
        'rating_count',
        'sales_count',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_path',
        'published_at',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'short_description' => 'array',
        'origin' => 'array',
        'highlights' => 'array',
        'badges' => 'array',
        'tags' => 'array',
        'ingredients' => 'array',
        'allergens' => 'array',
        'nutrition_facts' => 'array',
        'certifications' => 'array',
        'storage_instructions' => 'array',
        'usage_instructions' => 'array',
        'shipping_profile' => 'array',
        'return_policy' => 'array',
        'guarantee' => 'array',
        'rating_average' => 'decimal:2',
        'seo_title' => 'array',
        'seo_description' => 'array',
        'seo_keywords' => 'array',
        'published_at' => 'datetime',
        'is_active' => 'boolean',
        'price_includes_tax' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->where('role', 'gallery')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function iconImage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductImage::class)->where('role', 'icon');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
