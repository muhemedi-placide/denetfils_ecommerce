<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_token',
        'currency',
        'subtotal_cents',
        'tax_cents',
        'total_cents',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function shippingSelection(): HasOne
    {
        return $this->hasOne(CartShippingSelection::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = (int) $this->items()->sum('line_total_cents');

        $this->forceFill([
            'subtotal_cents' => $subtotal,
            'tax_cents' => 0,
            'total_cents' => $subtotal,
        ])->save();
    }
}
