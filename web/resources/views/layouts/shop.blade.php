@php
    $currentLocale = $locale ?? app()->getLocale();
    $alternateLocale = $currentLocale === 'fr' ? 'en' : 'fr';
    $alternateUrl = route('home.localized', ['locale' => $alternateLocale]);

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
            const storedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (storedTheme === 'dark' || (!storedTheme && systemPrefersDark)) {
                document.documentElement.classList.add('dark');
            }
        </script>
        <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
        <link rel="dns-prefetch" href="https://images.unsplash.com">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body
        class="theme-page surface-transition min-h-screen bg-cream text-cocoa dark:bg-ink dark:text-cream"
        x-data="shopApp({
            apiBaseUrl: @js(config('services.denetfils_api.base_url')),
            locale: @js($currentLocale),
            activeMenu: @js($activeMenu ?? 'home'),
            labels: {
                apiError: @js(__('home.cart.api_error')),
                cartExpired: @js(__('home.cart.expired')),
                emptyTotal: @js(__('home.cart.empty_total'))
            }
        })"
        x-init="init()"
    >
        <header class="sticky top-0 z-40 border-b border-leaf/10 bg-white/95 shadow-sm backdrop-blur dark:border-white/10 dark:bg-ink/95">
            <div class="border-b border-leaf/10 bg-cream px-4 py-2 text-xs font-semibold text-leaf dark:border-white/10 dark:bg-[#172414] dark:text-meadow sm:px-8" x-data="{ alerts: @js(trans('home.announcements')) }" x-init="setInterval(() => alertIndex = (alertIndex + 1) % alerts.length, 4200)">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                    <div class="relative h-5 min-w-0 flex-1 overflow-hidden">
                        <template x-for="(alert, index) in alerts" x-bind:key="alert">
                            <p x-show="alertIndex === index" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="translate-y-3 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-3 opacity-0" class="absolute inset-0 truncate" x-text="alert"></p>
                        </template>
                    </div>
                    <div class="hidden shrink-0 items-center gap-4 text-leaf/75 dark:text-meadow/80 md:flex">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#checkout" class="transition hover:text-forest dark:hover:text-meadow">{{ __('home.nav.checkout') }}</a>
                        <a href="{{ $alternateUrl }}" class="transition hover:text-forest dark:hover:text-meadow">{{ strtoupper($alternateLocale) }}</a>
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 sm:px-8 lg:py-4">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 lg:grid lg:grid-cols-[220px_1fr_auto] lg:gap-4">
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="flex min-w-0 items-center gap-2 sm:gap-3" x-on:click="closeMobileMenu(); activeMenu = 'home'">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-forest text-xs font-black text-white dark:bg-meadow dark:text-ink sm:h-11 sm:w-11 sm:text-sm">DF</span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-extrabold uppercase tracking-[0.16em] text-cocoa dark:text-cream sm:text-base">Denetfils</span>
                            <span class="block truncate text-[11px] font-medium text-cocoa/60 dark:text-cream/60 sm:text-xs">{{ __('home.nav.promise') }}</span>
                        </span>
                    </a>

                    <form action="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" method="GET" class="hidden overflow-hidden rounded-full border border-leaf/20 bg-linen p-1 dark:border-white/10 dark:bg-white/5 lg:flex">
                        <label class="sr-only" for="global-search">{{ __('home.filters.search') }}</label>
                        <input id="global-search" name="q" placeholder="{{ __('home.filters.search_placeholder') }}" class="min-w-0 flex-1 bg-transparent px-5 py-3 text-sm text-cocoa outline-none placeholder:text-cocoa/40 dark:text-cream dark:placeholder:text-cream/40">
                        <button type="submit" class="min-h-[44px] rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay">{{ __('home.filters.search') }}</button>
                    </form>

                    <div class="flex shrink-0 items-center justify-end gap-2">
                        <button type="button" class="hidden min-h-[44px] items-center justify-center rounded-full bg-terracotta px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-clay sm:inline-flex" x-on:click="openCart()">
                            {{ __('home.cart.title') }}
                            <span class="ml-1 rounded-full bg-white px-2 py-0.5 text-xs text-leaf" x-text="itemCount"></span>
                        </button>

                        <button type="button" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-leaf/10 bg-white text-leaf transition hover:bg-mint dark:border-white/10 dark:bg-white/5 dark:text-meadow" x-on:click="toggleTheme()" aria-label="{{ __('home.theme.toggle') }}">
                            <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>
                            <svg x-cloak x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.99 12.44A8.99 8.99 0 1 1 11.56 3a7 7 0 0 0 9.43 9.44Z"></path></svg>
                        </button>

                        <button
                            type="button"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-leaf/10 bg-white text-cocoa transition hover:bg-mint hover:text-leaf dark:border-white/10 dark:bg-white/5 dark:text-cream dark:hover:bg-white/10 lg:hidden"
                            x-on:click="toggleMobileMenu()"
                            x-bind:aria-expanded="mobileMenuOpen.toString()"
                            aria-controls="mobile-menu"
                            aria-label="Menu"
                        >
                            <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h16"></path></svg>
                            <svg x-cloak x-show="mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <nav class="hidden border-t border-leaf/10 bg-linen px-4 py-2 text-sm font-bold text-cocoa/75 dark:border-white/10 dark:bg-[#172414] dark:text-cream/75 sm:px-8 lg:block">
                <div class="mx-auto flex max-w-7xl items-center gap-2">
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" x-on:click="activeMenu = 'home'" x-bind:class="activeMenu === 'home' ? 'bg-white text-leaf shadow-sm dark:bg-white/10 dark:text-meadow' : 'hover:bg-white hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition">{{ __('home.nav.home') }}</a>
                    <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" x-on:click="activeMenu = 'about'" x-bind:class="activeMenu === 'about' ? 'bg-white text-leaf shadow-sm dark:bg-white/10 dark:text-meadow' : 'hover:bg-white hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition">{{ __('home.nav.about') }}</a>
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" x-on:click="activeMenu = 'products'" x-bind:class="activeMenu === 'products' ? 'bg-white text-leaf shadow-sm dark:bg-white/10 dark:text-meadow' : 'hover:bg-white hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition">{{ __('home.nav.shop') }}</a>
                    <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" x-on:click="activeMenu = 'blog'" x-bind:class="activeMenu === 'blog' ? 'bg-white text-leaf shadow-sm dark:bg-white/10 dark:text-meadow' : 'hover:bg-white hover:text-leaf dark:hover:bg-white/10'" class="min-h-[44px] rounded-full px-4 py-2.5 transition">{{ __('home.nav.blog') }}</a>
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#checkout" class="min-h-[44px] rounded-full px-4 py-2.5 transition hover:bg-white hover:text-leaf dark:hover:bg-white/10">{{ __('home.nav.checkout') }}</a>
                </div>
            </nav>

            <div
                id="mobile-menu"
                x-cloak
                x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-y-2 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="-translate-y-2 opacity-0"
                class="border-t border-leaf/10 bg-cream px-4 py-4 shadow-lg dark:border-white/10 dark:bg-ink lg:hidden"
            >
                <form action="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" method="GET" class="flex overflow-hidden rounded-full border border-leaf/20 bg-white p-1 dark:border-white/10 dark:bg-white/5" x-on:submit="closeMobileMenu()">
                    <label class="sr-only" for="mobile-search">{{ __('home.filters.search') }}</label>
                    <input id="mobile-search" name="q" placeholder="{{ __('home.filters.search_placeholder') }}" class="min-w-0 flex-1 bg-transparent px-4 py-2.5 text-sm text-cocoa outline-none placeholder:text-cocoa/40 dark:text-cream dark:placeholder:text-cream/40">
                    <button type="submit" class="min-h-[44px] rounded-full bg-terracotta px-4 py-2 text-xs font-bold uppercase tracking-wide text-white">{{ __('home.filters.search') }}</button>
                </form>

                <div class="mt-4 grid gap-2">
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'home'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream">{{ __('home.nav.home') }}<span class="text-leaf">→</span></a>
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" x-on:click="closeMobileMenu(); activeMenu = 'products'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-terracotta px-4 py-3 font-bold text-white shadow-sm">{{ __('home.nav.shop') }}<span>→</span></a>
                    <a href="{{ route('pages.about', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'about'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream">{{ __('home.nav.about') }}<span class="text-leaf">→</span></a>
                    <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu(); activeMenu = 'blog'" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream">{{ __('home.nav.blog') }}<span class="text-leaf">→</span></a>
                    <a href="{{ route('pages.delivery', ['locale' => $currentLocale]) }}" x-on:click="closeMobileMenu()" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream">{{ __('home.footer.delivery') }}<span class="text-leaf">→</span></a>
                    <a href="{{ $alternateUrl }}" x-on:click="closeMobileMenu()" class="flex min-h-[46px] items-center justify-between rounded-2xl bg-white px-4 py-3 font-bold text-cocoa shadow-sm dark:bg-white/5 dark:text-cream">{{ strtoupper($alternateLocale) }}<span class="text-leaf">→</span></a>
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

        <div x-cloak x-show="cartOpen" class="fixed inset-0 z-50">
            <button type="button" class="absolute inset-0 bg-black/45 backdrop-blur-sm" aria-label="{{ __('home.cart.close') }}" x-on:click="cartOpen = false"></button>
            <aside class="absolute right-0 top-0 flex h-full w-full max-w-md flex-col border-l border-leaf/10 bg-cream shadow-2xl dark:border-white/10 dark:bg-ink sm:w-[28rem]">
                <div class="flex items-center justify-between border-b border-leaf/10 bg-cream px-4 py-4 dark:border-white/10 dark:bg-ink sm:px-5">
                    <div class="min-w-0">
                        <h2 class="theme-title text-lg font-semibold text-cocoa dark:text-cream">{{ __('home.cart.title') }}</h2>
                        <p class="theme-muted text-sm text-cocoa/60 dark:text-cream/60">{{ __('home.cart.subtitle') }}</p>
                    </div>
                    <button type="button" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-cocoa transition hover:bg-mint hover:text-leaf dark:text-cream dark:hover:bg-white/10" x-on:click="cartOpen = false"><span class="sr-only">{{ __('home.cart.close') }}</span><span aria-hidden="true" class="text-2xl leading-none">&times;</span></button>
                </div>

                <div class="flex-1 overflow-y-auto bg-linen px-4 py-5 dark:bg-[#172414] sm:px-5">
                    <div x-show="cartLoading" class="theme-muted text-sm text-cocoa/70 dark:text-cream/70">{{ __('home.cart.loading') }}</div>
                    <div x-show="cartError" class="mb-4 rounded-lg border border-leaf/20 bg-mint px-4 py-3 text-sm text-leaf dark:bg-white/5"><span x-text="cartError"></span></div>
                    <div x-show="!cartLoading && cartItems.length === 0" class="rounded-lg border border-leaf/10 bg-white p-5 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70">{{ __('home.cart.empty') }}</div>
                    <div class="space-y-4">
                        <template x-for="item in cartItems" x-bind:key="item.id">
                            <article class="grid grid-cols-[72px_1fr] gap-3 rounded-lg border border-leaf/10 bg-white p-3 dark:border-white/10 dark:bg-white/5 sm:gap-4">
                                <img class="h-[72px] w-[72px] rounded-md object-cover" x-bind:src="item.product?.image?.url" x-bind:alt="item.product?.image?.alt_text || item.product?.name" loading="lazy" decoding="async" width="72" height="72">
                                <div class="min-w-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="theme-title truncate text-sm font-semibold text-cocoa dark:text-cream" x-text="item.product?.name"></h3>
                                            <p class="theme-muted mt-1 truncate text-xs text-cocoa/60 dark:text-cream/60" x-text="item.variant?.name || item.product?.origin"></p>
                                        </div>
                                        <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-lg leading-none text-cocoa/60 transition hover:bg-mint hover:text-leaf dark:text-cream/60 dark:hover:bg-white/10" x-on:click="removeCartItem(item.id)" x-bind:disabled="cartMutating" aria-label="{{ __('home.cart.remove') }}">&times;</button>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex min-h-[40px] items-center rounded-full border border-leaf/20 bg-mint/50 dark:border-white/20 dark:bg-white/5">
                                            <button type="button" class="px-3 py-2 text-sm" x-on:click="updateCartItem(item.id, item.quantity - 1)" x-bind:disabled="item.quantity <= 1 || cartMutating">&minus;</button>
                                            <input class="w-12 bg-transparent text-center text-sm outline-none" type="number" min="1" x-bind:value="item.quantity" x-on:change="updateCartItem(item.id, $event.target.value)">
                                            <button type="button" class="px-3 py-2 text-sm" x-on:click="updateCartItem(item.id, item.quantity + 1)" x-bind:disabled="cartMutating">+</button>
                                        </div>
                                        <span class="theme-title text-sm font-semibold text-leaf dark:text-cream" x-text="item.formatted_line_total"></span>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>

                <div class="safe-bottom border-t border-leaf/10 bg-cream p-4 dark:border-white/10 dark:bg-ink sm:p-5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="theme-muted text-cocoa/70 dark:text-cream/70">{{ __('home.cart.total') }}</span>
                        <strong class="theme-title text-lg text-leaf dark:text-cream" x-text="formattedTotal"></strong>
                    </div>
                    <button type="button" class="mt-4 w-full rounded-full bg-terracotta px-5 py-3 text-sm font-semibold text-white transition hover:bg-clay disabled:cursor-not-allowed disabled:opacity-60" disabled>{{ __('home.cart.checkout_later') }}</button>
                </div>
            </aside>
        </div>

        <div x-cloak x-show="!cartOpen" class="fixed inset-x-0 bottom-0 z-30 border-t border-leaf/10 bg-white/95 px-4 py-3 shadow-[0_-12px_30px_rgba(0,0,0,0.08)] backdrop-blur dark:border-white/10 dark:bg-ink/95 lg:hidden">
            <div class="mx-auto grid max-w-md grid-cols-2 gap-3">
                <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" class="inline-flex min-h-[46px] items-center justify-center rounded-full bg-terracotta px-4 py-3 text-sm font-bold uppercase tracking-wide text-white" x-on:click="closeMobileMenu()">{{ __('home.nav.shop') }}</a>
                <button type="button" class="inline-flex min-h-[46px] items-center justify-center rounded-full border border-leaf/20 bg-mint px-4 py-3 text-sm font-bold uppercase tracking-wide text-leaf dark:border-white/10 dark:bg-white/5 dark:text-cream" x-on:click="openCart()">
                    {{ __('home.cart.title') }}
                    <span class="ml-2 rounded-full bg-white px-2 py-0.5 text-xs text-leaf dark:bg-cream" x-text="itemCount"></span>
                </button>
            </div>
        </div>

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
                <p>Copyright © 2025 denetfils.fr. All rights reserved.</p>
                <p><a class="transition hover:text-leaf dark:hover:text-meadow" href="mailto:{{ __('home.contact.email') }}">{{ __('home.contact.email') }}</a></p>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
