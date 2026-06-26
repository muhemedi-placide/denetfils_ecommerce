<section class="soft-grid px-4 pb-28 pt-8 dark:bg-ink sm:px-6 lg:px-8 lg:pb-16 lg:pt-10" x-data="{ chronoModal: false }">
    <div class="mx-auto max-w-7xl">
        @include('partials.checkout-progress', ['currentLocale' => $locale, 'currentStep' => $orderConfirmed ? 'success' : 'checkout'])

        @if (! $orderConfirmed && ! $confirmedOrder)
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="section-kicker">{{ $locale === 'fr' ? 'Commande rapide' : 'Fast checkout' }}</p>
                    <h1 class="section-title mt-3">{{ $locale === 'fr' ? 'Valider votre commande' : 'Confirm your order' }}</h1>
                    <p class="section-copy mt-4">{{ $locale === 'fr' ? 'Choisissez votre adresse, puis sélectionnez Mondial Relay Locker, Mondial Points Relais, Chrono Relais ou Chronopost.' : 'Choose your address, then select Mondial Relay Locker, Mondial pickup, Chrono Relais or Chronopost.' }}</p>
                </div>
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

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_370px] lg:items-start">
                <form class="space-y-4" wire:submit.prevent="confirm">
                    <section class="form-card">
                        <div class="flex items-start gap-4">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-forest text-xs font-black text-cream dark:bg-meadow dark:text-ink">1</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h2 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Compte client' : 'Customer account' }}</h2>
                                        <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Le compte permet de garder l’historique, les adresses et les informations de livraison.' : 'The account keeps order history, addresses and delivery information.' }}</p>
                                    </div>
                                    @if (! $isAuthenticated)
                                        <div class="grid gap-2 sm:flex">
                                            <a href="{{ route('account.login', ['locale' => $locale]) }}" class="btn-primary px-4 py-2 text-xs" wire:navigate>{{ __('home.account.auth.sign_in') }}</a>
                                            <a href="{{ route('account.register', ['locale' => $locale]) }}" class="btn-secondary px-4 py-2 text-xs" wire:navigate>{{ __('home.account.auth.create_account') }}</a>
                                        </div>
                                    @endif
                                </div>

                                @if ($isAuthenticated)
                                    <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                        <div class="rounded-xl bg-linen px-3 py-3 dark:bg-white/5">
                                            <p class="text-[11px] font-black uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ $locale === 'fr' ? 'Client' : 'Customer' }}</p>
                                            <p class="mt-1 truncate font-black text-cocoa dark:text-cream">{{ $user['name'] ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) }}</p>
                                        </div>
                                        <div class="rounded-xl bg-linen px-3 py-3 dark:bg-white/5">
                                            <p class="text-[11px] font-black uppercase tracking-wide text-cocoa/50 dark:text-cream/50">Email</p>
                                            <p class="mt-1 truncate font-black text-cocoa dark:text-cream">{{ $user['email'] ?? '' }}</p>
                                        </div>
                                        <div class="rounded-xl bg-linen px-3 py-3 dark:bg-white/5">
                                            <p class="text-[11px] font-black uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ $locale === 'fr' ? 'Pays' : 'Country' }}</p>
                                            <p class="mt-1 truncate font-black text-cocoa dark:text-cream">{{ $countryNames[$user['country_code'] ?? ''] ?? ($user['country_code'] ?? '') }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-4 rounded-xl bg-coral/10 px-4 py-3 text-sm font-semibold text-cocoa dark:text-cream">
                                        {{ $locale === 'fr' ? 'Connexion requise pour continuer vers la livraison.' : 'Sign-in is required to continue to delivery.' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>

                    <section class="form-card">
                        <div class="flex items-start gap-4">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-forest text-xs font-black text-cream dark:bg-meadow dark:text-ink">2</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h2 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Adresse de livraison' : 'Delivery address' }}</h2>
                                        <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Elle sert au calcul transporteur et à la recherche du point de retrait le plus proche.' : 'Used for carrier pricing and nearest pickup search.' }}</p>
                                    </div>
                                    @if ($isAuthenticated)
                                        <a href="{{ route('account.show', ['locale' => $locale]) }}" class="btn-secondary px-4 py-2 text-xs" wire:navigate>{{ $locale === 'fr' ? 'Gérer' : 'Manage' }}</a>
                                    @endif
                                </div>

                                @if ($isAuthenticated && ! empty($addresses))
                                    <div class="mt-4 grid gap-2">
                                        @foreach ($addresses as $address)
                                            <label class="cursor-pointer rounded-xl border border-leaf/10 bg-linen p-4 text-sm transition dark:border-white/10 dark:bg-white/5 {{ (int) $selectedAddressId === (int) $address['id'] ? 'ring-2 ring-leaf/35 dark:ring-meadow/40' : 'hover:border-leaf/25' }}" wire:key="checkout-address-{{ $address['id'] }}">
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

                    <section class="overflow-hidden rounded-[1.35rem] border border-leaf/10 bg-white shadow-tropical dark:border-white/10 dark:bg-white/5">
                        <div class="bg-[#48a900] px-5 py-4 text-white">
                            <h2 class="flex items-center gap-4 text-2xl font-light uppercase tracking-wide"><span>3</span><span>{{ $locale === 'fr' ? 'Mode de livraison' : 'Delivery mode' }}</span></h2>
                        </div>

                        <div class="space-y-5 p-4 sm:p-6">
                            @forelse ($carriers as $key => $option)
                                <div wire:key="carrier-option-wrapper-{{ $key }}">
                                    <label class="grid cursor-pointer items-center gap-4 rounded-xl bg-neutral-100 px-5 py-5 transition hover:bg-neutral-50 dark:bg-white/5 dark:hover:bg-white/10 sm:grid-cols-[36px_86px_minmax(170px,1fr)_minmax(190px,1.3fr)_120px]">
                                        <span class="grid h-7 w-7 place-items-center rounded-full border border-cocoa/10 bg-white dark:border-white/20 dark:bg-white/5">
                                            <input class="h-4 w-4 accent-[#48a900]" type="radio" value="{{ $key }}" wire:model.live="carrier">
                                        </span>

                                        <span class="grid h-14 w-20 place-items-center">
                                            @if ($option['logo'] === 'mr')
                                                <span class="grid h-12 w-12 place-items-center rounded-xl bg-[#f7b6cd] text-2xl font-black text-[#a01455]">r</span>
                                            @elseif ($option['logo'] === 'chrono')
                                                <span class="text-xs font-black text-[#168bd0]">▣ chronopost</span>
                                            @else
                                                <span class="grid h-12 w-12 place-items-center rounded-xl bg-leaf/10 text-lg font-black uppercase text-leaf">{{ mb_substr($option['name'], 0, 2) }}</span>
                                            @endif
                                        </span>

                                        <span class="text-lg font-black leading-6 text-cocoa dark:text-cream">
                                            {{ $option['name'] }}
                                            <span class="mt-1 flex flex-wrap gap-2 text-[11px] font-black uppercase tracking-wide">
                                                <span class="rounded-full bg-white px-2 py-1 text-cocoa/55 ring-1 ring-cocoa/10 dark:bg-white/10 dark:text-cream/60 dark:ring-white/10">{{ $option['brand_label'] }}</span>
                                                <span class="rounded-full bg-mint px-2 py-1 text-forest dark:bg-white/10 dark:text-meadow">{{ $option['type_label'] }}</span>
                                            </span>
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

                            <div class="pt-2">
                                <label class="block text-base font-medium text-cocoa dark:text-cream">
                                    {{ $locale === 'fr' ? 'Si vous voulez nous laisser un message à propos de votre commande, merci de bien vouloir le renseigner dans le champ ci-contre' : 'Leave a message about your order if needed.' }}
                                    <textarea class="mt-3 h-24 w-full resize-none rounded-none border border-cocoa/15 bg-white px-3 py-2 outline-none focus:border-[#48a900] dark:border-white/10 dark:bg-white/5"></textarea>
                                </label>
                            </div>

                            <button type="submit" class="rounded-full bg-[#48ad4d] px-10 py-4 text-base font-black text-white shadow-[0_14px_32px_rgba(69,173,77,.25)] disabled:pointer-events-none disabled:opacity-50" wire:loading.attr="disabled" @disabled(count($this->cartItems()) === 0 || ! $isAuthenticated || ! $selectedAddressId || ($delivery === 'relay' && ! $selectedPickupPointDetails))>
                                <span wire:loading.remove>{{ $locale === 'fr' ? 'Créer la commande' : 'Create order' }}</span>
                                <span wire:loading>{{ __('home.cart.loading') }}</span>
                            </button>
                        </div>
                    </section>
                </form>

                <aside class="lg:sticky lg:top-32">
                    <div class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-tropical dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="section-kicker">{{ $locale === 'fr' ? 'Résumé' : 'Summary' }}</p>
                                <h2 class="mt-2 text-2xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Votre commande' : 'Your order' }}</h2>
                            </div>
                            <strong class="shrink-0 text-xl font-black text-forest dark:text-meadow">{{ $displayQuote['formatted_total'] }}</strong>
                        </div>

                        <div wire:loading.flex class="mt-3 items-center gap-3 rounded-lg bg-linen px-3 py-2 text-xs font-semibold text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                            <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-coral"></span>{{ __('home.cart.loading') }}
                        </div>

                        @if (count($this->cartItems()) > 0)
                            <div class="mt-4 space-y-3">
                                @foreach ($this->cartItems() as $item)
                                    @php
                                        $imageUrl = data_get($item, 'product.image.url');
                                        $productName = data_get($item, 'product.name', '-');
                                    @endphp
                                    <div class="flex items-start justify-between gap-3 border-b border-leaf/10 pb-3 text-sm dark:border-white/10" wire:key="checkout-cart-item-{{ $item['id'] }}">
                                        <div class="flex min-w-0 items-center gap-3">
                                            @if ($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="{{ $productName }}" class="h-14 w-14 shrink-0 rounded-xl object-cover ring-1 ring-leaf/10 dark:ring-white/10" loading="lazy" decoding="async">
                                            @else
                                                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-mint text-xs font-black text-forest dark:bg-white/10 dark:text-meadow">DF</span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="line-clamp-2 font-black text-cocoa dark:text-cream">{{ $productName }}</p>
                                            <p class="mt-1 truncate text-xs text-cocoa/55 dark:text-cream/55">{{ $item['quantity'] }} × {{ data_get($item, 'variant.name') ?: data_get($item, 'product.origin') }}</p>
                                            </div>
                                        </div>
                                        <strong class="shrink-0 text-forest dark:text-meadow">{{ $item['formatted_line_total'] }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-3 rounded-lg bg-linen px-3 py-3 text-sm text-cocoa/65 dark:bg-white/5 dark:text-cream/65">{{ $locale === 'fr' ? 'Panier vide.' : 'Empty cart.' }}</div>
                        @endif

                        <div class="mt-4 space-y-2 border-t border-leaf/10 pt-4 text-sm text-cocoa/70 dark:border-white/10 dark:text-cream/70">
                            <div class="flex items-center justify-between"><span>{{ $locale === 'fr' ? 'Sous-total' : 'Subtotal' }}</span><strong class="text-cocoa dark:text-cream">{{ $displayQuote['formatted_subtotal'] }}</strong></div>
                            <div class="flex items-center justify-between"><span>{{ $locale === 'fr' ? 'Livraison' : 'Shipping' }}</span><strong class="text-cocoa dark:text-cream">{{ $selectedCarrier['price'] ?? $displayQuote['formatted_shipping'] }}</strong></div>
                            <div class="flex items-center justify-between"><span>{{ $locale === 'fr' ? 'TVA' : 'VAT' }}</span><strong class="text-cocoa dark:text-cream">{{ $displayQuote['formatted_tax'] }}</strong></div>
                            <div class="flex items-center justify-between gap-3"><span>{{ $locale === 'fr' ? 'Transporteur' : 'Carrier' }}</span><span class="truncate text-right font-semibold">{{ $selectedCarrier['name'] ?? '-' }}</span></div>
                            @if ($selectedPickupPointDetails)
                                <div class="rounded-lg bg-mint p-3 text-xs leading-5 text-forest dark:bg-white/5 dark:text-meadow">
                                    <strong class="block">{{ $locale === 'fr' ? 'Point choisi' : 'Selected pickup' }}</strong>
                                    {{ $selectedPickupPointDetails['name'] }} · {{ $selectedPickupPointDetails['address'] }}
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
            <div class="mx-auto mt-8 grid max-w-5xl gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                <section class="rounded-[1.5rem] border border-leaf/10 bg-white p-6 shadow-tropical dark:border-white/10 dark:bg-white/5">
                    <p class="section-kicker">{{ $locale === 'fr' ? 'Paiement securise' : 'Secure payment' }}</p>
                    <h1 class="mt-3 text-3xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Regler votre commande' : 'Pay for your order' }}</h1>
                    <p class="mt-3 text-sm leading-6 text-cocoa/65 dark:text-cream/65">
                        {{ $locale === 'fr' ? 'Votre commande est creee. Choisissez le moyen de paiement pour finaliser.' : 'Your order has been created. Choose a payment method to complete it.' }}
                    </p>

                    @if ($paymentError)
                        <div class="mt-5 rounded-xl border border-coral/25 bg-coral/10 px-4 py-3 text-sm font-semibold text-cocoa dark:text-cream">{{ $paymentError }}</div>
                    @endif

                    <div class="mt-6 grid gap-2 rounded-xl bg-linen p-2 dark:bg-white/5 sm:grid-cols-2">
                        <button type="button" wire:click="selectPaymentProvider('stripe')" class="rounded-lg px-4 py-3 text-sm font-black transition {{ $paymentProvider === 'stripe' ? 'bg-white text-forest shadow-sm dark:bg-ink dark:text-meadow' : 'text-cocoa/65 hover:bg-white/60 dark:text-cream/65 dark:hover:bg-white/10' }}">
                            {{ $locale === 'fr' ? 'Carte bancaire' : 'Card' }}
                        </button>
                        <button type="button" wire:click="selectPaymentProvider('paypal')" class="rounded-lg px-4 py-3 text-sm font-black transition {{ $paymentProvider === 'paypal' ? 'bg-white text-forest shadow-sm dark:bg-ink dark:text-meadow' : 'text-cocoa/65 hover:bg-white/60 dark:text-cream/65 dark:hover:bg-white/10' }}">
                            PayPal
                        </button>
                    </div>

                    @if ($paymentProvider === 'stripe')
                        <div class="mt-6 rounded-xl border border-cocoa/10 bg-linen p-4 dark:border-white/10 dark:bg-white/5">
                            <div id="stripe-payment-element" wire:ignore></div>
                            <div id="stripe-payment-message" class="mt-3 hidden rounded-lg bg-coral/10 px-3 py-2 text-sm font-semibold text-cocoa dark:text-cream"></div>
                        </div>

                        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <button id="stripe-pay-button" type="button" class="rounded-full bg-[#48ad4d] px-8 py-4 text-base font-black text-white shadow-[0_14px_32px_rgba(69,173,77,.25)] disabled:pointer-events-none disabled:opacity-50">
                                <span data-stripe-pay-label>{{ $locale === 'fr' ? 'Payer maintenant' : 'Pay now' }}</span>
                                <span data-stripe-pay-loading class="hidden">{{ $locale === 'fr' ? 'Paiement en cours...' : 'Processing payment...' }}</span>
                            </button>
                            <button type="button" class="btn-secondary px-5 py-3 text-sm" wire:click="retryStripePayment">
                                {{ $locale === 'fr' ? 'Recharger Stripe' : 'Reload Stripe' }}
                            </button>
                        </div>

                        <p class="mt-4 text-xs font-semibold leading-5 text-cocoa/55 dark:text-cream/55">
                            {{ $locale === 'fr' ? 'Carte test rapide: 4242 4242 4242 4242, date future, CVC au choix.' : 'Quick test card: 4242 4242 4242 4242, future date, any CVC.' }}
                        </p>
                    @else
                        <div class="mt-6 rounded-xl border border-cocoa/10 bg-linen p-4 dark:border-white/10 dark:bg-white/5">
                            <div id="paypal-button-container" wire:ignore></div>
                            <div id="paypal-payment-message" class="mt-3 hidden rounded-lg bg-coral/10 px-3 py-2 text-sm font-semibold text-cocoa dark:text-cream"></div>
                        </div>

                        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <button type="button" class="btn-secondary px-5 py-3 text-sm" wire:click="retryPaypalPayment">
                                {{ $locale === 'fr' ? 'Recharger PayPal' : 'Reload PayPal' }}
                            </button>
                        </div>
                    @endif
                </section>

                <aside class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-tropical dark:border-white/10 dark:bg-white/5 lg:sticky lg:top-32">
                    <p class="section-kicker">{{ $locale === 'fr' ? 'Commande' : 'Order' }}</p>
                    <h2 class="mt-2 text-2xl font-black text-forest dark:text-meadow">{{ $confirmedOrder['order_number'] ?? '' }}</h2>
                    <div class="mt-4 space-y-2 text-sm text-cocoa/70 dark:text-cream/70">
                        <div class="flex items-center justify-between"><span>{{ $locale === 'fr' ? 'Statut' : 'Status' }}</span><strong class="text-cocoa dark:text-cream">{{ $confirmedOrder['payment_status'] ?? 'unpaid' }}</strong></div>
                        <div class="flex items-center justify-between"><span>{{ $locale === 'fr' ? 'Paiement' : 'Payment' }}</span><strong class="text-cocoa dark:text-cream">{{ $paymentProvider === 'paypal' ? 'PayPal' : 'Stripe' }}</strong></div>
                        <div class="flex items-center justify-between border-t border-leaf/10 pt-3 text-base dark:border-white/10">
                            <span class="font-black text-cocoa dark:text-cream">Total TTC</span>
                            <strong class="text-xl text-forest dark:text-meadow">{{ $confirmedOrder['formatted_total'] ?? '' }}</strong>
                        </div>
                    </div>
                </aside>
            </div>
        @else
            <div class="mx-auto mt-8 max-w-2xl rounded-[1.5rem] border border-leaf/10 bg-white p-8 text-center shadow-tropical dark:border-white/10 dark:bg-white/5">
                <div class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-full bg-sunshine text-sm font-black text-forest">OK</div>
                <p class="section-kicker">{{ $locale === 'fr' ? 'Confirmation' : 'Confirmation' }}</p>
                <h1 class="mt-3 text-4xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Commande validée.' : 'Order confirmed.' }}</h1>
                @if (! empty($confirmedOrder['order_number']))
                    <p class="mx-auto mt-4 w-fit rounded-full bg-mint px-4 py-2 text-sm font-black text-forest dark:bg-white/10 dark:text-meadow">{{ $confirmedOrder['order_number'] }}</p>
                @endif
                <p class="mx-auto mt-4 max-w-md text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $locale === 'fr' ? 'Le mode de livraison et le point de retrait sont conservés dans la commande.' : 'The delivery mode and pickup point are saved in the order.' }}</p>
                <div class="mt-7 grid gap-2 sm:grid-cols-2">
                    <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary w-full" wire:navigate>{{ $locale === 'fr' ? 'Retour à la boutique' : 'Back to shop' }}</a>
                    <a href="{{ route('account.show', ['locale' => $locale]) }}" class="btn-secondary w-full" wire:navigate>{{ $locale === 'fr' ? 'Voir mon compte' : 'View my account' }}</a>
                </div>
            </div>
        @endif
    </div>

    @script
        <script>
            (() => {
            const checkoutStorageKey = 'marche_peyi_cart_token';
            const legacyCheckoutStorageKey = 'denetfils_cart_token';
            const checkoutStoredToken = localStorage.getItem(checkoutStorageKey) || localStorage.getItem(legacyCheckoutStorageKey);
            $wire.restoreFromBrowser(checkoutStoredToken);
            const checkoutPayload = (event) => Array.isArray(event) ? (event[0] || {}) : (event || {});
            $wire.on('cart-token-stored', (event) => {
                const detail = checkoutPayload(event);
                if (detail.token) {
                    localStorage.setItem(checkoutStorageKey, detail.token);
                    localStorage.removeItem(legacyCheckoutStorageKey);
                }
            });
            $wire.on('cart-token-cleared', () => {
                localStorage.removeItem(checkoutStorageKey);
                localStorage.removeItem(legacyCheckoutStorageKey);
            });
            $wire.on('checkout-confirmed', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
            const stripeState = { stripe: null, elements: null, clientSecret: null };
            const paypalState = { sdkUrl: null, orderId: null };
            const stripeMessage = () => document.getElementById('stripe-payment-message');
            const stripePayButton = () => document.getElementById('stripe-pay-button');
            const paypalMessage = () => document.getElementById('paypal-payment-message');

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

            const loadStripeJs = () => new Promise((resolve, reject) => {
                if (window.Stripe) {
                    resolve();
                    return;
                }
                const existing = document.querySelector('script[src="https://js.stripe.com/v3/"]');
                if (existing) {
                    existing.addEventListener('load', resolve, { once: true });
                    existing.addEventListener('error', reject, { once: true });
                    return;
                }
                const script = document.createElement('script');
                script.src = 'https://js.stripe.com/v3/';
                script.async = true;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });

            const mountStripePayment = async (payment) => {
                try {
                    showStripeMessage('');
                    await loadStripeJs();
                    if (!payment?.publishable_key || !payment?.client_secret) {
                        throw new Error(@js($locale === 'fr' ? 'Configuration Stripe incomplete.' : 'Stripe configuration is incomplete.'));
                    }
                    const container = document.getElementById('stripe-payment-element');
                    if (!container) return;
                    container.innerHTML = '';
                    stripeState.clientSecret = payment.client_secret;
                    stripeState.stripe = window.Stripe(payment.publishable_key);
                    stripeState.elements = stripeState.stripe.elements({
                        clientSecret: payment.client_secret,
                        appearance: {
                            theme: document.documentElement.classList.contains('dark') ? 'night' : 'stripe',
                            variables: { borderRadius: '8px', colorPrimary: '#48ad4d' },
                        },
                    });
                    stripeState.elements.create('payment', { layout: 'tabs' }).mount(container);
                } catch (error) {
                    showStripeMessage(error.message || @js($locale === 'fr' ? 'Stripe ne peut pas etre charge.' : 'Stripe could not be loaded.'));
                    $wire.failStripePayment(error.message || null);
                }
            };

            $wire.on('stripe-payment-ready', (event) => {
                const detail = checkoutPayload(event);
                mountStripePayment(detail.payment || detail);
            });

            const loadPaypalSdk = (payment) => new Promise((resolve, reject) => {
                if (!payment?.client_id) {
                    reject(new Error(@js($locale === 'fr' ? 'Configuration PayPal incomplete.' : 'PayPal configuration is incomplete.')));
                    return;
                }

                const currency = (payment.currency || 'EUR').toUpperCase();
                const sdkUrl = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(payment.client_id)}&currency=${encodeURIComponent(currency)}&components=buttons`;

                if (window.paypal && paypalState.sdkUrl === sdkUrl) {
                    resolve();
                    return;
                }

                document.querySelectorAll('script[data-paypal-sdk="checkout"]').forEach((script) => script.remove());
                delete window.paypal;

                const script = document.createElement('script');
                script.src = sdkUrl;
                script.async = true;
                script.dataset.paypalSdk = 'checkout';
                script.onload = () => {
                    paypalState.sdkUrl = sdkUrl;
                    resolve();
                };
                script.onerror = reject;
                document.head.appendChild(script);
            });

            const mountPaypalPayment = async (payment) => {
                try {
                    showPaypalMessage('');
                    paypalState.orderId = payment?.external_id || null;
                    await loadPaypalSdk(payment);
                    const container = document.getElementById('paypal-button-container');
                    if (!container) return;
                    container.innerHTML = '';
                    if (!paypalState.orderId) {
                        throw new Error(@js($locale === 'fr' ? 'Ordre PayPal introuvable.' : 'PayPal order is missing.'));
                    }
                    window.paypal.Buttons({
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
                    }).render(container);
                } catch (error) {
                    const message = error.message || @js($locale === 'fr' ? 'PayPal ne peut pas etre charge.' : 'PayPal could not be loaded.');
                    showPaypalMessage(message);
                    $wire.failPaypalPayment(message);
                }
            };

            $wire.on('paypal-payment-ready', (event) => {
                const detail = checkoutPayload(event);
                mountPaypalPayment(detail.payment || detail);
            });

            document.addEventListener('click', async (event) => {
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
