<?php

namespace App\Services\Shipping\DTO;

final readonly class ShippingQuoteData
{
    public function __construct(
        public int $methodId,
        public string $methodCode,
        public string $carrierCode,
        public string $name,
        public string $deliveryType,
        public int $priceCents,
        public string $currency,
        public bool $requiresPickupPoint,
        public bool $requiresPhone,
        public ?int $minDeliveryDays,
        public ?int $maxDeliveryDays,
    ) {}

    public function toArray(): array
    {
        return [
            'method_id' => $this->methodId, 'method_code' => $this->methodCode, 'carrier_code' => $this->carrierCode,
            'name' => $this->name, 'delivery_type' => $this->deliveryType, 'price_cents' => $this->priceCents,
            'currency' => $this->currency, 'requires_pickup_point' => $this->requiresPickupPoint,
            'requires_phone' => $this->requiresPhone, 'min_delivery_days' => $this->minDeliveryDays,
            'max_delivery_days' => $this->maxDeliveryDays,
        ];
    }
}
