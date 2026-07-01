<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'provider',
        'display_name',
        'description',
        'environment',
        'status',
        'is_enabled',
        'sort_order',
        'countries',
        'currencies',
        'capabilities',
        'public_config',
        'credentials',
        'webhook_config',
        'last_tested_at',
        'last_test_status',
        'last_test_message',
    ];

    protected $casts = [
        'display_name' => 'array',
        'description' => 'array',
        'is_enabled' => 'boolean',
        'countries' => 'array',
        'currencies' => 'array',
        'capabilities' => 'array',
        'public_config' => 'array',
        'credentials' => 'encrypted:array',
        'webhook_config' => 'encrypted:array',
        'last_tested_at' => 'datetime',
    ];

    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }
}
