<?php

namespace App\Livewire\Shop;

use App\Livewire\Shop\Concerns\InteractsWithCart;
use App\Services\AccountApiClient;
use Livewire\Attributes\On;
use Livewire\Component;

class CheckoutReview extends Component
{
    use InteractsWithCart;

    public string $locale = 'fr';
    public ?array $user = null;
    public array $addresses = [];
    public array $countries = [];
    public int|string|null $selectedAddressId = null;
    public string $delivery = 'relay';
    public string $carrier = 'mondial_relay_locker';
    public ?string $selectedPickupPoint = null;
    public string $pickupQuery = '';
    public array $pickupPointOptions = [];
    public array $pickupMapCenter = ['latitude' => 48.8627, 'longitude' => 2.3726, 'zoom' => 13];
    public ?string $pickupPointError = null;
    public bool $orderConfirmed = false;
    public ?string $checkoutError = null;
    public ?string $quoteError = null;
    public array $quote = [];
    public ?array $confirmedOrder = null;

    public function mount(string $locale, ?array $user = null, array $addresses = [], array $countries = []): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->user = $user;
        $this->addresses = $addresses;
        $this->countries = $countries;
        $this->selectedAddressId = $this->defaultAddressId();
        $this->initializeCart();
        $this->refreshPickupPoints();
    }

    public function restoreFromBrowser(?string $token): void
    {
        $this->restoreCart($token);
        $this->refreshPickupPoints();
        $this->refreshQuote();
    }

    #[On('cart:changed')]
    public function syncCart(?string $token = null): void
    {
        $this->restoreCart($token ?: $this->cartToken);
        $this->refreshQuote();
    }

    public function confirm(AccountApiClient $api): void
    {
        $this->checkoutError = null;
        $this->quoteError = null;

        if (empty($this->cartItems())) {
            $this->checkoutError = $this->locale === 'fr' ? 'Votre panier est vide.' : 'Your cart is empty.';
            return;
        }

        if (! $this->user) {
            $this->checkoutError = $this->locale === 'fr' ? 'Connectez-vous pour continuer.' : 'Sign in to continue.';
            return;
        }

        if (! $this->token()) {
            $this->checkoutError = $this->locale === 'fr' ? 'Session expirée. Reconnectez-vous pour continuer.' : 'Session expired. Sign in again to continue.';
            return;
        }

        if (! $this->selectedAddressId) {
            $this->checkoutError = $this->locale === 'fr' ? 'Sélectionnez une adresse de livraison.' : 'Select a delivery address.';
            return;
        }

        if (! array_key_exists($this->carrier, $this->carrierOptions())) {
            $this->checkoutError = $this->locale === 'fr' ? 'Sélectionnez un transporteur disponible.' : 'Select an available carrier.';
            return;
        }

        if ($this->delivery === 'relay' && ! $this->selectedPickupPointDetails()) {
            $this->checkoutError = $this->locale === 'fr'
                ? 'Choisissez un point de retrait avant de confirmer.'
                : 'Choose a pickup point before confirming.';
            return;
        }

        $response = $api->createOrder($this->token(), $this->checkoutPayload());

        if (! $response['ok']) {
            $this->checkoutError = $this->firstApiError($response)
                ?: ($this->locale === 'fr' ? 'Impossible de créer la commande pour le moment.' : 'Unable to create the order right now.');
            return;
        }

        $this->confirmedOrder = $response['data'];
        $this->quote = [];
        $this->orderConfirmed = true;
        $this->clearCartState();
        $this->dispatch('checkout-confirmed');
    }

    public function updatedCarrier(string $value): void
    {
        $option = $this->carrierOptions()[$value] ?? null;

        if (! $option) {
            return;
        }

        $this->delivery = $option['type'] === 'home' ? 'home' : 'relay';

        if ($this->delivery === 'relay') {
            $this->refreshPickupPoints();
        } else {
            $this->selectedPickupPoint = null;
            $this->pickupPointOptions = [];
            $this->pickupPointError = null;
        }

        $this->refreshQuote();
    }

    public function updatedSelectedAddressId(): void
    {
        $this->refreshPickupPoints();
        $this->refreshQuote();
    }

    public function updatedPickupQuery(): void
    {
        $this->refreshPickupPoints();
    }

    public function selectPickupPoint(string $code): void
    {
        if (collect($this->allPickupPoints())->contains('code', $code)) {
            $this->selectedPickupPoint = $code;
            $this->refreshQuote();
        }
    }

    public function render()
    {
        return view('livewire.shop.checkout-review', [
            'isAuthenticated' => ! empty($this->user),
            'countryNames' => collect($this->countries)->pluck('name', 'code'),
            'carriers' => $this->carrierOptions(),
            'selectedCarrier' => $this->carrierOptions()[$this->carrier] ?? null,
            'pickupPoints' => $this->pickupPoints(),
            'selectedPickupPointDetails' => $this->selectedPickupPointDetails(),
            'nearestPickupPoint' => $this->nearestPickupPoint(),
            'pickupMapCenter' => $this->pickupMapCenter,
            'displayQuote' => $this->displayQuote(),
        ]);
    }

    private function refreshQuote(): void
    {
        $this->quoteError = null;

        if (! $this->token() || ! $this->cartToken || empty($this->cartItems()) || ! $this->selectedAddressId) {
            $this->quote = [];
            return;
        }

        $response = app(AccountApiClient::class)->checkoutQuote($this->token(), $this->checkoutPayload());

        if (! $response['ok']) {
            $this->quote = [];
            $this->quoteError = $this->firstApiError($response)
                ?: ($this->locale === 'fr' ? 'Le devis de livraison et TVA est indisponible.' : 'Delivery and VAT quote is unavailable.');
            return;
        }

        $this->quote = $response['data'];
    }

    private function refreshPickupPoints(): void
    {
        if ($this->delivery !== 'relay') {
            $this->pickupPointOptions = [];
            $this->pickupPointError = null;
            return;
        }

        if (! $this->token() || ! $this->selectedAddressId) {
            $this->pickupPointOptions = $this->filteredFallbackPickupPoints();
            $this->ensureSelectedPickupPointExists();
            return;
        }

        $response = app(AccountApiClient::class)->pickupPoints($this->token(), [
            'shipping_address_id' => (int) $this->selectedAddressId,
            'locale' => $this->locale,
            'carrier' => $this->carrier,
            'query' => $this->pickupQuery,
        ]);

        if (! $response['ok']) {
            $this->pickupPointOptions = $this->filteredFallbackPickupPoints();
            $this->pickupPointError = $this->firstApiError($response)
                ?: ($this->locale === 'fr' ? 'Recherche indisponible, affichage des points de secours.' : 'Search unavailable, fallback points are displayed.');
            $this->ensureSelectedPickupPointExists();
            return;
        }

        $this->pickupPointError = null;
        $this->pickupPointOptions = $response['data']['points'] ?? [];
        $this->pickupMapCenter = $response['data']['center'] ?? $this->pickupMapCenter;
        $this->ensureSelectedPickupPointExists();
    }

    private function checkoutPayload(): array
    {
        $payload = [
            'cart_token' => $this->cartToken,
            'shipping_address_id' => (int) $this->selectedAddressId,
            'locale' => $this->locale,
            'delivery_method' => $this->deliveryMethodForApi(),
            'carrier' => $this->carrier,
            'metadata' => [
                'delivery_choice' => [
                    'type' => $this->delivery,
                    'carrier' => $this->carrier,
                    'carrier_label' => $this->carrierOptions()[$this->carrier]['name'] ?? $this->carrier,
                ],
            ],
        ];

        if ($this->delivery === 'relay' && $this->selectedPickupPointDetails()) {
            $payload['metadata']['pickup_point'] = $this->selectedPickupPointDetails();
        }

        return $payload;
    }

    private function deliveryMethodForApi(): string
    {
        return $this->delivery === 'relay' ? 'relay' : 'standard';
    }

    private function displayQuote(): array
    {
        return array_replace([
            'formatted_subtotal' => $this->cart['formatted_subtotal'] ?? $this->formattedTotal(),
            'formatted_shipping' => $this->carrierOptions()[$this->carrier]['price'] ?? ($this->locale === 'fr' ? 'À calculer' : 'To calculate'),
            'formatted_tax' => $this->locale === 'fr' ? 'À calculer' : 'To calculate',
            'formatted_total' => $this->formattedTotal(),
        ], $this->quote);
    }

    private function token(): ?string
    {
        return session()->get('customer_api_token');
    }

    private function firstApiError(array $response): ?string
    {
        if (! empty($response['errors']) && is_array($response['errors'])) {
            foreach ($response['errors'] as $messages) {
                $message = collect((array) $messages)->first();
                if ($message) {
                    return (string) $message;
                }
            }
        }

        return $response['message'] ?: null;
    }

    private function defaultAddressId(): int|string|null
    {
        $defaultAddress = collect($this->addresses)->firstWhere('is_default', true) ?: collect($this->addresses)->first();
        return $defaultAddress['id'] ?? null;
    }

    private function selectedPickupPointDetails(): ?array
    {
        if ($this->delivery !== 'relay' || ! $this->selectedPickupPoint) {
            return null;
        }

        return collect($this->allPickupPoints())->firstWhere('code', $this->selectedPickupPoint);
    }

    private function nearestPickupPoint(): ?array
    {
        return collect($this->pickupPoints())->sortBy('distance_meters')->first();
    }

    private function pickupPoints(): array
    {
        if ($this->delivery !== 'relay') {
            return [];
        }

        return $this->pickupPointOptions ?: $this->filteredFallbackPickupPoints();
    }

    private function allPickupPoints(): array
    {
        return $this->pickupPointOptions ?: $this->filteredFallbackPickupPoints();
    }

    private function ensureSelectedPickupPointExists(): void
    {
        $points = $this->pickupPoints();

        if (empty($points)) {
            $this->selectedPickupPoint = null;
            return;
        }

        if (! $this->selectedPickupPoint || ! collect($points)->contains('code', $this->selectedPickupPoint)) {
            $this->selectedPickupPoint = (string) ($points[0]['code'] ?? '');
        }
    }

    private function filteredFallbackPickupPoints(): array
    {
        $points = collect($this->fallbackPickupPoints())
            ->filter(function (array $point) {
                if ($this->carrier === 'mondial_relay_locker') {
                    return ($point['provider'] ?? null) === 'mondial_relay' && ($point['type'] ?? null) === 'locker';
                }

                if ($this->carrier === 'mondial_relay_pickup') {
                    return ($point['provider'] ?? null) === 'mondial_relay' && ($point['type'] ?? null) === 'pickup';
                }

                if ($this->carrier === 'chrono_relais_pickup') {
                    return ($point['provider'] ?? null) === 'chrono_relais';
                }

                return ($point['carrier_code'] ?? null) === $this->carrier;
            });

        $query = trim(mb_strtolower($this->pickupQuery));

        if ($query !== '') {
            $points = $points->filter(fn (array $point) => str_contains(mb_strtolower($point['name'].' '.$point['address'].' '.$point['carrier']), $query));
        }

        return $points->sortBy('distance_meters')->values()->all();
    }

    private function fallbackPickupPoints(): array
    {
        return [
            $this->pickup('mr-locker-bricomarche-chalons', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'locker', 'Locker 24/7 Bricomarché Chalons', '4 rue Anne Josephe de Mericourt, 51000 - Châlons-en-Champagne', 'Ouvert 24/7', 350, 38, 22),
            $this->pickup('mr-locker-match-chalons', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'locker', 'Locker 24/7 Match Chalons', '1B avenue du Général Sarrail, 51000 - Châlons-en-Champagne', 'Ouvert 24/7', 650, 50, 61),
            $this->pickup('mr-locker-aldi-planchette', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'locker', 'Locker 24/7 Aldi', '2 rue de la Planchette, 51000 - Châlons-en-Champagne', 'Ouvert 24/7', 1200, 20, 72),
            $this->pickup('mr-locker-carrefour-contact', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'locker', 'Locker 24/7 Carrefour Contact', '34 avenue de Sainte-Menehould, 51000 - Châlons-en-Champagne', 'Ouvert 24/7', 1500, 61, 61),
            $this->pickup('mr-locker-laverie-ursulines', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'locker', 'Locker laverie des Ursulines', '20 rue André Hubert, 51000 - Châlons-en-Champagne', 'Ouvert 24/7', 1700, 58, 76),
            $this->pickup('mr-locker-lidl-chalons', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'locker', 'Locker 24/7 Lidl Chalons', '4 rue Romain Rolland, 51000 - Châlons-en-Champagne', 'Ouvert 24/7', 2100, 39, 86),
            $this->pickup('mr-locker-aldi-saint-memmie', 'mondial_relay_locker', 'mondial_relay', 'Mondial Relay', 'locker', 'Locker 24/7 Aldi Saint Memmie', '11 avenue Marc Hamet, 51470 - Saint-Memmie', 'Ouvert 24/7', 2900, 88, 66),
            $this->pickup('mr-point-centre-chalons', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'pickup', 'Tabac presse du centre', '8 rue de la Marne, 51000 - Châlons-en-Champagne', 'Lun-Sam 09:00-19:00', 950, 48, 50),
            $this->pickup('mr-point-superette-chalons', 'mondial_relay_pickup', 'mondial_relay', 'Mondial Relay', 'pickup', 'Supérette Saint-Jean', '12 rue Saint-Jean, 51000 - Châlons-en-Champagne', 'Lun-Sam 08:30-20:00', 1400, 34, 66),
            $this->pickup('chrono-carrefour-gouzon', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', 'pickup', 'Carrefour Market Gouzon', '15 avenue du Berry, 23230 Gouzon', 'Lun-Sam 09:00-19:00', 3900, 73, 22),
            $this->pickup('chrono-france-rurale-gouzon', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', 'pickup', 'France rurale', 'Bellevue, 23230 Gouzon', 'Lun-Sam 09:00-18:30', 4300, 43, 52),
            $this->pickup('chrono-epicurien-parsac', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', 'pickup', "Relais l'épicurien", '6 rue Eugène Parry, 23140 Parsac-Rimondeix', 'Lun-Sam 08:30-19:00', 5500, 34, 62),
            $this->pickup('chrono-tikki-soumans', 'chrono_relais_pickup', 'chrono_relais', 'Chrono Relais', 'pickup', "Le Tikki's", '22 rue des Acacias, 23600 Soumans', 'Lun-Sam 08:00-19:00', 7600, 52, 32),
        ];
    }

    private function pickup(string $code, string $carrierCode, string $provider, string $carrier, string $type, string $name, string $address, string $hours, int $distanceMeters, int $mapX, int $mapY): array
    {
        return [
            'code' => $code,
            'carrier_code' => $carrierCode,
            'provider' => $provider,
            'carrier' => $carrier,
            'type' => $type,
            'name' => $name,
            'address' => $address,
            'hours' => $hours,
            'distance' => $distanceMeters >= 1000 ? str_replace('.', ',', number_format($distanceMeters / 1000, 1)).' km' : $distanceMeters.' m',
            'distance_meters' => $distanceMeters,
            'map_x' => $mapX,
            'map_y' => $mapY,
        ];
    }

    private function carrierOptions(): array
    {
        return $this->locale === 'fr'
            ? [
                'mondial_relay_locker' => [
                    'name' => 'Mondial Relay Locker',
                    'type' => 'relay',
                    'brand' => 'Mondial Relay',
                    'logo' => 'mr',
                    'eta' => 'Délai de 3 à 5 jours à partir de la mise à disposition du colis.',
                    'price' => '2,99 € TTC',
                    'choose_label' => 'Utiliser ce locker',
                    'panel_title' => 'Sélectionnez votre Point Relais® ou Locker - Mondial Relay',
                ],
                'mondial_relay_pickup' => [
                    'name' => 'Mondial Points Relais®',
                    'type' => 'relay',
                    'brand' => 'Mondial Relay',
                    'logo' => 'mr',
                    'eta' => 'Délai de 5 à 7 jours à partir de la mise à disposition du colis.',
                    'price' => '4,19 € TTC',
                    'choose_label' => 'Utiliser ce point relais',
                    'panel_title' => 'Sélectionnez votre Point Relais® - Mondial Relay',
                ],
                'chrono_relais_pickup' => [
                    'name' => 'Chrono Relais',
                    'type' => 'relay',
                    'brand' => 'Chronopost',
                    'logo' => 'chrono',
                    'eta' => '4 à 7 jours',
                    'price' => '6,77 € TTC',
                    'choose_label' => 'Choisir ce point de retrait',
                    'panel_title' => 'Sélectionnez votre point de retrait Chrono Relais',
                ],
                'chronopost_home' => [
                    'name' => 'CHRONOPOST',
                    'type' => 'home',
                    'brand' => 'Chronopost',
                    'logo' => 'chrono',
                    'eta' => '72 Heures',
                    'price' => '12,94 € TTC',
                    'choose_label' => '',
                    'panel_title' => '',
                ],
            ]
            : [
                'mondial_relay_locker' => ['name' => 'Mondial Relay Locker', 'type' => 'relay', 'brand' => 'Mondial Relay', 'logo' => 'mr', 'eta' => '3 to 5 days from parcel availability.', 'price' => '€2.99 incl. VAT', 'choose_label' => 'Use this locker', 'panel_title' => 'Select your Mondial Relay pickup or locker'],
                'mondial_relay_pickup' => ['name' => 'Mondial Pickup Points®', 'type' => 'relay', 'brand' => 'Mondial Relay', 'logo' => 'mr', 'eta' => '5 to 7 days from parcel availability.', 'price' => '€4.19 incl. VAT', 'choose_label' => 'Use this pickup point', 'panel_title' => 'Select your Mondial Relay pickup point'],
                'chrono_relais_pickup' => ['name' => 'Chrono Relais', 'type' => 'relay', 'brand' => 'Chronopost', 'logo' => 'chrono', 'eta' => '4 to 7 days', 'price' => '€6.77 incl. VAT', 'choose_label' => 'Choose this pickup point', 'panel_title' => 'Select your Chrono Relais pickup point'],
                'chronopost_home' => ['name' => 'CHRONOPOST', 'type' => 'home', 'brand' => 'Chronopost', 'logo' => 'chrono', 'eta' => '72 hours', 'price' => '€12.94 incl. VAT', 'choose_label' => '', 'panel_title' => ''],
            ];
    }
}
