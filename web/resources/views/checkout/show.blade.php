@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Commande sécurisée' : 'Secure checkout') . ' | Denetfils')
@section('description', $locale === 'fr' ? 'Renseignez vos informations de livraison et vérifiez votre commande DEN & FILS.' : 'Enter delivery details and review your DEN & FILS order.')
@section('robots', 'noindex,nofollow')
@section('canonical', route('checkout.show', ['locale' => $locale]))

@section('content')
    <section class="soft-grid px-4 py-10 dark:bg-ink sm:px-8 lg:py-16" x-init="loadCart(false)">
        <div class="mx-auto max-w-7xl">
            <nav class="mobile-scrollbarless flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm font-semibold text-cocoa/60 dark:text-cream/60" aria-label="Breadcrumb">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-leaf">{{ __('home.nav.home') }}</a>
                <span>/</span>
                <a href="{{ route('cart.show', ['locale' => $locale]) }}" class="transition hover:text-leaf">{{ __('home.cart.title') }}</a>
                <span>/</span>
                <span class="text-leaf">{{ $locale === 'fr' ? 'Commande' : 'Checkout' }}</span>
            </nav>

            <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Commande sécurisée' : 'Secure checkout' }}</p>
                    <h1 class="mt-2 max-w-3xl text-3xl font-extrabold leading-tight text-cocoa dark:text-cream sm:text-5xl">
                        {{ $locale === 'fr' ? 'Finaliser votre commande.' : 'Complete your order.' }}
                    </h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-cocoa/70 dark:text-cream/70">
                        {{ $locale === 'fr' ? 'Un parcours court : coordonnées, livraison, résumé et paiement sécurisé.' : 'A short journey: contact details, delivery, summary and secure payment.' }}
                    </p>
                </div>
                <a href="{{ route('cart.show', ['locale' => $locale]) }}" class="btn-secondary w-full lg:w-auto">{{ $locale === 'fr' ? 'Retour au panier' : 'Back to cart' }}</a>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_380px] lg:items-start">
                <form class="space-y-5" method="POST" action="#" x-data="{ step: 1, delivery: 'standard', payment: 'card' }" x-on:submit.prevent>
                    <section class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
                        <div class="flex items-start gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-mint text-sm font-black text-leaf dark:bg-white/10 dark:text-meadow">1</span>
                            <div class="flex-1">
                                <h2 class="text-xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Coordonnées client' : 'Customer details' }}</h2>
                                <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Les informations nécessaires pour confirmer votre commande.' : 'Required information to confirm your order.' }}</p>

                                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="firstname">{{ $locale === 'fr' ? 'Prénom' : 'First name' }}</label>
                                        <input id="firstname" class="input-premium w-full" type="text" autocomplete="given-name" placeholder="Marie">
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="lastname">{{ $locale === 'fr' ? 'Nom' : 'Last name' }}</label>
                                        <input id="lastname" class="input-premium w-full" type="text" autocomplete="family-name" placeholder="Dupont">
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="email">Email</label>
                                        <input id="email" class="input-premium w-full" type="email" autocomplete="email" placeholder="client@email.com">
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="phone">{{ $locale === 'fr' ? 'Téléphone' : 'Phone' }}</label>
                                        <input id="phone" class="input-premium w-full" type="tel" autocomplete="tel" placeholder="+33 6 00 00 00 00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
                        <div class="flex items-start gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-mint text-sm font-black text-leaf dark:bg-white/10 dark:text-meadow">2</span>
                            <div class="flex-1">
                                <h2 class="text-xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Adresse de livraison' : 'Delivery address' }}</h2>
                                <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Les frais seront calculés selon l’adresse et le transporteur.' : 'Fees will be calculated according to the address and carrier.' }}</p>

                                <div class="mt-5 grid gap-3">
                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="address">{{ $locale === 'fr' ? 'Adresse' : 'Address' }}</label>
                                        <input id="address" class="input-premium w-full" type="text" autocomplete="street-address" placeholder="4 Rue des Grands Champs">
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-[150px_1fr_150px]">
                                        <div>
                                            <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="zip">{{ $locale === 'fr' ? 'Code postal' : 'ZIP code' }}</label>
                                            <input id="zip" class="input-premium w-full" type="text" autocomplete="postal-code" placeholder="75000">
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="city">{{ $locale === 'fr' ? 'Ville' : 'City' }}</label>
                                            <input id="city" class="input-premium w-full" type="text" autocomplete="address-level2" placeholder="Paris">
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="country">{{ $locale === 'fr' ? 'Pays' : 'Country' }}</label>
                                            <select id="country" class="input-premium w-full" autocomplete="country-name">
                                                <option>France</option>
                                                <option>Belgique</option>
                                                <option>Allemagne</option>
                                                <option>Italie</option>
                                                <option>Espagne</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
                        <div class="flex items-start gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-mint text-sm font-black text-leaf dark:bg-white/10 dark:text-meadow">3</span>
                            <div class="flex-1">
                                <h2 class="text-xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Livraison et paiement' : 'Delivery and payment' }}</h2>
                                <div class="mt-5 grid gap-3 md:grid-cols-2">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-[1.25rem] border border-leaf/10 bg-linen p-4 dark:border-white/10 dark:bg-white/5" x-bind:class="delivery === 'standard' ? 'ring-2 ring-leaf/30' : ''">
                                        <input class="mt-1" type="radio" value="standard" x-model="delivery">
                                        <span>
                                            <span class="block font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Livraison standard' : 'Standard delivery' }}</span>
                                            <span class="mt-1 block text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Option principale. Frais calculés après validation.' : 'Main option. Fees calculated after validation.' }}</span>
                                        </span>
                                    </label>
                                    <label class="flex cursor-pointer items-start gap-3 rounded-[1.25rem] border border-leaf/10 bg-linen p-4 dark:border-white/10 dark:bg-white/5" x-bind:class="delivery === 'relay' ? 'ring-2 ring-leaf/30' : ''">
                                        <input class="mt-1" type="radio" value="relay" x-model="delivery">
                                        <span>
                                            <span class="block font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Point relais' : 'Pickup point' }}</span>
                                            <span class="mt-1 block text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Préparé pour Mondial Relay/transporteur.' : 'Prepared for carrier pickup networks.' }}</span>
                                        </span>
                                    </label>
                                </div>

                                <div class="mt-5 rounded-[1.25rem] border border-leaf/10 bg-mint p-4 dark:border-white/10 dark:bg-white/5">
                                    <p class="text-sm font-extrabold text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Paiement sécurisé' : 'Secure payment' }}</p>
                                    <p class="mt-1 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $locale === 'fr' ? 'Emplacement prêt pour Stripe, PayPal ou module bancaire. Aucun paiement n’est encore déclenché dans cette version.' : 'Ready for Stripe, PayPal or a banking module. No payment is triggered in this version.' }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </form>

                <aside class="lg:sticky lg:top-36">
                    <div class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Récapitulatif' : 'Summary' }}</p>
                        <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Votre commande' : 'Your order' }}</h2>

                        <div x-show="cartItems.length === 0" class="mt-5 rounded-[1rem] bg-linen p-4 text-sm leading-6 text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                            {{ $locale === 'fr' ? 'Votre panier est vide. Ajoutez des produits avant de finaliser.' : 'Your cart is empty. Add products before completing checkout.' }}
                        </div>

                        <div x-show="cartItems.length > 0" class="mt-5 space-y-3">
                            <template x-for="item in cartItems" x-bind:key="item.id">
                                <div class="flex items-start justify-between gap-3 border-b border-leaf/10 pb-3 text-sm dark:border-white/10">
                                    <div class="min-w-0">
                                        <p class="font-extrabold text-cocoa dark:text-cream" x-text="item.product?.name"></p>
                                        <p class="mt-1 text-xs text-cocoa/55 dark:text-cream/55"><span x-text="item.quantity"></span> × <span x-text="item.variant?.name || item.product?.origin"></span></p>
                                    </div>
                                    <strong class="shrink-0 text-leaf dark:text-meadow" x-text="item.formatted_line_total"></strong>
                                </div>
                            </template>
                        </div>

                        <div class="mt-5 space-y-3 text-sm text-cocoa/70 dark:text-cream/70">
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Sous-total' : 'Subtotal' }}</span>
                                <strong class="text-cocoa dark:text-cream" x-text="formattedTotal"></strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>{{ $locale === 'fr' ? 'Livraison' : 'Delivery' }}</span>
                                <span>{{ $locale === 'fr' ? 'À confirmer' : 'To confirm' }}</span>
                            </div>
                        </div>

                        <button type="button" class="btn-primary mt-5 w-full cursor-not-allowed opacity-70" disabled>
                            {{ $locale === 'fr' ? 'Paiement bientôt connecté' : 'Payment coming soon' }}
                        </button>
                        <p class="mt-3 text-center text-xs leading-5 text-cocoa/55 dark:text-cream/55">
                            {{ $locale === 'fr' ? 'Version préparatoire du tunnel. La prochaine étape sera la connexion du paiement et de la création de commande.' : 'Checkout preparation version. Next step is connecting payment and order creation.' }}
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
