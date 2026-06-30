<?php

namespace App\Livewire\Shop;

use App\Livewire\Shop\Concerns\InteractsWithCart;
use App\Services\AccountApiClient;
use App\Services\ShopApiClient;
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
    public string $carrier = '';
    public ?string $selectedPickupPoint = null;
    public string $pickupQuery = '';
    public array $pickupPointOptions = [];
    public array $pickupMapCenter = ['latitude' => 48.8627, 'longitude' => 2.3726, 'zoom' => 13];
    public ?string $pickupPointError = null;
    public bool $orderConfirmed = false;
    public ?string $checkoutError = null;
    public ?string $quoteError = null;
    public ?string $shippingMethodError = null;
    public ?string $paymentError = null;
    public bool $paymentProcessing = false;
    public string $paymentProvider = 'stripe';
    public array $quote = [];
    public array $stripePaymentIntent = [];
    public array $paypalOrder = [];
    public array $shippingMethods = [];
    public ?int $selectedShippingMethodId = null;
    public ?array $confirmedOrder = null;
    public bool $authModalOpen = false;
    public string $authMode = 'choice';
    public string $loginEmail = '';
    public string $loginPassword = '';
    public string $firstName = '';
    public string $lastName = '';
    public string $checkoutEmail = '';
    public string $checkoutPhone = '';
    public string $checkoutPassword = '';
    public string $checkoutPasswordConfirmation = '';
    public string $streetLine1 = '';
    public string $streetLine2 = '';
    public string $postalCode = '';
    public string $city = '';
    public string $countryCode = 'FR';
    public string $visitorCountryCode = 'FR';

    public function mount(
        string $locale,
        ?array $user = null,
        array $addresses = [],
        array $countries = [],
        string $visitorCountryCode = 'FR',
    ): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->user = $user;
        $this->addresses = $addresses;
        $this->countries = $countries;
        $this->visitorCountryCode = strtoupper($visitorCountryCode);
        $this->countryCode = collect($countries)->contains('code', $this->visitorCountryCode)
            ? $this->visitorCountryCode
            : 'FR';
        $this->selectedAddressId = $this->defaultAddressId();
        $this->initializeCart();
        $this->refreshShippingMethods();
        $this->refreshPickupPoints();
    }

    public function restoreFromBrowser(?string $token): void
    {
        $this->restoreCart($token);
        $this->refreshShippingMethods();
        $this->refreshPickupPoints();
        $this->refreshQuote();
    }

    #[On('cart:changed')]
    public function syncCart(?string $token = null): void
    {
        $this->restoreCart($token ?: $this->cartToken);
        $this->refreshShippingMethods();
        $this->refreshPickupPoints();
        $this->refreshQuote();
    }

    public function confirm(AccountApiClient $api): void
    {
        $this->checkoutError = null;
        $this->quoteError = null;
        $this->paymentError = null;
        $this->paymentProvider = 'stripe';
        $this->stripePaymentIntent = [];
        $this->paypalOrder = [];

        if (empty($this->cartItems())) {
            $this->checkoutError = $this->locale === 'fr' ? 'Votre panier est vide.' : 'Your cart is empty.';
            return;
        }

        if (! $this->user) {
            $this->openAuthModal();
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
        $this->prepareStripePayment($api);
    }

    public function openAuthModal(string $mode = 'choice'): void
    {
        $this->resetValidation();
        $this->authMode = in_array($mode, ['choice', 'login', 'register'], true) ? $mode : 'choice';
        $this->authModalOpen = true;
    }

    public function closeAuthModal(): void
    {
        $this->authModalOpen = false;
        $this->resetValidation();
    }

    public function loginInline(AccountApiClient $api): void
    {
        $validated = $this->validate([
            'loginEmail' => ['required', 'email:rfc', 'max:255'],
            'loginPassword' => ['required', 'string'],
        ]);

        $response = $api->login([
            'email' => $validated['loginEmail'],
            'password' => $validated['loginPassword'],
            'device_name' => \Illuminate\Support\Str::slug(config('shop.name')).'-checkout',
        ]);

        if (! $response['ok']) {
            $this->applyApiErrors($response, 'loginEmail');
            return;
        }

        $token = $response['data']['token'] ?? null;
        session()->regenerate();
        session()->put('customer_api_token', $token);
        session()->put('customer_user', $response['data']['user'] ?? []);
        $addresses = $token ? $api->addresses($token) : ['ok' => false, 'data' => []];
        $this->finishInlineAuthentication($response['data']['user'] ?? [], $addresses['ok'] ? $addresses['data'] : []);
    }

    public function registerInline(AccountApiClient $api): void
    {
        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['required', 'string', 'max:120'],
            'checkoutEmail' => ['required', 'email:rfc', 'max:255'],
            'checkoutPhone' => ['nullable', 'string', 'max:32'],
            'checkoutPassword' => ['required', 'string', 'min:8', 'same:checkoutPasswordConfirmation'],
            'checkoutPasswordConfirmation' => ['required', 'string', 'min:8'],
            'streetLine1' => ['required', 'string', 'max:255'],
            'streetLine2' => ['nullable', 'string', 'max:255'],
            'postalCode' => ['required', 'string', 'max:32'],
            'city' => ['required', 'string', 'max:120'],
            'countryCode' => ['required', 'string', 'size:2'],
        ]);

        $response = $api->register([
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['checkoutEmail'],
            'phone' => $validated['checkoutPhone'],
            'password' => $validated['checkoutPassword'],
            'password_confirmation' => $validated['checkoutPasswordConfirmation'],
            'country_code' => $validated['countryCode'],
            'preferred_locale' => $this->locale,
            'timezone' => 'Europe/Paris',
            'privacy_policy_consent' => true,
            'terms_consent' => true,
            'marketing_consent' => false,
        ]);

        if (! $response['ok']) {
            $this->applyApiErrors($response, 'checkoutEmail');
            return;
        }

        $token = $response['data']['token'] ?? null;
        session()->regenerate();
        session()->put('customer_api_token', $token);
        session()->put('customer_user', $response['data']['user'] ?? []);

        $addressResponse = $token ? $api->createAddress($token, [
            'type' => 'shipping',
            'label' => $this->locale === 'fr' ? 'Domicile' : 'Home',
            'recipient_name' => trim($validated['firstName'].' '.$validated['lastName']),
            'street_line_1' => $validated['streetLine1'],
            'street_line_2' => $validated['streetLine2'],
            'postal_code' => $validated['postalCode'],
            'city' => $validated['city'],
            'country_code' => $validated['countryCode'],
            'phone' => $validated['checkoutPhone'],
            'is_default' => true,
        ]) : ['ok' => false, 'data' => []];

        if (! $addressResponse['ok']) {
            $this->applyApiErrors($addressResponse, 'streetLine1');
            return;
        }

        $this->finishInlineAuthentication($response['data']['user'] ?? [], [$addressResponse['data']]);
    }

    public function selectPaymentProvider(string $provider, AccountApiClient $api): void
    {
        if (! in_array($provider, ['stripe', 'paypal'], true)) {
            return;
        }

        $this->paymentProvider = $provider;
        $this->paymentError = null;

        if ($provider === 'stripe') {
            $this->prepareStripePayment($api);
            return;
        }

        $this->preparePaypalPayment($api);
    }

    public function retryStripePayment(AccountApiClient $api): void
    {
        $this->paymentError = null;
        $this->paymentProvider = 'stripe';

        if (! $this->confirmedOrder || ! $this->token()) {
            $this->paymentError = $this->locale === 'fr'
                ? 'La commande doit etre creee avant le paiement.'
                : 'The order must be created before payment.';
            return;
        }

        $this->prepareStripePayment($api);
    }

    public function retryPaypalPayment(AccountApiClient $api): void
    {
        $this->paymentError = null;
        $this->paymentProvider = 'paypal';

        if (! $this->confirmedOrder || ! $this->token()) {
            $this->paymentError = $this->locale === 'fr'
                ? 'La commande doit etre creee avant le paiement.'
                : 'The order must be created before payment.';
            return;
        }

        $this->preparePaypalPayment($api);
    }

    public function completeStripePayment(string $paymentIntentId, AccountApiClient $api): void
    {
        $orderId = data_get($this->confirmedOrder, 'id');

        if (! $orderId || ! $this->token()) {
            $this->failStripePayment($this->locale === 'fr'
                ? 'Impossible de confirmer ce paiement Stripe.'
                : 'Unable to confirm this Stripe payment.');
            return;
        }

        $this->paymentProcessing = true;
        $response = $api->confirmStripePaymentIntent($this->token(), $orderId, $paymentIntentId);

        if (! $response['ok']) {
            $this->failStripePayment($this->firstApiError($response)
                ?: ($this->locale === 'fr' ? 'Stripe na pas confirme le paiement.' : 'Stripe did not confirm the payment.'));
            return;
        }

        $order = $response['data']['order'] ?? [];

        if (($order['payment_status'] ?? null) !== 'paid') {
            $this->failStripePayment($this->locale === 'fr'
                ? 'Le paiement Stripe nest pas encore finalise.'
                : 'Stripe payment is not finalized yet.');
            return;
        }

        $this->confirmedOrder = array_replace($this->confirmedOrder, $order);
        $this->stripePaymentIntent = $response['data'];
        $this->paymentProcessing = false;
        $this->orderConfirmed = true;
        $this->clearCartState();
        $this->dispatch('checkout-confirmed');
    }

    public function failStripePayment(?string $message = null): void
    {
        $this->paymentProcessing = false;
        $this->paymentError = $message ?: ($this->locale === 'fr'
            ? 'Le paiement a echoue. Verifiez la carte ou reessayez.'
            : 'Payment failed. Check the card or try again.');
    }

    public function capturePaypalPayment(string $paypalOrderId, AccountApiClient $api): void
    {
        $orderId = data_get($this->confirmedOrder, 'id');

        if (! $orderId || ! $this->token()) {
            $this->failPaypalPayment($this->locale === 'fr'
                ? 'Impossible de finaliser ce paiement PayPal.'
                : 'Unable to complete this PayPal payment.');
            return;
        }

        $this->paymentProcessing = true;
        $response = $api->capturePaypalOrder($this->token(), $orderId, $paypalOrderId);

        if (! $response['ok']) {
            $this->failPaypalPayment($this->firstApiError($response)
                ?: ($this->locale === 'fr' ? 'PayPal a refuse ou interrompu le paiement.' : 'PayPal refused or interrupted the payment.'));
            return;
        }

        $this->paypalOrder = $response['data'];
        $this->completePaypalPayment((string) ($this->paypalOrder['status'] ?? 'COMPLETED'));
    }

    public function completePaypalPayment(string $status = 'COMPLETED'): void
    {
        if (! $this->confirmedOrder || ! in_array(strtoupper($status), ['COMPLETED', 'APPROVED'], true)) {
            return;
        }

        $this->paymentProcessing = false;
        $this->orderConfirmed = true;
        $this->clearCartState();
        $this->dispatch('checkout-confirmed');
    }

    public function failPaypalPayment(?string $message = null): void
    {
        $this->paymentProcessing = false;
        $this->paymentError = $message ?: ($this->locale === 'fr'
            ? 'Le paiement PayPal a echoue. Reessayez ou choisissez la carte bancaire.'
            : 'PayPal payment failed. Try again or choose card payment.');
    }

    public function updatedCarrier(string $value): void
    {
        $option = $this->carrierOptions()[$value] ?? null;

        if (! $option) {
            return;
        }

        $this->delivery = $option['type'] === 'home' ? 'home' : 'relay';
        $method = collect($this->shippingMethods)->firstWhere('method_code', $value);
        $this->selectedShippingMethodId = isset($method['method_id']) ? (int) $method['method_id'] : null;

        if ($this->delivery === 'relay') {
            $this->pickupQuery = '';
            $this->selectedPickupPoint = null;
            $this->refreshPickupPoints();
        } else {
            $this->selectedPickupPoint = null;
            $this->pickupPointOptions = [];
            $this->pickupPointError = null;
            $this->persistShippingSelection();
        }

        $this->refreshQuote();
    }

    public function updatedSelectedAddressId(): void
    {
        $this->pickupQuery = '';
        $this->selectedPickupPoint = null;
        $this->refreshShippingMethods();
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
            $this->persistShippingSelection();
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
            'pickupMapCenter' => $this->pickupMapCenter,
            'displayQuote' => $this->displayQuote(),
        ]);
    }

    private function refreshQuote(): void
    {
        $this->quoteError = null;

        if (! $this->cartToken || empty($this->cartItems())) {
            $this->quote = [];
            return;
        }

        if (! $this->token() || ! $this->selectedAddressId) {
            $response = app(ShopApiClient::class)->estimateCart(
                $this->cartToken,
                $this->locale,
                $this->visitorCountryCode,
            );

            if (! $response['ok']) {
                $this->quote = [];
                $this->quoteError = $this->locale === 'fr'
                    ? 'L’estimation TVA et livraison est indisponible.'
                    : 'VAT and delivery estimate is unavailable.';
                return;
            }

            $this->quote = $response['data'];
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

    private function refreshShippingMethods(): void
    {
        $this->shippingMethodError = null;

        if (! $this->token() || ! $this->cartToken || ! $this->selectedAddressId) {
            $this->shippingMethods = [];
            $this->selectedShippingMethodId = null;
            if ($this->token() && $this->selectedAddressId && ! $this->cartToken) {
                $this->shippingMethodError = $this->locale === 'fr'
                    ? 'Le panier doit être restauré avant de charger les transporteurs.'
                    : 'The cart must be restored before loading carriers.';
            }
            return;
        }

        $response = app(AccountApiClient::class)->shippingMethods($this->token(), [
            'cart_token' => $this->cartToken,
            'shipping_address_id' => (int) $this->selectedAddressId,
            'locale' => $this->locale,
        ]);
        if (! $response['ok']) {
            $this->shippingMethods = [];
            $this->selectedShippingMethodId = null;
            $this->carrier = '';
            $this->shippingMethodError = $this->firstApiError($response)
                ?: ($this->locale === 'fr' ? 'Impossible de charger les transporteurs pour cette adresse.' : 'Unable to load carriers for this address.');
            return;
        }

        $this->shippingMethods = is_array($response['data']) ? $response['data'] : [];
        $method = collect($this->shippingMethods)->firstWhere('method_code', $this->carrier)
            ?: collect($this->shippingMethods)->first();
        $this->selectedShippingMethodId = isset($method['method_id']) ? (int) $method['method_id'] : null;
        $this->carrier = (string) ($method['method_code'] ?? '');
        $this->delivery = ($method['delivery_type'] ?? null) === 'home' ? 'home' : 'relay';
        if ($this->shippingMethods === []) {
            $this->shippingMethodError = $this->locale === 'fr'
                ? 'Aucun transporteur actif avec tarif n’est disponible pour cette adresse et ce panier.'
                : 'No active carrier with a rate is available for this address and cart.';
            $this->pickupPointError = $this->shippingMethodError;
        } elseif ($this->delivery === 'home') {
            $this->persistShippingSelection();
        }
    }

    private function refreshPickupPoints(): void
    {
        if ($this->delivery !== 'relay') {
            $this->pickupPointOptions = [];
            $this->pickupPointError = null;
            return;
        }

        if (! $this->token() || ! $this->selectedAddressId) {
            $this->pickupPointOptions = [];
            $this->selectedPickupPoint = null;
            return;
        }

        if (! $this->selectedShippingMethodId) {
            $this->pickupPointOptions = [];
            $this->selectedPickupPoint = null;
            $this->pickupPointError = $this->locale === 'fr' ? 'Sélectionnez un mode de livraison disponible.' : 'Select an available delivery method.';
            return;
        }

        $search = trim($this->pickupQuery);
        $response = app(AccountApiClient::class)->shippingPickupPoints($this->token(), array_filter([
            'cart_token' => $this->cartToken,
            'shipping_method_id' => $this->selectedShippingMethodId,
            'shipping_address_id' => (int) $this->selectedAddressId,
            'postal_code' => $search !== '' && preg_match('/^[0-9 -]+$/', $search) ? $search : null,
            'city' => $search !== '' && ! preg_match('/^[0-9 -]+$/', $search) ? $search : null,
            'limit' => 15,
        ], fn ($value) => $value !== null && $value !== ''));

        if (! $response['ok']) {
            $this->pickupPointOptions = [];
            $this->selectedPickupPoint = null;
            $this->pickupPointError = $this->firstApiError($response)
                ?: ($this->locale === 'fr' ? 'La recherche Mondial Relay est indisponible. Aucun point statique n’est affiché.' : 'Mondial Relay search is unavailable. No static pickup points are displayed.');
            return;
        }

        $this->pickupPointError = null;
        $this->pickupPointOptions = $response['data']['points'] ?? [];
        $this->pickupMapCenter = $response['data']['center'] ?? $this->pickupMapCenter;
        $this->ensureSelectedPickupPointExists();
        $this->persistShippingSelection();
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

        if ($this->selectedShippingMethodId) {
            $payload['shipping_method_id'] = $this->selectedShippingMethodId;
        }

        if ($this->delivery === 'relay' && $this->selectedPickupPointDetails()) {
            $payload['metadata']['pickup_point'] = $this->selectedPickupPointDetails();
            if (isset($payload['metadata']['pickup_point']['id'])) {
                $payload['pickup_point_id'] = (int) $payload['metadata']['pickup_point']['id'];
            }
        }

        return $payload;
    }

    private function prepareStripePayment(AccountApiClient $api): void
    {
        $orderId = data_get($this->confirmedOrder, 'id');

        if (! $orderId || ! $this->token()) {
            $this->paymentError = $this->locale === 'fr'
                ? 'Impossible de preparer le paiement de cette commande.'
                : 'Unable to prepare payment for this order.';
            return;
        }

        $response = $api->createStripePaymentIntent($this->token(), $orderId);

        if (! $response['ok']) {
            $this->paymentError = $this->firstApiError($response)
                ?: ($this->locale === 'fr' ? 'Stripe est indisponible pour le moment.' : 'Stripe is unavailable right now.');
            return;
        }

        $this->stripePaymentIntent = $response['data'];
        $this->paymentProcessing = false;
        $this->dispatch('stripe-payment-ready', payment: $this->stripePaymentIntent);
    }

    private function preparePaypalPayment(AccountApiClient $api): void
    {
        $orderId = data_get($this->confirmedOrder, 'id');

        if (! $orderId || ! $this->token()) {
            $this->paymentError = $this->locale === 'fr'
                ? 'Impossible de preparer le paiement PayPal pour cette commande.'
                : 'Unable to prepare PayPal payment for this order.';
            return;
        }

        $response = $api->createPaypalOrder($this->token(), $orderId);

        if (! $response['ok']) {
            $this->paymentError = $this->firstApiError($response)
                ?: ($this->locale === 'fr' ? 'PayPal est indisponible pour le moment.' : 'PayPal is unavailable right now.');
            return;
        }

        $this->paypalOrder = $response['data'];
        $this->paymentProcessing = false;
        $this->dispatch('paypal-payment-ready', payment: $this->paypalOrder);
    }

    private function deliveryMethodForApi(): string
    {
        return $this->delivery === 'relay' ? 'relay' : 'standard';
    }

    private function persistShippingSelection(): void
    {
        $point = $this->selectedPickupPointDetails();
        $method = collect($this->shippingMethods)->firstWhere('method_id', $this->selectedShippingMethodId);
        $requiresPickupPoint = (bool) ($method['requires_pickup_point'] ?? false);
        if (! $this->token() || ! $this->cartToken || ! $this->selectedAddressId || ! $this->selectedShippingMethodId) return;
        if ($requiresPickupPoint && empty($point['id'])) return;
        $payload = [
            'cart_token' => $this->cartToken, 'shipping_method_id' => $this->selectedShippingMethodId,
            'shipping_address_id' => (int) $this->selectedAddressId, 'locale' => $this->locale,
        ];
        if (! empty($point['id'])) $payload['pickup_point_id'] = (int) $point['id'];
        $response = app(AccountApiClient::class)->selectShipping($this->token(), $payload);
        if (! $response['ok']) $this->pickupPointError = $this->firstApiError($response);
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

    private function finishInlineAuthentication(array $user, array $addresses): void
    {
        $this->user = $user;
        $this->addresses = $addresses;
        $this->selectedAddressId = $this->defaultAddressId();
        $this->authModalOpen = false;
        $this->authMode = 'choice';
        $this->checkoutError = null;
        $this->refreshShippingMethods();
        $this->refreshPickupPoints();
        $this->refreshQuote();
    }

    private function applyApiErrors(array $response, string $fallbackField): void
    {
        if (! empty($response['errors']) && is_array($response['errors'])) {
            foreach ($response['errors'] as $field => $messages) {
                $property = match ($field) {
                    'email' => $fallbackField,
                    'first_name' => 'firstName',
                    'last_name' => 'lastName',
                    'phone' => 'checkoutPhone',
                    'password' => 'checkoutPassword',
                    'street_line_1' => 'streetLine1',
                    'street_line_2' => 'streetLine2',
                    'postal_code' => 'postalCode',
                    'country_code' => 'countryCode',
                    default => $field,
                };
                foreach ((array) $messages as $message) {
                    $this->addError($property, $message);
                }
            }
            return;
        }

        $this->addError($fallbackField, $response['message'] ?: __('home.account.api_error'));
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

    private function pickupPoints(): array
    {
        if ($this->delivery !== 'relay') {
            return [];
        }

        return $this->pickupPointOptions;
    }

    private function allPickupPoints(): array
    {
        return $this->pickupPointOptions;
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

    private function carrierOptions(): array
    {
        return collect($this->shippingMethods)->mapWithKeys(function (array $method) {
            $min = $method['min_delivery_days'] ?? null;
            $max = $method['max_delivery_days'] ?? null;
            $eta = $min !== null && $max !== null
                ? ($this->locale === 'fr' ? "Livraison estimée sous {$min} à {$max} jours ouvrés." : "Estimated delivery in {$min} to {$max} business days.")
                : ($this->locale === 'fr' ? 'Délai communiqué par le transporteur.' : 'Timing provided by the carrier.');
            $carrierCode = (string) ($method['carrier_code'] ?? '');
            $deliveryType = (string) ($method['delivery_type'] ?? '');
            $typeLabel = match ($deliveryType) {
                'locker' => 'Locker',
                'home' => $this->locale === 'fr' ? 'Domicile' : 'Home',
                default => $this->locale === 'fr' ? 'Point relais' : 'Pickup point',
            };
            $brandLabel = match ($carrierCode) {
                'mondial_relay' => 'Mondial Relay',
                'chronopost' => 'Chronopost',
                default => $carrierCode !== '' ? str_replace('_', ' ', $carrierCode) : ($this->locale === 'fr' ? 'Transporteur' : 'Carrier'),
            };

            return [(string) $method['method_code'] => [
                'name' => $method['name'],
                'type' => $deliveryType === 'home' ? 'home' : 'relay',
                'delivery_type' => $deliveryType,
                'type_label' => $typeLabel,
                'brand_label' => $brandLabel,
                'brand' => $carrierCode,
                'logo' => $carrierCode === 'mondial_relay' ? 'mr' : ($carrierCode === 'chronopost' ? 'chrono' : 'generic'),
                'eta' => $eta,
                'price' => number_format(((int) $method['price_cents']) / 100, 2, ',', ' ').' '.($method['currency'] ?? 'EUR'),
                'requires_pickup_point' => (bool) ($method['requires_pickup_point'] ?? false),
                'choose_label' => $this->locale === 'fr' ? 'Choisir ce point relais' : 'Choose this pickup point',
                'panel_title' => $this->locale === 'fr' ? 'Sélectionnez votre point relais' : 'Select your pickup point',
            ]];
        })->all();
    }
}
