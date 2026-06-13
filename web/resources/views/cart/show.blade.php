@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Panier' : 'Cart') . ' | Denetfils')
@section('description', $locale === 'fr' ? 'Vérifiez vos produits DEN & FILS avant de passer à la commande.' : 'Review your DEN & FILS products before checkout.')
@section('robots', 'noindex,nofollow')
@section('canonical', route('cart.show', ['locale' => $locale]))

@section('content')
    <section class="soft-grid px-4 py-10 dark:bg-ink sm:px-8 lg:py-16" x-init="loadCart(false)">
        <div class="mx-auto max-w-7xl">
            <nav class="mobile-scrollbarless flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm font-semibold text-cocoa/60 dark:text-cream/60" aria-label="Breadcrumb">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-leaf">{{ __('home.nav.home') }}</a>
                <span>/</span>
                <span class="text-leaf">{{ __('home.cart.title') }}</span>
            </nav>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_380px] lg:items-start">
                <div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Votre sélection' : 'Your selection' }}</p>
                            <h1 class="mt-2 text-3xl font-extrabold text-cocoa dark:text-cream sm:text-5xl">{{ $locale === 'fr' ? 'Panier' : 'Cart' }}</h1>
                        </div>
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary w-full sm:w-auto">{{ $locale === 'fr' ? 'Continuer les achats' : 'Continue shopping' }}</a>
                    </div>

                    <div x-show="cartLoading" class="mt-6 rounded-[1.25rem] border border-leaf/10 bg-white p-5 text-sm font-semibold text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70">
                        {{ __('home.cart.loading') }}
                    </div>

                    <div x-show="cartError" class="mt-6 rounded-[1.25rem] border border-leaf/20 bg-mint p-5 text-sm font-semibold text-leaf dark:bg-white/5">
                        <span x-text="cartError"></span>
                    </div>

                    <div x-show="!cartLoading && cartItems.length === 0" class="mt-6 rounded-[1.5rem] border border-leaf/10 bg-white p-6 dark:border-white/10 dark:bg-white/5 sm:p-8">
                        <h2 class="text-2xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Votre panier est vide.' : 'Your cart is empty.' }}</h2>
                        <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ $locale === 'fr' ? 'Découvrez les produits DEN & FILS et ajoutez vos essentiels avant de commander.' : 'Discover DEN & FILS products and add your essentials before ordering.' }}</p>
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary mt-5 w-full sm:w-auto">{{ __('home.hero.primary_cta') }}</a>
                    </div>

                    <div x-show="cartItems.length > 0" class="mt-6 space-y-4">
                        <template x-for="item in cartItems" x-bind:key="item.id">
                            <article class="grid gap-4 rounded-[1.25rem] border border-leaf/10 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5 sm:grid-cols-[110px_1fr] sm:p-5">
                                <img class="h-32 w-full rounded-[1rem] object-cover sm:h-[110px] sm:w-[110px]" x-bind:src="item.product?.image?.url" x-bind:alt="item.product?.image?.alt_text || item.product?.name" width="110" height="110" loading="lazy" decoding="async">
                                <div class="min-w-0">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <h2 class="text-lg font-extrabold text-cocoa dark:text-cream" x-text="item.product?.name"></h2>
                                            <p class="mt-1 text-sm text-cocoa/60 dark:text-cream/60" x-text="item.variant?.name || item.product?.origin"></p>
                                            <p class="mt-2 text-xs font-bold uppercase tracking-wide text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Produit vérifié DEN & FILS' : 'Verified DEN & FILS product' }}</p>
                                        </div>
                                        <button type="button" class="inline-flex min-h-[40px] items-center justify-center rounded-full border border-leaf/10 px-4 py-2 text-xs font-bold uppercase tracking-wide text-cocoa/60 transition hover:bg-mint hover:text-leaf dark:border-white/10 dark:text-cream/60 dark:hover:bg-white/10" x-on:click="removeCartItem(item.id)" x-bind:disabled="cartMutating">
                                            {{ __('home.cart.remove') }}
                                        </button>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                                        <div class="flex min-h-[44px] items-center rounded-full border border-leaf/20 bg-mint/70 dark:border-white/10 dark:bg-white/5">
                                            <button type="button" class="px-4 py-2 text-sm font-bold" x-on:click="updateCartItem(item.id, item.quantity - 1)" x-bind:disabled="item.quantity <= 1 || cartMutating">−</button>
                                            <input class="w-14 bg-transparent text-center text-sm font-bold outline-none" type="number" min="1" x-bind:value="item.quantity" x-on:change="updateCartItem(item.id, $event.target.value)">
                                            <button type="button" class="px-4 py-2 text-sm font-bold" x-on:click="updateCartItem(item.id, item.quantity + 1)" x-bind:disabled="cartMutating">+</button>
                                        </div>
                                        <strong class="text-lg font-extrabold text-leaf dark:text-meadow" x-text="item.formatted_line_total"></strong>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>

                <aside class="lg:sticky lg:top-36">
                    <div class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Résumé' : 'Summary' }}</p>
                        <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Total panier' : 'Cart total' }}</h2>

                        <div class="mt-5 space-y-3 text-sm text-cocoa/70 dark:text-cream/70">
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Sous-total' : 'Subtotal' }}</span>
                                <strong class="text-cocoa dark:text-cream" x-text="formattedTotal"></strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Livraison' : 'Delivery' }}</span>
                                <span>{{ $locale === 'fr' ? 'Calculée à la commande' : 'Calculated at checkout' }}</span>
                            </div>
                        </div>

                        <div class="mt-5 rounded-[1rem] bg-mint p-4 text-sm leading-6 text-leaf dark:bg-white/5 dark:text-meadow">
                            {{ $locale === 'fr' ? 'Livraison gratuite à partir de 35€. Les frais exacts sont confirmés pendant la commande.' : 'Free delivery from €35. Exact fees are confirmed during checkout.' }}
                        </div>

                        <a href="{{ route('checkout.show', ['locale' => $locale]) }}" class="btn-primary mt-5 w-full" x-bind:class="cartItems.length === 0 ? 'pointer-events-none opacity-50' : ''">
                            {{ $locale === 'fr' ? 'Passer à la commande' : 'Proceed to checkout' }}
                        </a>
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary mt-3 w-full">{{ $locale === 'fr' ? 'Ajouter d’autres produits' : 'Add more products' }}</a>
                    </div>

                    <div class="mt-4 grid gap-3 rounded-[1.25rem] border border-leaf/10 bg-linen p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start gap-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70"><span class="text-leaf">✓</span><span>{{ $locale === 'fr' ? 'Paiement sécurisé préparé dans l’étape suivante.' : 'Secure payment prepared in the next step.' }}</span></div>
                        <div class="flex items-start gap-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70"><span class="text-leaf">✓</span><span>{{ $locale === 'fr' ? 'Support DEN & FILS disponible avant validation.' : 'DEN & FILS support available before validation.' }}</span></div>
                        <div class="flex items-start gap-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70"><span class="text-leaf">✓</span><span>{{ $locale === 'fr' ? 'Récapitulatif clair avant paiement.' : 'Clear summary before payment.' }}</span></div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if (! empty($recommendedProducts))
        <section class="bg-white px-4 py-12 dark:bg-ink sm:px-8 lg:py-14">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Compléter votre panier' : 'Complete your cart' }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ $locale === 'fr' ? 'Produits recommandés' : 'Recommended products' }}</h2>
                    </div>
                    <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary w-full sm:w-fit">{{ __('home.spotlight.cta') }}</a>
                </div>

                <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
                    @foreach ($recommendedProducts as $product)
                        <article class="group min-w-[250px] rounded-[1.25rem] border border-leaf/10 bg-linen p-4 transition hover:shadow-xl dark:border-white/10 dark:bg-white/5 lg:min-w-0">
                            <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}">
                                <img class="h-40 w-full rounded-[1rem] object-cover sm:h-48" src="{{ $product['primary_image']['url'] ?? '' }}" alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}" loading="lazy" decoding="async">
                                <h3 class="mt-4 line-clamp-2 text-base font-extrabold text-cocoa transition group-hover:text-leaf dark:text-cream sm:text-lg">{{ $product['name'] }}</h3>
                            </a>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <span class="font-extrabold text-leaf">{{ $product['formatted_price'] }}</span>
                                <button type="button" class="rounded-full bg-terracotta px-4 py-2 text-xs font-bold uppercase tracking-wide text-white" x-on:click="addToCart({{ $product['id'] }})">{{ __('home.products.cta') }}</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
