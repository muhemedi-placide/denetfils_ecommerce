<?php

namespace App\Models;

use App\Support\LocalizesJson;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;
    use LocalizesJson;

    protected $fillable = [
        'product_id',
        'url',
        'width',
        'height',
        'dominant_color',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'alt_text' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
