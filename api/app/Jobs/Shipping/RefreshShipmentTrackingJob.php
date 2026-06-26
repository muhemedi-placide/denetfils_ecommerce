<?php

namespace App\Jobs\Shipping;

use App\Models\OrderShipment;
use App\Services\Shipping\ShippingManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshShipmentTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $shipmentId) {}
    public function handle(ShippingManager $shipping): void
    {
        $shipment = OrderShipment::query()->with('carrier')->findOrFail($this->shipmentId);
        if (! $shipment->external_shipment_id) return;
        $tracking = $shipping->provider($shipment->carrier->provider)->tracking($shipment);
        $shipment->forceFill(['raw_payload' => [...($shipment->raw_payload ?? []), 'tracking' => $tracking]])->save();
    }
}
