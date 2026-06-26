<?php

namespace App\Services\Shipping\MondialRelay;

use App\Models\OrderShipment;
use App\Models\ShippingCarrier;
use App\Services\Shipping\Contracts\CarrierProviderInterface;
use App\Services\Shipping\DTO\PickupPointData;
use App\Services\Shipping\DTO\ShipmentData;
use Illuminate\Support\Arr;
use RuntimeException;

class MondialRelayProvider implements CarrierProviderInterface
{
    public function __construct(private MondialRelayClient $client) {}
    public function code(): string { return 'mondial_relay'; }

    public function searchPickupPoints(ShippingCarrier $carrier, array $criteria): array
    {
        $payload = $this->client->call('WSI4_PointRelais_Recherche', [
            'Pays' => strtoupper((string) ($criteria['country'] ?? 'FR')),
            'NumPointRelais' => '', 'Ville' => (string) ($criteria['city'] ?? ''),
            'CP' => (string) ($criteria['postal_code'] ?? ''), 'Latitude' => '', 'Longitude' => '',
            'Taille' => '', 'Poids' => (string) max(0, (int) ($criteria['weight_grams'] ?? 0)),
            'Action' => (string) ($criteria['service_code'] ?? '24R'), 'DelaiEnvoi' => '0',
            'RayonRecherche' => (string) min(100, max(1, (int) ($criteria['radius_km'] ?? 20))),
            'TypeActivite' => '', 'NACE' => '', 'NombreResultats' => (string) min(30, max(1, (int) ($criteria['limit'] ?? 10))),
        ], $this->credentials($carrier));

        $this->assertSuccess($payload);
        $points = Arr::get($payload, 'PointsRelais.PointRelais_Details', []);
        if (isset($points['Num'])) { $points = [$points]; }

        return collect($points)->map(fn (array $point) => $this->pickupPoint($point, $carrier->code))->all();
    }

    public function createShipment(OrderShipment $shipment): ShipmentData
    {
        $shipment->loadMissing(['order.addresses', 'method', 'pickupPoint', 'carrier']);
        $order = $shipment->order;
        $address = $order->addresses->firstWhere('type', 'shipping');
        if (! $address) { throw new RuntimeException('Shipping address is missing.'); }
        $sender = $this->sender($shipment->carrier);
        $this->assertSenderIsComplete($sender);
        $pickup = $shipment->pickupPoint;

        $payload = $this->client->call('WSI2_CreationExpedition', [
            'ModeCol' => 'CCC', 'ModeLiv' => $shipment->method->service_code, 'NDossier' => $order->order_number, 'NClient' => (string) $order->user_id,
            'Expe_Langage' => 'FR', 'Expe_Ad1' => (string) ($sender['name'] ?? ''), 'Expe_Ad2' => '', 'Expe_Ad3' => (string) ($sender['address'] ?? ''), 'Expe_Ad4' => (string) ($sender['address_2'] ?? ''),
            'Expe_Ville' => (string) ($sender['city'] ?? ''), 'Expe_CP' => (string) ($sender['postal_code'] ?? ''), 'Expe_Pays' => (string) ($sender['country'] ?? 'FR'), 'Expe_Tel1' => (string) ($sender['phone'] ?? ''), 'Expe_Tel2' => '', 'Expe_Mail' => (string) ($sender['email'] ?? ''),
            'Dest_Langage' => strtoupper($order->customer_locale ?: 'FR'), 'Dest_Ad1' => $address->recipient_name, 'Dest_Ad2' => (string) $address->company, 'Dest_Ad3' => $address->street_line_1, 'Dest_Ad4' => (string) $address->street_line_2,
            'Dest_Ville' => $address->city, 'Dest_CP' => $address->postal_code, 'Dest_Pays' => $address->country_code, 'Dest_Tel1' => (string) ($address->phone ?: $order->customer_phone), 'Dest_Tel2' => '', 'Dest_Mail' => $order->customer_email,
            'Poids' => (string) $order->items->sum(fn ($item) => ((int) $item->weight_grams) * $item->quantity), 'Longueur' => '', 'Taille' => '', 'NbColis' => '1', 'CRT_Valeur' => '0', 'CRT_Devise' => '', 'Exp_Valeur' => '0', 'Exp_Devise' => '',
            'COL_Rel_Pays' => '', 'COL_Rel' => '', 'LIV_Rel_Pays' => $pickup?->country ?? '', 'LIV_Rel' => $pickup?->external_id ?? '', 'TAvisage' => '', 'TReprise' => '', 'Montage' => '', 'TRDV' => '', 'Assurance' => '', 'Instructions' => '',
        ], $this->credentials($shipment->carrier));
        $this->assertSuccess($payload, 'WSI2_CreationExpedition');

        return new ShipmentData((string) ($payload['ExpeditionNum'] ?? ''), (string) ($payload['ExpeditionNum'] ?? ''), $payload['URL_Etiquette'] ?? null, 'created', $payload);
    }

    public function downloadLabel(OrderShipment $shipment): string
    {
        $url = data_get($shipment->raw_payload, 'URL_Etiquette');
        if (! is_string($url) || $url === '') { throw new RuntimeException('Mondial Relay did not provide a label URL.'); }
        return $this->client->download(str_starts_with($url, 'http') ? $url : 'https://www.mondialrelay.com'.$url);
    }

    public function tracking(OrderShipment $shipment): array
    {
        return $this->trackShipmentNumber($shipment->carrier, (string) $shipment->external_shipment_id, 'FR');
    }

    public function test(ShippingCarrier $carrier): array
    {
        $points = $this->searchPickupPoints($carrier, ['country' => 'FR', 'postal_code' => '75001', 'weight_grams' => 1000, 'limit' => 1]);
        return ['ok' => true, 'message' => 'Mondial Relay connection succeeded.', 'points_found' => count($points)];
    }

    public function searchPostalCodes(ShippingCarrier $carrier, string $country, string $postalCode = '', string $city = '', int $limit = 10): array
    {
        $payload = $this->client->call('WSI2_RechercheCP', [
            'Pays' => strtoupper($country),
            'CP' => $postalCode,
            'Ville' => $city,
            'NombreResultats' => (string) min(30, max(1, $limit)),
        ], $this->credentials($carrier));
        $this->assertSuccess($payload, 'WSI2_RechercheCP');

        return $payload;
    }

    public function pickupPointAddress(ShippingCarrier $carrier, string $number, string $country = 'FR'): array
    {
        $payload = $this->client->call('WSI2_AdressePointRelais', [
            'Pays' => strtoupper($country),
            'NumPointRelais' => $number,
        ], $this->credentials($carrier));
        $this->assertSuccess($payload, 'WSI2_AdressePointRelais');

        return $payload;
    }

    public function pickupPointDetail(ShippingCarrier $carrier, string $number, string $country = 'FR'): array
    {
        $payload = $this->client->call('WSI2_DetailPointRelais', [
            'Pays' => strtoupper($country),
            'NumPointRelais' => $number,
        ], $this->credentials($carrier));
        $this->assertSuccess($payload, 'WSI2_DetailPointRelais');

        return $payload;
    }

    public function pickupPointHours(ShippingCarrier $carrier, string $number, string $country = 'FR'): array
    {
        $payload = $this->client->call('WSI2_RecherchePointRelaisHoraires', [
            'Pays' => strtoupper($country),
            'NumPointRelais' => $number,
        ], $this->credentials($carrier));
        $this->assertSuccess($payload, 'WSI2_RecherchePointRelaisHoraires');

        return $payload;
    }

    public function trackShipmentNumber(ShippingCarrier $carrier, string $trackingNumber, string $language = 'FR'): array
    {
        $payload = $this->client->call('WSI2_TracingColisDetaille', [
            'Expedition' => $trackingNumber,
            'Langue' => strtoupper($language),
        ], $this->credentials($carrier));
        $this->assertSuccess($payload, 'WSI2_TracingColisDetaille');

        return $payload;
    }

    public function labelStatus(ShippingCarrier $carrier, string $trackingNumber): array
    {
        $payload = $this->client->call('WSI2_STAT_Label', [
            'Expedition' => $trackingNumber,
        ], $this->credentials($carrier));
        $this->assertSuccess($payload, 'WSI2_STAT_Label');

        return $payload;
    }

    public function labelsStatus(ShippingCarrier $carrier, array $trackingNumbers): array
    {
        $payload = $this->client->call('WSI2_STAT_Labels', [
            'Expeditions' => implode(';', array_filter(array_map('strval', $trackingNumbers))),
        ], $this->credentials($carrier));
        $this->assertSuccess($payload, 'WSI2_STAT_Labels');

        return $payload;
    }

    public function getLabels(ShippingCarrier $carrier, array $trackingNumbers, string $format = 'PDF'): array
    {
        $payload = $this->client->call('WSI3_GetEtiquettes', [
            'Expeditions' => implode(';', array_filter(array_map('strval', $trackingNumbers))),
            'Langue' => 'FR',
            'Format' => strtoupper($format),
        ], $this->credentials($carrier));
        $this->assertSuccess($payload, 'WSI3_GetEtiquettes');

        return $payload;
    }

    private function credentials(ShippingCarrier $carrier): array
    {
        return array_filter([...config('shipping.mondial_relay'), ...($carrier->credentials ?? [])], fn ($value) => ! is_array($value) && $value !== null);
    }

    private function sender(ShippingCarrier $carrier): array
    {
        $configured = config('shipping.mondial_relay.sender');
        $credentials = $carrier->credentials ?? [];

        return [
            'name' => $credentials['sender_name'] ?? $configured['name'] ?? null,
            'address' => $credentials['sender_address'] ?? $configured['address'] ?? null,
            'address_2' => $credentials['sender_address_2'] ?? $configured['address_2'] ?? null,
            'postal_code' => $credentials['sender_postal_code'] ?? $configured['postal_code'] ?? null,
            'city' => $credentials['sender_city'] ?? $configured['city'] ?? null,
            'country' => strtoupper((string) ($credentials['sender_country'] ?? $configured['country'] ?? 'FR')),
            'phone' => $credentials['sender_phone'] ?? $configured['phone'] ?? null,
            'email' => $credentials['sender_email'] ?? $configured['email'] ?? null,
        ];
    }

    private function assertSenderIsComplete(array $sender): void
    {
        $missing = collect(['name', 'address', 'postal_code', 'city', 'country', 'phone', 'email'])
            ->filter(fn (string $key) => blank($sender[$key] ?? null))
            ->values()
            ->all();

        if ($missing !== []) {
            throw new RuntimeException('Mondial Relay sender configuration is incomplete. Missing: '.implode(', ', $missing).'. Configure it in Admin > Livraison > Mondial Relay.');
        }
    }

    private function assertSuccess(array $payload, ?string $operation = null): void
    {
        $status = (string) ($payload['STAT'] ?? $payload['Stat'] ?? '');
        if ($status !== '' && $status !== '0' && $status !== '00') {
            $operationName = $operation ?: 'request';
            $meaning = $this->statusMeaning($status);
            $details = $meaning ? ": {$meaning}" : '';
            throw new MondialRelayRejectedException(
                $operationName,
                $status,
                $payload,
                "Mondial Relay rejected {$operationName} (status {$status}{$details}).",
            );
        }
    }

    private function statusMeaning(string $status): ?string
    {
        return [
            '95' => 'account, contract or credentials are not authorized for this operation; verify the Enseigne/private key and ask Mondial Relay to activate shipment/label webservice access for this account',
        ][$status] ?? null;
    }

    private function pickupPoint(array $point, string $carrierCode): PickupPointData
    {
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $hours = collect($days)->mapWithKeys(fn (string $day, int $index) => ["day_".($index + 1) => $point["Horaires_{$day}"] ?? []])->all();
        return new PickupPointData(
            $this->stringValue($point['Num'] ?? null), $carrierCode,
            str_contains(strtoupper($this->stringValue($point['TypeActivite'] ?? null)), 'LOCKER') ? 'locker' : 'pickup_point',
            strtoupper($this->stringValue($point['Pays'] ?? null, 'FR')), $this->stringValue($point['LgAdr1'] ?? $point['Nom'] ?? null, 'Point Relais'),
            $this->stringValue($point['LgAdr3'] ?? null), $this->stringValue($point['LgAdr4'] ?? null) ?: null,
            $this->stringValue($point['CP'] ?? null), $this->stringValue($point['Ville'] ?? null),
            $this->coordinate($point['Latitude'] ?? null), $this->coordinate($point['Longitude'] ?? null), $hours,
            isset($point['Distance']) ? (int) $point['Distance'] : null, $point,
        );
    }

    private function coordinate(mixed $value): ?float { if ($value === null || $value === '') return null; return (float) str_replace(',', '.', (string) $value); }
    private function stringValue(mixed $value, string $default = ''): string { return is_scalar($value) ? trim((string) $value) : $default; }
}
