@php
    $currentLocale = $locale ?? app()->getLocale();
    $alternateLocale = $currentLocale === 'fr' ? 'en' : 'fr';
    $alternateUrl = route('home.localized', ['locale' => $alternateLocale]);
    $accountUrl = session()->has('customer_api_token') ? route('account.show', ['locale' => $currentLocale]) : route('account.login', ['locale' => $currentLocale]);

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
    <body class="theme-page min-h-screen bg-cream text-cocoa dark:bg-ink dark:text-cream">
        <div id="shop-app" x-data="shopApp({ locale: @js($currentLocale), activeMenu: @js($activeMenu ?? 'home') })" x-init="init()">
            <input id="mobile-menu-state" class="sr-only" type="checkbox" autocomplete="off" aria-hidden="true">

            <header class="sticky top-0 z-40 border-b border-forest/10 bg-cream/95 shadow-sm backdrop-blur dark:border-white/10 dark:bg-ink/95">
                <div class="overflow-hidden bg-forest px-4 py-2 text-[11px] font-black uppercase tracking-[0.28em] text-cream sm:px-8">
                    <div class="market-ticker flex gap-8 whitespace-nowrap">
                        <span>Votre marché des saveurs exotiques 24h/24</span>
                        <span>Livraison offerte dès 49€</span>
                        <span>Paiement sécurisé</span>
                        <span>Produits authentiques</span>
                        <span>Votre marché des saveurs exotiques 24h/24</span>
                    </div>
                </div>

                <div class="px-4 py-3 sm:px-8 sm:py-4">
                    <div class="mx-auto grid max-w-7xl grid-cols-[44px_minmax(0,1fr)_auto] items-center gap-2 lg:grid-cols-[auto_1fr_auto] lg:gap-6">
                        <label for="mobile-menu-state" data-mobile-menu-toggle class="inline-flex h-11 w-11 items-center justify-center rounded-full text-2xl text-forest transition hover:bg-mint dark:text-meadow dark:hover:bg-white/10 lg:hidden" role="button" tabindex="0" aria-label="Menu">
                            <span data-mobile-menu-icon="open">☰</span>
                            <span data-mobile-menu-icon="close" class="hidden">×</span>
                        </label>

                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="flex min-w-0 items-center justify-center gap-2 justify-self-center lg:justify-self-start" wire:navigate.hover>
                            <span class="text-2xl leading-none text-forest dark:text-meadow">⌂</span>
                            <span class="truncate text-xl font-black tracking-tight text-forest dark:text-meadow sm:text-2xl">Marché<span class="text-coral">.</span>Peyi</span>
                        </a>

                        <nav class="hidden items-center justify-center gap-7 text-sm font-bold uppercase tracking-wide text-forest/75 dark:text-cream/75 lg:flex">
                            <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="transition hover:text-forest dark:hover:text-meadow {{ request()->routeIs('home') || request()->routeIs('home.localized') ? 'text-forest dark:text-meadow' : '' }}" wire:navigate.hover>Accueil</a>
                            <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="transition hover:text-forest dark:hover:text-meadow {{ request()->routeIs('shop.index') ? 'text-forest dark:text-meadow' : '' }}" wire:navigate.hover>Boutique</a>
                            <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="transition hover:text-forest dark:hover:text-meadow" wire:navigate.hover>Catégories</a>
                            <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" class="transition hover:text-forest dark:hover:text-meadow {{ request()->routeIs('blog.index') ? 'text-forest dark:text-meadow' : '' }}" wire:navigate.hover>Recettes</a>
                            <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" class="transition hover:text-forest dark:hover:text-meadow {{ request()->routeIs('pages.about') ? 'text-forest dark:text-meadow' : '' }}" wire:navigate.hover>Notre histoire</a>
                            <a href="{{ route('pages.tracking', ['locale' => $currentLocale]) }}" class="transition hover:text-forest dark:hover:text-meadow {{ request()->routeIs('pages.tracking') ? 'text-forest dark:text-meadow' : '' }}" wire:navigate.hover>Suivi colis</a>
                            <a href="{{ route('pages.contact', ['locale' => $currentLocale]) }}" class="transition hover:text-forest dark:hover:text-meadow {{ request()->routeIs('pages.contact') ? 'text-forest dark:text-meadow' : '' }}" wire:navigate.hover>Contact</a>
                        </nav>

                        <div class="flex items-center justify-end gap-2 sm:gap-3">
                            <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="hidden h-10 w-10 items-center justify-center rounded-full text-forest transition hover:bg-mint dark:text-meadow dark:hover:bg-white/10 lg:inline-flex" aria-label="Recherche" wire:navigate.hover>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                            </a>
                            <a href="{{ $accountUrl }}" class="hidden h-10 w-10 items-center justify-center rounded-full text-forest transition hover:bg-mint dark:text-meadow dark:hover:bg-white/10 lg:inline-flex" aria-label="Compte" wire:navigate.hover>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c1.8-4 4.5-6 8-6s6.2 2 8 6"></path></svg>
                            </a>
                            <a href="{{ $alternateUrl }}" class="hidden h-10 items-center justify-center rounded-full border border-forest/20 px-3 text-xs font-black uppercase tracking-wide text-forest transition hover:bg-mint dark:border-white/15 dark:text-meadow dark:hover:bg-white/10 lg:inline-flex" aria-label="{{ $currentLocale === 'fr' ? 'Switch to English' : 'Passer en français' }}" wire:navigate.hover>{{ strtoupper($alternateLocale) }}</a>
                            <button type="button" class="hidden h-10 w-10 items-center justify-center rounded-full border border-forest/20 text-forest transition hover:bg-mint dark:border-white/15 dark:text-meadow dark:hover:bg-white/10 lg:inline-flex" aria-label="Changer le thème" x-on:click="toggleTheme()"><span x-show="theme !== 'dark'" aria-hidden="true">☀</span><span x-show="theme === 'dark'" aria-hidden="true">☾</span></button>
                            @persist('cart-manager-'.$currentLocale)
                                <livewire:shop.cart-manager :locale="$currentLocale" />
                            @endpersist
                        </div>
                    </div>
                </div>

                <div id="mobile-menu" data-mobile-menu class="mobile-menu-panel border-t border-forest/10 bg-cream px-4 py-4 shadow-lg dark:border-white/10 dark:bg-ink lg:hidden">
                    <div class="grid gap-2">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-white px-4 py-3 font-black text-forest dark:bg-white/5 dark:text-cream" wire:navigate.hover>Accueil</a>
                        <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-forest px-4 py-3 font-black text-cream" wire:navigate.hover>Boutique</a>
                        <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-white px-4 py-3 font-black text-forest dark:bg-white/5 dark:text-cream" wire:navigate.hover>Recettes</a>
                        <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-white px-4 py-3 font-black text-forest dark:bg-white/5 dark:text-cream" wire:navigate.hover>Notre histoire</a>
                        <a href="{{ route('pages.tracking', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-white px-4 py-3 font-black text-forest dark:bg-white/5 dark:text-cream" wire:navigate.hover>Suivi colis</a>
                        <a href="{{ route('pages.contact', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-white px-4 py-3 font-black text-forest dark:bg-white/5 dark:text-cream" wire:navigate.hover>Contact</a>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ $alternateUrl }}" class="rounded-2xl bg-sunshine px-4 py-3 text-center font-black text-forest" wire:navigate.hover>{{ strtoupper($alternateLocale) }}</a>
                            <button type="button" class="rounded-2xl border border-forest/20 bg-white px-4 py-3 font-black text-forest dark:border-white/10 dark:bg-white/5 dark:text-cream" x-on:click="toggleTheme(); closeMobileMenu()"><span x-show="theme !== 'dark'">Mode sombre</span><span x-show="theme === 'dark'">Mode clair</span></button>
                        </div>
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

            <footer class="bg-forest text-cream">
                <section class="border-y border-forest/10 bg-cream px-4 py-16 text-center text-forest dark:border-white/10 dark:bg-ink dark:text-cream sm:px-8 lg:py-20">
                    <div class="mx-auto max-w-3xl">
                        <p class="text-xs font-black uppercase tracking-[0.35em] text-coral">Newsletter</p>
                        <h2 class="mt-4 text-4xl font-black uppercase leading-none tracking-tight sm:text-5xl">
                            {{ $currentLocale === 'fr' ? 'Ne ratez rien du marché !' : 'Nuh miss ah ting!' }}
                        </h2>
                        <p class="mt-4 text-base font-semibold text-forest/75 dark:text-cream/80">
                            {{ $currentLocale === 'fr' ? 'Pas de spam. Juste des saveurs. 10% de réduction sur votre première commande 👀.' : 'No spam. Just spice. 10% off your first order 👀.' }}
                        </p>
                        <form class="mx-auto mt-9 flex max-w-xl flex-col items-center justify-center gap-3 sm:flex-row" action="#" method="POST">
                            <label class="sr-only" for="footer-newsletter-email">Email</label>
                            <input id="footer-newsletter-email" type="email" required placeholder="email@example.com" class="h-14 w-full rounded-full border-2 border-forest bg-white px-6 text-base font-bold text-forest outline-none placeholder:text-forest/55 focus:ring-4 focus:ring-sunshine/40 dark:bg-cream dark:text-forest sm:flex-1">
                            <button type="submit" class="h-14 rounded-full border-2 border-forest bg-forest px-7 text-sm font-black uppercase tracking-wide text-cream shadow-[0_7px_0_#ffc829] transition hover:-translate-y-0.5 hover:bg-leaf">
                                {{ $currentLocale === 'fr' ? 'S’abonner' : 'Subscribe' }}
                            </button>
                        </form>
                    </div>
                </section>

                <div class="bg-forest px-4 pb-8 pt-16 sm:px-8 lg:pt-20">
                    <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[1.1fr_1.15fr_0.55fr_0.75fr]">
                        <div>
                            <div class="inline-block text-5xl font-black uppercase leading-[0.78] tracking-[-0.08em] text-cream sm:text-7xl">
                                <span class="block">Marché</span>
                                <span class="block">Peyi</span>
                            </div>
                        </div>

                        <div>
                            <p class="max-w-xl text-base font-bold leading-8 text-cream">
                                {{ $currentLocale === 'fr' ? 'Marché Peyi rend les saveurs caribéennes, haïtiennes et africaines faciles à retrouver, à cuisiner et à partager au quotidien.' : 'Marché Peyi makes Caribbean, Haitian and African flavors easy to find, cook and share every day.' }}
                            </p>
                            <div class="mt-8 flex items-center gap-5 text-xl text-cream">
                                <a href="#" aria-label="Facebook" class="transition hover:text-sunshine">f</a>
                                <a href="#" aria-label="Instagram" class="transition hover:text-sunshine">◎</a>
                                <a href="#" aria-label="TikTok" class="transition hover:text-sunshine">♪</a>
                            </div>
                        </div>

                        <nav class="space-y-4 text-base font-black text-cream" aria-label="Footer primary">
                            <a class="block hover:text-sunshine" href="{{ route('home.localized', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Accueil' : 'Home' }}</a>
                            <a class="block hover:text-sunshine" href="{{ route('shop.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Boutique' : 'Shop' }}</a>
                            <a class="block hover:text-sunshine" href="{{ route('blog.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Recettes' : 'Recipes' }}</a>
                            <a class="block hover:text-sunshine" href="{{ route('pages.tracking', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Suivi colis' : 'Track parcel' }}</a>
                            <a class="block hover:text-sunshine" href="{{ route('pages.contact', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Nous trouver' : 'Find us' }}</a>
                        </nav>

                        <nav class="space-y-4 text-base font-black text-cream" aria-label="Footer secondary">
                            <a class="block hover:text-sunshine" href="{{ route('pages.about', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Notre histoire' : 'Our story' }}</a>
                            <a class="block hover:text-sunshine" href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Livraison & retours' : 'Shipping & returns' }}</a>
                            <a class="block hover:text-sunshine" href="{{ route('pages.contact', ['locale' => $currentLocale]) }}" wire:navigate.hover>Contact</a>
                            <a class="block hover:text-sunshine" href="{{ route('pages.legal', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ $currentLocale === 'fr' ? 'Mentions légales' : 'Legal notice' }}</a>
                        </nav>
                    </div>

                    <div class="mx-auto mt-20 flex max-w-7xl flex-col gap-6 border-t border-cream/10 pt-8 text-sm font-bold text-cream sm:flex-row sm:items-end sm:justify-between">
                        <p>© 2026, Marché Peyi.</p>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded bg-cream px-2 py-1 text-[11px] font-black text-forest">AMEX</span>
                            <span class="rounded bg-cream px-2 py-1 text-[11px] font-black text-forest">Apple Pay</span>
                            <span class="rounded bg-cream px-2 py-1 text-[11px] font-black text-forest">Visa</span>
                            <span class="rounded bg-cream px-2 py-1 text-[11px] font-black text-forest">G Pay</span>
                            <span class="rounded bg-cream px-2 py-1 text-[11px] font-black text-forest">Mastercard</span>
                            <span class="rounded bg-cream px-2 py-1 text-[11px] font-black text-forest">PayPal</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        @livewireScriptConfig
    </body>
</html>
