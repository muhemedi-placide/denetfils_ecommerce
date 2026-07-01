<?php

namespace App\Livewire\Shop;

use App\Livewire\Shop\Concerns\InteractsWithCart;
use Livewire\Attributes\On;
use Livewire\Component;

class CartPage extends Component
{
    use InteractsWithCart;

    public string $locale = 'fr';

    public array $recommendedProducts = [];
    public string $countryCode = 'FR';
    public array $estimate = [];
    public ?string $estimateError = null;
    public ?string $recoveryToken = null;
    public ?string $recoveryUrl = null;
    public ?string $recoveryExpiresAt = null;
    public ?string $recoveryMessage = null;
    public bool $recoveredFromLink = false;

    public function mount(
        string $locale,
        array $recommendedProducts = [],
        string $countryCode = 'FR',
        ?string $recoveryToken = null,
    ): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->recommendedProducts = $recommendedProducts;
        $this->countryCode = strtoupper($countryCode);
        $this->initializeCart();
        $this->recoveryToken = $recoveryToken;

        if ($recoveryToken) {
            $response = $this->cartApi()->recoverCart($recoveryToken, $this->locale);

            if ($response['ok']) {
                $this->setCartFromResponse($response['data']);
                $this->recoveredFromLink = true;
                $this->recoveryMessage = $this->locale === 'fr'
                    ? 'Panier restauré. Vous pouvez continuer vos achats.'
                    : 'Cart restored. You can continue shopping.';
                $this->refreshEstimate();
            } else {
                $this->cartError = $this->locale === 'fr'
                    ? 'Ce lien de récupération est invalide ou a expiré.'
                    : 'This recovery link is invalid or has expired.';
            }
        }
    }

    public function restoreFromBrowser(?string $token): void
    {
        $this->restoreCart($token);
        $this->refreshEstimate();
    }

    #[On('cart:changed')]
    public function syncCart(?string $token = null): void
    {
        $this->restoreCart($token ?: $this->cartToken);
        $this->refreshEstimate();
    }

    public function incrementItem(int $itemId, int $quantity): void
    {
        $this->updateCartLine($itemId, $quantity + 1);
        $this->refreshEstimate();
    }

    public function decrementItem(int $itemId, int $quantity): void
    {
        $this->updateCartLine($itemId, $quantity - 1);
        $this->refreshEstimate();
    }

    public function updateItem(int $itemId, mixed $quantity): void
    {
        $this->updateCartLine($itemId, (int) $quantity);
        $this->refreshEstimate();
    }

    public function removeItem(int $itemId): void
    {
        $this->removeCartLine($itemId);
        $this->refreshEstimate();
    }

    public function addRecommended(int $productId): void
    {
        $this->addProductToCart($productId);
        $this->refreshEstimate();
    }

    public function createRecoveryLink(): void
    {
        $this->recoveryMessage = null;

        if (! $this->cartToken || empty($this->cartItems())) {
            $this->recoveryMessage = $this->locale === 'fr'
                ? 'Ajoutez au moins un produit avant de créer un lien.'
                : 'Add at least one product before creating a link.';

            return;
        }

        $response = $this->cartApi()->createCartRecoveryLink($this->cartToken, $this->locale);

        if (! $response['ok'] || empty($response['data']['token'])) {
            $this->recoveryMessage = $response['message'] ?: ($this->locale === 'fr'
                ? 'Impossible de créer le lien pour le moment.'
                : 'Unable to create the link right now.');

            return;
        }

        $this->recoveryUrl = route('cart.recover', [
            'locale' => $this->locale,
            'recoveryToken' => $response['data']['token'],
        ]);
        $this->recoveryExpiresAt = $response['data']['expires_at'] ?? null;
        $this->recoveryMessage = $this->locale === 'fr'
            ? 'Lien sécurisé créé. Copiez-le pour partager ou rappeler ce panier.'
            : 'Secure link created. Copy it to share or remind someone about this cart.';
    }

    public function render()
    {
        return view('livewire.shop.cart-page');
    }

    #[On('cart:cleared')]
    public function clearEstimate(): void
    {
        $this->estimate = [];
        $this->estimateError = null;
    }

    private function refreshEstimate(): void
    {
        $this->estimate = [];
        $this->estimateError = null;

        if (! $this->cartToken || empty($this->cartItems())) {
            return;
        }

        $response = app(\App\Services\ShopApiClient::class)
            ->estimateCart($this->cartToken, $this->locale, $this->countryCode);

        if (! $response['ok']) {
            $this->estimateError = $this->locale === 'fr'
                ? 'Estimation TVA et livraison indisponible.'
                : 'VAT and delivery estimate unavailable.';
            return;
        }

        $this->estimate = $response['data'];
    }
}
