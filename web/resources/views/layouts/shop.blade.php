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
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body
        class="theme-page surface-transition min-h-screen bg-linen text-cocoa dark:bg-ink dark:text-cream"
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
        <header class="absolute inset-x-0 top-0 z-30">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
                <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="text-lg font-semibold tracking-wide text-white">
                    Denetfils
                </a>
                <div class="flex items-center gap-2 rounded-full bg-white/12 p-1 text-sm text-white ring-1 ring-white/20 backdrop-blur">
                    <a
                        href="{{ route('home.localized', ['locale' => $alternateLocale]) }}"
                        class="rounded-full px-3 py-2 font-medium transition hover:bg-white/15"
                        aria-label="{{ __('home.locale.switch_to', ['language' => __('home.locale.' . $alternateLocale)]) }}"
                    >
                        {{ strtoupper($alternateLocale) }}
                    </a>
                    <button
                        type="button"
                        class="rounded-full px-3 py-2 font-medium transition hover:bg-white/15"
                        x-on:click="loadCart(true)"
                    >
                        {{ __('home.cart.title') }}
                        <span class="ml-1 rounded-full bg-white px-2 py-0.5 text-xs text-cocoa" x-text="itemCount"></span>
                    </button>
                    <div class="flex rounded-full bg-black/12 p-0.5" aria-label="{{ __('home.theme.label') }}">
                        <button
                            type="button"
                            class="rounded-full px-3 py-1.5 font-medium transition"
                            x-bind:class="theme === 'light' ? 'bg-white text-cocoa' : 'text-white hover:bg-white/15'"
                            x-on:click="setTheme('light')"
                        >
                            {{ __('home.theme.light') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full px-3 py-1.5 font-medium transition"
                            x-bind:class="theme === 'dark' ? 'bg-white text-cocoa' : 'text-white hover:bg-white/15'"
                            x-on:click="setTheme('dark')"
                        >
                            {{ __('home.theme.dark') }}
                        </button>
                    </div>
                </div>
            </nav>
        </header>

        <main>
            @yield('content')
        </main>

        <div x-cloak x-show="cartOpen" class="fixed inset-0 z-40">
            <button
                type="button"
                class="absolute inset-0 bg-black/45"
                aria-label="{{ __('home.cart.close') }}"
                x-on:click="cartOpen = false"
            ></button>
            <aside class="theme-card absolute right-0 top-0 flex h-full w-full max-w-md flex-col border-l border-cocoa/10 bg-linen shadow-2xl dark:border-white/10 dark:bg-ink">
                <div class="flex items-center justify-between border-b border-cocoa/10 px-5 py-4 dark:border-white/10">
                    <div>
                        <h2 class="theme-title text-lg font-semibold text-cocoa dark:text-cream">{{ __('home.cart.title') }}</h2>
                        <p class="theme-muted text-sm text-cocoa/65 dark:text-cream/65">{{ __('home.cart.subtitle') }}</p>
                    </div>
                    <button type="button" class="rounded-full p-2 text-cocoa transition hover:bg-cocoa/10 dark:text-cream dark:hover:bg-white/10" x-on:click="cartOpen = false">
                        <span class="sr-only">{{ __('home.cart.close') }}</span>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-5">
                    <div x-show="cartLoading" class="theme-muted text-sm text-cocoa/70 dark:text-cream/70">
                        {{ __('home.cart.loading') }}
                    </div>

                    <div x-show="cartError" class="mb-4 rounded-lg border border-terracotta/30 bg-terracotta/10 px-4 py-3 text-sm text-terracotta">
                        <span x-text="cartError"></span>
                    </div>

                    <div x-show="!cartLoading && cartItems.length === 0" class="theme-card rounded-lg border border-cocoa/10 bg-white p-5 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70">
                        {{ __('home.cart.empty') }}
                    </div>

                    <div class="space-y-4">
                        <template x-for="item in cartItems" x-bind:key="item.id">
                            <article class="theme-card grid grid-cols-[72px_1fr] gap-4 rounded-lg border border-cocoa/10 bg-white p-3 dark:border-white/10 dark:bg-white/5">
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
                                            class="rounded-full px-2 text-lg leading-none text-cocoa/60 transition hover:bg-cocoa/10 hover:text-terracotta dark:text-cream/60 dark:hover:bg-white/10"
                                            x-on:click="removeCartItem(item.id)"
                                            x-bind:disabled="cartMutating"
                                            aria-label="{{ __('home.cart.remove') }}"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        <div class="flex items-center rounded-full border border-cocoa/15 dark:border-white/15">
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
                                        <span class="theme-title text-sm font-semibold text-cocoa dark:text-cream" x-text="item.formatted_line_total"></span>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>

                <div class="border-t border-cocoa/10 p-5 dark:border-white/10">
                    <div class="flex items-center justify-between text-sm">
                        <span class="theme-muted text-cocoa/70 dark:text-cream/70">{{ __('home.cart.total') }}</span>
                        <strong class="theme-title text-lg text-cocoa dark:text-cream" x-text="formattedTotal"></strong>
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

        @livewireScripts
    </body>
</html>
