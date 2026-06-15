<div>
    <section class="soft-grid px-4 py-8 dark:bg-ink sm:px-8 lg:py-12">
        <div class="mx-auto max-w-7xl">
            @include('partials.checkout-progress', ['currentLocale' => $locale, 'currentStep' => 'cart'])

            <nav class="mobile-scrollbarless mx-auto flex max-w-fit items-center justify-center gap-2 overflow-x-auto whitespace-nowrap rounded-full border border-leaf/10 bg-white/80 px-4 py-2 text-sm font-semibold text-cocoa/60 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5 dark:text-cream/60" aria-label="Breadcrumb">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-leaf" wire:navigate>{{ __('home.nav.home') }}</a>
                <span>/</span>
                <span class="text-leaf">{{ __('home.cart.title') }}</span>
            </nav>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_380px] lg:items-start">
                <div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Étape 1' : 'Step 1' }}</p>
                            <h1 class="mt-2 text-3xl font-extrabold text-cocoa dark:text-cream sm:text-5xl">{{ $locale === 'fr' ? 'Vérifier le panier' : 'Review cart' }}</h1>
                            <p class="mt-3 max-w-xl text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ $locale === 'fr' ? 'Gardez uniquement les produits souhaités, ajustez les quantités, puis passez directement à la livraison.' : 'Keep only the products you want, adjust quantities, then go straight to delivery.' }}</p>
                        </div>
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary w-full sm:w-auto" wire:navigate>{{ $locale === 'fr' ? 'Continuer les achats' : 'Continue shopping' }}</a>
                    </div>

                    <div wire:loading.flex class="mt-6 rounded-[1.25rem] border border-leaf/10 bg-white p-5 text-sm font-semibold text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70">
                        {{ __('home.cart.loading') }}
                    </div>

                    @if ($cartError)
                        <div class="mt-6 rounded-[1.25rem] border border-leaf/20 bg-mint p-5 text-sm font-semibold text-leaf dark:bg-white/5">
                            {{ $cartError }}
                        </div>
                    @endif

                    @if (! $cartLoading && count($this->cartItems()) === 0)
                        <div class="mt-6 rounded-[1.5rem] border border-leaf/10 bg-white p-6 dark:border-white/10 dark:bg-white/5 sm:p-8">
                            <h2 class="text-2xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Votre panier est vide.' : 'Your cart is empty.' }}</h2>
                            <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ $locale === 'fr' ? 'Découvrez les produits DEN & FILS et ajoutez vos essentiels avant de commander.' : 'Discover DEN & FILS products and add your essentials before ordering.' }}</p>
                            <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary mt-5 w-full sm:w-auto" wire:navigate>{{ __('home.hero.primary_cta') }}</a>
                        </div>
                    @endif

                    @if (count($this->cartItems()) > 0)
                        <div class="mt-6 space-y-3">
                            @foreach ($this->cartItems() as $item)
                                <article class="grid gap-4 rounded-[1.25rem] border border-leaf/10 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5 sm:grid-cols-[96px_1fr] sm:p-5" wire:key="cart-page-item-{{ $item['id'] }}">
                                    <img class="h-28 w-full rounded-[1rem] object-cover sm:h-24 sm:w-24" src="{{ data_get($item, 'product.image.url') }}" alt="{{ data_get($item, 'product.image.alt_text', data_get($item, 'product.name')) }}" width="96" height="96" loading="lazy" decoding="async">
                                    <div class="min-w-0">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <h2 class="text-lg font-extrabold text-cocoa dark:text-cream">{{ data_get($item, 'product.name') }}</h2>
                                                <p class="mt-1 text-sm text-cocoa/60 dark:text-cream/60">{{ data_get($item, 'variant.name') ?: data_get($item, 'product.origin') }}</p>
                                            </div>
                                            <button type="button" class="inline-flex min-h-[40px] items-center justify-center rounded-full border border-leaf/10 px-4 py-2 text-xs font-bold uppercase tracking-wide text-cocoa/60 transition hover:bg-mint hover:text-leaf disabled:opacity-60 dark:border-white/10 dark:text-cream/60 dark:hover:bg-white/10" wire:click="removeItem({{ (int) $item['id'] }})" wire:loading.attr="disabled">
                                                {{ __('home.cart.remove') }}
                                            </button>
                                        </div>

                                        <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                                            <div class="flex min-h-[44px] items-center rounded-full border border-leaf/20 bg-mint/70 dark:border-white/10 dark:bg-white/5">
                                                <button type="button" class="px-4 py-2 text-sm font-bold disabled:opacity-50" wire:click="decrementItem({{ (int) $item['id'] }}, {{ (int) $item['quantity'] }})" @disabled((int) $item['quantity'] <= 1)>-</button>
                                                <input class="w-14 bg-transparent text-center text-sm font-bold outline-none" type="number" min="1" value="{{ (int) $item['quantity'] }}" wire:change="updateItem({{ (int) $item['id'] }}, $event.target.value)">
                                                <button type="button" class="px-4 py-2 text-sm font-bold" wire:click="incrementItem({{ (int) $item['id'] }}, {{ (int) $item['quantity'] }})">+</button>
                                            </div>
                                            <strong class="text-lg font-extrabold text-leaf dark:text-meadow">{{ $item['formatted_line_total'] }}</strong>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside class="lg:sticky lg:top-36">
                    <div class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Résumé rapide' : 'Quick summary' }}</p>
                        <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Total panier' : 'Cart total' }}</h2>

                        <div class="mt-5 space-y-3 text-sm text-cocoa/70 dark:text-cream/70">
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Sous-total' : 'Subtotal' }}</span>
                                <strong class="text-cocoa dark:text-cream">{{ $this->formattedTotal() }}</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Livraison' : 'Delivery' }}</span>
                                <span>{{ $locale === 'fr' ? 'Étape suivante' : 'Next step' }}</span>
                            </div>
                        </div>

                        <div class="mt-5 rounded-[1rem] bg-mint p-4 text-sm leading-6 text-leaf dark:bg-white/5 dark:text-meadow">
                            {{ $locale === 'fr' ? 'Objectif : passer du panier à la validation sans perdre du temps.' : 'Goal: go from cart to confirmation without wasting time.' }}
                        </div>

                        <a href="{{ route('checkout.show', ['locale' => $locale]) }}" class="btn-primary mt-5 w-full {{ count($this->cartItems()) === 0 ? 'pointer-events-none opacity-50' : '' }}" wire:navigate>
                            {{ $locale === 'fr' ? 'Continuer vers livraison' : 'Continue to delivery' }}
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if (! empty($recommendedProducts))
        <section class="bg-white px-4 py-10 dark:bg-ink sm:px-8 lg:py-12">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Compléter votre panier' : 'Complete your cart' }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ $locale === 'fr' ? 'Produits recommandés' : 'Recommended products' }}</h2>
                    </div>
                    <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary w-full sm:w-fit" wire:navigate>{{ __('home.spotlight.cta') }}</a>
                </div>

                <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
                    @foreach ($recommendedProducts as $product)
                        <article class="group min-w-[250px] rounded-[1.25rem] border border-leaf/10 bg-linen p-4 transition hover:shadow-xl dark:border-white/10 dark:bg-white/5 lg:min-w-0" wire:key="recommended-{{ $product['id'] }}">
                            <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" wire:navigate>
                                <img class="h-40 w-full rounded-[1rem] object-cover sm:h-48" src="{{ $product['primary_image']['url'] ?? '' }}" alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}" loading="lazy" decoding="async">
                                <h3 class="mt-4 line-clamp-2 text-base font-extrabold text-cocoa transition group-hover:text-leaf dark:text-cream sm:text-lg">{{ $product['name'] }}</h3>
                            </a>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <span class="font-extrabold text-leaf">{{ $product['formatted_price'] }}</span>
                                <button type="button" class="rounded-full bg-terracotta px-4 py-2 text-xs font-bold uppercase tracking-wide text-white disabled:opacity-60" wire:click="addRecommended({{ (int) $product['id'] }})" wire:loading.attr="disabled">{{ __('home.products.cta') }}</button>
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
