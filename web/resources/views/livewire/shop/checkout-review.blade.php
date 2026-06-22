<section class="soft-grid px-4 pb-28 pt-8 dark:bg-ink sm:px-6 lg:px-8 lg:pb-16 lg:pt-10">
    <div class="mx-auto max-w-7xl">
        @include('partials.checkout-progress', ['currentLocale' => $locale, 'currentStep' => $orderConfirmed ? 'success' : 'checkout'])

        @if (! $orderConfirmed)
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="section-kicker">{{ $locale === 'fr' ? 'Commande rapide' : 'Fast checkout' }}</p>
                    <h1 class="section-title mt-3">{{ $locale === 'fr' ? 'Valider votre commande' : 'Confirm your order' }}</h1>
                    <p class="section-copy mt-4">{{ $locale === 'fr' ? 'Un parcours court : compte, adresse, transporteur, point relais si nécessaire, puis confirmation.' : 'A short flow: account, address, carrier, pickup point if needed, then confirmation.' }}</p>
                </div>
                <a href="{{ route('cart.show', ['locale' => $locale]) }}" class="btn-secondary w-full sm:w-auto" wire:navigate>
                    {{ $locale === 'fr' ? 'Modifier le panier' : 'Edit cart' }}
                </a>
            </div>

            @if ($checkoutError)
                <div class="mb-4 rounded-xl border border-coral/25 bg-coral/10 px-4 py-3 text-sm font-semibold text-cocoa dark:text-cream">
                    {{ $checkoutError }}
                </div>
            @endif

            @if ($quoteError)
                <div class="mb-4 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-forest dark:border-white/10 dark:bg-white/5 dark:text-meadow">
                    {{ $quoteError }}
                </div>
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
                                        <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Identifiez le client pour garder l’historique et les adresses.' : 'Identify the customer to keep history and addresses.' }}</p>
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
                                        <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Elle sert au calcul transporteur et à la recherche de relais.' : 'Used for carrier pricing and pickup search.' }}</p>
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

                    <section class="form-card">
                        <div class="flex items-start gap-4">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-forest text-xs font-black text-cream dark:bg-meadow dark:text-ink">3</span>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Mode de livraison' : 'Delivery mode' }}</h2>
                                <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Priorité aux relais utilisés par DEN & FILS : Mondial Relay et Chrono Relais.' : 'Priority to DEN & FILS carriers: Mondial Relay and Chrono Relais.' }}</p>

                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <label class="cursor-pointer rounded-xl border border-leaf/10 bg-linen p-4 transition dark:border-white/10 dark:bg-white/5 {{ $delivery === 'relay' ? 'ring-2 ring-leaf/35 dark:ring-meadow/40' : 'hover:border-leaf/25' }}">
                                        <span class="flex items-start gap-3">
                                            <input class="mt-1" type="radio" value="relay" wire:model.live="delivery">
                                            <span>
                                                <span class="block font-black text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Point relais' : 'Pickup point' }}</span>
                                                <span class="mt-1 block text-xs leading-5 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Souvent plus flexible et économique.' : 'Often more flexible and cost-efficient.' }}</span>
                                            </span>
                                        </span>
                                    </label>

                                    <label class="cursor-pointer rounded-xl border border-leaf/10 bg-linen p-4 transition dark:border-white/10 dark:bg-white/5 {{ $delivery === 'home' ? 'ring-2 ring-leaf/35 dark:ring-meadow/40' : 'hover:border-leaf/25' }}">
                                        <span class="flex items-start gap-3">
                                            <input class="mt-1" type="radio" value="home" wire:model.live="delivery">
                                            <span>
                                                <span class="block font-black text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Domicile' : 'Home delivery' }}</span>
                                                <span class="mt-1 block text-xs leading-5 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Prévu pour Chronopost domicile.' : 'Prepared for Chronopost home delivery.' }}</span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="form-card">
                        <div class="flex items-start gap-4">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-forest text-xs font-black text-cream dark:bg-meadow dark:text-ink">4</span>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Transporteur' : 'Carrier' }}</h2>
                                <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Le tarif exact sera branché à l’API transporteur.' : 'Exact price will be connected to the carrier API.' }}</p>

                                <div class="mt-4 grid gap-3">
                                    @foreach ($carriers as $key => $option)
                                        <label class="cursor-pointer rounded-xl border border-leaf/10 bg-linen p-4 transition dark:border-white/10 dark:bg-white/5 {{ $carrier === $key ? 'ring-2 ring-leaf/35 dark:ring-meadow/40' : 'hover:border-leaf/25' }}" wire:key="checkout-carrier-{{ $key }}">
                                            <span class="flex items-start gap-3">
                                                <input class="mt-1" type="radio" value="{{ $key }}" wire:model.live="carrier">
                                                <span class="min-w-0 flex-1">
                                                    <span class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                        <span class="font-black text-cocoa dark:text-cream">{{ $option['name'] }}</span>
                                                        <span class="flex flex-wrap gap-2 text-xs font-bold text-cocoa/60 dark:text-cream/60">
                                                            <span class="rounded-full bg-white px-2 py-1 dark:bg-white/10">{{ $option['eta'] }}</span>
                                                            <span class="rounded-full bg-mint px-2 py-1 text-forest dark:bg-white/10 dark:text-meadow">{{ $option['price'] }}</span>
                                                        </span>
                                                    </span>
                                                    <span class="mt-2 block text-xs leading-5 text-cocoa/60 dark:text-cream/60">{{ $option['description'] }}</span>
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </section>

                    @if ($delivery === 'relay')
                        <section class="form-card">
                            <div class="flex items-start gap-4">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-forest text-xs font-black text-cream dark:bg-meadow dark:text-ink">5</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h2 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Point relais' : 'Pickup point' }}</h2>
                                            <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Recherche par ville, code postal ou nom du relais.' : 'Search by city, postal code or pickup name.' }}</p>
                                        </div>
                                        <input class="input-premium w-full sm:max-w-[220px]" type="search" wire:model.live.debounce.250ms="pickupQuery" placeholder="{{ $locale === 'fr' ? 'Rechercher' : 'Search' }}">
                                    </div>

                                    <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_0.9fr]">
                                        <div class="space-y-2">
                                            @forelse ($pickupPoints as $point)
                                                <button type="button" class="w-full rounded-xl border border-leaf/10 bg-linen p-4 text-left transition dark:border-white/10 dark:bg-white/5 {{ $selectedPickupPoint === $point['code'] ? 'ring-2 ring-leaf/35 dark:ring-meadow/40' : 'hover:border-leaf/25' }}" wire:click="selectPickupPoint('{{ $point['code'] }}')" wire:key="pickup-point-{{ $point['code'] }}">
                                                    <span class="flex items-start justify-between gap-3">
                                                        <span class="min-w-0">
                                                            <span class="block text-xs font-black uppercase tracking-wide text-forest dark:text-meadow">{{ $point['carrier'] }}</span>
                                                            <span class="mt-1 block font-black text-cocoa dark:text-cream">{{ $point['name'] }}</span>
                                                            <span class="mt-1 block text-xs leading-5 text-cocoa/60 dark:text-cream/60">{{ $point['address'] }}</span>
                                                            <span class="mt-1 block text-xs leading-5 text-cocoa/60 dark:text-cream/60">{{ $point['hours'] }}</span>
                                                        </span>
                                                        <span class="shrink-0 rounded-full bg-white px-2 py-1 text-xs font-bold text-cocoa/60 dark:bg-white/10 dark:text-cream/70">{{ $point['distance'] }}</span>
                                                    </span>
                                                </button>
                                            @empty
                                                <div class="rounded-xl bg-linen px-4 py-3 text-sm text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                                                    {{ $locale === 'fr' ? 'Aucun relais trouvé pour cette recherche.' : 'No pickup point found for this search.' }}
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="overflow-hidden rounded-xl border border-leaf/10 bg-linen dark:border-white/10 dark:bg-white/5">
                                            <div class="relative min-h-[230px] bg-[linear-gradient(135deg,#fff7df_0%,#e8f5dc_56%,#f8ecd0_100%)] dark:bg-[linear-gradient(135deg,#172414_0%,#121a10_100%)]">
                                                <div class="absolute inset-4 rounded-xl border border-leaf/15 bg-white/70 dark:border-white/10 dark:bg-white/5"></div>
                                                <div class="absolute left-[18%] top-[38%] h-4 w-4 rounded-full bg-forest shadow-[0_0_0_8px_rgba(15,95,34,0.13)] dark:bg-meadow"></div>
                                                <div class="absolute left-[55%] top-[54%] h-4 w-4 rounded-full bg-coral shadow-[0_0_0_8px_rgba(255,112,71,0.16)]"></div>
                                                <div class="absolute left-[74%] top-[27%] h-4 w-4 rounded-full bg-forest shadow-[0_0_0_8px_rgba(15,95,34,0.13)] dark:bg-meadow"></div>
                                                <div class="absolute bottom-4 left-4 right-4 rounded-xl bg-white/90 p-3 text-xs font-semibold text-cocoa shadow-sm dark:bg-ink/90 dark:text-cream">
                                                    {{ $locale === 'fr' ? 'La carte réelle sera branchée avec l’API Mondial Relay / Chrono Relais.' : 'The real map will be connected to the Mondial Relay / Chrono Relais API.' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    @endif

                    <section class="form-card">
                        <div class="flex items-start gap-4">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-forest text-xs font-black text-cream dark:bg-meadow dark:text-ink">{{ $delivery === 'relay' ? '6' : '5' }}</span>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Paiement et confirmation' : 'Payment and confirmation' }}</h2>
                                <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'La commande sera créée dans l’API avant le paiement réel Stripe/PayPal.' : 'The order will be created in the API before real Stripe/PayPal payment.' }}</p>
                                <button type="submit" class="btn-primary mt-5 w-full py-3 text-sm disabled:pointer-events-none disabled:opacity-50" wire:loading.attr="disabled" @disabled(count($this->cartItems()) === 0 || ! $isAuthenticated || ! $selectedAddressId)>
                                    <span wire:loading.remove>{{ $locale === 'fr' ? 'Créer la commande' : 'Create order' }}</span>
                                    <span wire:loading>{{ __('home.cart.loading') }}</span>
                                </button>
                            </div>
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
                            <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-coral"></span>
                            {{ __('home.cart.loading') }}
                        </div>

                        @if ($cartError)
                            <div class="mt-3 rounded-lg border border-leaf/20 bg-mint px-3 py-2 text-xs font-semibold text-forest dark:bg-white/5">
                                {{ $cartError }}
                            </div>
                        @endif

                        @if (! $cartLoading && count($this->cartItems()) === 0)
                            <div class="mt-3 rounded-lg bg-linen px-3 py-3 text-sm text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                                {{ $locale === 'fr' ? 'Panier vide.' : 'Empty cart.' }}
                            </div>
                        @endif

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
                        @endif

                        <div class="mt-4 space-y-2 border-t border-leaf/10 pt-4 text-sm text-cocoa/70 dark:border-white/10 dark:text-cream/70">
                            <div class="flex items-center justify-between"><span>{{ $locale === 'fr' ? 'Sous-total' : 'Subtotal' }}</span><strong class="text-cocoa dark:text-cream">{{ $displayQuote['formatted_subtotal'] }}</strong></div>
                            <div class="flex items-center justify-between"><span>{{ $locale === 'fr' ? 'Livraison' : 'Shipping' }}</span><strong class="text-cocoa dark:text-cream">{{ $displayQuote['formatted_shipping'] }}</strong></div>
                            <div class="flex items-center justify-between"><span>{{ $locale === 'fr' ? 'TVA' : 'VAT' }}</span><strong class="text-cocoa dark:text-cream">{{ $displayQuote['formatted_tax'] }}</strong></div>
                            <div class="flex items-center justify-between gap-3"><span>{{ $locale === 'fr' ? 'Transporteur' : 'Carrier' }}</span><span class="truncate text-right font-semibold">{{ $selectedCarrier['name'] ?? '-' }}</span></div>
                            <div class="flex items-center justify-between gap-3"><span>{{ $locale === 'fr' ? 'Délai' : 'ETA' }}</span><span class="text-right font-semibold">{{ $selectedCarrier['eta'] ?? '-' }}</span></div>
                            @if ($selectedPickupPointDetails)
                                <div class="rounded-lg bg-mint p-3 text-xs leading-5 text-forest dark:bg-white/5 dark:text-meadow">
                                    <strong class="block">{{ $locale === 'fr' ? 'Relais choisi' : 'Selected pickup' }}</strong>
                                    {{ $selectedPickupPointDetails['name'] }} · {{ $selectedPickupPointDetails['address'] }}
                                </div>
                            @endif
                            <div class="flex items-center justify-between"><span>{{ $locale === 'fr' ? 'Paiement' : 'Payment' }}</span><span class="font-semibold">{{ $locale === 'fr' ? 'Étape prête' : 'Ready step' }}</span></div>
                            <div class="flex items-center justify-between border-t border-leaf/10 pt-3 text-base dark:border-white/10">
                                <span class="font-black text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Total' : 'Total' }}</span>
                                <strong class="text-xl text-forest dark:text-meadow">{{ $displayQuote['formatted_total'] }}</strong>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        @else
            <div class="mx-auto mt-8 max-w-2xl rounded-[1.5rem] border border-leaf/10 bg-white p-8 text-center shadow-tropical dark:border-white/10 dark:bg-white/5">
                <div class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-full bg-sunshine text-sm font-black text-forest">
                    OK
                </div>
                <p class="section-kicker">{{ $locale === 'fr' ? 'Confirmation' : 'Confirmation' }}</p>
                <h1 class="mt-3 text-4xl font-black text-forest dark:text-meadow">
                    {{ $locale === 'fr' ? 'Commande validée.' : 'Order confirmed.' }}
                </h1>
                @if (! empty($confirmedOrder['order_number']))
                    <p class="mx-auto mt-4 w-fit rounded-full bg-mint px-4 py-2 text-sm font-black text-forest dark:bg-white/10 dark:text-meadow">
                        {{ $confirmedOrder['order_number'] }}
                    </p>
                @endif
                <p class="mx-auto mt-4 max-w-md text-sm leading-6 text-cocoa/65 dark:text-cream/65">
                    {{ $locale === 'fr' ? 'Votre panier a été vidé. Le parcours est prêt pour la prochaine étape de paiement réel et création d’expédition.' : 'Your cart has been cleared. The flow is ready for real payment and shipment creation.' }}
                </p>
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
