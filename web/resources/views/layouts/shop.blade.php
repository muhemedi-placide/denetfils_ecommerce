@php
    $currentLocale = $locale ?? app()->getLocale();
    $alternateLocale = $currentLocale === 'fr' ? 'en' : 'fr';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', __('home.meta.title'))</title>
        <meta name="description" content="@yield('description', __('home.meta.description'))">
        <script>
            const storedTheme = localStorage.getItem('theme');

            if (storedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        </script>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body
        class="theme-page surface-transition min-h-screen bg-cream text-cocoa dark:bg-ink dark:text-cream"
        x-data="shopApp({
            apiBaseUrl: @js(config('services.denetfils_api.base_url')),
            locale: @js($currentLocale),
            labels: {
                apiError: @js(__('home.cart.api_error')),
                cartExpired: @js(__('home.cart.expired')),
                emptyTotal: @js(__('home.cart.empty_total'))
            }
        })"
        x-init="init()"
    >
        <header class="sticky top-0 z-40 border-b border-leaf/10 bg-white/95 shadow-sm backdrop-blur dark:border-white/10 dark:bg-ink/95">
            <div class="bg-forest px-5 py-2 text-xs font-semibold text-white dark:bg-[#172414] sm:px-8">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                    <p class="truncate">{{ __('home.announcement') }}</p>
                    <div class="hidden items-center gap-4 text-white/80 md:flex">
                        <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#checkout" class="hover:text-white">{{ __('home.nav.checkout') }}</a>
                        <a href="{{ route('home.localized', ['locale' => $alternateLocale]) }}" class="hover:text-white">{{ strtoupper($alternateLocale) }}</a>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 sm:px-8">
                <div class="mx-auto grid max-w-7xl items-center gap-4 lg:grid-cols-[220px_1fr_auto]">
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-forest text-sm font-black text-white dark:bg-meadow dark:text-ink">DF</span>
                        <span>
                            <span class="block text-base font-extrabold uppercase tracking-[0.18em] text-cocoa dark:text-cream">Denetfils</span>
                            <span class="text-xs font-medium text-cocoa/60 dark:text-cream/60">{{ __('home.nav.promise') }}</span>
                        </span>
                    </a>

                    <form action="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" method="GET" class="hidden overflow-hidden rounded-full border border-leaf/15 bg-linen p-1 dark:border-white/10 dark:bg-white/5 md:flex">
                        <label class="sr-only" for="global-search">{{ __('home.filters.search') }}</label>
                        <input id="global-search" name="q" placeholder="{{ __('home.filters.search_placeholder') }}" class="min-w-0 flex-1 bg-transparent px-5 py-3 text-sm text-cocoa outline-none placeholder:text-cocoa/45 dark:text-cream dark:placeholder:text-cream/45">
                        <button type="submit" class="rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay">
                            {{ __('home.filters.search') }}
                        </button>
                    </form>

                    <div class="flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-full bg-terracotta px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-clay"
                            x-on:click="loadCart(true)"
                        >
                            {{ __('home.cart.title') }}
                            <span class="ml-1 rounded-full bg-white px-2 py-0.5 text-xs text-leaf" x-text="itemCount"></span>
                        </button>

                        <div class="hidden rounded-full border border-leaf/10 bg-white p-1 text-xs font-bold dark:border-white/10 dark:bg-white/5 sm:flex" aria-label="{{ __('home.theme.label') }}">
                            <button
                                type="button"
                                class="rounded-full px-2.5 py-1.5 transition"
                                x-bind:class="theme === 'light' ? 'bg-forest text-white dark:bg-meadow dark:text-ink' : 'text-cocoa/60 dark:text-cream/60'"
                                x-on:click="setTheme('light')"
                            >
                                {{ __('home.theme.light') }}
                            </button>
                            <button
                                type="button"
                                class="rounded-full px-2.5 py-1.5 transition"
                                x-bind:class="theme === 'dark' ? 'bg-forest text-white dark:bg-meadow dark:text-ink' : 'text-cocoa/60 dark:text-cream/60'"
                                x-on:click="setTheme('dark')"
                            >
                                {{ __('home.theme.dark') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="hidden border-t border-leaf/10 bg-linen px-5 py-2 text-sm font-bold text-cocoa/75 dark:border-white/10 dark:bg-[#172414] dark:text-cream/75 sm:px-8 lg:block">
                <div class="mx-auto flex max-w-7xl items-center gap-6 overflow-x-auto">
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#categories" class="whitespace-nowrap hover:text-leaf">{{ __('home.categories.eyebrow') }}</a>
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#offers" class="whitespace-nowrap hover:text-leaf">{{ __('home.offers.main_eyebrow') }}</a>
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#products" class="whitespace-nowrap hover:text-leaf">{{ __('home.products.eyebrow') }}</a>
                    <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}#checkout" class="whitespace-nowrap hover:text-leaf">{{ __('home.nav.checkout') }}</a>
                </div>
            </nav>
        </header>

        <main>
            @yield('content')
        </main>

        <div x-cloak x-show="cartOpen" class="fixed inset-0 z-50">
            <button
                type="button"
                class="absolute inset-0 bg-black/45 backdrop-blur-sm"
                aria-label="{{ __('home.cart.close') }}"
                x-on:click="cartOpen = false"
            ></button>
            <aside class="absolute right-0 top-0 flex h-full w-full max-w-md flex-col border-l border-leaf/10 bg-cream shadow-2xl dark:border-white/10 dark:bg-ink">
                <div class="flex items-center justify-between border-b border-leaf/10 bg-cream px-5 py-4 dark:border-white/10 dark:bg-ink">
                    <div>
                        <h2 class="theme-title text-lg font-semibold text-cocoa dark:text-cream">{{ __('home.cart.title') }}</h2>
                        <p class="theme-muted text-sm text-cocoa/65 dark:text-cream/65">{{ __('home.cart.subtitle') }}</p>
                    </div>
                    <button type="button" class="rounded-full p-2 text-cocoa transition hover:bg-mint hover:text-leaf dark:text-cream dark:hover:bg-white/10" x-on:click="cartOpen = false">
                        <span class="sr-only">{{ __('home.cart.close') }}</span>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto bg-linen px-5 py-5 dark:bg-[#172414]">
                    <div x-show="cartLoading" class="theme-muted text-sm text-cocoa/70 dark:text-cream/70">
                        {{ __('home.cart.loading') }}
                    </div>

                    <div x-show="cartError" class="mb-4 rounded-lg border border-leaf/25 bg-mint px-4 py-3 text-sm text-leaf dark:bg-white/5">
                        <span x-text="cartError"></span>
                    </div>

                    <div x-show="!cartLoading && cartItems.length === 0" class="rounded-lg border border-leaf/10 bg-white p-5 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70">
                        {{ __('home.cart.empty') }}
                    </div>

                    <div class="space-y-4">
                        <template x-for="item in cartItems" x-bind:key="item.id">
                            <article class="grid grid-cols-[72px_1fr] gap-4 rounded-lg border border-leaf/10 bg-white p-3 dark:border-white/10 dark:bg-white/5">
                                <img
                                    class="h-[72px] w-[72px] rounded-md object-cover"
                                    x-bind:src="item.product?.image?.url"
                                    x-bind:alt="item.product?.image?.alt_text || item.product?.name"
                                >
                                <div>
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="theme-title text-sm font-semibold text-cocoa dark:text-cream" x-text="item.product?.name"></h3>
                                            <p class="theme-muted mt-1 text-xs text-cocoa/60 dark:text-cream/60" x-text="item.variant?.name || item.product?.origin"></p>
                                        </div>
                                        <button
                                            type="button"
                                            class="rounded-full px-2 text-lg leading-none text-cocoa/60 transition hover:bg-mint hover:text-leaf dark:text-cream/60 dark:hover:bg-white/10"
                                            x-on:click="removeCartItem(item.id)"
                                            x-bind:disabled="cartMutating"
                                            aria-label="{{ __('home.cart.remove') }}"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        <div class="flex items-center rounded-full border border-leaf/15 bg-mint/50 dark:border-white/15 dark:bg-white/5">
                                            <button type="button" class="px-3 py-1 text-sm" x-on:click="updateCartItem(item.id, item.quantity - 1)" x-bind:disabled="item.quantity <= 1 || cartMutating">&minus;</button>
                                            <input
                                                class="w-12 bg-transparent text-center text-sm outline-none"
                                                type="number"
                                                min="1"
                                                x-bind:value="item.quantity"
                                                x-on:change="updateCartItem(item.id, $event.target.value)"
                                            >
                                            <button type="button" class="px-3 py-1 text-sm" x-on:click="updateCartItem(item.id, item.quantity + 1)" x-bind:disabled="cartMutating">+</button>
                                        </div>
                                        <span class="theme-title text-sm font-semibold text-leaf dark:text-cream" x-text="item.formatted_line_total"></span>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>

                <div class="border-t border-leaf/10 bg-cream p-5 dark:border-white/10 dark:bg-ink">
                    <div class="flex items-center justify-between text-sm">
                        <span class="theme-muted text-cocoa/70 dark:text-cream/70">{{ __('home.cart.total') }}</span>
                        <strong class="theme-title text-lg text-leaf dark:text-cream" x-text="formattedTotal"></strong>
                    </div>
                    <button
                        type="button"
                        class="mt-4 w-full rounded-full bg-terracotta px-5 py-3 text-sm font-semibold text-white transition hover:bg-clay disabled:cursor-not-allowed disabled:opacity-60"
                        disabled
                    >
                        {{ __('home.cart.checkout_later') }}
                    </button>
                </div>
            </aside>
        </div>

        <footer class="theme-band-soft border-t border-leaf/10 bg-white px-5 py-10 text-sm text-cocoa/65 dark:border-white/10 dark:bg-ink dark:text-cream/65 sm:px-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="font-semibold text-leaf dark:text-cream">Denetfils</p>
                <p>{{ __('home.footer.line') }}</p>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
