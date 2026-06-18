<div>
    <section class="soft-grid px-4 py-10 dark:bg-ink sm:px-8 lg:py-14">
        <div class="mx-auto max-w-7xl">
            @include('partials.checkout-progress', ['currentLocale' => $locale, 'currentStep' => 'cart'])

            <nav class="mobile-scrollbarless flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm font-semibold text-cocoa/60 dark:text-cream/60" aria-label="Breadcrumb">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-forest" wire:navigate>{{ __('home.nav.home') }}</a>
                <span>/</span>
                <span class="text-forest">{{ __('home.cart.title') }}</span>
            </nav>

            <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start">
                <div>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="section-kicker">{{ $locale === 'fr' ? 'Étape 1' : 'Step 1' }}</p>
                            <h1 class="section-title mt-3">{{ $locale === 'fr' ? 'Vérifier le panier' : 'Review cart' }}</h1>
                            <p class="section-copy mt-4">{{ $locale === 'fr' ? 'Gardez uniquement les produits souhaités, ajustez les quantités, puis passez à la livraison.' : 'Keep only the products you want, adjust quantities, then continue to delivery.' }}</p>
                        </div>
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary w-full sm:w-auto" wire:navigate>{{ $locale === 'fr' ? 'Continuer les achats' : 'Continue shopping' }}</a>
                    </div>

                    <div wire:loading.flex class="mt-6 rounded-[1.25rem] border border-leaf/10 bg-white p-5 text-sm font-semibold text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70">
                        {{ __('home.cart.loading') }}
                    </div>

                    @if ($cartError)
                        <div class="mt-6 rounded-[1.25rem] border border-coral/25 bg-coral/10 p-5 text-sm font-semibold text-cocoa dark:text-cream">
                            {{ $cartError }}
                        </div>
                    @endif

                    @if (! $cartLoading && count($this->cartItems()) === 0)
                        <div class="utility-section mt-6">
                            <h2 class="text-2xl font-black text-forest dark:text-meadow">{{ __('home.cart.empty') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ $locale === 'fr' ? 'Découvrez les produits DEN & FILS et ajoutez vos essentiels avant de commander.' : 'Discover DEN & FILS products and add your essentials before ordering.' }}</p>
                            <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary mt-5 w-full sm:w-auto" wire:navigate>{{ __('home.hero.primary_cta') }}</a>
                        </div>
                    @endif

                    @if (count($this->cartItems()) > 0)
                        <div class="mt-6 space-y-4">
                            @foreach ($this->cartItems() as $item)
                                @php($imageUrl = data_get($item, 'product.image.url'))
                                <article class="grid gap-4 rounded-[1.5rem] border border-leaf/10 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5 sm:grid-cols-[110px_1fr] sm:p-5" wire:key="cart-page-item-{{ $item['id'] }}">
                                    @if ($imageUrl)
                                        <img class="h-32 w-full rounded-[1rem] object-cover sm:h-28 sm:w-28" src="{{ $imageUrl }}" alt="{{ data_get($item, 'product.image.alt_text', data_get($item, 'product.name')) }}" width="112" height="112" loading="lazy" decoding="async">
                                    @else
                                        <div class="grid h-32 w-full place-items-center rounded-[1rem] bg-sunshine/35 text-sm font-black text-forest sm:h-28 sm:w-28">DF</div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <h2 class="text-xl font-black text-forest dark:text-meadow">{{ data_get($item, 'product.name') }}</h2>
                                                <p class="mt-1 text-sm text-cocoa/60 dark:text-cream/60">{{ data_get($item, 'variant.name') ?: data_get($item, 'product.origin') }}</p>
                                            </div>
                                            <button type="button" class="inline-flex min-h-[40px] items-center justify-center rounded-full border border-leaf/15 px-4 py-2 text-xs font-black uppercase tracking-wide text-cocoa/60 transition hover:border-coral hover:text-coral disabled:opacity-60 dark:border-white/10 dark:text-cream/60" wire:click="removeItem({{ (int) $item['id'] }})" wire:loading.attr="disabled">
                                                {{ __('home.cart.remove') }}
                                            </button>
                                        </div>

                                        <div class="mt-5 flex flex-wrap items-center justify-between gap-4">
                                            <div class="flex min-h-[44px] items-center rounded-full border border-leaf/20 bg-mint/70 dark:border-white/10 dark:bg-white/5">
                                                <button type="button" class="px-4 py-2 text-sm font-bold disabled:opacity-50" wire:click="decrementItem({{ (int) $item['id'] }}, {{ (int) $item['quantity'] }})" @disabled((int) $item['quantity'] <= 1)>−</button>
                                                <input class="w-14 bg-transparent text-center text-sm font-bold outline-none" type="number" min="1" value="{{ (int) $item['quantity'] }}" wire:change="updateItem({{ (int) $item['id'] }}, $event.target.value)">
                                                <button type="button" class="px-4 py-2 text-sm font-bold" wire:click="incrementItem({{ (int) $item['id'] }}, {{ (int) $item['quantity'] }})">+</button>
                                            </div>
                                            <strong class="brand-display text-2xl text-forest dark:text-meadow">{{ $item['formatted_line_total'] }}</strong>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside class="lg:sticky lg:top-32">
                    <div class="rounded-[1.5rem] border border-leaf/10 bg-white p-6 shadow-tropical dark:border-white/10 dark:bg-white/5">
                        <p class="section-kicker">{{ $locale === 'fr' ? 'Résumé' : 'Summary' }}</p>
                        <h2 class="mt-3 text-2xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Total panier' : 'Cart total' }}</h2>

                        <div class="mt-6 space-y-4 text-sm text-cocoa/70 dark:text-cream/70">
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Sous-total' : 'Subtotal' }}</span>
                                <strong class="text-cocoa dark:text-cream">{{ $this->formattedTotal() }}</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Livraison' : 'Delivery' }}</span>
                                <span>{{ $locale === 'fr' ? 'Calculée à l’étape suivante' : 'Calculated next step' }}</span>
                            </div>
                        </div>

                        <div class="mt-6 rounded-[1rem] bg-mint p-4 text-sm font-semibold leading-6 text-forest dark:bg-white/5 dark:text-meadow">
                            {{ $locale === 'fr' ? 'Objectif : passer du panier à la validation sans friction.' : 'Goal: go from cart to confirmation without friction.' }}
                        </div>

                        <a href="{{ route('checkout.show', ['locale' => $locale]) }}" class="btn-primary mt-6 w-full {{ count($this->cartItems()) === 0 ? 'pointer-events-none opacity-50' : '' }}" wire:navigate>
                            {{ $locale === 'fr' ? 'Continuer vers livraison' : 'Continue to delivery' }}
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if (! empty($recommendedProducts))
        <section class="bg-cream px-4 py-12 dark:bg-ink sm:px-8 lg:py-16">
            <div class="mx-auto max-w-7xl">
                <div class="mb-7 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="section-kicker">{{ $locale === 'fr' ? 'Compléter votre panier' : 'Complete your cart' }}</p>
                        <h2 class="mt-3 text-3xl font-black text-forest dark:text-meadow">{{ __('home.product.related_title') }}</h2>
                    </div>
                    <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary w-full sm:w-fit" wire:navigate>{{ __('home.spotlight.cta') }}</a>
                </div>

                <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
                    @foreach ($recommendedProducts as $product)
                        <article class="market-card group min-w-[260px] overflow-hidden bg-white dark:bg-white/5 lg:min-w-0" wire:key="recommended-{{ $product['id'] }}">
                            <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" wire:navigate>
                                @if (! empty($product['primary_image']['url']))
                                    <img class="h-48 w-full object-cover transition group-hover:scale-[1.04]" src="{{ $product['primary_image']['url'] }}" alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}" loading="lazy" decoding="async">
                                @else
                                    <div class="grid h-48 place-items-center bg-sunshine/35 text-forest">DF</div>
                                @endif
                            </a>
                            <div class="p-5">
                                <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" wire:navigate>
                                    <h3 class="line-clamp-2 text-xl font-black text-forest transition group-hover:text-leaf dark:text-meadow">{{ $product['name'] }}</h3>
                                </a>
                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <span class="brand-display text-2xl text-forest dark:text-meadow">{{ $product['formatted_price'] }}</span>
                                    <button type="button" class="btn-primary px-4 py-2 text-xs" wire:click="addRecommended({{ (int) $product['id'] }})" wire:loading.attr="disabled">{{ __('home.products.cta') }}</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @script
        <script>
            const cartPageStorageKey = 'denetfils_cart_token';
            $wire.restoreFromBrowser(localStorage.getItem(cartPageStorageKey));

            const cartPagePayload = (event) => Array.isArray(event) ? (event[0] || {}) : (event || {});

            $wire.on('cart-token-stored', (event) => {
                const detail = cartPagePayload(event);
                if (detail.token) {
                    localStorage.setItem(cartPageStorageKey, detail.token);
                }
            });

            $wire.on('cart-token-cleared', () => {
                localStorage.removeItem(cartPageStorageKey);
            });
        </script>
    @endscript
</div>
