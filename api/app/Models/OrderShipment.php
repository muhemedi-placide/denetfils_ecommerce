<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderShipment extends Model
{
    protected $fillable = ['order_id', 'shipping_carrier_id', 'shipping_method_id', 'pickup_point_id', 'tracking_number', 'label_path', 'external_shipment_id', 'status', 'last_error', 'raw_payload', 'shipped_at'];
    protected $casts = ['raw_payload' => 'array', 'shipped_at' => 'datetime'];
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function carrier(): BelongsTo { return $this->belongsTo(ShippingCarrier::class, 'shipping_carrier_id'); }
    public function method(): BelongsTo { return $this->belongsTo(ShippingMethod::class, 'shipping_method_id'); }
    public function pickupPoint(): BelongsTo { return $this->belongsTo(PickupPoint::class); }
}
