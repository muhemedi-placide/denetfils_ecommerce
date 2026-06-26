<?php

namespace App\Services\Shipping\DTO;

final readonly class PickupPointData
{
    public function __construct(
        public string $externalId,
        public string $carrierCode,
        public string $type,
        public string $country,
        public string $name,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $postalCode,
        public string $city,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public array $openingHours = [],
        public ?int $distanceMeters = null,
        public array $rawPayload = [],
    ) {}

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId, 'code' => $this->externalId, 'carrier_code' => $this->carrierCode,
            'type' => $this->type, 'country' => $this->country, 'country_code' => $this->country,
            'name' => $this->name, 'address_line1' => $this->addressLine1, 'address_line2' => $this->addressLine2,
            'address' => trim($this->addressLine1.', '.$this->postalCode.' '.$this->city),
            'postal_code' => $this->postalCode, 'city' => $this->city, 'latitude' => $this->latitude,
            'longitude' => $this->longitude, 'opening_hours' => $this->openingHours,
            'distance_meters' => $this->distanceMeters,
        ];
    }
}
