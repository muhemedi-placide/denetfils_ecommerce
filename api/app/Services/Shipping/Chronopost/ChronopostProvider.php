<?php

namespace App\Services\Shipping\Chronopost;

use App\Models\OrderShipment;
use App\Models\ShippingCarrier;
use App\Services\Shipping\Contracts\CarrierProviderInterface;
use App\Services\Shipping\DTO\ShipmentData;
use RuntimeException;

class ChronopostProvider implements CarrierProviderInterface
{
    public function code(): string { return 'chronopost'; }
    public function searchPickupPoints(ShippingCarrier $carrier, array $criteria): array { throw new RuntimeException('Chronopost pickup API is not configured.'); }
    public function createShipment(OrderShipment $shipment): ShipmentData { throw new RuntimeException('Chronopost shipment API is not configured.'); }
    public function downloadLabel(OrderShipment $shipment): string { throw new RuntimeException('Chronopost label API is not configured.'); }
    public function tracking(OrderShipment $shipment): array { throw new RuntimeException('Chronopost tracking API is not configured.'); }
    public function test(ShippingCarrier $carrier): array { return ['ok' => true, 'message' => 'Chronopost is configured as a manual delivery carrier.']; }
}
