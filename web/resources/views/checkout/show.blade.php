@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Commande rapide' : 'Quick checkout') . ' | Denetfils')
@section('description', $locale === 'fr' ? 'Commande DEN & FILS simplifiée : coordonnées, livraison et validation.' : 'Simplified DEN & FILS checkout: details, delivery and confirmation.')
@section('robots', 'noindex,nofollow')
@section('canonical', route('checkout.show', ['locale' => $locale]))

@section('content')
    <section class="soft-grid px-4 py-8 dark:bg-ink sm:px-8 lg:py-12" x-init="loadCart(false)" x-data="{ orderConfirmed: false, delivery: 'standard' }">
        <div class="mx-auto max-w-7xl">
            <div x-show="!orderConfirmed">
                @include('partials.checkout-progress', ['currentLocale' => $locale, 'currentStep' => 'checkout'])
            </div>
            <div x-cloak x-show="orderConfirmed" x-transition>
                @include('partials.checkout-progress', ['currentLocale' => $locale, 'currentStep' => 'success'])
            </div>

            <nav class="mobile-scrollbarless mx-auto flex max-w-fit items-center justify-center gap-2 overflow-x-auto whitespace-nowrap rounded-full border border-leaf/10 bg-white/80 px-4 py-2 text-sm font-semibold text-cocoa/60 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5 dark:text-cream/60" aria-label="Breadcrumb">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-leaf">{{ __('home.nav.home') }}</a>
                <span>/</span>
                <a href="{{ route('cart.show', ['locale' => $locale]) }}" class="transition hover:text-leaf">{{ __('home.cart.title') }}</a>
                <span>/</span>
                <span class="text-leaf" x-text="orderConfirmed ? '{{ $locale === 'fr' ? 'Félicitations' : 'Congratulations' }}' : '{{ $locale === 'fr' ? 'Commande' : 'Checkout' }}'"></span>
            </nav>

            <div x-show="!orderConfirmed" x-transition>
                <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Étape 2' : 'Step 2' }}</p>
                        <h1 class="mt-2 max-w-3xl text-3xl font-extrabold leading-tight text-cocoa dark:text-cream sm:text-5xl">
                            {{ $locale === 'fr' ? 'Livraison rapide.' : 'Fast delivery details.' }}
                        </h1>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-cocoa/70 dark:text-cream/70">
                            {{ $locale === 'fr' ? 'Un seul formulaire : vos coordonnées, votre adresse et le mode de livraison.' : 'One form: your contact details, address and delivery method.' }}
                        </p>
                    </div>
                    <a href="{{ route('cart.show', ['locale' => $locale]) }}" class="btn-secondary w-full lg:w-auto">{{ $locale === 'fr' ? 'Retour au panier' : 'Back to cart' }}</a>
                </div>

                <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_380px] lg:items-start">
                    <form class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6" method="POST" action="#" x-on:submit.prevent="orderConfirmed = true; window.scrollTo({ top: 0, behavior: 'smooth' })">
                        <div class="flex items-start gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-leaf text-sm font-black text-white dark:bg-meadow dark:text-ink">🚚</span>
                            <div class="flex-1">
                                <h2 class="text-xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Informations essentielles' : 'Essential information' }}</h2>
                                <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Nous gardons uniquement les champs nécessaires pour ne pas ralentir la commande.' : 'Only essential fields are kept to avoid slowing down checkout.' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="fullname">{{ $locale === 'fr' ? 'Nom complet' : 'Full name' }}</label>
                                    <input id="fullname" class="input-premium w-full" type="text" autocomplete="name" placeholder="Marie Dupont" required>
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="phone">{{ $locale === 'fr' ? 'Téléphone' : 'Phone' }}</label>
                                    <input id="phone" class="input-premium w-full" type="tel" autocomplete="tel" placeholder="+33 6 00 00 00 00" required>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="email">Email</label>
                                <input id="email" class="input-premium w-full" type="email" autocomplete="email" placeholder="client@email.com" required>
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60" for="address">{{ $locale === 'fr' ? 'Adresse complète' : 'Full address' }}</label>
                                <input id="address" class="input-premium w-full" type="text" autocomplete="street-address" placeholder="Rue, ville, code postal, pays" required>
                            </div>

                            <div>
                                <p class="mb-2 block text-xs font-bold uppercase tracking-wide text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Livraison' : 'Delivery' }}</p>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-[1.25rem] border border-leaf/10 bg-linen p-4 transition dark:border-white/10 dark:bg-white/5" x-bind:class="delivery === 'standard' ? 'ring-2 ring-leaf/30' : ''">
                                        <input class="mt-1" type="radio" value="standard" x-model="delivery">
                                        <span>
                                            <span class="block font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'À domicile' : 'Home delivery' }}</span>
                                            <span class="mt-1 block text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Livraison standard.' : 'Standard delivery.' }}</span>
                                        </span>
                                    </label>
                                    <label class="flex cursor-pointer items-start gap-3 rounded-[1.25rem] border border-leaf/10 bg-linen p-4 transition dark:border-white/10 dark:bg-white/5" x-bind:class="delivery === 'relay' ? 'ring-2 ring-leaf/30' : ''">
                                        <input class="mt-1" type="radio" value="relay" x-model="delivery">
                                        <span>
                                            <span class="block font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Point relais' : 'Pickup point' }}</span>
                                            <span class="mt-1 block text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Préparé pour transporteur.' : 'Prepared for carrier.' }}</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-[1.25rem] border border-leaf/10 bg-mint p-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-sm font-extrabold text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Validation sans friction' : 'Frictionless confirmation' }}</p>
                            <p class="mt-1 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $locale === 'fr' ? 'Le paiement réel sera connecté ensuite. Ici, on valide le parcours et l’expérience utilisateur.' : 'Real payment will be connected next. Here we validate the journey and UX.' }}</p>
                        </div>

                        <button type="submit" class="btn-primary mt-6 w-full py-4 text-base" x-bind:class="cartItems.length === 0 ? 'pointer-events-none opacity-50' : ''">
                            {{ $locale === 'fr' ? 'Valider la commande' : 'Confirm order' }}
                        </button>
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
                                    <span x-text="delivery === 'relay' ? '{{ $locale === 'fr' ? 'Point relais' : 'Pickup point' }}' : '{{ $locale === 'fr' ? 'À domicile' : 'Home delivery' }}'"></span>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

            <div x-cloak x-show="orderConfirmed" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" class="mt-8">
                <div class="mx-auto max-w-3xl rounded-[2rem] border border-leaf/10 bg-white p-6 text-center shadow-xl dark:border-white/10 dark:bg-white/5 sm:p-10">
                    <div class="relative mx-auto mb-6 h-24 w-24">
                        <span class="confetti-dot absolute left-2 top-6 h-3 w-3 rounded-full bg-meadow"></span>
                        <span class="confetti-dot absolute right-4 top-3 h-2.5 w-2.5 rounded-full bg-leaf"></span>
                        <span class="confetti-dot absolute bottom-5 left-4 h-2.5 w-2.5 rounded-full bg-terracotta"></span>
                        <span class="confetti-dot absolute bottom-7 right-2 h-3 w-3 rounded-full bg-meadow"></span>
                        <span class="celebration-medal mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-mint text-5xl shadow-lg dark:bg-white/10">🏅</span>
                    </div>

                    <p class="text-xs font-black uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Félicitations' : 'Congratulations' }}</p>
                    <h1 class="mt-3 text-3xl font-extrabold text-cocoa dark:text-cream sm:text-5xl">
                        {{ $locale === 'fr' ? 'Commande validée avec succès.' : 'Order successfully confirmed.' }}
                    </h1>
                    <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-cocoa/70 dark:text-cream/70">
                        {{ $locale === 'fr' ? 'Votre parcours de commande est maintenant clair, court et rassurant. La prochaine étape technique sera de connecter la création réelle de commande et le paiement.' : 'The checkout journey is now clear, short and reassuring. The next technical step is connecting real order creation and payment.' }}
                    </p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary w-full">{{ $locale === 'fr' ? 'Retour à la boutique' : 'Back to shop' }}</a>
                        <button type="button" class="btn-secondary w-full" x-on:click="orderConfirmed = false">
                            {{ $locale === 'fr' ? 'Modifier la commande' : 'Edit order' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
