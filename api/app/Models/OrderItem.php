<?php

namespace App\Models;

use App\Support\LocalizesJson;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;
    use LocalizesJson;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'category_id',
        'product_name',
        'product_slug',
        'product_sku',
        'variant_name',
        'variant_sku',
        'category_slug',
        'category_name',
        'image_url',
        'image_alt_text',
        'weight_grams',
        'quantity',
        'unit_price_cents',
        'line_total_cents',
        'currency',
        'tax_class',
        'tax_rate_percent',
        'tax_cents',
    ];

    protected $casts = [
        'product_name' => 'array',
        'variant_name' => 'array',
        'category_name' => 'array',
        'image_alt_text' => 'array',
        'tax_rate_percent' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
