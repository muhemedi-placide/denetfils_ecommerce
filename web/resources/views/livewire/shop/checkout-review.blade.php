<section class="checkout-compact store-page pb-20 pt-4 lg:pb-12 lg:pt-6" x-data="{ chronoModal: false }">
    <div class="store-container">
        @include('partials.checkout-progress', ['currentLocale' => $locale, 'currentStep' => $orderConfirmed ? 'success' : 'checkout'])

        @if (! $orderConfirmed && ! $confirmedOrder)
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Valider votre commande' : 'Confirm your order' }}</h1>
                <a href="{{ route('cart.show', ['locale' => $locale]) }}" class="btn-secondary w-full sm:w-auto" wire:navigate>
                    {{ $locale === 'fr' ? 'Modifier le panier' : 'Edit cart' }}
                </a>
            </div>

            @if ($checkoutError)
                <div class="mb-4 rounded-xl border border-coral/25 bg-coral/10 px-4 py-3 text-sm font-semibold text-cocoa dark:text-cream">{{ $checkoutError }}</div>
            @endif
            @if ($quoteError)
                <div class="mb-4 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-forest dark:border-white/10 dark:bg-white/5 dark:text-meadow">{{ $quoteError }}</div>
            @endif
            @if (! empty($quote['is_estimate']))
                <div class="mb-4 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-forest dark:border-white/10 dark:bg-white/5 dark:text-meadow">
                    @if (! ($quote['is_supported'] ?? true))
                        {{ $locale === 'fr'
                            ? "La livraison n’est pas disponible pour le pays détecté ({$visitorCountryCode}). Choisissez un pays pris en charge."
                            : "Delivery is unavailable for the detected country ({$visitorCountryCode}). Choose a supported country." }}
                    @else
                        {{ $locale === 'fr' ? 'Estimation pour' : 'Estimate for' }}
                        {{ data_get($quote, 'destination_country.name', $visitorCountryCode) }} —
                        {{ $locale === 'fr' ? 'le montant final sera recalculé avec votre adresse de livraison.' : 'the final amount will be recalculated using your delivery address.' }}
                    @endif
                </div>
            @endif

            <div class="checkout-onepage-grid grid gap-6 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-start">
                <form class="checkout-onepage-form form-card space-y-0 overflow-hidden p-0" wire:submit.prevent="confirm">
                    <section class="checkout-express px-4 py-6 text-center sm:px-7">
                        <p class="text-base font-semibold text-cocoa/65 dark:text-cream/65">{{ $locale === 'fr' ? 'Paiement express' : 'Express checkout' }}</p>
                        <button type="button" class="mx-auto mt-4 flex min-h-[52px] w-full max-w-sm items-center justify-center rounded-xl bg-[#ffc439] px-8 text-2xl font-black italic text-[#003087] transition hover:bg-[#f4b72f] disabled:cursor-wait disabled:opacity-65" wire:click="startPaypalExpressCheckout" wire:loading.attr="disabled" wire:target="startPaypalExpressCheckout">
                            <span wire:loading.remove wire:target="startPaypalExpressCheckout">Pay<span class="text-[#009cde]">Pal</span></span>
                            <span class="text-sm not-italic" wire:loading wire:target="startPaypalExpressCheckout">{{ $locale === 'fr' ? 'Connexion à PayPal…' : 'Connecting to PayPal…' }}</span>
                        </button>
                        <div class="mt-5 flex items-center gap-4 text-sm font-semibold text-cocoa/50 dark:text-cream/50">
                            <span class="h-px flex-1 bg-black/10 dark:bg-white/10"></span>
                            <span>{{ $locale === 'fr' ? 'OU' : 'OR' }}</span>
                            <span class="h-px flex-1 bg-black/10 dark:bg-white/10"></span>
                        </div>
                    </section>

                    <section class="form-card">
                        <div class="flex items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h2 class="text-2xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Contact' : 'Contact' }}</h2>
                                    </div>
                                    @if (! $isAuthenticated)
                                        <div class="grid gap-2 sm:flex">
                                            <button type="button" class="btn-primary px-4 py-2 text-xs" wire:click="openAuthModal('login')">{{ __('home.account.auth.sign_in') }}</button>
                                            <button type="button" class="btn-secondary px-4 py-2 text-xs" wire:click="openAuthModal('register')">{{ __('home.account.auth.create_account') }}</button>
                                        </div>
                                    @endif
                                </div>

                                @if ($isAuthenticated)
                                    <p class="mt-2 truncate text-sm font-semibold text-cocoa/70 dark:text-cream/70">
                                        {{ $user['name'] ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) }} · {{ $user['email'] ?? '' }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </section>

                    <section class="form-card">
                        <div class="flex items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h2 class="text-2xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Livraison' : 'Delivery' }}</h2>
                                    </div>
                                </div>

                                @if ($isAuthenticated && ! empty($addresses))
                                    <div class="mt-4 grid gap-2">
                                        @foreach ($addresses as $address)
                                            <label class="cursor-pointer rounded-xl border border-leaf/10 bg-linen p-3 text-sm transition dark:border-white/10 dark:bg-white/5 {{ (int) $selectedAddressId === (int) $address['id'] ? 'ring-2 ring-leaf/35 dark:ring-meadow/40' : 'hover:border-leaf/25' }}" wire:key="checkout-address-{{ $address['id'] }}">
                                                <span class="flex items-start gap-3">
                                                    <input class="mt-1" type="radio" wire:model.live="selectedAddressId" value="{{ $address['id'] }}">
                                                    <span class="min-w-0">
                                                        <span class="flex flex-wrap items-center gap-2">
                                                            <span class="font-black text-cocoa dark:text-cream">{{ $address['label'] ?: $address['recipient_name'] }}</span>
                                                            @if ($address['is_default'])
                                                                <span class="rounded-full bg-mint px-2 py-0.5 text-[10px] font-black uppercase tracking-wide text-forest dark:bg-white/10 dark:text-meadow">{{ __('home.account.addresses.default_badge') }}</span>
                                                            @endif
                                                        </span>
                                                        <span class="mt-1 block text-xs leading-5 text-cocoa/65 dark:text-cream/65">
                                                            {{ $address['recipient_name'] }} · {{ $address['street_line_1'] }}, {{ $address['postal_code'] }} {{ $address['city'] }} · {{ $countryNames[$address['country_code']] ?? $address['country_code'] }}
                                                        </span>
                                                    </span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($isAuthenticated)
                                    <div class="mt-4 rounded-xl bg-linen px-4 py-3 text-sm font-semibold text-cocoa dark:bg-white/5 dark:text-cream">
                                        {{ $locale === 'fr' ? 'Aucune adresse enregistrée. Ajoutez une adresse depuis votre compte.' : 'No saved address. Add an address from your account.' }}
                                    </div>
                                @else
                                    <div class="mt-4 rounded-xl bg-linen px-4 py-3 text-sm text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                                        {{ $locale === 'fr' ? 'Connectez-vous pour sélectionner une adresse.' : 'Sign in to select an address.' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>

                    <section class="form-card overflow-hidden p-0">
                        <div class="px-4 pb-1 pt-4 sm:px-7">
                            <h2 class="text-2xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Mode de livraison' : 'Shipping method' }}</h2>
                        </div>

                        <div class="space-y-3 p-3 sm:p-4">
                            @forelse ($carriers as $key => $option)
                                <div wire:key="carrier-option-wrapper-{{ $key }}">
                                    <label class="grid cursor-pointer items-center gap-3 rounded-xl bg-neutral-100 px-3 py-3 transition hover:bg-neutral-50 dark:bg-white/5 dark:hover:bg-white/10 sm:grid-cols-[28px_64px_minmax(150px,1fr)_minmax(150px,1fr)_90px]">
                                        <span class="grid h-7 w-7 place-items-center rounded-full border border-cocoa/10 bg-white dark:border-white/20 dark:bg-white/5">
                                            <input class="h-4 w-4 accent-[#f97316]" type="radio" value="{{ $key }}" wire:model.live="carrier">
                                        </span>

                                        <span class="grid h-10 w-14 place-items-center">
                                            @if ($option['logo'] === 'mr')
                                                <span class="grid h-9 w-9 place-items-center rounded-lg bg-[#f7b6cd] text-lg font-black text-[#a01455]">r</span>
                                            @elseif ($option['logo'] === 'chrono')
                                                <span class="text-xs font-black text-[#168bd0]">▣ chronopost</span>
                                            @else
                                                <span class="grid h-12 w-12 place-items-center rounded-xl bg-leaf/10 text-lg font-black uppercase text-leaf">{{ mb_substr($option['name'], 0, 2) }}</span>
                                            @endif
                                        </span>

                                        <span class="font-black leading-5 text-cocoa dark:text-cream">
                                            {{ $option['name'] }}
                                        </span>
                                        <span class="text-base leading-6 text-cocoa/80 dark:text-cream/75">{{ $option['eta'] }}</span>
                                        <span class="text-lg font-semibold text-cocoa dark:text-cream">{{ $option['price'] }}</span>
                                    </label>

                                    @if ($carrier === $key && $option['requires_pickup_point'])
                                        <div class="mt-4">
                                            @include('partials.checkout-pickup-panel', [
                                                'locale' => $locale,
                                                'pickupPoints' => $pickupPoints,
                                                'selectedPickupPoint' => $selectedPickupPoint,
                                                'pickupMapCenter' => $pickupMapCenter,
                                                'panelTitle' => $option['panel_title'],
                                                'buttonLabel' => $option['choose_label'],
                                            ])
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-cocoa/15 bg-linen px-4 py-5 text-sm font-semibold leading-6 text-cocoa/65 dark:border-white/10 dark:bg-white/5 dark:text-cream/65">
                                    {{ $shippingMethodError ?: ($locale === 'fr' ? 'Aucun transporteur n’est affiché pour le moment. Vérifiez que le panier, l’adresse et les tarifs livraison sont disponibles.' : 'No carrier is displayed right now. Check that the cart, address and shipping rates are available.') }}
                                </div>
                            @endforelse

                            <button type="submit" class="store-button disabled:pointer-events-none disabled:opacity-50" wire:loading.attr="disabled" @disabled(count($this->cartItems()) === 0 || ! $isAuthenticated || ! $selectedAddressId || ($delivery === 'relay' && ! $selectedPickupPointDetails))>
                                <span wire:loading.remove>{{ $locale === 'fr' ? 'Créer la commande' : 'Create order' }}</span>
                                <span wire:loading>{{ __('home.cart.loading') }}</span>
                            </button>
                        </div>
                    </section>
                </form>

                <aside class="lg:sticky lg:top-32">
                    <div class="form-card p-4">
                        <h2 class="text-lg font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Votre commande' : 'Your order' }}</h2>

                        <div wire:loading.flex class="mt-3 items-center gap-3 rounded-lg bg-linen px-3 py-2 text-xs font-semibold text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                            <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-coral"></span>{{ __('home.cart.loading') }}
                        </div>

                        @if (count($this->cartItems()) > 0)
                            <div class="mt-3 space-y-2">
                                @foreach ($this->cartItems() as $item)
                                    @php
                                        $productName = data_get($item, 'product.name', '-');
                                    @endphp
                                    <div class="flex items-start justify-between gap-3 border-b border-leaf/10 pb-2 text-sm dark:border-white/10" wire:key="checkout-cart-item-{{ $item['id'] }}">
                                        <div class="min-w-0">
                                                <p class="line-clamp-2 font-black text-cocoa dark:text-cream">{{ $productName }}</p>
                                            <p class="truncate text-xs text-cocoa/55 dark:text-cream/55">{{ $item['quantity'] }} × {{ data_get($item, 'variant.name') ?: data_get($item, 'product.origin') }}</p>
                                        </div>
                                        <strong class="shrink-0 text-forest dark:text-meadow">{{ $item['formatted_line_total'] }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-3 rounded-lg bg-linen px-3 py-3 text-sm text-cocoa/65 dark:bg-white/5 dark:text-cream/65">{{ $locale === 'fr' ? 'Panier vide.' : 'Empty cart.' }}</div>
                        @endif

                        <div class="mt-3 space-y-2 border-t border-leaf/10 pt-3 text-sm text-cocoa/70 dark:border-white/10 dark:text-cream/70">
                            <div class="flex items-center justify-between"><span>{{ $locale === 'fr' ? 'Sous-total TTC' : 'Subtotal incl. tax' }}</span><strong class="text-cocoa dark:text-cream">{{ $displayQuote['formatted_subtotal'] }}</strong></div>
                            <div class="flex items-center justify-between"><span>{{ ! empty($quote['is_estimate']) ? ($locale === 'fr' ? 'Livraison à partir de' : 'Shipping from') : ($locale === 'fr' ? 'Livraison' : 'Shipping') }}</span><strong class="text-cocoa dark:text-cream">{{ $selectedCarrier['price'] ?? $displayQuote['formatted_shipping'] }}</strong></div>
                            @if ($selectedPickupPointDetails)
                                <div class="rounded-lg bg-mint p-2 text-xs leading-5 text-forest dark:bg-white/5 dark:text-meadow">
                                    <strong>{{ $locale === 'fr' ? 'Point choisi :' : 'Selected pickup:' }}</strong>
                                    {{ $selectedPickupPointDetails['name'] }}
                                </div>
                            @endif
                            <div class="flex items-center justify-between border-t border-leaf/10 pt-3 text-base dark:border-white/10">
                                <span class="font-black text-cocoa dark:text-cream">Total TTC</span>
                                <strong class="text-xl text-forest dark:text-meadow">{{ $displayQuote['formatted_total'] }}</strong>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            @if ($carrier === 'chrono_relais_pickup')
                <div x-cloak x-show="chronoModal" x-transition.opacity class="fixed inset-0 z-50 bg-black/35 p-4" style="display: none;">
                    <div class="mx-auto mt-8 max-w-6xl rounded-none bg-white p-4 shadow-2xl dark:bg-ink">
                        <div class="mb-3 flex justify-end">
                            <button type="button" class="grid h-10 w-10 place-items-center rounded-full bg-black text-2xl font-black text-white" @click="chronoModal = false">×</button>
                        </div>
                        @include('partials.checkout-pickup-panel', [
                            'locale' => $locale,
                            'pickupPoints' => $pickupPoints,
                            'selectedPickupPoint' => $selectedPickupPoint,
                            'pickupMapCenter' => $pickupMapCenter,
                            'panelTitle' => $selectedCarrier['panel_title'] ?? '',
                            'buttonLabel' => $selectedCarrier['choose_label'] ?? '',
                            'isModal' => true,
                        ])
                    </div>
                </div>
            @endif
        @elseif ($confirmedOrder && ! $orderConfirmed)
            <div class="mx-auto mt-4 grid max-w-5xl gap-4 lg:grid-cols-[minmax(0,1fr)_300px]" x-data="{ paymentTab: @js($paymentProvider ?: 'stripe') }">
                <section class="form-card p-4">
                    <h1 class="text-2xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Régler votre commande' : 'Pay for your order' }}</h1>

                    @if ($paymentError)
                        <div class="mt-5 rounded-xl border border-coral/25 bg-coral/10 px-4 py-3 text-sm font-semibold text-cocoa dark:text-cream">{{ $paymentError }}</div>
                    @endif

                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        <button type="button" x-on:click="paymentTab = 'stripe'; $dispatch('checkout-payment-provider', { provider: 'stripe' })" x-bind:class="paymentTab === 'stripe' ? '!bg-[#f97316] !text-white' : ''" class="store-button store-button-outline min-h-[48px] w-full">
                            <span class="flex items-center gap-3"><x-icon name="credit-card" class="h-5 w-5" /> {{ $locale === 'fr' ? 'Carte bancaire' : 'Payment card' }}</span>
                        </button>
                        <button type="button" x-on:click="paymentTab = 'paypal'; $dispatch('checkout-payment-provider', { provider: 'paypal' })" x-bind:class="paymentTab === 'paypal' ? '!bg-[#f97316] !text-white' : ''" class="store-button store-button-outline min-h-[48px] w-full">
                            PayPal
                        </button>
                    </div>

                    <div data-payment-panel="stripe" x-cloak x-show="paymentTab === 'stripe'">
                        <div class="mt-4 rounded-xl border border-cocoa/10 bg-linen p-3 dark:border-white/10 dark:bg-white/5">
                            <div data-stripe-placeholder class="flex min-h-12 items-center gap-3 text-sm font-semibold text-cocoa/60 dark:text-cream/60">
                                <span class="h-4 w-4 animate-spin rounded-full border-2 border-orange-200 border-t-orange-500"></span>
                                {{ $locale === 'fr' ? 'Préparation du paiement sécurisé…' : 'Preparing secure payment…' }}
                            </div>
                            <div id="stripe-payment-element" wire:ignore></div>
                            <div id="stripe-payment-message" class="mt-3 hidden rounded-lg bg-coral/10 px-3 py-2 text-sm font-semibold text-cocoa dark:text-cream"></div>
                        </div>

                        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                            <button id="stripe-pay-button" type="button" class="store-button disabled:pointer-events-none disabled:opacity-50">
                                <span data-stripe-pay-label>{{ $locale === 'fr' ? 'Payer maintenant' : 'Pay now' }}</span>
                                <span data-stripe-pay-loading class="hidden">{{ $locale === 'fr' ? 'Paiement en cours...' : 'Processing payment...' }}</span>
                            </button>
                            @if ($paymentError)
                                <button type="button" class="btn-secondary px-4 py-2 text-sm" wire:click="retryStripePayment">{{ $locale === 'fr' ? 'Réessayer' : 'Retry' }}</button>
                            @endif
                        </div>
                    </div>

                    <div data-payment-panel="paypal" x-cloak x-show="paymentTab === 'paypal'">
                        <div class="mt-4 rounded-xl border border-cocoa/10 bg-linen p-3 dark:border-white/10 dark:bg-white/5">
                            <div data-paypal-placeholder class="flex min-h-12 items-center gap-3 text-sm font-semibold text-cocoa/60 dark:text-cream/60">
                                <span class="h-4 w-4 animate-spin rounded-full border-2 border-orange-200 border-t-orange-500"></span>
                                {{ $locale === 'fr' ? 'Préparation de PayPal…' : 'Preparing PayPal…' }}
                            </div>
                            <div id="paypal-button-container" wire:ignore></div>
                            <div id="paypal-payment-message" class="mt-3 hidden rounded-lg bg-coral/10 px-3 py-2 text-sm font-semibold text-cocoa dark:text-cream"></div>
                        </div>

                        @if ($paymentError)
                            <button type="button" class="btn-secondary mt-3 px-4 py-2 text-sm" wire:click="retryPaypalPayment">{{ $locale === 'fr' ? 'Réessayer' : 'Retry' }}</button>
                        @endif
                    </div>
                </section>

                <aside class="form-card p-4 lg:sticky lg:top-32">
                    <h2 class="text-lg font-black text-forest dark:text-meadow">{{ $confirmedOrder['order_number'] ?? '' }}</h2>
                    <div class="mt-3 space-y-2 text-sm text-cocoa/70 dark:text-cream/70">
                        <div class="flex items-center justify-between border-t border-leaf/10 pt-3 text-base dark:border-white/10">
                            <span class="font-black text-cocoa dark:text-cream">Total TTC</span>
                            <strong class="text-xl text-forest dark:text-meadow">{{ $confirmedOrder['formatted_total'] ?? '' }}</strong>
                        </div>
                    </div>
                </aside>
            </div>
        @else
            <div class="form-card mx-auto mt-4 max-w-2xl p-5 text-center">
                <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-[#f97316] text-xs font-bold text-white">OK</div>
                <h1 class="text-3xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Commande validée.' : 'Order confirmed.' }}</h1>
                @if (! empty($confirmedOrder['order_number']))
                    <p class="mx-auto mt-4 w-fit rounded-full bg-mint px-4 py-2 text-sm font-black text-forest dark:bg-white/10 dark:text-meadow">{{ $confirmedOrder['order_number'] }}</p>
                @endif
                <div class="mt-5 grid gap-2 sm:grid-cols-2">
                    <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary w-full" wire:navigate>{{ $locale === 'fr' ? 'Retour à la boutique' : 'Back to shop' }}</a>
                    <a href="{{ route('account.show', ['locale' => $locale]) }}" class="btn-secondary w-full" wire:navigate>{{ $locale === 'fr' ? 'Voir mon compte' : 'View my account' }}</a>
                </div>
            </div>
        @endif

        @if ($authModalOpen && ! $isAuthenticated)
            <div class="fixed inset-0 z-[100] grid place-items-center bg-black/60 p-4 backdrop-blur-sm" wire:key="checkout-auth-modal">
                <section class="max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-[28px] border bg-white p-5 shadow-2xl dark:bg-[#1a1a1c] sm:p-7" style="border-color:var(--store-border)">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="section-kicker">{{ $locale === 'fr' ? 'Continuer sans perdre le panier' : 'Continue without losing your cart' }}</p>
                            <h2 class="mt-2 text-2xl font-bold text-cocoa dark:text-white">{{ $locale === 'fr' ? 'Vos informations de commande' : 'Your checkout details' }}</h2>
                        </div>
                        <button type="button" class="store-icon-button text-2xl" wire:click="closeAuthModal" aria-label="{{ $locale === 'fr' ? 'Fermer' : 'Close' }}">×</button>
                    </div>

                    @if ($authMode === 'choice')
                        <div class="mt-7 grid gap-4 sm:grid-cols-2">
                            <button type="button" class="store-button w-full" wire:click="openAuthModal('login')">{{ $locale === 'fr' ? 'J’ai déjà un compte' : 'I already have an account' }}</button>
                            <button type="button" class="store-button store-button-outline w-full" wire:click="openAuthModal('register')">{{ $locale === 'fr' ? 'Je suis nouveau client' : 'I am a new customer' }}</button>
                        </div>
                    @elseif ($authMode === 'login')
                        <form class="mt-6 space-y-4" wire:submit.prevent="loginInline">
                            <div>
                                <label class="text-sm font-bold" for="checkout-login-email">Email</label>
                                <input id="checkout-login-email" class="input-premium mt-2 w-full" type="email" wire:model="loginEmail" autocomplete="email">
                                @error('loginEmail')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="text-sm font-bold" for="checkout-login-password">{{ $locale === 'fr' ? 'Mot de passe' : 'Password' }}</label>
                                <input id="checkout-login-password" class="input-premium mt-2 w-full" type="password" wire:model="loginPassword" autocomplete="current-password">
                                @error('loginPassword')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <button class="store-button flex-1" type="submit" wire:loading.attr="disabled" wire:target="loginInline">{{ $locale === 'fr' ? 'Se connecter et continuer' : 'Sign in and continue' }}</button>
                                <button class="store-button store-button-outline" type="button" wire:click="openAuthModal('choice')">{{ $locale === 'fr' ? 'Retour' : 'Back' }}</button>
                            </div>
                        </form>
                    @else
                        <form class="mt-6 grid gap-4 sm:grid-cols-2" wire:submit.prevent="registerInline">
                            <div>
                                <label class="text-sm font-bold" for="checkout-first-name">{{ $locale === 'fr' ? 'Prénom' : 'First name' }}</label>
                                <input id="checkout-first-name" class="input-premium mt-2 w-full" wire:model="firstName" autocomplete="given-name">
                                @error('firstName')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="text-sm font-bold" for="checkout-last-name">{{ $locale === 'fr' ? 'Nom' : 'Last name' }}</label>
                                <input id="checkout-last-name" class="input-premium mt-2 w-full" wire:model="lastName" autocomplete="family-name">
                                @error('lastName')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-sm font-bold" for="checkout-register-email">Email</label>
                                <input id="checkout-register-email" class="input-premium mt-2 w-full" type="email" wire:model="checkoutEmail" autocomplete="email">
                                @error('checkoutEmail')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="text-sm font-bold" for="checkout-phone">{{ $locale === 'fr' ? 'Téléphone' : 'Phone' }}</label>
                                <input id="checkout-phone" class="input-premium mt-2 w-full" wire:model="checkoutPhone" autocomplete="tel">
                            </div>
                            <div>
                                <label class="text-sm font-bold" for="checkout-country">{{ $locale === 'fr' ? 'Pays' : 'Country' }}</label>
                                <select id="checkout-country" class="input-premium mt-2 w-full" wire:model="countryCode">
                                    @foreach ($countries as $country)<option value="{{ $country['code'] }}">{{ $country['name'] }}</option>@endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-sm font-bold" for="checkout-street">{{ $locale === 'fr' ? 'Adresse de livraison' : 'Delivery address' }}</label>
                                <input id="checkout-street" class="input-premium mt-2 w-full" wire:model="streetLine1" autocomplete="address-line1">
                                @error('streetLine1')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <input class="input-premium w-full" wire:model="streetLine2" autocomplete="address-line2" placeholder="{{ $locale === 'fr' ? 'Complément d’adresse (optionnel)' : 'Address line 2 (optional)' }}">
                            </div>
                            <div>
                                <label class="text-sm font-bold" for="checkout-postal">{{ $locale === 'fr' ? 'Code postal' : 'Postal code' }}</label>
                                <input id="checkout-postal" class="input-premium mt-2 w-full" wire:model="postalCode" autocomplete="postal-code">
                                @error('postalCode')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="text-sm font-bold" for="checkout-city">{{ $locale === 'fr' ? 'Ville' : 'City' }}</label>
                                <input id="checkout-city" class="input-premium mt-2 w-full" wire:model="city" autocomplete="address-level2">
                                @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="text-sm font-bold" for="checkout-password">{{ $locale === 'fr' ? 'Créer un mot de passe' : 'Create a password' }}</label>
                                <input id="checkout-password" class="input-premium mt-2 w-full" type="password" wire:model="checkoutPassword" autocomplete="new-password">
                                @error('checkoutPassword')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="text-sm font-bold" for="checkout-password-confirmation">{{ $locale === 'fr' ? 'Confirmer le mot de passe' : 'Confirm password' }}</label>
                                <input id="checkout-password-confirmation" class="input-premium mt-2 w-full" type="password" wire:model="checkoutPasswordConfirmation" autocomplete="new-password">
                            </div>
                            <p class="text-xs leading-5 text-cocoa/60 dark:text-white/60 sm:col-span-2">{{ $locale === 'fr' ? 'En continuant, vous acceptez les conditions et la politique de confidentialité. Le compte est créé immédiatement et un email de bienvenue vous est envoyé.' : 'By continuing, you accept the terms and privacy policy. Your account is created immediately and a welcome email is sent.' }}</p>
                            <div class="grid gap-3 sm:col-span-2 sm:grid-cols-[1fr_auto]">
                                <button class="store-button" type="submit" wire:loading.attr="disabled" wire:target="registerInline">{{ $locale === 'fr' ? 'Créer le compte et continuer' : 'Create account and continue' }}</button>
                                <button class="store-button store-button-outline" type="button" wire:click="openAuthModal('choice')">{{ $locale === 'fr' ? 'Retour' : 'Back' }}</button>
                            </div>
                        </form>
                    @endif
                </section>
            </div>
        @endif
    </div>

    @script
        <script>
            (() => {
            const checkoutStorageKey = @js(\Illuminate\Support\Str::slug(config('shop.name'), '_').'_cart_token');
            const checkoutStoredToken = localStorage.getItem(checkoutStorageKey);
            const checkoutAlreadyCompleted = @js($orderConfirmed);
            if (checkoutAlreadyCompleted) {
                localStorage.removeItem(checkoutStorageKey);
            } else {
                $wire.restoreFromBrowser(checkoutStoredToken);
            }
            const checkoutRoot = $wire.$el;
            const checkoutPayload = (event) => Array.isArray(event) ? (event[0] || {}) : (event || {});
            $wire.on('cart-token-stored', (event) => {
                const detail = checkoutPayload(event);
                if (detail.token) {
                    localStorage.setItem(checkoutStorageKey, detail.token);
                }
            });
            $wire.on('cart-token-cleared', () => {
                localStorage.removeItem(checkoutStorageKey);
            });
            $wire.on('checkout-confirmed', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
            $wire.on('paypal-express-redirect', (event) => {
                const detail = checkoutPayload(event);
                if (!detail.url) return;
                const destination = new URL(detail.url, window.location.origin);
                if (destination.protocol === 'https:' && (destination.hostname === 'paypal.com' || destination.hostname.endsWith('.paypal.com'))) {
                    window.location.assign(destination.href);
                }
            });
            const stripeState = { stripe: null, elements: null, paymentElement: null, clientSecret: null, payload: null, sdkPromise: null };
            const paypalState = { sdkUrl: null, sdkPromise: null, orderId: null, payload: null, buttons: null };
            let paymentPreloadStarted = false;
            const stripeMessage = () => document.getElementById('stripe-payment-message');
            const stripePayButton = () => document.getElementById('stripe-pay-button');
            const paypalMessage = () => document.getElementById('paypal-payment-message');
            const paymentPanelVisible = (provider) => {
                const panel = document.querySelector(`[data-payment-panel="${provider}"]`);
                return panel && getComputedStyle(panel).display !== 'none';
            };
            const setPaymentPlaceholder = (provider, visible) => {
                document.querySelector(`[data-${provider}-placeholder]`)?.classList.toggle('hidden', !visible);
            };

            const setStripeLoading = (loading) => {
                const button = stripePayButton();
                if (!button) return;
                button.disabled = loading;
                button.querySelector('[data-stripe-pay-label]')?.classList.toggle('hidden', loading);
                button.querySelector('[data-stripe-pay-loading]')?.classList.toggle('hidden', !loading);
            };

            const showStripeMessage = (message) => {
                const element = stripeMessage();
                if (!element) return;
                element.textContent = message || '';
                element.classList.toggle('hidden', !message);
            };

            const showPaypalMessage = (message) => {
                const element = paypalMessage();
                if (!element) return;
                element.textContent = message || '';
                element.classList.toggle('hidden', !message);
            };

            const loadStripeJs = () => {
                if (window.Stripe) {
                    return Promise.resolve();
                }
                if (stripeState.sdkPromise) return stripeState.sdkPromise;

                stripeState.sdkPromise = new Promise((resolve, reject) => {
                    const timer = window.setTimeout(() => {
                        stripeState.sdkPromise = null;
                        document.querySelector('script[src="https://js.stripe.com/v3/"]')?.remove();
                        reject(new Error(@js($locale === 'fr' ? 'Le chargement de Stripe a expiré.' : 'Stripe loading timed out.')));
                    }, 12000);
                    const loaded = () => { window.clearTimeout(timer); resolve(); };
                    const failed = (error) => {
                        window.clearTimeout(timer);
                        stripeState.sdkPromise = null;
                        document.querySelector('script[src="https://js.stripe.com/v3/"]')?.remove();
                        reject(error instanceof Error ? error : new Error('Stripe SDK failed to load.'));
                    };
                    const existing = document.querySelector('script[src="https://js.stripe.com/v3/"]');
                    if (existing) {
                        existing.addEventListener('load', loaded, { once: true });
                        existing.addEventListener('error', failed, { once: true });
                        return;
                    }
                    const script = document.createElement('script');
                    script.src = 'https://js.stripe.com/v3/';
                    script.async = true;
                    script.onload = loaded;
                    script.onerror = failed;
                    document.head.appendChild(script);
                });

                return stripeState.sdkPromise;
            };

            const mountStripePayment = async (payment) => {
                try {
                    showStripeMessage('');
                    await loadStripeJs();
                    if (!payment?.publishable_key || !payment?.client_secret) {
                        throw new Error(@js($locale === 'fr' ? 'Configuration Stripe incomplete.' : 'Stripe configuration is incomplete.'));
                    }
                    const container = document.getElementById('stripe-payment-element');
                    if (!container) return;
                    if (stripeState.clientSecret === payment.client_secret && stripeState.paymentElement) {
                        setPaymentPlaceholder('stripe', false);
                        return;
                    }
                    stripeState.paymentElement?.destroy?.();
                    container.innerHTML = '';
                    stripeState.clientSecret = payment.client_secret;
                    stripeState.stripe = window.Stripe(payment.publishable_key);
                    stripeState.elements = stripeState.stripe.elements({
                        clientSecret: payment.client_secret,
                        appearance: {
                            theme: document.documentElement.classList.contains('dark') ? 'night' : 'stripe',
                            variables: { borderRadius: '12px', colorPrimary: '#f97316' },
                        },
                    });
                    stripeState.paymentElement = stripeState.elements.create('payment', { layout: 'tabs' });
                    stripeState.paymentElement.mount(container);
                    setPaymentPlaceholder('stripe', false);
                } catch (error) {
                    setPaymentPlaceholder('stripe', false);
                    showStripeMessage(error.message || @js($locale === 'fr' ? 'Stripe ne peut pas etre charge.' : 'Stripe could not be loaded.'));
                    $wire.failStripePayment(error.message || null);
                }
            };

            $wire.on('stripe-payment-ready', (event) => {
                const detail = checkoutPayload(event);
                stripeState.payload = detail.payment || detail;
                if (paymentPanelVisible('stripe')) mountStripePayment(stripeState.payload);
            });

            const loadPaypalSdk = (payment) => {
                if (!payment?.client_id) {
                    return Promise.reject(new Error(@js($locale === 'fr' ? 'Configuration PayPal incomplete.' : 'PayPal configuration is incomplete.')));
                }

                const currency = (payment.currency || 'EUR').toUpperCase();
                const sdkUrl = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(payment.client_id)}&currency=${encodeURIComponent(currency)}&components=buttons`;

                if (window.paypal && paypalState.sdkUrl === sdkUrl) {
                    return Promise.resolve();
                }
                if (paypalState.sdkPromise && paypalState.sdkUrl === sdkUrl) return paypalState.sdkPromise;

                document.querySelectorAll('script[data-paypal-sdk="checkout"]').forEach((script) => script.remove());
                delete window.paypal;

                paypalState.sdkUrl = sdkUrl;
                paypalState.sdkPromise = new Promise((resolve, reject) => {
                    const timer = window.setTimeout(() => {
                        paypalState.sdkPromise = null;
                        document.querySelector('script[data-paypal-sdk="checkout"]')?.remove();
                        reject(new Error(@js($locale === 'fr' ? 'Le chargement de PayPal a expiré.' : 'PayPal loading timed out.')));
                    }, 12000);
                    const script = document.createElement('script');
                    script.src = sdkUrl;
                    script.async = true;
                    script.dataset.paypalSdk = 'checkout';
                    script.onload = () => { window.clearTimeout(timer); resolve(); };
                    script.onerror = (error) => {
                        window.clearTimeout(timer);
                        paypalState.sdkPromise = null;
                        script.remove();
                        reject(error instanceof Error ? error : new Error('PayPal SDK failed to load.'));
                    };
                    document.head.appendChild(script);
                });

                return paypalState.sdkPromise;
            };

            const mountPaypalPayment = async (payment) => {
                try {
                    showPaypalMessage('');
                    paypalState.orderId = payment?.external_id || null;
                    await loadPaypalSdk(payment);
                    const container = document.getElementById('paypal-button-container');
                    if (!container) return;
                    if (paypalState.buttons && paypalState.orderId === payment?.external_id && container.childElementCount > 0) {
                        setPaymentPlaceholder('paypal', false);
                        return;
                    }
                    container.innerHTML = '';
                    if (!paypalState.orderId) {
                        throw new Error(@js($locale === 'fr' ? 'Ordre PayPal introuvable.' : 'PayPal order is missing.'));
                    }
                    paypalState.buttons = window.paypal.Buttons({
                        style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'paypal' },
                        createOrder: () => paypalState.orderId,
                        onApprove: (data) => {
                            const orderId = data?.orderID || paypalState.orderId;
                            if (!orderId) {
                                showPaypalMessage(@js($locale === 'fr' ? 'PayPal na pas renvoye didentifiant de commande.' : 'PayPal did not return an order id.'));
                                return;
                            }
                            $wire.capturePaypalPayment(orderId);
                        },
                        onCancel: () => showPaypalMessage(@js($locale === 'fr' ? 'Paiement PayPal annule.' : 'PayPal payment cancelled.')),
                        onError: (error) => {
                            const message = error?.message || @js($locale === 'fr' ? 'PayPal ne peut pas finaliser le paiement.' : 'PayPal could not complete the payment.');
                            showPaypalMessage(message);
                            $wire.failPaypalPayment(message);
                        },
                    });
                    await paypalState.buttons.render(container);
                    setPaymentPlaceholder('paypal', false);
                } catch (error) {
                    setPaymentPlaceholder('paypal', false);
                    const message = error.message || @js($locale === 'fr' ? 'PayPal ne peut pas etre charge.' : 'PayPal could not be loaded.');
                    showPaypalMessage(message);
                    $wire.failPaypalPayment(message);
                }
            };

            $wire.on('paypal-payment-ready', (event) => {
                const detail = checkoutPayload(event);
                paypalState.payload = detail.payment || detail;
                loadPaypalSdk(paypalState.payload).catch(() => {});
                if (paymentPanelVisible('paypal')) mountPaypalPayment(paypalState.payload);
            });

            $wire.on('payment-step-ready', () => {
                if (paymentPreloadStarted) return;
                paymentPreloadStarted = true;
                loadStripeJs().catch(() => {});
                $wire.preloadPaymentMethods();
            });

            $wire.on('payment-method-settled', (event) => {
                const detail = checkoutPayload(event);
                if (detail.ok || !['stripe', 'paypal'].includes(detail.provider)) return;
                setPaymentPlaceholder(detail.provider, false);
                const message = detail.provider === 'stripe'
                    ? @js($locale === 'fr' ? 'Stripe n’a pas pu être préparé. Cliquez sur Carte bancaire pour réessayer.' : 'Stripe could not be prepared. Click Payment card to retry.')
                    : @js($locale === 'fr' ? 'PayPal n’a pas pu être préparé. Cliquez sur PayPal pour réessayer.' : 'PayPal could not be prepared. Click PayPal to retry.');
                detail.provider === 'stripe' ? showStripeMessage(message) : showPaypalMessage(message);
            });

            checkoutRoot.addEventListener('checkout-payment-provider', (event) => {
                const provider = event.detail?.provider;
                if (!['stripe', 'paypal'].includes(provider)) return;
                $wire.$set('paymentProvider', provider, false);

                requestAnimationFrame(() => {
                    if (provider === 'stripe' && stripeState.payload) {
                        mountStripePayment(stripeState.payload);
                    } else if (provider === 'paypal' && paypalState.payload) {
                        mountPaypalPayment(paypalState.payload);
                    } else {
                        setPaymentPlaceholder(provider, true);
                        $wire.selectPaymentProvider(provider);
                    }
                });
            });

            checkoutRoot.addEventListener('click', async (event) => {
                if (!event.target.closest('#stripe-pay-button')) return;
                event.preventDefault();
                showStripeMessage('');
                if (!stripeState.stripe || !stripeState.elements) {
                    showStripeMessage(@js($locale === 'fr' ? 'Le formulaire Stripe nest pas encore pret.' : 'Stripe form is not ready yet.'));
                    return;
                }
                setStripeLoading(true);
                const { error, paymentIntent } = await stripeState.stripe.confirmPayment({
                    elements: stripeState.elements,
                    confirmParams: { return_url: window.location.href },
                    redirect: 'if_required',
                });
                setStripeLoading(false);
                if (error) {
                    const message = error.message || @js($locale === 'fr' ? 'Le paiement a echoue.' : 'Payment failed.');
                    showStripeMessage(message);
                    $wire.failStripePayment(message);
                    return;
                }
                if (paymentIntent && paymentIntent.status === 'succeeded') {
                    $wire.completeStripePayment(paymentIntent.id);
                    return;
                }
                if (paymentIntent && paymentIntent.status === 'processing') {
                    showStripeMessage(@js($locale === 'fr' ? 'Paiement en traitement. Patientez quelques secondes puis verifiez votre commande.' : 'Payment is processing. Wait a few seconds and check your order.'));
                    return;
                }
                showStripeMessage(@js($locale === 'fr' ? 'Paiement non finalise. Reessayez.' : 'Payment was not completed. Try again.'));
            });
            })();
        </script>
    @endscript
</section>
