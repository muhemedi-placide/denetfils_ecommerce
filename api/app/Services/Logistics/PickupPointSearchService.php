<?php

namespace App\Services\Logistics;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PickupPointSearchService
{
    public function searchForUser(User $user, array $data): array
    {
        $locale = $this->locale($data['locale'] ?? $user->preferred_locale ?? 'fr');
        $carrier = (string) ($data['carrier'] ?? 'mondial_relay_pickup');
        $query = trim((string) ($data['query'] ?? ''));
        $address = $this->address($user, (int) $data['shipping_address_id']);
        $center = $this->mapCenter($address);

        $points = collect($this->catalog($address, $locale))
            ->when($carrier !== '', fn (Collection $items) => $items->filter(
                fn (array $point) => $point['carrier_code'] === $carrier
                    || ($carrier === 'mondial_relay_pickup' && $point['provider'] === 'mondial_relay')
                    || ($carrier === 'chrono_relais_pickup' && $point['provider'] === 'chrono_relais')
            ))
            ->when($query !== '', function (Collection $items) use ($query) {
                $normalized = mb_strtolower($query);

                return $items->filter(function (array $point) use ($normalized) {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $point['name'] ?? null,
                        $point['carrier'] ?? null,
                        $point['address'] ?? null,
                        $point['postal_code'] ?? null,
                        $point['city'] ?? null,
                    ])));

                    return str_contains($haystack, $normalized);
                });
            })
            ->values()
            ->all();

        return [
            'source' => 'local_pickup_catalog',
            'external_call_executed' => false,
            'message' => $locale === 'fr'
                ? 'Catalogue de relais prêt pour le branchement Mondial Relay/Chrono Relais.'
                : 'Pickup catalog ready for Mondial Relay/Chrono Relais connection.',
            'address' => [
                'id' => $address->id,
                'city' => $address->city,
                'postal_code' => $address->postal_code,
                'country_code' => $address->country_code,
            ],
            'carrier' => $carrier,
            'center' => $center,
            'points' => $points,
        ];
    }

    private function address(User $user, int $addressId): UserAddress
    {
        $address = $user->addresses()->whereKey($addressId)->first();

        if (! $address) {
            throw ValidationException::withMessages([
                'shipping_address_id' => 'The selected address is invalid.',
            ]);
        }

        return $address;
    }

    private function catalog(UserAddress $address, string $locale): array
    {
        $postalCode = preg_replace('/\D+/', '', (string) $address->postal_code);
        $isParis = str_starts_with($postalCode, '75');
        $city = trim((string) $address->city) ?: ($locale === 'fr' ? 'votre ville' : 'your city');
        $country = strtoupper((string) ($address->country_code ?: 'FR'));

        if ($isParis || $country === 'FR') {
            return $this->parisCatalog($locale);
        }

        return $this->genericCatalog($locale, $city, $country);
    }

    private function parisCatalog(string $locale): array
    {
        return $locale === 'fr'
            ? [
                $this->point('mr-paris-oberkampf', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'Commerce partenaire - Oberkampf', '12 rue Oberkampf', '75011', 'Paris', 'FR', 'Lun-Sam 09:00-19:30', 450, 48.86512, 2.37764, 22, 42),
                $this->point('mr-locker-voltaire', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'Locker centre-ville', '24 boulevard Voltaire', '75011', 'Paris', 'FR', 'Ouvert 7j/7', 1100, 48.85756, 2.38133, 68, 31, 'locker'),
                $this->point('mr-relais-republique', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'Relais République', '6 avenue de la République', '75011', 'Paris', 'FR', 'Lun-Ven 08:30-18:00', 700, 48.86739, 2.36358, 46, 56),
                $this->point('chrono-poste-paris-11', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', 'Bureau de poste Paris 11', '6 avenue de la République', '75011', 'Paris', 'FR', 'Lun-Ven 08:30-18:00', 730, 48.86588, 2.36741, 53, 63),
            ]
            : [
                $this->point('mr-paris-oberkampf', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'Partner shop - Oberkampf', '12 rue Oberkampf', '75011', 'Paris', 'FR', 'Mon-Sat 09:00-19:30', 450, 48.86512, 2.37764, 22, 42),
                $this->point('mr-locker-voltaire', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'City center locker', '24 boulevard Voltaire', '75011', 'Paris', 'FR', 'Open 7 days/week', 1100, 48.85756, 2.38133, 68, 31, 'locker'),
                $this->point('mr-relais-republique', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'République pickup', '6 avenue de la République', '75011', 'Paris', 'FR', 'Mon-Fri 08:30-18:00', 700, 48.86739, 2.36358, 46, 56),
                $this->point('chrono-poste-paris-11', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', 'Paris 11 post office', '6 avenue de la République', '75011', 'Paris', 'FR', 'Mon-Fri 08:30-18:00', 730, 48.86588, 2.36741, 53, 63),
            ];
    }

    private function genericCatalog(string $locale, string $city, string $country): array
    {
        return [
            $this->point('mr-local-main', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', $locale === 'fr' ? 'Relais partenaire - '.$city : 'Partner pickup - '.$city, 'Centre-ville', '', $city, $country, $locale === 'fr' ? 'Horaires selon relais' : 'Hours depend on pickup point', 650, 48.8566, 2.3522, 32, 44),
            $this->point('mr-local-locker', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', $locale === 'fr' ? 'Locker '.$city : $city.' locker', 'Zone commerciale', '', $city, $country, $locale === 'fr' ? 'Ouvert 7j/7 selon disponibilité' : 'Open 7 days/week depending on availability', 1300, 48.8576, 2.3622, 70, 34, 'locker'),
            $this->point('chrono-local-pickup', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', $locale === 'fr' ? 'Relais Pickup - '.$city : 'Pickup relay - '.$city, 'Agence partenaire', '', $city, $country, $locale === 'fr' ? 'Horaires selon relais' : 'Hours depend on pickup point', 900, 48.8520, 2.3450, 50, 66),
        ];
    }

    private function point(
        string $code,
        string $carrierCode,
        string $provider,
        string $carrier,
        string $name,
        string $street,
        string $postalCode,
        string $city,
        string $countryCode,
        string $hours,
        int $distanceMeters,
        float $latitude,
        float $longitude,
        int $mapX,
        int $mapY,
        string $type = 'pickup'
    ): array {
        $distance = $distanceMeters >= 1000
            ? str_replace('.', ',', number_format($distanceMeters / 1000, 1)).' km'
            : $distanceMeters.' m';

        return [
            'code' => $code,
            'carrier_code' => $carrierCode,
            'provider' => $provider,
            'carrier' => $carrier,
            'type' => $type,
            'name' => $name,
            'address' => trim($street.', '.$postalCode.' '.$city),
            'street' => $street,
            'postal_code' => $postalCode,
            'city' => $city,
            'country_code' => $countryCode,
            'hours' => $hours,
            'distance' => $distance,
            'distance_meters' => $distanceMeters,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'map_x' => max(5, min(95, $mapX)),
            'map_y' => max(5, min(95, $mapY)),
        ];
    }

    private function mapCenter(UserAddress $address): array
    {
        $postalCode = preg_replace('/\D+/', '', (string) $address->postal_code);

        if (str_starts_with($postalCode, '75')) {
            return ['latitude' => 48.8627, 'longitude' => 2.3726, 'zoom' => 13];
        }

        return ['latitude' => 48.8566, 'longitude' => 2.3522, 'zoom' => 12];
    }

    private function locale(string $locale): string
    {
        return in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
    }
}
