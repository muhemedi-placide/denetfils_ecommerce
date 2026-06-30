@php
    $currentLocale = $locale ?? app()->getLocale();
    $alternateLocale = $currentLocale === 'fr' ? 'en' : 'fr';
    $alternateUrl = route('home.localized', ['locale' => $alternateLocale]);
    $accountUrl = session()->has('customer_api_token') ? route('account.show', ['locale' => $currentLocale]) : route('account.login', ['locale' => $currentLocale]);
    $visitorCountryOptions = $visitorContext['supported_countries'] ?? [];
    $selectedVisitorCountry = collect($visitorCountryOptions)->firstWhere('code', $visitorContext['country_code'] ?? 'FR')
        ?? ['code' => 'FR', 'name' => 'France'];
    $alternateLocaleFlagCountry = $alternateLocale === 'fr' ? 'fr' : 'us';

    if (request()->routeIs('shop.index')) {
        $alternateUrl = route('shop.index', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('pages.about')) {
        $alternateUrl = route('pages.about', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('pages.contact')) {
        $alternateUrl = route('pages.contact', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('blog.index')) {
        $alternateUrl = route('blog.index', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('blog.show')) {
        $alternateUrl = route('blog.show', ['locale' => $alternateLocale, 'slug' => request()->route('slug')]);
    } elseif (request()->routeIs('pages.delivery')) {
        $alternateUrl = route('pages.delivery', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('pages.tracking')) {
        $alternateUrl = route('pages.tracking', ['locale' => $alternateLocale, 'tracking_number' => request('tracking_number')]);
    } elseif (request()->routeIs('pages.legal')) {
        $alternateUrl = route('pages.legal', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('pages.terms')) {
        $alternateUrl = route('pages.terms', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('pages.payment')) {
        $alternateUrl = route('pages.payment', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('account.login')) {
        $alternateUrl = route('account.login', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('account.register')) {
        $alternateUrl = route('account.register', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('account.show')) {
        $alternateUrl = route('account.show', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('account.orders.*')) {
        $alternateUrl = route('account.orders.show', ['locale' => $alternateLocale, 'order' => request()->route('order')]);
    } elseif (request()->routeIs('products.show')) {
        $alternateUrl = route('products.show', ['locale' => $alternateLocale, 'slug' => request()->route('slug')]);
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.seo')
        <script>
            let storedTheme = null;
            try { storedTheme = localStorage.getItem('theme'); } catch (error) { storedTheme = null; }
            if (storedTheme === 'dark') { document.documentElement.classList.add('dark'); }
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="store-page theme-page min-h-screen">
        <div id="shop-app" x-data="shopApp({ locale: @js($currentLocale), activeMenu: @js($activeMenu ?? 'home') })" x-init="init()">
            <input id="mobile-menu-state" class="sr-only" type="checkbox" autocomplete="off" aria-hidden="true">

            <header class="store-header sticky top-0 z-40">
                <div class="bg-[#1a1a1c] px-5 py-4 text-sm font-bold text-white">
                    {{ config('shop.name') }} · {{ $currentLocale === 'fr' ? 'Épicerie fine – Produits d’exception' : 'Fine grocery – Exceptional products' }}
                </div>

                <div class="store-container flex min-h-[80px] items-center justify-between gap-5">
                    <label for="mobile-menu-state" data-mobile-menu-toggle class="store-icon-button lg:hidden" role="button" tabindex="0" aria-label="Menu">
                        <span data-mobile-menu-icon="open">☰</span>
                        <span data-mobile-menu-icon="close" class="hidden">×</span>
                    </label>

                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="store-logo truncate" wire:navigate.hover>{{ config('shop.name') }}</a>

                    <nav class="hidden items-center gap-8 lg:flex">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="store-nav-link" @if(request()->routeIs('home*')) aria-current="page" @endif wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Accueil' : 'Home' }}</a>
                        <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="store-nav-link" @if(request()->routeIs('shop.index') || request()->routeIs('products.*')) aria-current="page" @endif wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Produits' : 'Products' }}</a>
                        <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="store-nav-link" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Coffrets' : 'Gift boxes' }}</a>
                        <a href="{{ route('pages.contact', ['locale' => $currentLocale]) }}" class="store-nav-link" @if(request()->routeIs('pages.contact')) aria-current="page" @endif wire:navigate.hover>Contact</a>
                    </nav>

                    <div class="flex items-center gap-2">
                        <form
                            method="POST"
                            action="{{ route('visitor.preferences.update') }}"
                            class="relative hidden sm:block"
                            x-data="{ countryOpen: false }"
                            x-on:mouseenter="countryOpen = true"
                            x-on:mouseleave="countryOpen = false"
                            x-on:keydown.escape.window="countryOpen = false"
                            x-on:click.outside="countryOpen = false"
                        >
                            @csrf
                            <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                            <button
                                type="button"
                                class="flex min-w-40 cursor-pointer items-center gap-2 rounded-full border border-black/10 bg-transparent px-4 py-3 text-sm font-bold dark:border-white/15"
                                x-on:click="countryOpen = ! countryOpen"
                                x-on:focus="countryOpen = true"
                                x-bind:aria-expanded="countryOpen"
                                aria-haspopup="listbox"
                            >
                                <span class="fi fi-{{ strtolower($selectedVisitorCountry['code']) }} rounded-sm shadow-sm" aria-hidden="true"></span>
                                <span class="truncate">{{ $selectedVisitorCountry['name'] }}</span>
                                <svg class="ml-auto h-4 w-4 transition" x-bind:class="countryOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 7.5 5 5 5-5"/></svg>
                            </button>
                            <div
                                x-cloak
                                x-show="countryOpen"
                                x-transition.opacity.duration.150ms
                                role="listbox"
                                class="absolute right-0 z-50 mt-2 max-h-80 min-w-56 overflow-y-auto rounded-2xl border border-black/10 bg-white p-2 shadow-2xl dark:border-white/15 dark:bg-[#201d1a]"
                            >
                                @foreach ($visitorCountryOptions as $country)
                                    @php($isActiveCountry = $country['code'] === $selectedVisitorCountry['code'])
                                    <button
                                        type="submit"
                                        name="country_code"
                                        value="{{ $country['code'] }}"
                                        role="option"
                                        aria-selected="{{ $isActiveCountry ? 'true' : 'false' }}"
                                        @if ($isActiveCountry) aria-current="true" @endif
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition {{ $isActiveCountry ? 'bg-[#f97316] text-white' : 'hover:bg-black/5 dark:hover:bg-white/10' }}"
                                    >
                                        <span class="fi fi-{{ strtolower($country['code']) }} rounded-sm shadow-sm" aria-hidden="true"></span>
                                        <span>{{ $country['name'] }}</span>
                                        @if ($isActiveCountry)
                                            <svg class="ml-auto h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m4 10 4 4 8-8"/></svg>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </form>
                        <button type="button" class="store-theme-toggle hidden sm:inline-flex" aria-label="Changer le thème" x-on:click="toggleTheme()">
                            <span :class="theme === 'light' ? 'is-active' : ''"><x-icon name="sun" class="h-4 w-4" /></span>
                            <span :class="theme === 'dark' ? 'is-active' : ''"><x-icon name="moon" class="h-4 w-4" /></span>
                        </button>
                        <a href="{{ $accountUrl }}" class="store-icon-button hidden sm:inline-grid" aria-label="Compte" wire:navigate.hover>
                            <x-icon name="user" class="h-5 w-5" />
                        </a>
                        <form method="POST" action="{{ route('visitor.preferences.update') }}">
                            @csrf
                            <input type="hidden" name="locale" value="{{ $alternateLocale }}">
                            <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                            <button
                                type="submit"
                                class="store-icon-button text-xl"
                                title="{{ $alternateLocale === 'fr' ? 'Français' : 'English' }}"
                                aria-label="{{ $alternateLocale === 'fr' ? 'Afficher le site en français' : 'View the website in English' }}"
                            ><span class="fi fi-{{ $alternateLocaleFlagCountry }} rounded-sm shadow-sm" aria-hidden="true"></span></button>
                        </form>
                        @persist('cart-manager-'.$currentLocale)
                            <livewire:shop.cart-manager :locale="$currentLocale" />
                        @endpersist
                    </div>
                </div>

                <div id="mobile-menu" data-mobile-menu class="mobile-menu-panel border-t px-4 py-4 shadow-lg lg:hidden" style="border-color:var(--store-border);background:var(--store-bg)">
                    <div class="grid gap-2">
                        <form method="POST" action="{{ route('visitor.preferences.update') }}">
                            @csrf
                            <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                            <details class="group">
                                <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl border border-black/10 px-4 py-3 text-sm font-bold dark:border-white/15">
                                    <span class="fi fi-{{ strtolower($selectedVisitorCountry['code']) }} rounded-sm shadow-sm" aria-hidden="true"></span>
                                    <span>{{ $selectedVisitorCountry['name'] }}</span>
                                    <svg class="ml-auto h-4 w-4 transition group-open:rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 7.5 5 5 5-5"/></svg>
                                </summary>
                                <div class="mt-2 max-h-72 overflow-y-auto rounded-xl border border-black/10 bg-white p-2 dark:border-white/15 dark:bg-[#201d1a]">
                                    @foreach ($visitorCountryOptions as $country)
                                        @php($isActiveCountry = $country['code'] === $selectedVisitorCountry['code'])
                                        <button
                                            type="submit"
                                            name="country_code"
                                            value="{{ $country['code'] }}"
                                            aria-current="{{ $isActiveCountry ? 'true' : 'false' }}"
                                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-semibold {{ $isActiveCountry ? 'bg-[#f97316] text-white' : 'hover:bg-black/5 dark:hover:bg-white/10' }}"
                                        >
                                            <span class="fi fi-{{ strtolower($country['code']) }} rounded-sm shadow-sm" aria-hidden="true"></span>
                                            <span>{{ $country['name'] }}</span>
                                            @if ($isActiveCountry)
                                                <svg class="ml-auto h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m4 10 4 4 8-8"/></svg>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </details>
                        </form>
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="store-nav-link rounded-xl px-4 py-3" wire:navigate.hover>Accueil</a>
                        <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="store-nav-link rounded-xl px-4 py-3" wire:navigate.hover>Produits</a>
                        <a href="{{ route('pages.contact', ['locale' => $currentLocale]) }}" class="store-nav-link rounded-xl px-4 py-3" wire:navigate.hover>Contact</a>
                        <button type="button" class="store-button mt-2" x-on:click="toggleTheme(); closeMobileMenu()"><span x-show="theme !== 'dark'">Mode sombre</span><span x-show="theme === 'dark'">Mode clair</span></button>
                    </div>
                </div>
            </header>

            @if (isset($visitorContext) && ! $visitorContext['is_supported'])
                <div class="border-b border-amber-300 bg-amber-50 px-5 py-3 text-center text-sm font-semibold text-amber-900">
                    {{ $currentLocale === 'fr'
                        ? "La livraison n’est pas disponible pour le pays détecté ({$visitorContext['country_code']}). Choisissez une destination prise en charge."
                        : "Delivery is unavailable for the detected country ({$visitorContext['country_code']}). Choose a supported destination." }}
                </div>
            @endif

            <main>@yield('content')</main>

            @if (request()->routeIs('products.show') && isset($product))
                @include('partials.product-reviews', ['product' => $product, 'currentLocale' => $currentLocale])
            @endif

            <footer class="store-footer">
                <div class="store-container">
                    <div class="grid gap-9 md:grid-cols-2 lg:grid-cols-4">
                        <section>
                            <h4>{{ config('shop.name') }}</h4>
                            <p class="text-sm leading-7">{{ $currentLocale === 'fr' ? 'Épicerie fine de saveurs caribéennes, haïtiennes, africaines et tropicales.' : config('shop.name').' makes Caribbean, Haitian and African flavors easy to find, cook and share every day.' }}</p>
                            <p class="mt-3 flex items-center gap-2 text-sm"><x-icon name="location" class="h-4 w-4" /> France · Livraison en Europe</p>
                        </section>
                        <nav class="space-y-3" aria-label="Boutique">
                            <h4>{{ $currentLocale === 'fr' ? 'Boutique' : 'Shop' }}</h4>
                            <a class="block text-sm" href="{{ route('shop.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Tous les produits' : 'All products' }}</a>
                            <a class="block text-sm" href="{{ route('shop.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Coffrets cadeaux' : 'Gift boxes' }}</a>
                            <a class="block text-sm" href="{{ route('blog.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Recettes' : 'Recipes' }}</a>
                        </nav>
                        <nav class="space-y-3" aria-label="Service client">
                            <h4>{{ $currentLocale === 'fr' ? 'Service client' : 'Customer service' }}</h4>
                            <a class="block text-sm" href="{{ route('pages.contact', ['locale' => $currentLocale]) }}" wire:navigate.hover>Contact</a>
                            <a class="block text-sm" href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Livraison' : 'Shipping' }}</a>
                            <a class="block text-sm" href="{{ route('pages.tracking', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Suivi colis' : 'Track parcel' }}</a>
                            <a class="block text-sm" href="{{ route('pages.legal', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Mentions légales' : 'Legal notice' }}</a>
                        </nav>
                        <section>
                            <h4>Newsletter</h4>
                            <p class="text-sm">{{ $currentLocale === 'fr' ? 'Recevez nos offres et recettes.' : 'Receive our offers and recipes.' }}</p>
                            <form class="mt-4 flex overflow-hidden rounded-full border" style="border-color:var(--store-border);background:var(--store-card)">
                                <label class="sr-only" for="footer-newsletter-email">Email</label>
                                <input id="footer-newsletter-email" class="min-w-0 flex-1 bg-transparent px-4 py-2 text-sm outline-none" type="email" placeholder="votre@email.fr">
                                <button class="grid w-12 place-items-center bg-[#f97316] text-white" type="submit" aria-label="Envoyer"><x-icon name="paper-airplane" class="h-4 w-4" /></button>
                            </form>
                        </section>
                    </div>

                    <div class="mt-12 flex flex-col gap-5 border-t pt-6 text-sm sm:flex-row sm:items-center sm:justify-between" style="border-color:var(--store-border);color:var(--store-muted)">
                        <span>© {{ now()->year }} {{ config('shop.name') }} – {{ $currentLocale === 'fr' ? 'Tous droits réservés.' : 'All rights reserved.' }}</span>
                        <span>{{ $currentLocale === 'fr' ? 'Paiement sécurisé · Livraison suivie' : 'Secure payment · Tracked delivery' }}</span>
                    </div>
                </div>
            </footer>
        </div>

        @livewireScriptConfig
    </body>
</html>
