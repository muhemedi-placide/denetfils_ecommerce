<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_token',
        'customer_id',
        'currency',
        'subtotal_cents',
        'tax_cents',
        'total_cents',
        'expires_at',
        'last_activity_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function shippingSelection(): HasOne
    {
        return $this->hasOne(CartShippingSelection::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function recoveryLinks(): HasMany
    {
        return $this->hasMany(CartRecoveryLink::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = (int) $this->items()->sum('line_total_cents');

        $this->forceFill([
            'subtotal_cents' => $subtotal,
            'tax_cents' => 0,
            'total_cents' => $subtotal,
            'last_activity_at' => now(),
            'expires_at' => now()->addDays(30),
        ])->save();
    }
}
