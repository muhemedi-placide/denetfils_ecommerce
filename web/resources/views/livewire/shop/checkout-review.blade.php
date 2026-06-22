<section class="soft-grid px-4 pb-28 pt-8 dark:bg-ink sm:px-6 lg:px-8 lg:pb-16 lg:pt-10" x-data="{ chronoModal: false }">
    <div class="mx-auto max-w-7xl">
        @include('partials.checkout-progress', ['currentLocale' => $locale, 'currentStep' => $orderConfirmed ? 'success' : 'checkout'])

        @if (! $orderConfirmed)
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
                            @foreach ($carriers as $key => $option)
                                <div wire:key="carrier-option-wrapper-{{ $key }}">
                                    <label class="grid cursor-pointer items-center gap-4 rounded-xl bg-neutral-100 px-5 py-5 transition hover:bg-neutral-50 dark:bg-white/5 dark:hover:bg-white/10 sm:grid-cols-[36px_86px_minmax(170px,1fr)_minmax(190px,1.3fr)_120px]">
                                        <span class="grid h-7 w-7 place-items-center rounded-full border border-cocoa/10 bg-white dark:border-white/20 dark:bg-white/5">
                                            <input class="h-4 w-4 accent-[#48a900]" type="radio" value="{{ $key }}" wire:model.live="carrier">
                                        </span>

                                        <span class="grid h-14 w-20 place-items-center">
                                            @if ($option['logo'] === 'mr')
                                                <span class="grid h-12 w-12 place-items-center rounded-xl bg-[#f7b6cd] text-2xl font-black text-[#a01455]">r</span>
                                            @else
                                                <span class="text-xs font-black text-[#168bd0]">▣ chronopost</span>
                                            @endif
                                        </span>

                                        <span class="text-lg font-black leading-6 text-cocoa dark:text-cream">{{ $option['name'] }}</span>
                                        <span class="text-base leading-6 text-cocoa/80 dark:text-cream/75">{{ $option['eta'] }}</span>
                                        <span class="text-lg font-semibold text-cocoa dark:text-cream">{{ $option['price'] }}</span>
                                    </label>

                                    @if ($carrier === $key && in_array($key, ['mondial_relay_locker', 'mondial_relay_pickup'], true))
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

                                    @if ($carrier === $key && $key === 'chrono_relais_pickup' && $nearestPickupPoint)
                                        <div class="mt-0 rounded-b-xl bg-neutral-100 px-6 pb-5 pt-1 text-cocoa dark:bg-white/5 dark:text-cream">
                                            <p class="text-base leading-7">
                                                <strong>{{ $locale === 'fr' ? 'Point de retrait le plus proche :' : 'Nearest pickup point:' }}</strong>
                                                {{ $nearestPickupPoint['name'] }}<br>
                                                {{ $nearestPickupPoint['address'] }} {{ $nearestPickupPoint['distance'] ? '(' . $nearestPickupPoint['distance'] . ')' : '' }}
                                            </p>
                                            <button type="button" class="mt-1 text-base font-semibold underline underline-offset-4" @click="chronoModal = true">
                                                {{ $locale === 'fr' ? 'Autre point de retrait' : 'Other pickup point' }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <div class="pt-2">
                                <label class="block text-base font-medium text-cocoa dark:text-cream">
                                    {{ $locale === 'fr' ? 'Si vous voulez nous laisser un message à propos de votre commande, merci de bien vouloir le renseigner dans le champ ci-contre' : 'Leave a message about your order if needed.' }}
                                    <textarea class="mt-3 h-24 w-full resize-none rounded-none border border-cocoa/15 bg-white px-3 py-2 outline-none focus:border-[#48a900] dark:border-white/10 dark:bg-white/5"></textarea>
                                </label>
                            </div>

                            <button type="submit" class="rounded-full bg-[#48ad4d] px-10 py-4 text-base font-black text-white shadow-[0_14px_32px_rgba(69,173,77,.25)] disabled:pointer-events-none disabled:opacity-50" wire:loading.attr="disabled" @disabled(count($this->cartItems()) === 0 || ! $isAuthenticated || ! $selectedAddressId || ($delivery === 'relay' && ! $selectedPickupPointDetails))>
                                <span wire:loading.remove>{{ $locale === 'fr' ? 'Continuer' : 'Continue' }}</span>
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
                                    <div class="flex items-start justify-between gap-3 border-b border-leaf/10 pb-3 text-sm dark:border-white/10" wire:key="checkout-cart-item-{{ $item['id'] }}">
                                        <div class="min-w-0">
                                            <p class="line-clamp-2 font-black text-cocoa dark:text-cream">{{ data_get($item, 'product.name') }}</p>
                                            <p class="mt-1 truncate text-xs text-cocoa/55 dark:text-cream/55">{{ $item['quantity'] }} × {{ data_get($item, 'variant.name') ?: data_get($item, 'product.origin') }}</p>
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
            const checkoutStorageKey = 'denetfils_cart_token';
            $wire.restoreFromBrowser(localStorage.getItem(checkoutStorageKey));
            const checkoutPayload = (event) => Array.isArray(event) ? (event[0] || {}) : (event || {});
            $wire.on('cart-token-stored', (event) => {
                const detail = checkoutPayload(event);
                if (detail.token) localStorage.setItem(checkoutStorageKey, detail.token);
            });
            $wire.on('cart-token-cleared', () => localStorage.removeItem(checkoutStorageKey));
            $wire.on('checkout-confirmed', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        </script>
    @endscript
</section>
