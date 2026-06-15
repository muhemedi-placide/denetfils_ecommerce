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

    public string $delivery = 'standard';

    public string $carrier = 'chronopost_home';

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

        if (! array_key_exists($this->carrier, $this->carrierOptions())) {
            $this->checkoutError = $this->locale === 'fr'
                ? 'Sélectionnez un transporteur.'
                : 'Select a carrier.';

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
        $this->carrier = $value === 'relay' ? 'mondial_relay_pickup' : 'chronopost_home';
    }

    public function render()
    {
        return view('livewire.shop.checkout-review', [
            'isAuthenticated' => ! empty($this->user),
            'countryNames' => collect($this->countries)->pluck('name', 'code'),
            'carriers' => $this->carrierOptions(),
            'selectedCarrier' => $this->carrierOptions()[$this->carrier] ?? null,
        ]);
    }

    private function defaultAddressId(): int|string|null
    {
        $defaultAddress = collect($this->addresses)->firstWhere('is_default', true)
            ?: collect($this->addresses)->first();

        return $defaultAddress['id'] ?? null;
    }

    private function carrierOptions(): array
    {
        if ($this->locale === 'fr') {
            return [
                'chronopost_home' => [
                    'name' => 'Chronopost domicile',
                    'type' => 'home',
                    'eta' => '24 à 48 h ouvrées',
                    'price' => 'Calcul à venir',
                    'description' => 'Livraison suivie à domicile, prête pour l’API Chronopost.',
                ],
                'chronopost_pickup' => [
                    'name' => 'Chronopost relais',
                    'type' => 'relay',
                    'eta' => '24 à 72 h ouvrées',
                    'price' => 'Calcul à venir',
                    'description' => 'Sélection de point relais Chronopost avec carte à brancher.',
                ],
                'mondial_relay_pickup' => [
                    'name' => 'Mondial Relay',
                    'type' => 'relay',
                    'eta' => '3 à 5 jours ouvrés',
                    'price' => 'Calcul à venir',
                    'description' => 'Point relais économique, prêt pour l’API Mondial Relay.',
                ],
            ];
        }

        return [
            'chronopost_home' => [
                'name' => 'Chronopost home',
                'type' => 'home',
                'eta' => '24 to 48 business hours',
                'price' => 'To be calculated',
                'description' => 'Tracked home delivery, ready for the Chronopost API.',
            ],
            'chronopost_pickup' => [
                'name' => 'Chronopost pickup',
                'type' => 'relay',
                'eta' => '24 to 72 business hours',
                'price' => 'To be calculated',
                'description' => 'Chronopost pickup selection with map integration ready.',
            ],
            'mondial_relay_pickup' => [
                'name' => 'Mondial Relay',
                'type' => 'relay',
                'eta' => '3 to 5 business days',
                'price' => 'To be calculated',
                'description' => 'Cost-efficient pickup point, ready for the Mondial Relay API.',
            ],
        ];
    }
}
