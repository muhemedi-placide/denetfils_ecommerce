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

            try {
                storedTheme = localStorage.getItem('theme');
            } catch (error) {
                storedTheme = null;
            }

            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if ((storedTheme !== 'light' && storedTheme !== 'dark') || storedTheme === null) {
                storedTheme = systemPrefersDark ? 'dark' : 'light';
            }

            if (storedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        </script>
        <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
        <link rel="dns-prefetch" href="https://images.unsplash.com">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="theme-page surface-transition min-h-screen bg-cream text-cocoa dark:bg-ink dark:text-cream">
        <div
            id="shop-app"
            x-data="shopApp({
            locale: @js($currentLocale),
            activeMenu: @js($activeMenu ?? 'home')
        })"
            x-init="init()"
        >
            <input id="mobile-menu-state" class="sr-only" type="checkbox" autocomplete="off" aria-hidden="true">

        <header class="sticky top-0 z-40 border-b border-leaf/10 bg-white/95 shadow-sm backdrop-blur dark:border-white/10 dark:bg-ink/95">
            <div class="border-b border-leaf/10 bg-cream px-4 py-2 text-xs font-semibold text-leaf dark:border-white/10 dark:bg-[#172414] dark:text-meadow sm:px-8" x-data="{ alerts: @js(trans('home.announcements')), alertIndex: 0 }" x-init="setInterval(() => alertIndex = (alertIndex + 1) % alerts.length, 4200)">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                    <div class="relative h-5 min-w-0 flex-1 overflow-hidden">
                        <template x-for="(alert, index) in alerts" x-bind:key="alert">
                            <p x-show="alertIndex === index" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="translate-y-3 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-3 opacity-0" class="absolute inset-0 truncate" x-text="alert"></p>
                        </template>
                    </div>
                    <div class="hidden shrink-0 items-center gap-4 text-leaf/75 dark:text-meadow/80 md:flex">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#checkout" class="transition hover:text-forest dark:hover:text-meadow" wire:navigate.hover>{{ __('home.nav.checkout') }}</a>
                        <a href="{{ $alternateUrl }}" class="transition hover:text-forest dark:hover:text-meadow" wire:navigate.hover>{{ strtoupper($alternateLocale) }}</a>
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 sm:px-8 lg:py-4">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 lg:grid lg:grid-cols-[220px_1fr_auto] lg:gap-4">
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="flex min-w-0 items-center gap-2 sm:gap-3" x-on:click="closeMobileMenu(); activeMenu = 'home'" wire:navigate.hover>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-forest text-xs font-black text-white dark:bg-meadow dark:text-ink sm:h-11 sm:w-11 sm:text-sm">DF</span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-extrabold uppercase tracking-[0.16em] text-cocoa dark:text-cream sm:text-base">Denetfils</span>
                            <span class="block truncate text-[11px] font-medium text-cocoa/60 dark:text-cream/60 sm:text-xs">{{ __('home.nav.promise') }}</span>
                        </span>
                    </a>

                    <livewire:shop.header-search
                        :locale="$currentLocale"
                        input-id="global-search"
                        form-class="hidden overflow-hidden rounded-full border border-leaf/20 bg-linen p-1 dark:border-white/10 dark:bg-white/5 lg:flex"
                        :on-catalog-page="request()->routeIs('home') || request()->routeIs('home.localized')"
                    />

                    <div class="flex shrink-0 items-center justify-end gap-2">
                        @persist('cart-manager-'.$currentLocale)
                            <livewire:shop.cart-manager :locale="$currentLocale" />
                        @endpersist

                        <a href="{{ $accountUrl }}" class="hidden min-h-[44px] items-center justify-center rounded-full border border-leaf/15 bg-white px-4 py-2.5 text-sm font-bold text-cocoa transition hover:border-leaf hover:text-leaf dark:border-white/10 dark:bg-white/5 dark:text-cream dark:hover:border-meadow dark:hover:text-meadow sm:inline-flex" wire:navigate.hover>
                            {{ __('home.account.nav') }}
                        </a>

                        <button type="button" data-testid="theme-toggle-button" data-theme-toggle class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-leaf/10 bg-white text-leaf transition hover:bg-mint dark:border-white/10 dark:bg-white/5 dark:text-meadow" aria-label="{{ __('home.theme.toggle') }}">
                            <svg data-theme-icon="light" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>
                            <svg data-theme-icon="dark" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.99 12.44A8.99 8.99 0 1 1 11.56 3a7 7 0 0 0 9.43 9.44Z"></path></svg>
                        </button>

                        <label
                            for="mobile-menu-state"
                            data-mobile-menu-toggle
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-leaf/10 bg-white text-cocoa transition hover:bg-mint hover:text-leaf dark:border-white/10 dark:bg-white/5 dark:text-cream dark:hover:bg-white/10 lg:hidden"
                            role="button"
                            tabindex="0"
                            aria-expanded="false"
                            aria-controls="mobile-menu"
                            aria-label="Menu"
                        >
                            <svg data-mobile-menu-icon="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h16"></path></svg>
                            <svg data-mobile-menu-icon="close" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                        </label>
                    </div>
                </div>
            </div>

            <nav class="hidden border-t border-leaf/10 bg-linen px-4 py-2 text-sm font-bold text-cocoa/75 dark:border-white/10 dark:bg-[#172414] dark:text-cream/75 sm:px-8 lg:block">
                <div class="mx-auto flex max-w-7xl items-center gap-2">
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" x-on:click="activeMenu = 'home'" x-bind:class="activeMenu === 'home' ? 'bg-white text-leaf shadow-sm dark:bg-white/10 dark:text-meadow' : 'hover:bg-white hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.home') }}</a>
                    <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" x-on:click="activeMenu = 'about'" x-bind:class="activeMenu === 'about' ? 'bg-white text-leaf shadow-sm dark:bg-white/10 dark:text-meadow' : 'hover:bg-white hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.about') }}</a>
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" x-on:click="activeMenu = 'products'" x-bind:class="activeMenu === 'products' ? 'bg-white text-leaf shadow-sm dark:bg-white/10 dark:text-meadow' : 'hover:bg-white hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.shop') }}</a>
                    <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" x-on:click="activeMenu = 'blog'" x-bind:class="activeMenu === 'blog' ? 'bg-white text-leaf shadow-sm dark:bg-white/10 dark:text-meadow' : 'hover:bg-white hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.nav.blog') }}</a>
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#checkout" class="min-h-[44px] rounded-full px-4 py-2.5 transition hover:bg-white hover:text-leaf dark:hover:bg-white/10" wire:navigate.hover>{{ __('home.nav.checkout') }}</a>
                    <a href="{{ $accountUrl }}" x-on:click="activeMenu = 'account'" x-bind:class="activeMenu === 'account' ? 'bg-white text-leaf shadow-sm dark:bg-white/10 dark:text-meadow' : 'hover:bg-white hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition" wire:navigate.hover>{{ __('home.account.nav') }}</a>
                </div>
            </nav>

            <div
                id="mobile-menu"
                data-mobile-menu
                class="mobile-menu-panel border-t border-leaf/10 bg-cream px-4 py-4 shadow-lg dark:border-white/10 dark:bg-ink lg:hidden"
            >
                <livewire:shop.header-search
                    :locale="$currentLocale"
                    input-id="mobile-search"
                    form-class="flex overflow-hidden rounded-full border border-leaf/20 bg-white p-1 dark:border-white/10 dark:bg-white/5"
                    :on-catalog-page="request()->routeIs('home') || request()->routeIs('home.localized')"
                />

                <div class="mt-4 grid gap-2">
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'home'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.nav.home') }}<span class="text-leaf">&rarr;</span></a>
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" x-on:click="closeMobileMenu(); activeMenu = 'products'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-terracotta px-4 py-3 font-bold text-white shadow-sm" wire:navigate.hover>{{ __('home.nav.shop') }}<span>&rarr;</span></a>
                    <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'about'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.nav.about') }}<span class="text-leaf">&rarr;</span></a>
                    <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'blog'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.nav.blog') }}<span class="text-leaf">&rarr;</span></a>
                    <a href="{{ $accountUrl }}" x-on:click="closeMobileMenu(); activeMenu = 'account'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.account.nav') }}<span class="text-leaf">&rarr;</span></a>
                    <a href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu()" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ __('home.footer.delivery') }}<span class="text-leaf">&rarr;</span></a>
                    <a href="{{ $alternateUrl }}" x-on:click="closeMobileMenu()" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream" wire:navigate.hover>{{ strtoupper($alternateLocale) }}<span class="text-leaf">&rarr;</span></a>
                </div>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        @if (request()->routeIs('home') || request()->routeIs('home.localized'))
            @include('partials.testimonials', ['currentLocale' => $currentLocale])
        @endif

        @if (request()->routeIs('products.show') && isset($product))
            @include('partials.product-reviews', ['product' => $product, 'currentLocale' => $currentLocale])
        @endif

        <footer class="border-t border-leaf/10 bg-cream px-4 pb-24 pt-12 text-sm text-cocoa dark:border-white/10 dark:bg-ink dark:text-cream sm:px-8 sm:pb-12 sm:pt-14 lg:pb-0">
            <div class="mx-auto grid max-w-7xl gap-8 pb-10 sm:grid-cols-2 lg:grid-cols-[1.25fr_0.8fr_0.95fr_1.1fr] lg:gap-10">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-forest text-sm font-black text-white dark:bg-meadow dark:text-ink">DF</span>
                        <div class="min-w-0">
                            <p class="text-lg font-extrabold uppercase tracking-[0.18em] text-cocoa dark:text-cream">DEN & FILS</p>
                            <p class="text-cocoa/60 dark:text-cream/60">{{ __('home.nav.promise') }}</p>
                        </div>
                    </div>
                    <p class="mt-5 max-w-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.footer.line') }}</p>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ __('home.contact.vat') }}</p>
                </div>

                <div>
                    <h3 class="text-base font-extrabold uppercase tracking-wide text-cocoa dark:text-cream">{{ __('home.footer.products_title') }}</h3>
                    <ul class="mt-4 space-y-1 text-cocoa/70 dark:text-cream/70">
                        <li><a class="block rounded-lg py-2 transition hover:text-leaf dark:hover:text-meadow" href="{{ route('home.localized', ['locale' => $currentLocale]) }}#offers">{{ __('home.footer.promotions') }}</a></li>
                        <li><a class="block rounded-lg py-2 transition hover:text-leaf dark:hover:text-meadow" href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products">{{ __('home.footer.new_products') }}</a></li>
                        <li><a class="block rounded-lg py-2 transition hover:text-leaf dark:hover:text-meadow" href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products">{{ __('home.footer.best_sellers') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-base font-extrabold uppercase tracking-wide text-cocoa dark:text-cream">{{ __('home.footer.useful_links_title') }}</h3>
                    <ul class="mt-4 space-y-1 text-cocoa/70 dark:text-cream/70">
                        <li><a class="block rounded-lg py-2 transition hover:text-leaf dark:hover:text-meadow" href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}">{{ __('home.footer.delivery') }}</a></li>
                        <li><a class="block rounded-lg py-2 transition hover:text-leaf dark:hover:text-meadow" href="{{ route('pages.legal', ['locale' => $currentLocale]) }}">{{ __('home.footer.legal') }}</a></li>
                        <li><a class="block rounded-lg py-2 transition hover:text-leaf dark:hover:text-meadow" href="{{ route('pages.terms', ['locale' => $currentLocale]) }}">{{ __('home.footer.terms') }}</a></li>
                        <li><a class="block rounded-lg py-2 transition hover:text-leaf dark:hover:text-meadow" href="{{ route('pages.about', ['locale' => $currentLocale]) }}">{{ __('home.nav.about') }}</a></li>
                        <li><a class="block rounded-lg py-2 transition hover:text-leaf dark:hover:text-meadow" href="{{ route('pages.payment', ['locale' => $currentLocale]) }}">{{ __('home.footer.secure_payment') }}</a></li>
                        <li><a class="block rounded-lg py-2 transition hover:text-leaf dark:hover:text-meadow" href="{{ $accountUrl }}">{{ __('home.account.nav') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-base font-extrabold uppercase tracking-wide text-cocoa dark:text-cream">{{ __('home.footer.information_title') }}</h3>
                    <div class="mt-5 space-y-3 text-cocoa/70 dark:text-cream/70">
                        <p class="font-semibold text-cocoa dark:text-cream">{{ __('home.contact.company') }}</p>
                        <p>{{ __('home.contact.address') }}</p>
                        <p><a class="transition hover:text-leaf dark:hover:text-meadow" href="tel:+33695737390">{{ __('home.contact.phone') }}</a></p>
                        <p><a class="break-all transition hover:text-leaf dark:hover:text-meadow" href="mailto:{{ __('home.contact.email') }}">{{ __('home.contact.email') }}</a></p>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a class="min-h-[40px] rounded-full border border-leaf/20 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wide text-cocoa/80 transition hover:border-leaf hover:text-leaf dark:border-white/15 dark:bg-white/5 dark:text-cream/80 dark:hover:border-meadow dark:hover:text-meadow" href="https://www.facebook.com/denetfils" target="_blank" rel="noopener">Facebook</a>
                        <a class="min-h-[40px] rounded-full border border-leaf/20 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wide text-cocoa/80 transition hover:border-leaf hover:text-leaf dark:border-white/15 dark:bg-white/5 dark:text-cream/80 dark:hover:border-meadow dark:hover:text-meadow" href="https://www.instagram.com/denetfils" target="_blank" rel="noopener">Instagram</a>
                        <a class="min-h-[40px] rounded-full border border-leaf/20 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wide text-cocoa/80 transition hover:border-leaf hover:text-leaf dark:border-white/15 dark:bg-white/5 dark:text-cream/80 dark:hover:border-meadow dark:hover:text-meadow" href="https://www.tiktok.com/@denetfils" target="_blank" rel="noopener">TikTok</a>
                    </div>
                </div>
            </div>

            <div class="mx-auto flex max-w-7xl flex-col gap-2 border-t border-leaf/10 py-5 text-xs text-cocoa/55 dark:border-white/10 dark:text-cream/55 sm:flex-row sm:items-center sm:justify-between">
                <p>Copyright &copy; 2025 denetfils.fr. All rights reserved.</p>
                <p><a class="transition hover:text-leaf dark:hover:text-meadow" href="mailto:{{ __('home.contact.email') }}">{{ __('home.contact.email') }}</a></p>
            </div>
        </footer>

        </div>

        @livewireScriptConfig
    </body>
</html>
