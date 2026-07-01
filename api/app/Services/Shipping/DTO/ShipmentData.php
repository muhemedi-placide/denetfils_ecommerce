<?php

namespace App\Services\Shipping\DTO;

final readonly class ShipmentData
{
    public function __construct(
        public string $externalId,
        public ?string $trackingNumber,
        public ?string $labelUrl,
        public string $status,
        public array $rawPayload = [],
    ) {}
}
