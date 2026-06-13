<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
