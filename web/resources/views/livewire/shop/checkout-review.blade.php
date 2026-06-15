<section class="soft-grid px-4 py-4 dark:bg-ink sm:px-6 lg:py-5">
    <div class="mx-auto max-w-[1400px] lg:pr-[390px]">
        @include('partials.checkout-progress', ['currentLocale' => $locale, 'currentStep' => $orderConfirmed ? 'success' : 'checkout'])

        @if (! $orderConfirmed)
            @if ($checkoutError)
                <div class="mt-3 max-w-4xl rounded-lg border border-terracotta/25 bg-terracotta/10 px-3 py-2 text-sm font-semibold text-cocoa dark:text-cream">
                    {{ $checkoutError }}
                </div>
            @endif

            <div class="mt-3 lg:flex lg:items-start lg:gap-5">
                <form class="w-full max-w-4xl space-y-2" wire:submit.prevent="confirm">
                    <details open class="group overflow-hidden rounded-lg border border-leaf/10 bg-white/90 dark:border-white/10 dark:bg-white/5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-2 text-sm font-extrabold text-cocoa dark:text-cream [&::-webkit-details-marker]:hidden">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-leaf text-[10px] font-black text-white dark:bg-meadow dark:text-ink">1</span>
                                <span>{{ $locale === 'fr' ? 'Compte client' : 'Customer account' }}</span>
                            </span>
                            <span class="text-xs font-black text-leaf transition group-open:rotate-180 dark:text-meadow">v</span>
                        </summary>

                        <div class="border-t border-leaf/10 px-3 py-2 dark:border-white/10">
                            @if ($isAuthenticated)
                                <div class="grid gap-2 text-sm sm:grid-cols-3">
                                    <div class="rounded-md bg-linen px-3 py-2 dark:bg-white/5">
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ $locale === 'fr' ? 'Client' : 'Customer' }}</p>
                                        <p class="mt-1 truncate font-extrabold text-cocoa dark:text-cream">{{ $user['name'] ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) }}</p>
                                    </div>
                                    <div class="rounded-md bg-linen px-3 py-2 dark:bg-white/5">
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-cocoa/50 dark:text-cream/50">Email</p>
                                        <p class="mt-1 truncate font-extrabold text-cocoa dark:text-cream">{{ $user['email'] ?? '' }}</p>
                                    </div>
                                    <div class="rounded-md bg-linen px-3 py-2 dark:bg-white/5">
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ $locale === 'fr' ? 'Pays' : 'Country' }}</p>
                                        <p class="mt-1 font-extrabold text-cocoa dark:text-cream">{{ $countryNames[$user['country_code'] ?? ''] ?? ($user['country_code'] ?? '') }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-col gap-2 rounded-md bg-terracotta/10 px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Connectez-vous pour continuer.' : 'Sign in to continue.' }}</p>
                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <a href="{{ route('account.login', ['locale' => $locale]) }}" class="btn-primary px-4 py-2 text-xs" wire:navigate>{{ __('home.account.auth.sign_in') }}</a>
                                        <a href="{{ route('account.register', ['locale' => $locale]) }}" class="btn-secondary px-4 py-2 text-xs" wire:navigate>{{ __('home.account.auth.create_account') }}</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </details>

                    <details @if($isAuthenticated) open @endif class="group overflow-hidden rounded-lg border border-leaf/10 bg-white/90 dark:border-white/10 dark:bg-white/5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-2 text-sm font-extrabold text-cocoa dark:text-cream [&::-webkit-details-marker]:hidden">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-leaf text-[10px] font-black text-white dark:bg-meadow dark:text-ink">2</span>
                                <span>{{ $locale === 'fr' ? 'Adresse de livraison' : 'Delivery address' }}</span>
                            </span>
                            <span class="text-xs font-black text-leaf transition group-open:rotate-180 dark:text-meadow">v</span>
                        </summary>

                        <div class="border-t border-leaf/10 px-3 py-2 dark:border-white/10">
                            @if ($isAuthenticated && ! empty($addresses))
                                <div class="grid gap-2">
                                    @foreach ($addresses as $address)
                                        <label class="cursor-pointer rounded-md border border-leaf/10 bg-linen px-3 py-2 text-sm transition dark:border-white/10 dark:bg-white/5 {{ (int) $selectedAddressId === (int) $address['id'] ? 'ring-2 ring-leaf/35 dark:ring-meadow/40' : '' }}" wire:key="checkout-address-{{ $address['id'] }}">
                                            <span class="flex items-start gap-2">
                                                <input class="mt-1" type="radio" wire:model="selectedAddressId" value="{{ $address['id'] }}">
                                                <span class="min-w-0">
                                                    <span class="flex flex-wrap items-center gap-2">
                                                        <span class="font-extrabold text-cocoa dark:text-cream">{{ $address['label'] ?: $address['recipient_name'] }}</span>
                                                        @if ($address['is_default'])
                                                            <span class="rounded-full bg-mint px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-leaf dark:bg-white/10 dark:text-meadow">{{ __('home.account.addresses.default_badge') }}</span>
                                                        @endif
                                                    </span>
                                                    <span class="mt-1 block text-xs leading-5 text-cocoa/65 dark:text-cream/65">
                                                        {{ $address['recipient_name'] }} &middot; {{ $address['street_line_1'] }}, {{ $address['postal_code'] }} {{ $address['city'] }} &middot; {{ $countryNames[$address['country_code']] ?? $address['country_code'] }}
                                                    </span>
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($isAuthenticated)
                                <div class="flex flex-col gap-2 rounded-md bg-linen px-3 py-2 text-sm dark:bg-white/5 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Aucune adresse enregistrée.' : 'No saved address.' }}</p>
                                    <a href="{{ route('account.show', ['locale' => $locale]) }}" class="btn-secondary px-4 py-2 text-xs" wire:navigate>{{ $locale === 'fr' ? 'Gérer les adresses' : 'Manage addresses' }}</a>
                                </div>
                            @else
                                <div class="rounded-md bg-linen px-3 py-2 text-xs text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                                    {{ $locale === 'fr' ? 'Connexion requise.' : 'Sign-in required.' }}
                                </div>
                            @endif
                        </div>
                    </details>

                    <details class="group overflow-hidden rounded-lg border border-leaf/10 bg-white/90 dark:border-white/10 dark:bg-white/5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-2 text-sm font-extrabold text-cocoa dark:text-cream [&::-webkit-details-marker]:hidden">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-leaf text-[10px] font-black text-white dark:bg-meadow dark:text-ink">3</span>
                                <span>{{ $locale === 'fr' ? 'Livraison' : 'Delivery' }}</span>
                            </span>
                            <span class="text-xs font-black text-leaf transition group-open:rotate-180 dark:text-meadow">v</span>
                        </summary>

                        <div class="grid gap-2 border-t border-leaf/10 px-3 py-2 dark:border-white/10 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-2 rounded-md border border-leaf/10 bg-linen px-3 py-2 text-sm transition dark:border-white/10 dark:bg-white/5 {{ $delivery === 'standard' ? 'ring-2 ring-leaf/30' : '' }}">
                                <input class="mt-1" type="radio" value="standard" wire:model.live="delivery">
                                <span>
                                    <span class="block font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'À domicile' : 'Home delivery' }}</span>
                                    <span class="mt-1 block text-xs text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Adresse sélectionnée.' : 'Selected address.' }}</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-2 rounded-md border border-leaf/10 bg-linen px-3 py-2 text-sm transition dark:border-white/10 dark:bg-white/5 {{ $delivery === 'relay' ? 'ring-2 ring-leaf/30' : '' }}">
                                <input class="mt-1" type="radio" value="relay" wire:model.live="delivery">
                                <span>
                                    <span class="block font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Point relais' : 'Pickup point' }}</span>
                                    <span class="mt-1 block text-xs text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Carte relais prévue.' : 'Pickup map ready.' }}</span>
                                </span>
                            </label>
                        </div>
                    </details>

                    <details class="group overflow-hidden rounded-lg border border-leaf/10 bg-white/90 dark:border-white/10 dark:bg-white/5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-2 text-sm font-extrabold text-cocoa dark:text-cream [&::-webkit-details-marker]:hidden">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-leaf text-[10px] font-black text-white dark:bg-meadow dark:text-ink">4</span>
                                <span>{{ $locale === 'fr' ? 'Transporteur' : 'Carrier' }}</span>
                            </span>
                            <span class="text-xs font-black text-leaf transition group-open:rotate-180 dark:text-meadow">v</span>
                        </summary>

                        <div class="border-t border-leaf/10 px-3 py-2 dark:border-white/10">
                            <div class="grid gap-2">
                                @foreach ($carriers as $key => $option)
                                    @php
                                        $isDisabled = $delivery === 'standard' && $option['type'] === 'relay';
                                        $isSelected = $carrier === $key;
                                    @endphp
                                    <label class="grid cursor-pointer gap-2 rounded-md border border-leaf/10 bg-linen px-3 py-2 text-sm transition dark:border-white/10 dark:bg-white/5 sm:grid-cols-[auto_1fr_auto] sm:items-center {{ $isSelected ? 'ring-2 ring-leaf/30 dark:ring-meadow/40' : '' }} {{ $isDisabled ? 'opacity-50' : '' }}" wire:key="checkout-carrier-{{ $key }}">
                                        <input class="mt-1 sm:mt-0" type="radio" value="{{ $key }}" wire:model.live="carrier" @disabled($isDisabled)>
                                        <span class="font-extrabold text-cocoa dark:text-cream">{{ $option['name'] }}</span>
                                        <span class="flex flex-wrap gap-2 text-xs font-bold text-cocoa/60 dark:text-cream/60 sm:justify-end">
                                            <span class="rounded-full bg-white px-2 py-0.5 dark:bg-white/10">{{ $option['eta'] }}</span>
                                            <span class="rounded-full bg-mint px-2 py-0.5 text-leaf dark:bg-white/10 dark:text-meadow">{{ $option['price'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            @if ($delivery === 'relay')
                                <div class="mt-2 overflow-hidden rounded-md border border-leaf/10 bg-linen dark:border-white/10 dark:bg-white/5">
                                    <div class="grid min-h-[104px] lg:grid-cols-[1fr_0.7fr]">
                                        <div class="relative min-h-[104px] bg-[linear-gradient(135deg,#eaf7df_0%,#ffffff_55%,#dcefd3_100%)] dark:bg-[linear-gradient(135deg,#172414_0%,#121a10_100%)]">
                                            <div class="absolute inset-2 rounded-md border border-leaf/15 bg-white/70 dark:border-white/10 dark:bg-white/5"></div>
                                            <div class="absolute left-[20%] top-[35%] h-2.5 w-2.5 rounded-full bg-leaf shadow-[0_0_0_6px_rgba(47,125,27,0.12)] dark:bg-meadow"></div>
                                            <div class="absolute left-[58%] top-[50%] h-2.5 w-2.5 rounded-full bg-terracotta shadow-[0_0_0_6px_rgba(79,176,0,0.12)]"></div>
                                            <div class="absolute left-[76%] top-[28%] h-2.5 w-2.5 rounded-full bg-leaf shadow-[0_0_0_6px_rgba(47,125,27,0.12)] dark:bg-meadow"></div>
                                        </div>
                                        <div class="space-y-1.5 p-2">
                                            @foreach (['Centre-ville', 'Bureau de poste', 'Commerce partenaire'] as $pickup)
                                                <div class="rounded-md bg-white px-2 py-1.5 text-xs font-semibold text-cocoa dark:bg-white/5 dark:text-cream">{{ $pickup }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </details>

                    <button type="submit" class="btn-primary w-full py-3 text-sm disabled:pointer-events-none disabled:opacity-50" wire:loading.attr="disabled" @disabled(count($this->cartItems()) === 0 || ! $isAuthenticated || ! $selectedAddressId)>
                        {{ $locale === 'fr' ? 'Confirmer sans paiement' : 'Confirm without payment' }}
                    </button>
                </form>

                <aside class="mt-4 lg:fixed lg:right-6 lg:top-32 lg:z-30 lg:mt-0 lg:w-[350px] xl:right-8">
                    <div class="rounded-lg border border-leaf/10 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-white/10 dark:bg-ink/95">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-base font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Récapitulatif' : 'Summary' }}</h2>
                            <strong class="shrink-0 text-leaf dark:text-meadow">{{ $this->formattedTotal() }}</strong>
                        </div>

                        <div wire:loading.flex class="mt-2 rounded-md bg-linen px-3 py-2 text-xs font-semibold text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                            {{ __('home.cart.loading') }}
                        </div>

                        @if ($cartError)
                            <div class="mt-2 rounded-md border border-leaf/20 bg-mint px-3 py-2 text-xs font-semibold text-leaf dark:bg-white/5">
                                {{ $cartError }}
                            </div>
                        @endif

                        @if (! $cartLoading && count($this->cartItems()) === 0)
                            <div class="mt-2 rounded-md bg-linen px-3 py-2 text-xs leading-5 text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                                {{ $locale === 'fr' ? 'Panier vide.' : 'Empty cart.' }}
                            </div>
                        @endif

                        @if (count($this->cartItems()) > 0)
                            <div class="mt-2 space-y-1.5">
                                @foreach ($this->cartItems() as $item)
                                    <div class="flex items-start justify-between gap-3 border-b border-leaf/10 pb-1.5 text-xs dark:border-white/10" wire:key="checkout-cart-item-{{ $item['id'] }}">
                                        <div class="min-w-0">
                                            <p class="truncate font-extrabold text-cocoa dark:text-cream">{{ data_get($item, 'product.name') }}</p>
                                            <p class="mt-0.5 truncate text-cocoa/55 dark:text-cream/55">{{ $item['quantity'] }} x {{ data_get($item, 'variant.name') ?: data_get($item, 'product.origin') }}</p>
                                        </div>
                                        <strong class="shrink-0 text-leaf dark:text-meadow">{{ $item['formatted_line_total'] }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-2 space-y-1.5 border-t border-leaf/10 pt-2 text-xs text-cocoa/70 dark:border-white/10 dark:text-cream/70">
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Sous-total' : 'Subtotal' }}</span>
                                <strong class="text-cocoa dark:text-cream">{{ $this->formattedTotal() }}</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Transporteur' : 'Carrier' }}</span>
                                <span class="max-w-[190px] truncate text-right">{{ $selectedCarrier['name'] ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Délai' : 'ETA' }}</span>
                                <span class="text-right">{{ $selectedCarrier['eta'] ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Paiement' : 'Payment' }}</span>
                                <span>{{ $locale === 'fr' ? 'Étape suivante' : 'Next step' }}</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        @else
            <div class="mt-4">
                <div class="mx-auto max-w-2xl rounded-lg border border-leaf/10 bg-white p-5 text-center shadow-sm dark:border-white/10 dark:bg-white/5">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-mint text-sm font-black text-leaf dark:bg-white/10 dark:text-meadow">
                        OK
                    </div>

                    <h1 class="text-2xl font-extrabold text-cocoa dark:text-cream">
                        {{ $locale === 'fr' ? 'Commande validée.' : 'Order confirmed.' }}
                    </h1>

                    <div class="mt-5 grid gap-2 sm:grid-cols-2">
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary w-full" wire:navigate>{{ $locale === 'fr' ? 'Retour à la boutique' : 'Back to shop' }}</a>
                        <a href="{{ route('account.show', ['locale' => $locale]) }}" class="btn-secondary w-full" wire:navigate>
                            {{ $locale === 'fr' ? 'Voir mon compte' : 'View my account' }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($orderConfirmed)
        @teleport('body')
            <div
                x-data="{ open: true }"
                x-show="open"
                x-cloak
                x-transition.opacity.duration.150ms
                x-on:keydown.escape.window="open = false"
                class="fixed inset-0 z-[90] flex items-end justify-center bg-black/45 p-4 backdrop-blur-sm sm:items-center"
                role="dialog"
                aria-modal="true"
                aria-label="{{ $locale === 'fr' ? 'Commande validée' : 'Order confirmed' }}"
            >
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="translate-y-4 opacity-0 sm:scale-95"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
                    class="w-full max-w-md rounded-lg border border-leaf/10 bg-white p-5 text-center shadow-2xl dark:border-white/10 dark:bg-ink"
                >
                    <h2 class="text-xl font-extrabold text-cocoa dark:text-cream">
                        {{ $locale === 'fr' ? 'Commande validée.' : 'Order confirmed.' }}
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-cocoa/70 dark:text-cream/70">
                        {{ $locale === 'fr' ? 'Votre panier a été vidé.' : 'Your cart has been cleared.' }}
                    </p>
                    <div class="mt-5 grid gap-2 sm:grid-cols-2">
                        <button type="button" class="btn-secondary w-full" x-on:click="open = false">
                            {{ $locale === 'fr' ? 'Fermer' : 'Close' }}
                        </button>
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary w-full" wire:navigate>
                            {{ $locale === 'fr' ? 'Continuer' : 'Continue' }}
                        </a>
                    </div>
                </div>
            </div>
        @endteleport
    @endif

    @script
        <script>
            const checkoutStorageKey = 'denetfils_cart_token';
            $wire.restoreFromBrowser(localStorage.getItem(checkoutStorageKey));

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

            $wire.on('checkout-confirmed', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        </script>
    @endscript
</section>
