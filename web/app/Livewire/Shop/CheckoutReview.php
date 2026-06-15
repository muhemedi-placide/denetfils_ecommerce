<?php

namespace App\Livewire\Shop;

use App\Livewire\Shop\Concerns\InteractsWithCart;
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

    public ?string $selectedPickupPoint = 'mr-paris-11';

    public string $pickupQuery = '';

    public bool $orderConfirmed = false;

    public ?string $checkoutError = null;

    public function mount(string $locale, ?array $user = null, array $addresses = [], array $countries = []): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->user = $user;
        $this->addresses = $addresses;
        $this->countries = $countries;
        $this->selectedAddressId = $this->defaultAddressId();
        $this->initializeCart();
    }

    #[On('cart:changed')]
    public function syncCart(?string $token = null): void
    {
        $this->restoreCart($token ?: $this->cartToken);
    }

    public function confirm(): void
    {
        $this->checkoutError = null;

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
                ? 'Choisissez un point relais avant de confirmer.'
                : 'Choose a pickup point before confirming.';

            return;
        }

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
            $this->selectedPickupPoint = $this->selectedPickupPoint ?: 'mr-paris-11';

            return;
        }

        $this->carrier = 'chronopost_home';
        $this->selectedPickupPoint = null;
    }

    public function updatedCarrier(string $value): void
    {
        $option = $this->carrierOptions()[$value] ?? null;

        if (! $option) {
            return;
        }

        $this->delivery = $option['type'] === 'relay' ? 'relay' : 'home';
        $this->selectedPickupPoint = $this->delivery === 'relay'
            ? ($this->selectedPickupPoint ?: 'mr-paris-11')
            : null;
    }

    public function selectPickupPoint(string $code): void
    {
        if (collect($this->pickupPoints())->contains('code', $code)) {
            $this->selectedPickupPoint = $code;
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
        ]);
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

        return collect($this->pickupPoints())->firstWhere('code', $this->selectedPickupPoint);
    }

    private function pickupPoints(): array
    {
        if ($this->delivery !== 'relay') {
            return [];
        }

        $points = $this->locale === 'fr'
            ? [
                [
                    'code' => 'mr-paris-11',
                    'carrier' => 'Mondial Relay',
                    'name' => 'Commerce partenaire - Paris 11',
                    'address' => '12 rue Oberkampf, 75011 Paris',
                    'hours' => 'Lun-Sam 09:00-19:30',
                    'distance' => '450 m',
                ],
                [
                    'code' => 'chrono-pickup-poste',
                    'carrier' => 'Chrono Relais',
                    'name' => 'Relais Pickup - Bureau de poste',
                    'address' => '6 avenue de la Republique, 75011 Paris',
                    'hours' => 'Lun-Ven 08:30-18:00',
                    'distance' => '700 m',
                ],
                [
                    'code' => 'mr-locker-centre',
                    'carrier' => 'Mondial Relay',
                    'name' => 'Locker centre-ville',
                    'address' => '24 boulevard Voltaire, 75011 Paris',
                    'hours' => 'Ouvert 7j/7',
                    'distance' => '1,1 km',
                ],
            ]
            : [
                [
                    'code' => 'mr-paris-11',
                    'carrier' => 'Mondial Relay',
                    'name' => 'Partner shop - Paris 11',
                    'address' => '12 rue Oberkampf, 75011 Paris',
                    'hours' => 'Mon-Sat 09:00-19:30',
                    'distance' => '450 m',
                ],
                [
                    'code' => 'chrono-pickup-poste',
                    'carrier' => 'Chrono Relais',
                    'name' => 'Pickup relay - Post office',
                    'address' => '6 avenue de la Republique, 75011 Paris',
                    'hours' => 'Mon-Fri 08:30-18:00',
                    'distance' => '700 m',
                ],
                [
                    'code' => 'mr-locker-centre',
                    'carrier' => 'Mondial Relay',
                    'name' => 'City center locker',
                    'address' => '24 boulevard Voltaire, 75011 Paris',
                    'hours' => 'Open 7 days/week',
                    'distance' => '1.1 km',
                ],
            ];

        $query = trim(mb_strtolower($this->pickupQuery));

        if ($query === '') {
            return $points;
        }

        return collect($points)
            ->filter(fn (array $point) => str_contains(mb_strtolower($point['name'] . ' ' . $point['address'] . ' ' . $point['carrier']), $query))
            ->values()
            ->all();
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
                    'description' => 'Point relais ou locker, prêt pour la recherche de relais et les étiquettes Mondial Relay.',
                ],
                'chrono_relais_pickup' => [
                    'name' => 'Chrono Relais',
                    'type' => 'relay',
                    'eta' => '24 à 72 h ouvrées',
                    'price' => 'Calcul à venir',
                    'description' => 'Relais Pickup Chronopost, prévu pour la sélection de relais et le suivi colis.',
                ],
                'chronopost_home' => [
                    'name' => 'Chronopost domicile',
                    'type' => 'home',
                    'eta' => '24 à 48 h ouvrées',
                    'price' => 'Calcul à venir',
                    'description' => 'Option domicile gardée pour évoluer sans refaire le checkout.',
                ],
            ];
        }

        return [
            'mondial_relay_pickup' => [
                'name' => 'Mondial Relay',
                'type' => 'relay',
                'eta' => '3 to 5 business days',
                'price' => 'To be calculated',
                'description' => 'Pickup point or locker, ready for Mondial Relay pickup search and labels.',
            ],
            'chrono_relais_pickup' => [
                'name' => 'Chrono Relais',
                'type' => 'relay',
                'eta' => '24 to 72 business hours',
                'price' => 'To be calculated',
                'description' => 'Chronopost Pickup relay, ready for relay selection and parcel tracking.',
            ],
            'chronopost_home' => [
                'name' => 'Chronopost home',
                'type' => 'home',
                'eta' => '24 to 48 business hours',
                'price' => 'To be calculated',
                'description' => 'Home option kept so checkout can evolve without being rebuilt.',
            ],
        ];
    }
}
