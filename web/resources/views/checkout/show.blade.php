@extends('layouts.shop')

@php
    $isAuthenticated = ! empty($user);
    $defaultAddress = collect($addresses)->firstWhere('is_default', true) ?: collect($addresses)->first();
    $defaultAddressId = $defaultAddress['id'] ?? null;
    $countryNames = collect($countries)->pluck('name', 'code');
@endphp

@section('title', ($locale === 'fr' ? 'Validation de commande' : 'Checkout review') . ' | Denetfils')
@section('description', $locale === 'fr' ? 'Vérifiez le panier, le compte client et l’adresse de livraison avant le paiement.' : 'Review cart, customer account and delivery address before payment.')
@section('robots', 'noindex,nofollow')
@section('canonical', route('checkout.show', ['locale' => $locale]))

@section('content')
    <section
        class="soft-grid px-4 py-8 dark:bg-ink sm:px-8 lg:py-12"
        x-init="loadCart(false)"
        x-data="{
            orderConfirmed: false,
            delivery: 'standard',
            selectedAddressId: @js($defaultAddressId)
        }"
    >
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
                <span class="text-leaf" x-text="orderConfirmed ? '{{ $locale === 'fr' ? 'Confirmation' : 'Confirmation' }}' : '{{ $locale === 'fr' ? 'Validation' : 'Review' }}'"></span>
            </nav>

            <div x-show="!orderConfirmed" x-transition>
                <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Étape 2' : 'Step 2' }}</p>
                        <h1 class="mt-2 max-w-3xl text-3xl font-extrabold leading-tight text-cocoa dark:text-cream sm:text-5xl">
                            {{ $locale === 'fr' ? 'Vérifier avant paiement.' : 'Review before payment.' }}
                        </h1>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-cocoa/70 dark:text-cream/70">
                            {{ $locale === 'fr' ? 'Le panier vient de l’API panier invité. Le profil et les adresses viennent du compte client connecté.' : 'The cart comes from the guest cart API. Profile and addresses come from the signed-in customer account.' }}
                        </p>
                    </div>
                    <a href="{{ route('cart.show', ['locale' => $locale]) }}" class="btn-secondary w-full lg:w-auto">{{ $locale === 'fr' ? 'Retour au panier' : 'Back to cart' }}</a>
                </div>

                <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_390px] lg:items-start">
                    <form class="space-y-6" method="POST" action="#" x-on:submit.prevent="if (cartItems.length > 0 && @js($isAuthenticated) && selectedAddressId) { orderConfirmed = true; window.scrollTo({ top: 0, behavior: 'smooth' }) }">
                        @csrf

                        <section class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
                            <div class="flex items-start gap-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-leaf text-sm font-black text-white dark:bg-meadow dark:text-ink">1</span>
                                <div>
                                    <h2 class="text-xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Compte client' : 'Customer account' }}</h2>
                                    <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">
                                        {{ $locale === 'fr' ? 'Le checkout utilise le compte pour éviter de ressaisir les informations et préparer les futures factures.' : 'Checkout uses the account to avoid retyping details and prepare future invoices.' }}
                                    </p>
                                </div>
                            </div>

                            @if ($isAuthenticated)
                                <div class="mt-5 grid gap-4 rounded-[1.25rem] bg-linen p-4 dark:bg-white/5 sm:grid-cols-3">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ $locale === 'fr' ? 'Client' : 'Customer' }}</p>
                                        <p class="mt-1 font-extrabold text-cocoa dark:text-cream">{{ $user['name'] ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-cocoa/50 dark:text-cream/50">Email</p>
                                        <p class="mt-1 break-all font-extrabold text-cocoa dark:text-cream">{{ $user['email'] ?? '' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ $locale === 'fr' ? 'Pays' : 'Country' }}</p>
                                        <p class="mt-1 font-extrabold text-cocoa dark:text-cream">{{ $countryNames[$user['country_code'] ?? ''] ?? ($user['country_code'] ?? '') }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="mt-5 rounded-[1.25rem] border border-terracotta/20 bg-terracotta/10 p-5">
                                    <h3 class="font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Connectez-vous pour continuer.' : 'Sign in to continue.' }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-cocoa/70 dark:text-cream/70">
                                        {{ $locale === 'fr' ? 'Cette tranche connecte le checkout au compte client et aux adresses API. La commande finale sera ouverte après connexion.' : 'This step connects checkout to the customer account and API addresses. Final confirmation is available after sign-in.' }}
                                    </p>
                                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                                        <a href="{{ route('account.login', ['locale' => $locale]) }}" class="btn-primary w-full sm:w-auto">{{ __('home.account.auth.sign_in') }}</a>
                                        <a href="{{ route('account.register', ['locale' => $locale]) }}" class="btn-secondary w-full sm:w-auto">{{ __('home.account.auth.create_account') }}</a>
                                    </div>
                                </div>
                            @endif
                        </section>

                        <section class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
                            <div class="flex items-start gap-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-leaf text-sm font-black text-white dark:bg-meadow dark:text-ink">2</span>
                                <div>
                                    <h2 class="text-xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Adresse de livraison' : 'Delivery address' }}</h2>
                                    <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">
                                        {{ $locale === 'fr' ? 'Les adresses viennent de /api/v1/me/addresses et restent liées au compte connecté.' : 'Addresses come from /api/v1/me/addresses and stay linked to the signed-in account.' }}
                                    </p>
                                </div>
                            </div>

                            @if ($isAuthenticated && ! empty($addresses))
                                <div class="mt-5 grid gap-3">
                                    @foreach ($addresses as $address)
                                        <label class="cursor-pointer rounded-[1.25rem] border border-leaf/10 bg-linen p-4 transition dark:border-white/10 dark:bg-white/5" x-bind:class="Number(selectedAddressId) === {{ (int) $address['id'] }} ? 'ring-2 ring-leaf/35 dark:ring-meadow/40' : ''">
                                            <div class="flex items-start gap-3">
                                                <input class="mt-1" type="radio" name="address_id" value="{{ $address['id'] }}" x-model="selectedAddressId">
                                                <span class="min-w-0">
                                                    <span class="flex flex-wrap items-center gap-2">
                                                        <span class="font-extrabold text-cocoa dark:text-cream">{{ $address['label'] ?: $address['recipient_name'] }}</span>
                                                        @if ($address['is_default'])
                                                            <span class="rounded-full bg-mint px-3 py-1 text-xs font-bold uppercase tracking-wide text-leaf dark:bg-white/10 dark:text-meadow">{{ __('home.account.addresses.default_badge') }}</span>
                                                        @endif
                                                    </span>
                                                    <span class="mt-1 block text-sm leading-6 text-cocoa/65 dark:text-cream/65">
                                                        {{ $address['recipient_name'] }} · {{ $address['street_line_1'] }}, {{ $address['postal_code'] }} {{ $address['city'] }} · {{ $countryNames[$address['country_code']] ?? $address['country_code'] }}
                                                    </span>
                                                    <span class="mt-1 block text-xs font-bold uppercase tracking-wide text-leaf dark:text-meadow">{{ __('home.account.addresses.' . $address['type']) }}</span>
                                                </span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($isAuthenticated)
                                <div class="mt-5 rounded-[1.25rem] border border-leaf/10 bg-linen p-5 dark:border-white/10 dark:bg-white/5">
                                    <h3 class="font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Aucune adresse enregistrée.' : 'No saved address.' }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">
                                        {{ $locale === 'fr' ? 'Ajoutez une adresse depuis votre compte avant de finaliser la commande.' : 'Add an address from your account before confirming the order.' }}
                                    </p>
                                    <a href="{{ route('account.show', ['locale' => $locale]) }}" class="btn-secondary mt-4 w-full sm:w-auto">{{ $locale === 'fr' ? 'Gérer les adresses' : 'Manage addresses' }}</a>
                                </div>
                            @else
                                <div class="mt-5 rounded-[1.25rem] bg-linen p-5 text-sm leading-6 text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                                    {{ $locale === 'fr' ? 'Les adresses seront affichées après connexion.' : 'Addresses will be shown after sign-in.' }}
                                </div>
                            @endif
                        </section>

                        <section class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
                            <div class="flex items-start gap-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-leaf text-sm font-black text-white dark:bg-meadow dark:text-ink">3</span>
                                <div>
                                    <h2 class="text-xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Mode de livraison' : 'Delivery method' }}</h2>
                                    <p class="mt-1 text-sm leading-6 text-cocoa/60 dark:text-cream/60">
                                        {{ $locale === 'fr' ? 'Les frais réels seront calculés dans la tranche livraison/paiement. Ici, on prépare le choix utilisateur.' : 'Real fees will be calculated in the delivery/payment step. Here, the customer choice is prepared.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <label class="flex cursor-pointer items-start gap-3 rounded-[1.25rem] border border-leaf/10 bg-linen p-4 transition dark:border-white/10 dark:bg-white/5" x-bind:class="delivery === 'standard' ? 'ring-2 ring-leaf/30' : ''">
                                    <input class="mt-1" type="radio" value="standard" x-model="delivery">
                                    <span>
                                        <span class="block font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'À domicile' : 'Home delivery' }}</span>
                                        <span class="mt-1 block text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Livraison standard vers les pays supportés.' : 'Standard delivery to supported countries.' }}</span>
                                    </span>
                                </label>
                                <label class="flex cursor-pointer items-start gap-3 rounded-[1.25rem] border border-leaf/10 bg-linen p-4 transition dark:border-white/10 dark:bg-white/5" x-bind:class="delivery === 'relay' ? 'ring-2 ring-leaf/30' : ''">
                                    <input class="mt-1" type="radio" value="relay" x-model="delivery">
                                    <span>
                                        <span class="block font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Point relais' : 'Pickup point' }}</span>
                                        <span class="mt-1 block text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Option préparée pour un futur transporteur.' : 'Prepared for a future carrier integration.' }}</span>
                                    </span>
                                </label>
                            </div>
                        </section>

                        <button type="submit" class="btn-primary w-full py-4 text-base disabled:pointer-events-none disabled:opacity-50" x-bind:disabled="cartItems.length === 0 || !@js($isAuthenticated) || !selectedAddressId">
                            {{ $locale === 'fr' ? 'Confirmer sans paiement' : 'Confirm without payment' }}
                        </button>
                    </form>

                    <aside class="lg:sticky lg:top-36">
                        <div class="rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Récapitulatif' : 'Summary' }}</p>
                            <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Votre commande' : 'Your order' }}</h2>

                            <div x-show="cartLoading" class="mt-5 rounded-[1rem] bg-linen p-4 text-sm font-semibold text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                                {{ __('home.cart.loading') }}
                            </div>

                            <div x-show="cartError" class="mt-5 rounded-[1rem] border border-leaf/20 bg-mint p-4 text-sm font-semibold text-leaf dark:bg-white/5">
                                <span x-text="cartError"></span>
                            </div>

                            <div x-show="!cartLoading && cartItems.length === 0" class="mt-5 rounded-[1rem] bg-linen p-4 text-sm leading-6 text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
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
                                <div class="flex items-center justify-between">
                                    <span>{{ $locale === 'fr' ? 'Paiement' : 'Payment' }}</span>
                                    <span>{{ $locale === 'fr' ? 'Prochaine tranche' : 'Next step' }}</span>
                                </div>
                            </div>

                            <div class="mt-5 rounded-[1rem] bg-mint p-4 text-sm leading-6 text-leaf dark:bg-white/5 dark:text-meadow">
                                {{ $locale === 'fr' ? 'Aucune transaction bancaire n’est déclenchée dans cette tranche.' : 'No bank transaction is triggered in this step.' }}
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

            <div x-cloak x-show="orderConfirmed" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" class="mt-8">
                <div class="mx-auto max-w-3xl rounded-[2rem] border border-leaf/10 bg-white p-6 text-center shadow-xl dark:border-white/10 dark:bg-white/5 sm:p-10">
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-mint text-4xl font-black text-leaf shadow-lg dark:bg-white/10 dark:text-meadow">
                        OK
                    </div>

                    <p class="text-xs font-black uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $locale === 'fr' ? 'Pré-validation' : 'Pre-confirmation' }}</p>
                    <h1 class="mt-3 text-3xl font-extrabold text-cocoa dark:text-cream sm:text-5xl">
                        {{ $locale === 'fr' ? 'Le parcours checkout est connecté.' : 'Checkout journey is connected.' }}
                    </h1>
                    <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-cocoa/70 dark:text-cream/70">
                        {{ $locale === 'fr' ? 'Le panier, le compte client et l’adresse sélectionnée sont maintenant réunis dans le checkout. La prochaine étape sera la création réelle de commande, puis le paiement.' : 'Cart, customer account and selected address are now connected in checkout. The next step is real order creation, then payment.' }}
                    </p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary w-full">{{ $locale === 'fr' ? 'Retour à la boutique' : 'Back to shop' }}</a>
                        <button type="button" class="btn-secondary w-full" x-on:click="orderConfirmed = false">
                            {{ $locale === 'fr' ? 'Modifier la validation' : 'Edit review' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
