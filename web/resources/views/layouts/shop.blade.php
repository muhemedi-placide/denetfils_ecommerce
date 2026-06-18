@php
    $currentLocale = $locale ?? app()->getLocale();
    $alternateLocale = $currentLocale === 'fr' ? 'en' : 'fr';
    $alternateUrl = route('home.localized', ['locale' => $alternateLocale]);
    $accountUrl = session()->has('customer_api_token')
        ? route('account.show', ['locale' => $currentLocale])
        : route('account.login', ['locale' => $currentLocale]);

    if (request()->routeIs('shop.index')) {
        $alternateUrl = route('shop.index', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('pages.about')) {
        $alternateUrl = route('pages.about', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('blog.index')) {
        $alternateUrl = route('blog.index', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('blog.show')) {
        $alternateUrl = route('blog.show', ['locale' => $alternateLocale, 'slug' => request()->route('slug')]);
    } elseif (request()->routeIs('pages.delivery')) {
        $alternateUrl = route('pages.delivery', ['locale' => $alternateLocale]);
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
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if ((storedTheme !== 'light' && storedTheme !== 'dark') || storedTheme === null) {
                storedTheme = systemPrefersDark ? 'dark' : 'light';
            }
            if (storedTheme === 'dark') { document.documentElement.classList.add('dark'); }
        </script>
        <link rel="preconnect" href="https://moodboard-to-shop.lovable.app" crossorigin>
        <link rel="dns-prefetch" href="https://moodboard-to-shop.lovable.app">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="theme-page surface-transition min-h-screen bg-cream text-cocoa dark:bg-ink dark:text-cream">
        <div id="shop-app" x-data="shopApp({ locale: @js($currentLocale), activeMenu: @js($activeMenu ?? 'home') })" x-init="init()">
            <input id="mobile-menu-state" class="sr-only" type="checkbox" autocomplete="off" aria-hidden="true">

            <header class="sticky top-0 z-40 border-b border-leaf/15 bg-cream/95 shadow-sm backdrop-blur dark:border-white/10 dark:bg-ink/95">
                <div class="overflow-hidden bg-sunshine px-4 py-2 text-xs font-black uppercase tracking-[0.12em] text-forest sm:px-8">
                    <div class="market-ticker flex gap-6">
                        <span>{{ $currentLocale === 'fr' ? 'Votre marché des saveurs exotiques 24h/24' : 'Your exotic flavors market 24/7' }}</span><span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Livraison offerte dès 49€' : 'Free delivery from €49' }}</span><span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Produits authentiques des Antilles & d’Afrique' : 'Authentic Caribbean & African products' }}</span><span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Récolte fraîche chaque semaine' : 'Fresh harvest every week' }}</span><span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Votre marché des saveurs exotiques 24h/24' : 'Your exotic flavors market 24/7' }}</span>
                    </div>
                </div>

                <div class="px-4 py-4 sm:px-8">
                    <div class="mx-auto grid max-w-7xl grid-cols-[auto_1fr_auto] items-center gap-3">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="flex min-w-0 items-center gap-2" x-on:click="closeMobileMenu(); activeMenu = 'home'" wire:navigate.hover>
                            <span class="brand-display text-2xl uppercase leading-none text-leaf dark:text-meadow sm:text-3xl">Marché<span class="text-tomato">.</span>Peyi</span>
                        </a>

                        <nav class="hidden items-center justify-center gap-1 text-sm font-black uppercase tracking-wide text-cocoa/75 dark:text-cream/75 lg:flex">
                            <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" x-bind:class="activeMenu === 'home' ? 'bg-leaf text-white' : 'hover:bg-mint hover:text-leaf dark:hover:bg-white/10'" class="rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.home') }}</a>
                            <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" x-bind:class="activeMenu === 'products' ? 'bg-leaf text-white' : 'hover:bg-mint hover:text-leaf dark:hover:bg-white/10'" class="rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.shop') }}</a>
                            <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" x-bind:class="activeMenu === 'about' ? 'bg-leaf text-white' : 'hover:bg-mint hover:text-leaf dark:hover:bg-white/10'" class="rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.about') }}</a>
                            <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" x-bind:class="activeMenu === 'blog' ? 'bg-leaf text-white' : 'hover:bg-mint hover:text-leaf dark:hover:bg-white/10'" class="rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.blog') }}</a>
                            <a href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}" class="rounded-full px-4 py-2.5 transition hover:bg-mint hover:text-leaf dark:hover:bg-white/10" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Contact' : 'Contact' }}</a>
                        </nav>

                        <div class="flex items-center justify-end gap-2">
                            @persist('cart-manager-'.$currentLocale)
                                <livewire:shop.cart-manager :locale="$currentLocale" />
                            @endpersist

                            <a href="{{ $alternateUrl }}" class="hidden min-h-[40px] items-center rounded-full bg-sunshine px-4 py-2 text-xs font-black uppercase tracking-wide text-forest transition hover:bg-mango sm:inline-flex" wire:navigate.hover>{{ strtoupper($alternateLocale) }}</a>

                            <button type="button" data-theme-toggle class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-full border border-leaf/20 bg-white text-leaf transition hover:bg-mint dark:border-white/10 dark:bg-white/5 dark:text-meadow sm:inline-flex" aria-label="{{ __('home.theme.toggle') }}">
                                <span data-theme-icon="light">☀</span><span data-theme-icon="dark" class="hidden">☾</span>
                            </button>

                            <label for="mobile-menu-state" data-mobile-menu-toggle class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-leaf/20 bg-white text-cocoa transition hover:bg-mint hover:text-leaf dark:border-white/10 dark:bg-white/5 dark:text-cream lg:hidden" role="button" tabindex="0" aria-expanded="false" aria-controls="mobile-menu" aria-label="Menu">
                                <span data-mobile-menu-icon="open">☰</span><span data-mobile-menu-icon="close" class="hidden">×</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="mobile-menu" data-mobile-menu class="mobile-menu-panel border-t border-leaf/10 bg-cream px-4 py-4 shadow-lg dark:border-white/10 dark:bg-ink lg:hidden">
                    <div class="grid gap-2">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'home'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-black text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.nav.home') }}<span class="text-leaf">→</span></a>
                        <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'products'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-leaf px-4 py-3 font-black text-white shadow-sm" wire:navigate.hover>{{ __('home.nav.shop') }}<span>→</span></a>
                        <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'about'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-black text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.nav.about') }}<span class="text-leaf">→</span></a>
                        <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'blog'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-black text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.nav.blog') }}<span class="text-leaf">→</span></a>
                        <a href="{{ $accountUrl }}" x-on:click="closeMobileMenu(); activeMenu = 'account'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-black text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.account.nav') }}<span class="text-leaf">→</span></a>
                        <a href="{{ $alternateUrl }}" x-on:click="closeMobileMenu()" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-sunshine px-4 py-3 font-black text-forest shadow-sm" wire:navigate.hover>{{ strtoupper($alternateLocale) }}<span>→</span></a>
                    </div>
                </div>
            </header>

            <main>@yield('content')</main>

            @if (request()->routeIs('home') || request()->routeIs('home.localized'))
                @include('partials.testimonials', ['currentLocale' => $currentLocale])
            @endif
            @if (request()->routeIs('products.show') && isset($product))
                @include('partials.product-reviews', ['product' => $product, 'currentLocale' => $currentLocale])
            @endif

            <footer class="border-t border-leaf/10 bg-forest px-4 pb-24 pt-12 text-sm text-cream sm:px-8 sm:pb-12 sm:pt-14 lg:pb-0">
                <div class="mx-auto grid max-w-7xl gap-8 pb-10 sm:grid-cols-2 lg:grid-cols-[1.15fr_0.85fr_0.95fr_1.05fr]">
                    <div>
                        <p class="brand-display text-4xl uppercase leading-none text-sunshine">Marché<span class="text-cream">.</span>Peyi</p>
                        <p class="mt-2 text-xs font-black uppercase tracking-[0.18em] text-cream/70">Exotic & Tropical Tastes</p>
                        <p class="mt-5 max-w-sm leading-7 text-cream/72">{{ __('home.footer.line') }}</p>
                    </div>
                    <div><h3 class="text-base font-black uppercase tracking-wide text-sunshine">{{ __('home.footer.products_title') }}</h3><ul class="mt-4 space-y-1 text-cream/72"><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('shop.index', ['locale' => $currentLocale]) }}">{{ $currentLocale === 'fr' ? 'Tous les produits' : 'All products' }}</a></li><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('shop.index', ['locale' => $currentLocale]) }}">{{ $currentLocale === 'fr' ? 'Sauces & Pikliz' : 'Sauces & Pikliz' }}</a></li><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('shop.index', ['locale' => $currentLocale]) }}">{{ __('home.footer.best_sellers') }}</a></li></ul></div>
                    <div><h3 class="text-base font-black uppercase tracking-wide text-sunshine">{{ $currentLocale === 'fr' ? 'Maison' : 'House' }}</h3><ul class="mt-4 space-y-1 text-cream/72"><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('pages.about', ['locale' => $currentLocale]) }}">{{ __('home.nav.about') }}</a></li><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}">{{ $currentLocale === 'fr' ? 'Contact' : 'Contact' }}</a></li><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('blog.index', ['locale' => $currentLocale]) }}">{{ __('home.nav.blog') }}</a></li></ul></div>
                    <div><h3 class="text-base font-black uppercase tracking-wide text-sunshine">{{ $currentLocale === 'fr' ? 'Paiement sécurisé' : 'Secure payment' }}</h3><div class="mt-5 flex flex-wrap items-center justify-center gap-3 rounded-[1.5rem] bg-white/10 p-4 text-center"><span class="inline-flex h-12 min-w-20 items-center justify-center rounded-xl bg-white px-3 text-xs font-black text-forest shadow-sm">▣ Visa</span><span class="inline-flex h-12 min-w-24 items-center justify-center rounded-xl bg-white px-3 text-xs font-black text-forest shadow-sm">▣ Mastercard</span><span class="inline-flex h-12 min-w-24 items-center justify-center rounded-xl bg-white px-3 text-xs font-black text-forest shadow-sm">▣ Apple Pay</span><span class="inline-flex h-12 min-w-24 items-center justify-center rounded-xl bg-white px-3 text-xs font-black text-forest shadow-sm">▣ Google Pay</span><span class="inline-flex h-12 min-w-20 items-center justify-center rounded-xl bg-white px-3 text-xs font-black text-forest shadow-sm">▣ PayPal</span></div></div>
                </div>
                <div class="mx-auto flex max-w-7xl flex-col gap-2 border-t border-cream/15 py-5 text-xs text-cream/58 sm:flex-row sm:items-center sm:justify-between"><p>© 2026 Marché Peyi — Tous droits réservés.</p><p>CGV · Mentions légales · Politique de confidentialité</p></div>
            </footer>
        </div>

        @livewireScriptConfig
    </body>
</html>
