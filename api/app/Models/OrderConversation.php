<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderConversation extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'customer_unread_count',
        'staff_unread_count',
        'customer_last_read_at',
        'staff_last_read_at',
        'closed_at',
    ];

    protected $casts = [
        'customer_last_read_at' => 'datetime',
        'staff_last_read_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class);
    }
}
