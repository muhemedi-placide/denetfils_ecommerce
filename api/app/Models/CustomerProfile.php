<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'accepts_marketing',
        'marketing_consented_at',
        'preferences',
    ];

    protected $casts = [
        'accepts_marketing' => 'boolean',
        'marketing_consented_at' => 'datetime',
        'preferences' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
