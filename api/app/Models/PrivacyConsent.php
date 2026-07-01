<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'version',
        'accepted',
        'locale',
        'country_code',
        'ip_address',
        'user_agent',
        'consented_at',
    ];

    protected $casts = [
        'accepted' => 'boolean',
        'consented_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
