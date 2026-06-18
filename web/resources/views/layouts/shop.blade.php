@php
    $currentLocale = $locale ?? app()->getLocale();
    $alternateLocale = $currentLocale === 'fr' ? 'en' : 'fr';
    $alternateUrl = route('home.localized', ['locale' => $alternateLocale]);
    $accountUrl = session()->has('customer_api_token')
        ? route('account.show', ['locale' => $currentLocale])
        : route('account.login', ['locale' => $currentLocale]);

    if (request()->routeIs('pages.about')) {
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
        <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
        <link rel="dns-prefetch" href="https://images.unsplash.com">
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
                        <span>{{ $currentLocale === 'fr' ? 'Paiement sécurisé' : 'Secure payment' }}</span><span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Produits authentiques des Antilles & d’Afrique' : 'Authentic Caribbean & African products' }}</span><span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Votre marché des saveurs exotiques 24h/24' : 'Your exotic flavors market 24/7' }}</span>
                    </div>
                </div>

                <div class="px-4 py-4 sm:px-8">
                    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 lg:grid lg:grid-cols-[230px_1fr_auto] lg:gap-5">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="flex min-w-0 items-center gap-3" x-on:click="closeMobileMenu(); activeMenu = 'home'" wire:navigate.hover>
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-leaf bg-cream text-base font-black text-leaf shadow-sm dark:bg-white/5 dark:text-meadow">MP</span>
                            <span class="min-w-0">
                                <span class="brand-display block truncate text-2xl uppercase leading-none tracking-wide text-leaf dark:text-meadow">Marché Peyi</span>
                                <span class="block truncate text-[11px] font-black uppercase tracking-[0.12em] text-cocoa/55 dark:text-cream/60">Exotic & Tropical Tastes</span>
                            </span>
                        </a>

                        <livewire:shop.header-search
                            :locale="$currentLocale"
                            input-id="global-search"
                            form-class="hidden overflow-hidden rounded-full border border-leaf/20 bg-white p-1 dark:border-white/10 dark:bg-white/5 lg:flex"
                            :on-catalog-page="request()->routeIs('home') || request()->routeIs('home.localized')"
                        />

                        <div class="flex shrink-0 items-center justify-end gap-2">
                            @persist('cart-manager-'.$currentLocale)
                                <livewire:shop.cart-manager :locale="$currentLocale" />
                            @endpersist

                            <a href="{{ $accountUrl }}" class="hidden min-h-[44px] items-center justify-center rounded-full border border-leaf/20 bg-white px-4 py-2.5 text-sm font-black text-leaf transition hover:bg-mint dark:border-white/10 dark:bg-white/5 dark:text-cream dark:hover:bg-white/10 sm:inline-flex" wire:navigate.hover>{{ __('home.account.nav') }}</a>

                            <button type="button" data-theme-toggle class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-leaf/20 bg-white text-leaf transition hover:bg-mint dark:border-white/10 dark:bg-white/5 dark:text-meadow" aria-label="{{ __('home.theme.toggle') }}">
                                <span data-theme-icon="light">☀</span><span data-theme-icon="dark" class="hidden">☾</span>
                            </button>

                            <label for="mobile-menu-state" data-mobile-menu-toggle class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-leaf/20 bg-white text-cocoa transition hover:bg-mint hover:text-leaf dark:border-white/10 dark:bg-white/5 dark:text-cream lg:hidden" role="button" tabindex="0" aria-expanded="false" aria-controls="mobile-menu" aria-label="Menu">
                                <span data-mobile-menu-icon="open">☰</span><span data-mobile-menu-icon="close" class="hidden">×</span>
                            </label>
                        </div>
                    </div>
                </div>

                <nav class="hidden border-t border-leaf/10 bg-white px-4 py-2 text-sm font-black uppercase tracking-wide text-cocoa/75 dark:border-white/10 dark:bg-[#163319] dark:text-cream/75 sm:px-8 lg:block">
                    <div class="mx-auto flex max-w-7xl items-center gap-2">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" x-on:click="activeMenu = 'home'" x-bind:class="activeMenu === 'home' ? 'bg-leaf text-white shadow-sm' : 'hover:bg-mint hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.home') }}</a>
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" x-on:click="activeMenu = 'products'" x-bind:class="activeMenu === 'products' ? 'bg-leaf text-white shadow-sm' : 'hover:bg-mint hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.shop') }}</a>
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#categories" class="min-h-[44px] rounded-full px-4 py-2.5 transition hover:bg-mint hover:text-leaf dark:hover:bg-white/10" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Catégories' : 'Categories' }}</a>
                        <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" x-on:click="activeMenu = 'blog'" x-bind:class="activeMenu === 'blog' ? 'bg-leaf text-white shadow-sm' : 'hover:bg-mint hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.blog') }}</a>
                        <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" x-on:click="activeMenu = 'about'" x-bind:class="activeMenu === 'about' ? 'bg-leaf text-white shadow-sm' : 'hover:bg-mint hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.about') }}</a>
                        <a href="{{ $alternateUrl }}" class="ml-auto min-h-[44px] rounded-full bg-sunshine px-4 py-2.5 text-forest transition hover:bg-mango" wire:navigate.hover>{{ strtoupper($alternateLocale) }}</a>
                    </div>
                </nav>

                <div id="mobile-menu" data-mobile-menu class="mobile-menu-panel border-t border-leaf/10 bg-cream px-4 py-4 shadow-lg dark:border-white/10 dark:bg-ink lg:hidden">
                    <livewire:shop.header-search :locale="$currentLocale" input-id="mobile-search" form-class="flex overflow-hidden rounded-full border border-leaf/20 bg-white p-1 dark:border-white/10 dark:bg-white/5" :on-catalog-page="request()->routeIs('home') || request()->routeIs('home.localized')" />
                    <div class="mt-4 grid gap-2">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'home'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-black text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.nav.home') }}<span class="text-leaf">→</span></a>
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" x-on:click="closeMobileMenu(); activeMenu = 'products'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-leaf px-4 py-3 font-black text-white shadow-sm" wire:navigate.hover>{{ __('home.nav.shop') }}<span>→</span></a>
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#categories" x-on:click="closeMobileMenu()" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-black text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Catégories' : 'Categories' }}<span class="text-leaf">→</span></a>
                        <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'blog'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-black text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.nav.blog') }}<span class="text-leaf">→</span></a>
                        <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'about'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-black text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.nav.about') }}<span class="text-leaf">→</span></a>
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
                <div class="mx-auto grid max-w-7xl gap-8 pb-10 sm:grid-cols-2 lg:grid-cols-[1.2fr_0.8fr_0.95fr_1.1fr]">
                    <div>
                        <div class="flex items-center gap-3"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-sunshine text-sm font-black text-sunshine">MP</span><div><p class="brand-display text-3xl uppercase leading-none text-sunshine">Marché Peyi</p><p class="text-cream/70">Exotic & Tropical Tastes</p></div></div>
                        <p class="mt-5 max-w-sm leading-7 text-cream/72">{{ __('home.footer.line') }}</p>
                    </div>
                    <div><h3 class="text-base font-black uppercase tracking-wide text-sunshine">{{ __('home.footer.products_title') }}</h3><ul class="mt-4 space-y-1 text-cream/72"><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products">{{ $currentLocale === 'fr' ? 'Tous les produits' : 'All products' }}</a></li><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('home.localized', ['locale' => $currentLocale]) }}#categories">{{ $currentLocale === 'fr' ? 'Catégories' : 'Categories' }}</a></li><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('home.localized', ['locale' => $currentLocale]) }}#best-sellers">{{ __('home.footer.best_sellers') }}</a></li></ul></div>
                    <div><h3 class="text-base font-black uppercase tracking-wide text-sunshine">{{ $currentLocale === 'fr' ? 'Service client' : 'Customer service' }}</h3><ul class="mt-4 space-y-1 text-cream/72"><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}">{{ __('home.footer.delivery') }}</a></li><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('pages.payment', ['locale' => $currentLocale]) }}">{{ __('home.footer.secure_payment') }}</a></li><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('pages.terms', ['locale' => $currentLocale]) }}">{{ __('home.footer.terms') }}</a></li><li><a class="block rounded-lg py-2 transition hover:text-sunshine" href="{{ route('pages.about', ['locale' => $currentLocale]) }}">{{ __('home.nav.about') }}</a></li></ul></div>
                    <div><h3 class="text-base font-black uppercase tracking-wide text-sunshine">{{ __('home.footer.information_title') }}</h3><div class="mt-5 space-y-3 text-cream/72"><p class="font-semibold text-cream">{{ __('home.contact.company') }}</p><p>{{ __('home.contact.address') }}</p><p>{{ __('home.contact.phone') }}</p><p>{{ __('home.contact.email') }}</p></div></div>
                </div>
                <div class="mx-auto flex max-w-7xl flex-col gap-2 border-t border-cream/15 py-5 text-xs text-cream/58 sm:flex-row sm:items-center sm:justify-between"><p>Copyright &copy; 2026 Marché Peyi. All rights reserved.</p><p>{{ __('home.contact.email') }}</p></div>
            </footer>
        </div>

        @livewireScriptConfig
    </body>
</html>
