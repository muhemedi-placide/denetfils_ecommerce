@php
    $currentLocale = $locale ?? app()->getLocale();
    $alternateLocale = $currentLocale === 'fr' ? 'en' : 'fr';
    $alternateUrl = route('home.localized', ['locale' => $alternateLocale]);
    $accountUrl = session()->has('customer_api_token') ? route('account.show', ['locale' => $currentLocale]) : route('account.login', ['locale' => $currentLocale]);

    if (request()->routeIs('shop.index')) {
        $alternateUrl = route('shop.index', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('pages.about')) {
        $alternateUrl = route('pages.about', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('blog.index')) {
        $alternateUrl = route('blog.index', ['locale' => $alternateLocale]);
    } elseif (request()->routeIs('pages.delivery')) {
        $alternateUrl = route('pages.delivery', ['locale' => $alternateLocale]);
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

                <div class="px-4 py-4 sm:px-8">
                    <div class="mx-auto grid max-w-7xl grid-cols-[auto_1fr_auto] items-center gap-6">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="flex items-center gap-3" wire:navigate.hover>
                            <span class="grid h-8 w-8 place-items-center rounded-t-xl bg-forest text-sm font-black text-sunshine">MP</span>
                            <span class="text-2xl font-black tracking-tight text-forest">Marché<span class="text-coral">.</span>Peyi</span>
                        </a>

                        <nav class="hidden items-center justify-center gap-7 text-sm font-bold uppercase tracking-wide text-forest/75 lg:flex">
                            <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="transition hover:text-forest {{ request()->routeIs('home') || request()->routeIs('home.localized') ? 'text-forest' : '' }}" wire:navigate.hover>Accueil</a>
                            <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="transition hover:text-forest {{ request()->routeIs('shop.index') ? 'text-forest' : '' }}" wire:navigate.hover>Boutique</a>
                            <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="transition hover:text-forest" wire:navigate.hover>Catégories</a>
                            <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" class="transition hover:text-forest {{ request()->routeIs('blog.index') ? 'text-forest' : '' }}" wire:navigate.hover>Recettes</a>
                            <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" class="transition hover:text-forest {{ request()->routeIs('pages.about') ? 'text-forest' : '' }}" wire:navigate.hover>Notre histoire</a>
                            <a href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}" class="transition hover:text-forest" wire:navigate.hover>Contact</a>
                        </nav>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="hidden h-10 w-10 items-center justify-center rounded-full text-forest transition hover:bg-mint sm:inline-flex" aria-label="Recherche" wire:navigate.hover>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                            </a>
                            <a href="{{ $accountUrl }}" class="hidden h-10 w-10 items-center justify-center rounded-full text-forest transition hover:bg-mint sm:inline-flex" aria-label="Compte" wire:navigate.hover>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c1.8-4 4.5-6 8-6s6.2 2 8 6"></path></svg>
                            </a>
                            @persist('cart-manager-'.$currentLocale)
                                <livewire:shop.cart-manager :locale="$currentLocale" />
                            @endpersist
                            <label for="mobile-menu-state" data-mobile-menu-toggle class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-forest/20 text-forest lg:hidden" role="button" tabindex="0" aria-label="Menu"><span data-mobile-menu-icon="open">Menu</span><span data-mobile-menu-icon="close" class="hidden">x</span></label>
                        </div>
                    </div>
                </div>

                <div id="mobile-menu" data-mobile-menu class="mobile-menu-panel border-t border-forest/10 bg-cream px-4 py-4 shadow-lg dark:bg-ink lg:hidden">
                    <div class="grid gap-2">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-white px-4 py-3 font-black text-forest" wire:navigate.hover>Accueil</a>
                        <a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-forest px-4 py-3 font-black text-cream" wire:navigate.hover>Boutique</a>
                        <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-white px-4 py-3 font-black text-forest" wire:navigate.hover>Recettes</a>
                        <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-white px-4 py-3 font-black text-forest" wire:navigate.hover>Notre histoire</a>
                        <a href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}" class="rounded-2xl bg-white px-4 py-3 font-black text-forest" wire:navigate.hover>Contact</a>
                        <a href="{{ $alternateUrl }}" class="rounded-2xl bg-sunshine px-4 py-3 font-black text-forest" wire:navigate.hover>{{ strtoupper($alternateLocale) }}</a>
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

            <footer class="bg-forest px-4 pt-16 text-sm text-cream sm:px-8">
                <div class="mx-auto grid max-w-7xl gap-12 pb-14 lg:grid-cols-[1.25fr_0.75fr_0.85fr_0.85fr]">
                    <div>
                        <div class="flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-t-xl bg-cream text-xs font-black text-forest">MP</span><p class="text-2xl font-black text-cream">Marché<span class="text-sunshine">.</span>Peyi</p></div>
                        <p class="mt-6 max-w-sm leading-7 text-cream/75">L'épicerie en ligne des saveurs caribéennes et tropicales. Produits authentiques, sourcés directement chez les producteurs.</p>
                        <div class="mt-6 space-y-2 text-cream/75"><p>bonjour@marchepeyi.com</p><p>+33 1 23 45 67 89</p><p>Paris · Pointe-à-Pitre</p></div>
                        <div class="mt-7 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                            <span class="rounded-full border border-cream/20 px-3 py-1 text-[11px] font-black uppercase text-cream/80">Visa</span>
                            <span class="rounded-full border border-cream/20 px-3 py-1 text-[11px] font-black uppercase text-cream/80">Mastercard</span>
                            <span class="rounded-full border border-cream/20 px-3 py-1 text-[11px] font-black uppercase text-cream/80">Apple Pay</span>
                            <span class="rounded-full border border-cream/20 px-3 py-1 text-[11px] font-black uppercase text-cream/80">Google Pay</span>
                            <span class="rounded-full border border-cream/20 px-3 py-1 text-[11px] font-black uppercase text-cream/80">PayPal</span>
                        </div>
                    </div>

                    <div><h3 class="text-lg font-black text-cream">Boutique</h3><ul class="mt-5 space-y-3 text-cream/75"><li><a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>Tous les produits</a></li><li><a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>Sauces & Pikliz</a></li><li><a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>Épices</a></li><li><a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>Boissons tropicales</a></li><li><a href="{{ route('shop.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>Produits frais</a></li></ul></div>
                    <div><h3 class="text-lg font-black text-cream">Service client</h3><ul class="mt-5 space-y-3 text-cream/75"><li><a href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}" wire:navigate.hover>Contact</a></li><li><a href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}" wire:navigate.hover>Livraison & retours</a></li><li><a href="{{ route('checkout.show', ['locale' => $currentLocale]) }}" wire:navigate.hover>Suivi de commande</a></li><li><a href="{{ route('pages.payment', ['locale' => $currentLocale]) }}" wire:navigate.hover>FAQ</a></li></ul></div>
                    <div><h3 class="text-lg font-black text-cream">Informations</h3><ul class="mt-5 space-y-3 text-cream/75"><li><a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" wire:navigate.hover>Notre histoire</a></li><li><a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>Recettes</a></li><li><a href="{{ route('pages.terms', ['locale' => $currentLocale]) }}" wire:navigate.hover>Engagements</a></li><li><a href="{{ route('pages.legal', ['locale' => $currentLocale]) }}" wire:navigate.hover>Mentions légales</a></li></ul></div>
                </div>
                <div class="mx-auto flex max-w-7xl flex-col gap-3 border-t border-cream/10 py-6 text-xs text-cream/60 sm:flex-row sm:items-center sm:justify-between"><p>© 2026 Marché Peyi — Tous droits réservés.</p><p>CGV · Mentions légales · Politique de confidentialité</p></div>
            </footer>
        </div>

        @livewireScriptConfig
    </body>
</html>
