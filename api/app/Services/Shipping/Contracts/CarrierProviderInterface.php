<?php

namespace App\Services\Shipping\Contracts;

use App\Models\OrderShipment;
use App\Models\ShippingCarrier;
use App\Services\Shipping\DTO\ShipmentData;

interface CarrierProviderInterface
{
    public function code(): string;
    public function searchPickupPoints(ShippingCarrier $carrier, array $criteria): array;
    public function createShipment(OrderShipment $shipment): ShipmentData;
    public function downloadLabel(OrderShipment $shipment): string;
    public function tracking(OrderShipment $shipment): array;
    public function test(ShippingCarrier $carrier): array;
}
