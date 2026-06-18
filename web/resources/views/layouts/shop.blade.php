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

    $navItems = [
        ['key' => 'home', 'label' => __('home.nav.home'), 'url' => route('home.localized', ['locale' => $currentLocale])],
        ['key' => 'products', 'label' => __('home.nav.shop'), 'url' => route('home.localized', ['locale' => $currentLocale]) . '#products'],
        ['key' => 'categories', 'label' => $currentLocale === 'fr' ? 'Catégories' : 'Categories', 'url' => route('home.localized', ['locale' => $currentLocale]) . '#categories'],
        ['key' => 'blog', 'label' => __('home.nav.blog'), 'url' => route('blog.index', ['locale' => $currentLocale])],
        ['key' => 'about', 'label' => __('home.nav.about'), 'url' => route('pages.about', ['locale' => $currentLocale])],
    ];
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
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
        <link rel="dns-prefetch" href="https://images.unsplash.com">
        <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=DM+Sans:wght@400;500;700;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="theme-page surface-transition min-h-screen bg-cream text-cocoa dark:bg-ink dark:text-cream">
        <div id="shop-app" x-data="shopApp({ locale: @js($currentLocale), activeMenu: @js($activeMenu ?? 'home') })" x-init="init()">
            <input id="mobile-menu-state" class="sr-only" type="checkbox" autocomplete="off" aria-hidden="true">

            <header class="sticky top-0 z-40 border-b border-leaf/10 bg-cream/95 backdrop-blur dark:border-white/10 dark:bg-ink/95">
                <div class="overflow-hidden bg-forest px-4 py-2 text-[11px] font-black uppercase tracking-[0.24em] text-cream sm:px-8">
                    <div class="market-ticker flex w-max gap-7">
                        <span>{{ $currentLocale === 'fr' ? 'Livraison offerte dès 35€' : 'Free delivery from €35' }}</span>
                        <span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Paiement sécurisé' : 'Secure payment' }}</span>
                        <span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Produits haïtiens authentiques' : 'Authentic Haitian products' }}</span>
                        <span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Votre marché des saveurs 24h/24' : 'Your flavor market 24/7' }}</span>
                        <span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Livraison offerte dès 35€' : 'Free delivery from €35' }}</span>
                        <span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Paiement sécurisé' : 'Secure payment' }}</span>
                        <span>•</span>
                        <span>{{ $currentLocale === 'fr' ? 'Produits haïtiens authentiques' : 'Authentic Haitian products' }}</span>
                    </div>
                </div>

                <div class="px-4 py-4 sm:px-8">
                    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="flex min-w-0 items-center gap-3" x-on:click="closeMobileMenu(); activeMenu = 'home'" wire:navigate.hover>
                            <span class="brand-mark" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 11.5 12 4l9 7.5"></path>
                                    <path d="M5.5 10.5V20h13v-9.5"></path>
                                    <path d="M9.5 20v-5h5v5"></path>
                                </svg>
                            </span>
                            <span class="min-w-0">
                                <span class="brand-display block truncate text-xl uppercase leading-none text-forest sm:text-2xl">DEN & FILS</span>
                                <span class="block truncate text-[10px] font-black uppercase tracking-[0.2em] text-cocoa/55 dark:text-cream/60">{{ __('home.nav.promise') }}</span>
                            </span>
                        </a>

                        <nav class="hidden items-center justify-center gap-1 text-sm font-black uppercase tracking-wide text-cocoa/70 dark:text-cream/70 lg:flex">
                            @foreach ($navItems as $item)
                                <a
                                    href="{{ $item['url'] }}"
                                    x-on:click="activeMenu = @js($item['key'])"
                                    x-bind:class="activeMenu === @js($item['key']) ? 'text-forest' : 'hover:text-forest dark:hover:text-meadow'"
                                    class="rounded-full px-4 py-2.5 transition"
                                    wire:navigate.hover
                                >{{ $item['label'] }}</a>
                            @endforeach
                        </nav>

                        <div class="flex shrink-0 items-center justify-end gap-2">
                            <livewire:shop.header-search
                                :locale="$currentLocale"
                                input-id="global-search"
                                form-class="hidden overflow-hidden rounded-full border border-leaf/15 bg-white p-1 shadow-sm dark:border-white/10 dark:bg-white/5 xl:flex"
                                :on-catalog-page="request()->routeIs('home') || request()->routeIs('home.localized')"
                            />

                            <a href="{{ $accountUrl }}" class="inline-flex h-11 w-11 items-center justify-center rounded-full text-forest transition hover:bg-mint dark:text-meadow dark:hover:bg-white/10" aria-label="{{ __('home.account.nav') }}" wire:navigate.hover>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </a>

                            @persist('cart-manager-'.$currentLocale)
                                <livewire:shop.cart-manager :locale="$currentLocale" />
                            @endpersist

                            <a href="{{ $alternateUrl }}" class="hidden min-h-[42px] items-center justify-center rounded-full bg-sunshine px-3 text-xs font-black uppercase tracking-wide text-forest transition hover:bg-mango sm:inline-flex" wire:navigate.hover>{{ strtoupper($alternateLocale) }}</a>

                            <button type="button" data-theme-toggle class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-forest transition hover:bg-mint dark:text-meadow dark:hover:bg-white/10" aria-label="{{ __('home.theme.toggle') }}">
                                <span data-theme-icon="light">☀</span><span data-theme-icon="dark" class="hidden">☾</span>
                            </button>

                            <label for="mobile-menu-state" data-mobile-menu-toggle class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-leaf/15 bg-white text-forest transition hover:bg-mint dark:border-white/10 dark:bg-white/5 dark:text-cream lg:hidden" role="button" tabindex="0" aria-expanded="false" aria-controls="mobile-menu" aria-label="Menu">
                                <span data-mobile-menu-icon="open">☰</span><span data-mobile-menu-icon="close" class="hidden">×</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="mobile-menu" data-mobile-menu class="mobile-menu-panel border-t border-leaf/10 bg-cream px-4 py-4 shadow-lg dark:border-white/10 dark:bg-ink lg:hidden">
                    <livewire:shop.header-search :locale="$currentLocale" input-id="mobile-search" form-class="flex overflow-hidden rounded-full border border-leaf/20 bg-white p-1 dark:border-white/10 dark:bg-white/5" :on-catalog-page="request()->routeIs('home') || request()->routeIs('home.localized')" />
                    <div class="mt-4 grid gap-2">
                        @foreach ($navItems as $item)
                            <a href="{{ $item['url'] }}" x-on:click="closeMobileMenu(); activeMenu = @js($item['key'])" class="flex min-h-[48px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-black text-cocoa shadow-sm transition hover:bg-mint dark:bg-white/5 dark:text-cream" wire:navigate.hover>
                                <span>{{ $item['label'] }}</span><span class="text-forest">→</span>
                            </a>
                        @endforeach
                        <a href="{{ $accountUrl }}" x-on:click="closeMobileMenu(); activeMenu = 'account'" class="flex min-h-[48px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-black text-cocoa shadow-sm transition hover:bg-mint dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.account.nav') }}<span class="text-forest">→</span></a>
                        <a href="{{ $alternateUrl }}" x-on:click="closeMobileMenu()" class="flex min-h-[48px] items-center justify-between rounded-2xl bg-sunshine px-4 py-3 font-black text-forest shadow-sm" wire:navigate.hover>{{ strtoupper($alternateLocale) }}<span>→</span></a>
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

            <footer class="bg-forest px-4 pb-24 pt-14 text-sm text-cream sm:px-8 sm:pb-12 lg:pb-0">
                <div class="mx-auto grid max-w-7xl gap-10 pb-12 sm:grid-cols-2 lg:grid-cols-[1.35fr_0.85fr_0.95fr_1fr]">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="inline-grid h-10 w-10 place-items-center rounded-full bg-cream text-forest">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 11.5 12 4l9 7.5"></path>
                                    <path d="M5.5 10.5V20h13v-9.5"></path>
                                </svg>
                            </span>
                            <p class="brand-display text-2xl uppercase leading-none text-cream">DEN & FILS</p>
                        </div>
                        <p class="mt-5 max-w-sm text-base font-semibold leading-7 text-cream/72">{{ __('home.footer.line') }}</p>
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach (['Visa', 'Mastercard', 'Apple Pay', 'Google Pay', 'PayPal'] as $method)
                                <span class="rounded-full border border-cream/15 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-cream/65">{{ $method }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-black text-cream">{{ __('home.footer.products_title') }}</h3>
                        <ul class="mt-4 space-y-2 text-cream/72">
                            <li><a class="transition hover:text-sunshine" href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products">{{ $currentLocale === 'fr' ? 'Tous les produits' : 'All products' }}</a></li>
                            <li><a class="transition hover:text-sunshine" href="{{ route('home.localized', ['locale' => $currentLocale]) }}#categories">{{ $currentLocale === 'fr' ? 'Catégories' : 'Categories' }}</a></li>
                            <li><a class="transition hover:text-sunshine" href="{{ route('home.localized', ['locale' => $currentLocale]) }}#best-sellers">{{ __('home.footer.best_sellers') }}</a></li>
                            <li><a class="transition hover:text-sunshine" href="{{ route('blog.index', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ __('home.nav.blog') }}</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-base font-black text-cream">{{ $currentLocale === 'fr' ? 'Service client' : 'Customer service' }}</h3>
                        <ul class="mt-4 space-y-2 text-cream/72">
                            <li><a class="transition hover:text-sunshine" href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ __('home.footer.delivery') }}</a></li>
                            <li><a class="transition hover:text-sunshine" href="{{ route('pages.payment', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ __('home.footer.secure_payment') }}</a></li>
                            <li><a class="transition hover:text-sunshine" href="{{ route('pages.terms', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ __('home.footer.terms') }}</a></li>
                            <li><a class="transition hover:text-sunshine" href="{{ route('pages.legal', ['locale' => $currentLocale]) }}" wire:navigate.hover>{{ __('home.footer.legal') }}</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-base font-black text-cream">{{ __('home.footer.information_title') }}</h3>
                        <div class="mt-4 space-y-3 text-cream/72">
                            <p class="font-black text-cream">{{ __('home.contact.company') }}</p>
                            <p>{{ __('home.contact.address') }}</p>
                            <p>{{ __('home.contact.phone') }}</p>
                            <p>{{ __('home.contact.email') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mx-auto flex max-w-7xl flex-col gap-2 border-t border-cream/15 py-5 text-xs text-cream/58 sm:flex-row sm:items-center sm:justify-between">
                    <p>© 2026 DEN & FILS — {{ $currentLocale === 'fr' ? 'Tous droits réservés.' : 'All rights reserved.' }}</p>
                    <p>{{ $currentLocale === 'fr' ? 'CGV · Mentions légales · Politique de confidentialité' : 'Terms · Legal notice · Privacy policy' }}</p>
                </div>
            </footer>
        </div>

        @livewireScriptConfig
    </body>
</html>
