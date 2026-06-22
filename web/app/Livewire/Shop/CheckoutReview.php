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

    public string $carrier = 'mondial_relay_pickup';

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
            $this->checkoutError = $this->locale === 'fr'
                ? 'Votre panier est vide.'
                : 'Your cart is empty.';

            return;
        }

        if (! $this->user) {
            $this->checkoutError = $this->locale === 'fr'
                ? 'Connectez-vous pour continuer.'
                : 'Sign in to continue.';

            return;
        }

        if (! $this->token()) {
            $this->checkoutError = $this->locale === 'fr'
                ? 'Session expirée. Reconnectez-vous pour continuer.'
                : 'Session expired. Sign in again to continue.';

            return;
        }

        if (! $this->selectedAddressId) {
            $this->checkoutError = $this->locale === 'fr'
                ? 'Sélectionnez une adresse de livraison.'
                : 'Select a delivery address.';

            return;
        }

        if (! array_key_exists($this->carrier, $this->availableCarrierOptions())) {
            $this->checkoutError = $this->locale === 'fr'
                ? 'Sélectionnez un transporteur disponible.'
                : 'Select an available carrier.';

            return;
        }

        if ($this->delivery === 'relay' && ! $this->selectedPickupPointDetails()) {
            $this->checkoutError = $this->locale === 'fr'
                ? 'Choisissez un point relais sur la liste ou sur la carte avant de confirmer.'
                : 'Choose a pickup point from the list or map before confirming.';

            return;
        }

        $response = $api->createOrder($this->token(), $this->checkoutPayload());

        if (! $response['ok']) {
            $this->checkoutError = $this->firstApiError($response)
                ?: ($this->locale === 'fr'
                    ? 'Impossible de créer la commande pour le moment.'
                    : 'Unable to create the order right now.');

            return;
        }

        $this->confirmedOrder = $response['data'];
        $this->quote = [];
        $this->orderConfirmed = true;
        $this->clearCartState();
        $this->dispatch('checkout-confirmed');
    }

    public function editReview(): void
    {
        $this->orderConfirmed = false;
    }

    public function updatedDelivery(string $value): void
    {
        if ($value === 'relay') {
            $this->carrier = 'mondial_relay_pickup';
            $this->refreshPickupPoints();
            $this->refreshQuote();

            return;
        }

        $this->carrier = 'chronopost_home';
        $this->selectedPickupPoint = null;
        $this->pickupPointOptions = [];
        $this->pickupPointError = null;
        $this->refreshQuote();
    }

    public function updatedCarrier(string $value): void
    {
        $option = $this->carrierOptions()[$value] ?? null;

        if (! $option) {
            return;
        }

        $this->delivery = $option['type'] === 'relay' ? 'relay' : 'home';

        if ($this->delivery === 'relay') {
            $this->refreshPickupPoints();
        } else {
            $this->selectedPickupPoint = null;
            $this->pickupPointOptions = [];
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
            'carriers' => $this->availableCarrierOptions(),
            'selectedCarrier' => $this->carrierOptions()[$this->carrier] ?? null,
            'pickupPoints' => $this->pickupPoints(),
            'selectedPickupPointDetails' => $this->selectedPickupPointDetails(),
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
                ?: ($this->locale === 'fr'
                    ? 'Le devis de livraison et TVA est indisponible.'
                    : 'Delivery and VAT quote is unavailable.');

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
            $this->pickupPointOptions = $this->fallbackPickupPoints();
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
                ?: ($this->locale === 'fr'
                    ? 'La recherche des points relais est indisponible, affichage des relais de secours.'
                    : 'Pickup search is unavailable, fallback pickup points are displayed.');
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
            'formatted_shipping' => $this->locale === 'fr' ? 'À calculer' : 'To calculate',
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
        $defaultAddress = collect($this->addresses)->firstWhere('is_default', true)
            ?: collect($this->addresses)->first();

        return $defaultAddress['id'] ?? null;
    }

    private function availableCarrierOptions(): array
    {
        return collect($this->carrierOptions())
            ->filter(fn (array $option) => $this->delivery === 'relay'
                ? $option['type'] === 'relay'
                : $option['type'] === 'home')
            ->all();
    }

    private function selectedPickupPointDetails(): ?array
    {
        if ($this->delivery !== 'relay' || ! $this->selectedPickupPoint) {
            return null;
        }

        return collect($this->allPickupPoints())->firstWhere('code', $this->selectedPickupPoint);
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
        return $this->pickupPointOptions ?: $this->fallbackPickupPoints();
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
        $points = $this->fallbackPickupPoints();
        $query = trim(mb_strtolower($this->pickupQuery));

        if ($query === '') {
            return $points;
        }

        return collect($points)
            ->filter(fn (array $point) => str_contains(mb_strtolower($point['name'].' '.$point['address'].' '.$point['carrier']), $query))
            ->values()
            ->all();
    }

    private function fallbackPickupPoints(): array
    {
        return $this->locale === 'fr'
            ? [
                [
                    'code' => 'mr-paris-oberkampf',
                    'carrier_code' => 'mondial_relay_pickup',
                    'provider' => 'mondial_relay',
                    'carrier' => 'Mondial Relay',
                    'type' => 'pickup',
                    'name' => 'Commerce partenaire - Oberkampf',
                    'address' => '12 rue Oberkampf, 75011 Paris',
                    'hours' => 'Lun-Sam 09:00-19:30',
                    'distance' => '450 m',
                    'distance_meters' => 450,
                    'latitude' => 48.86512,
                    'longitude' => 2.37764,
                    'map_x' => 22,
                    'map_y' => 42,
                ],
                [
                    'code' => 'mr-locker-voltaire',
                    'carrier_code' => 'mondial_relay_pickup',
                    'provider' => 'mondial_relay',
                    'carrier' => 'Mondial Relay',
                    'type' => 'locker',
                    'name' => 'Locker centre-ville',
                    'address' => '24 boulevard Voltaire, 75011 Paris',
                    'hours' => 'Ouvert 7j/7',
                    'distance' => '1,1 km',
                    'distance_meters' => 1100,
                    'latitude' => 48.85756,
                    'longitude' => 2.38133,
                    'map_x' => 68,
                    'map_y' => 31,
                ],
                [
                    'code' => 'chrono-poste-paris-11',
                    'carrier_code' => 'chrono_relais_pickup',
                    'provider' => 'chrono_relais',
                    'carrier' => 'Chrono Relais',
                    'type' => 'pickup',
                    'name' => 'Bureau de poste Paris 11',
                    'address' => '6 avenue de la République, 75011 Paris',
                    'hours' => 'Lun-Ven 08:30-18:00',
                    'distance' => '730 m',
                    'distance_meters' => 730,
                    'latitude' => 48.86588,
                    'longitude' => 2.36741,
                    'map_x' => 53,
                    'map_y' => 63,
                ],
            ]
            : [
                [
                    'code' => 'mr-paris-oberkampf',
                    'carrier_code' => 'mondial_relay_pickup',
                    'provider' => 'mondial_relay',
                    'carrier' => 'Mondial Relay',
                    'type' => 'pickup',
                    'name' => 'Partner shop - Oberkampf',
                    'address' => '12 rue Oberkampf, 75011 Paris',
                    'hours' => 'Mon-Sat 09:00-19:30',
                    'distance' => '450 m',
                    'distance_meters' => 450,
                    'latitude' => 48.86512,
                    'longitude' => 2.37764,
                    'map_x' => 22,
                    'map_y' => 42,
                ],
                [
                    'code' => 'mr-locker-voltaire',
                    'carrier_code' => 'mondial_relay_pickup',
                    'provider' => 'mondial_relay',
                    'carrier' => 'Mondial Relay',
                    'type' => 'locker',
                    'name' => 'City center locker',
                    'address' => '24 boulevard Voltaire, 75011 Paris',
                    'hours' => 'Open 7 days/week',
                    'distance' => '1.1 km',
                    'distance_meters' => 1100,
                    'latitude' => 48.85756,
                    'longitude' => 2.38133,
                    'map_x' => 68,
                    'map_y' => 31,
                ],
                [
                    'code' => 'chrono-poste-paris-11',
                    'carrier_code' => 'chrono_relais_pickup',
                    'provider' => 'chrono_relais',
                    'carrier' => 'Chrono Relais',
                    'type' => 'pickup',
                    'name' => 'Paris 11 post office',
                    'address' => '6 avenue de la République, 75011 Paris',
                    'hours' => 'Mon-Fri 08:30-18:00',
                    'distance' => '730 m',
                    'distance_meters' => 730,
                    'latitude' => 48.86588,
                    'longitude' => 2.36741,
                    'map_x' => 53,
                    'map_y' => 63,
                ],
            ];
    }

    private function carrierOptions(): array
    {
        if ($this->locale === 'fr') {
            return [
                'mondial_relay_pickup' => [
                    'name' => 'Mondial Relay',
                    'type' => 'relay',
                    'eta' => '3 à 5 jours ouvrés',
                    'price' => 'Calcul à venir',
                    'description' => 'Point relais ou locker. Le client choisit le relais depuis la liste ou directement sur la carte.',
                ],
                'chrono_relais_pickup' => [
                    'name' => 'Chrono Relais',
                    'type' => 'relay',
                    'eta' => '24 à 72 h ouvrées',
                    'price' => 'Calcul à venir',
                    'description' => 'Relais Pickup Chronopost, sélectionnable depuis la même carte.',
                ],
                'chronopost_home' => [
                    'name' => 'Chronopost domicile',
                    'type' => 'home',
                    'eta' => '24 à 48 h ouvrées',
                    'price' => 'Calcul à venir',
                    'description' => 'Livraison à domicile sur l’adresse client sélectionnée.',
                ],
            ];
        }

        return [
            'mondial_relay_pickup' => [
                'name' => 'Mondial Relay',
                'type' => 'relay',
                'eta' => '3 to 5 business days',
                'price' => 'To be calculated',
                'description' => 'Pickup point or locker. The customer picks it from the list or directly on the map.',
            ],
            'chrono_relais_pickup' => [
                'name' => 'Chrono Relais',
                'type' => 'relay',
                'eta' => '24 to 72 business hours',
                'price' => 'To be calculated',
                'description' => 'Chronopost Pickup relay selectable from the same map.',
            ],
            'chronopost_home' => [
                'name' => 'Chronopost home',
                'type' => 'home',
                'eta' => '24 to 48 business hours',
                'price' => 'To be calculated',
                'description' => 'Home delivery to the selected customer address.',
            ],
        ];
    }
}
