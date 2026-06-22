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
        $carrier = (string) ($data['carrier'] ?? 'mondial_relay_locker');
        $query = trim((string) ($data['query'] ?? ''));
        $address = $this->address($user, (int) $data['shipping_address_id']);
        $center = $this->mapCenter($address);

        $points = collect($this->catalog($address, $locale))
            ->when($carrier !== '', fn (Collection $items) => $this->filterByCarrier($items, $carrier))
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
            ->sortBy('distance_meters')
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

    private function filterByCarrier(Collection $items, string $carrier): Collection
    {
        return $items->filter(function (array $point) use ($carrier) {
            if ($carrier === 'mondial_relay_locker') {
                return ($point['provider'] ?? null) === 'mondial_relay' && ($point['type'] ?? null) === 'locker';
            }

            if ($carrier === 'mondial_relay_pickup') {
                return ($point['provider'] ?? null) === 'mondial_relay' && ($point['type'] ?? null) === 'pickup';
            }

            if ($carrier === 'chrono_relais_pickup') {
                return ($point['provider'] ?? null) === 'chrono_relais';
            }

            return ($point['carrier_code'] ?? null) === $carrier;
        });
    }

    private function catalog(UserAddress $address, string $locale): array
    {
        $postalCode = preg_replace('/\D+/', '', (string) $address->postal_code);
        $city = trim((string) $address->city) ?: ($locale === 'fr' ? 'votre ville' : 'your city');
        $country = strtoupper((string) ($address->country_code ?: 'FR'));

        if (str_starts_with($postalCode, '51')) {
            return $this->chalonsCatalog($locale);
        }

        if (str_starts_with($postalCode, '23')) {
            return $this->gouzonCatalog($locale);
        }

        if (str_starts_with($postalCode, '75')) {
            return $this->parisCatalog($locale);
        }

        return $this->genericCatalog($locale, $city, $country);
    }

    private function chalonsCatalog(string $locale): array
    {
        return [
            $this->point('mr-locker-bricomarche-chalons', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'Locker 24/7 Bricomarché Chalons', '4 rue Anne Josephe de Mericourt', '51000', 'Châlons-en-Champagne', 'FR', 'Ouvert 24/7', 350, 48.9639, 4.3630, 38, 22, 'locker'),
            $this->point('mr-locker-match-chalons', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'Locker 24/7 Match Chalons', '1B avenue du Général Sarrail', '51000', 'Châlons-en-Champagne', 'FR', 'Ouvert 24/7', 650, 48.9579, 4.3663, 50, 61, 'locker'),
            $this->point('mr-locker-aldi-planchette', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'Locker 24/7 Aldi', '2 rue de la Planchette', '51000', 'Châlons-en-Champagne', 'FR', 'Ouvert 24/7', 1200, 48.9516, 4.3300, 20, 72, 'locker'),
            $this->point('mr-locker-carrefour-contact', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'Locker 24/7 Carrefour Contact', '34 avenue de Sainte-Menehould', '51000', 'Châlons-en-Champagne', 'FR', 'Ouvert 24/7', 1500, 48.9549, 4.3775, 61, 61, 'locker'),
            $this->point('mr-locker-laverie-ursulines', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'Locker laverie des Ursulines', '20 rue André Hubert', '51000', 'Châlons-en-Champagne', 'FR', 'Ouvert 24/7', 1700, 48.9510, 4.3692, 58, 76, 'locker'),
            $this->point('mr-locker-lidl-chalons', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'Locker 24/7 Lidl Chalons', '4 rue Romain Rolland', '51000', 'Châlons-en-Champagne', 'FR', 'Ouvert 24/7', 2100, 48.9484, 4.3540, 39, 86, 'locker'),
            $this->point('mr-locker-aldi-saint-memmie', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'Locker 24/7 Aldi Saint Memmie', '11 avenue Marc Hamet', '51470', 'Saint-Memmie', 'FR', 'Ouvert 24/7', 2900, 48.9518, 4.4149, 88, 66, 'locker'),
            $this->point('mr-point-centre-chalons', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'Tabac presse du centre', '8 rue de la Marne', '51000', 'Châlons-en-Champagne', 'FR', 'Lun-Sam 09:00-19:00', 950, 48.9574, 4.3636, 48, 50),
            $this->point('mr-point-superette-chalons', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'Supérette Saint-Jean', '12 rue Saint-Jean', '51000', 'Châlons-en-Champagne', 'FR', 'Lun-Sam 08:30-20:00', 1400, 48.9551, 4.3528, 34, 66),
            $this->point('chrono-relais-chalons-poste', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', 'Chrono Relais - Bureau de poste', '2 place de la République', '51000', 'Châlons-en-Champagne', 'FR', 'Lun-Ven 08:30-18:00', 1100, 48.9567, 4.3626, 46, 56),
        ];
    }

    private function gouzonCatalog(string $locale): array
    {
        return [
            $this->point('chrono-carrefour-gouzon', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', 'Carrefour Market Gouzon', '15 avenue du Berry', '23230', 'Gouzon', 'FR', 'Lun-Sam 09:00-19:00', 3900, 46.1915, 2.2382, 73, 22),
            $this->point('chrono-france-rurale-gouzon', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', 'France rurale', 'Bellevue', '23230', 'Gouzon', 'FR', 'Lun-Sam 09:00-18:30', 4300, 46.1887, 2.2324, 43, 52),
            $this->point('chrono-epicurien-parsac', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', "Relais l'épicurien", '6 rue Eugène Parry', '23140', 'Parsac-Rimondeix', 'FR', 'Lun-Sam 08:30-19:00', 5500, 46.2031, 2.1679, 34, 62),
            $this->point('chrono-tikki-soumans', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', "Le Tikki's", '22 rue des Acacias', '23600', 'Soumans', 'FR', 'Lun-Sam 08:00-19:00', 7600, 46.3070, 2.3047, 52, 32),
            $this->point('mr-gouzon-locker', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'Locker centre Gouzon', 'Route de Montluçon', '23230', 'Gouzon', 'FR', 'Ouvert 24/7', 4200, 46.1921, 2.2371, 48, 48, 'locker'),
            $this->point('mr-gouzon-point', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'Relais partenaire Gouzon', 'Centre-ville', '23230', 'Gouzon', 'FR', 'Lun-Sam 09:00-19:00', 4100, 46.1910, 2.2360, 55, 58),
        ];
    }

    private function parisCatalog(string $locale): array
    {
        return [
            $this->point('mr-paris-oberkampf', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', $locale === 'fr' ? 'Commerce partenaire - Oberkampf' : 'Partner shop - Oberkampf', '12 rue Oberkampf', '75011', 'Paris', 'FR', $locale === 'fr' ? 'Lun-Sam 09:00-19:30' : 'Mon-Sat 09:00-19:30', 450, 48.86512, 2.37764, 22, 42),
            $this->point('mr-locker-voltaire', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', $locale === 'fr' ? 'Locker centre-ville' : 'City center locker', '24 boulevard Voltaire', '75011', 'Paris', 'FR', $locale === 'fr' ? 'Ouvert 7j/7' : 'Open 7 days/week', 1100, 48.85756, 2.38133, 68, 31, 'locker'),
            $this->point('mr-relais-republique', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', $locale === 'fr' ? 'Relais République' : 'République pickup', '6 avenue de la République', '75011', 'Paris', 'FR', $locale === 'fr' ? 'Lun-Ven 08:30-18:00' : 'Mon-Fri 08:30-18:00', 700, 48.86739, 2.36358, 46, 56),
            $this->point('chrono-poste-paris-11', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', $locale === 'fr' ? 'Bureau de poste Paris 11' : 'Paris 11 post office', '6 avenue de la République', '75011', 'Paris', 'FR', $locale === 'fr' ? 'Lun-Ven 08:30-18:00' : 'Mon-Fri 08:30-18:00', 730, 48.86588, 2.36741, 53, 63),
        ];
    }

    private function genericCatalog(string $locale, string $city, string $country): array
    {
        return [
            $this->point('mr-local-locker', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', $locale === 'fr' ? 'Locker '.$city : $city.' locker', 'Zone commerciale', '', $city, $country, $locale === 'fr' ? 'Ouvert 7j/7 selon disponibilité' : 'Open 7 days/week depending on availability', 850, 48.8576, 2.3622, 35, 34, 'locker'),
            $this->point('mr-local-main', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', $locale === 'fr' ? 'Relais partenaire - '.$city : 'Partner pickup - '.$city, 'Centre-ville', '', $city, $country, $locale === 'fr' ? 'Horaires selon relais' : 'Hours depend on pickup point', 650, 48.8566, 2.3522, 52, 50),
            $this->point('chrono-local-pickup', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', $locale === 'fr' ? 'Relais Pickup - '.$city : 'Pickup relay - '.$city, 'Agence partenaire', '', $city, $country, $locale === 'fr' ? 'Horaires selon relais' : 'Hours depend on pickup point', 900, 48.8520, 2.3450, 70, 66),
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

        if (str_starts_with($postalCode, '51')) {
            return ['latitude' => 48.9567, 'longitude' => 4.3642, 'zoom' => 13];
        }

        if (str_starts_with($postalCode, '23')) {
            return ['latitude' => 46.1910, 'longitude' => 2.2360, 'zoom' => 11];
        }

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
