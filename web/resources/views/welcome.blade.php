@extends('layouts.shop')

@section('title', __('home.meta.title'))
@section('description', __('home.meta.description'))

@section('content')
    <section class="relative isolate min-h-[88vh] overflow-hidden">
        <img
            class="absolute inset-0 -z-20 h-full w-full object-cover"
            src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1800&q=85"
            alt="{{ __('home.hero.image_alt') }}"
        >
        <div class="absolute inset-0 -z-10 bg-cocoa/62 dark:bg-black/70"></div>
        <div class="mx-auto flex min-h-[88vh] max-w-7xl items-end px-5 pb-16 pt-28 sm:px-8 lg:pb-20">
            <div class="max-w-3xl text-white">
                <p class="text-sm font-semibold uppercase tracking-wide text-cream/85">
                    {{ __('home.hero.eyebrow') }}
                </p>
                <h1 class="mt-4 max-w-2xl text-4xl font-semibold leading-tight sm:text-6xl">
                    {{ __('home.hero.title') }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-cream/88 sm:text-lg">
                    {{ __('home.hero.body') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#products" class="rounded-full bg-terracotta px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-black/20 transition hover:bg-clay">
                        {{ __('home.hero.primary_cta') }}
                    </a>
                    <button type="button" x-on:click="loadCart(true)" class="rounded-full bg-white/12 px-5 py-3 text-sm font-semibold text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white/18">
                        {{ __('home.hero.secondary_cta') }}
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="theme-band surface-transition bg-linen px-5 py-8 dark:bg-ink sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-4 md:grid-cols-3">
            @foreach (trans('home.stats') as $stat)
                <div class="theme-card rounded-lg border border-cocoa/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <p class="theme-title text-2xl font-semibold text-terracotta dark:text-cream">{{ $stat['value'] }}</p>
                    <p class="theme-muted mt-2 text-sm leading-6 text-cocoa/70 dark:text-cream/70">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="products" class="theme-band-soft surface-transition bg-white px-5 py-16 dark:bg-[#211914] sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <p class="theme-subtle text-sm font-semibold uppercase tracking-wide text-sage dark:text-cream/70">
                        {{ __('home.products.eyebrow') }}
                    </p>
                    <h2 class="theme-title mt-3 text-3xl font-semibold text-cocoa dark:text-cream">
                        {{ __('home.products.title') }}
                    </h2>
                </div>
                <p class="theme-muted max-w-xl text-sm leading-7 text-cocoa/70 dark:text-cream/70">
                    {{ __('home.products.body') }}
                </p>
            </div>

            <form
                method="GET"
                action="{{ route('home.localized', ['locale' => $locale]) }}"
                class="theme-card mt-8 grid gap-3 rounded-lg border border-cocoa/10 bg-linen p-4 dark:border-white/10 dark:bg-white/5 md:grid-cols-[1fr_220px_180px_auto]"
            >
                <label class="sr-only" for="q">{{ __('home.filters.search') }}</label>
                <input
                    id="q"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="{{ __('home.filters.search_placeholder') }}"
                    class="rounded-lg border border-cocoa/15 bg-white px-4 py-3 text-sm text-cocoa outline-none transition placeholder:text-cocoa/45 focus:border-terracotta dark:border-white/15 dark:bg-ink dark:text-cream dark:placeholder:text-cream/45"
                >

                <label class="sr-only" for="category">{{ __('home.filters.category') }}</label>
                <select
                    id="category"
                    name="category"
                    class="rounded-lg border border-cocoa/15 bg-white px-4 py-3 text-sm text-cocoa outline-none transition focus:border-terracotta dark:border-white/15 dark:bg-ink dark:text-cream"
                >
                    <option value="">{{ __('home.filters.all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['slug'] }}" @selected(($filters['category'] ?? '') === $category['slug'])>
                            {{ $category['name'] }} ({{ $category['products_count'] }})
                        </option>
                    @endforeach
                </select>

                <label class="sr-only" for="sort">{{ __('home.filters.sort') }}</label>
                <select
                    id="sort"
                    name="sort"
                    class="rounded-lg border border-cocoa/15 bg-white px-4 py-3 text-sm text-cocoa outline-none transition focus:border-terracotta dark:border-white/15 dark:bg-ink dark:text-cream"
                >
                    @foreach (trans('home.filters.sort_options') as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'default') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-full bg-terracotta px-5 py-3 text-sm font-semibold text-white transition hover:bg-clay">
                        {{ __('home.filters.apply') }}
                    </button>
                    @if (($filters['q'] ?? '') !== '' || ($filters['category'] ?? '') !== '' || ($filters['sort'] ?? 'default') !== 'default')
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="rounded-full px-4 py-3 text-sm font-semibold text-cocoa/70 transition hover:bg-cocoa/10 dark:text-cream/70 dark:hover:bg-white/10">
                            {{ __('home.filters.reset') }}
                        </a>
                    @endif
                </div>
            </form>

            @if ($apiError)
                <div class="mt-8 rounded-lg border border-terracotta/25 bg-terracotta/10 px-5 py-4 text-sm text-terracotta">
                    {{ $apiError }}
                </div>
            @endif

            <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($products as $product)
                    <article class="theme-card overflow-hidden rounded-lg border border-cocoa/10 bg-linen shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-ink">
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}">
                            <img class="h-56 w-full object-cover" src="{{ $product['primary_image']['url'] ?? '' }}" alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}">
                        </a>
                        <div class="p-5">
                            <p class="theme-subtle text-xs font-semibold uppercase tracking-wide text-sage dark:text-cream/60">
                                {{ $product['origin'] }}
                            </p>
                            <h3 class="theme-title mt-2 text-xl font-semibold text-cocoa dark:text-cream">
                                <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="transition hover:text-terracotta">
                                    {{ $product['name'] }}
                                </a>
                            </h3>
                            <p class="theme-muted mt-3 line-clamp-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70">
                                {{ $product['description'] }}
                            </p>
                            <div class="mt-5 flex items-center justify-between gap-3">
                                <span class="theme-title text-lg font-semibold text-terracotta dark:text-cream">{{ $product['formatted_price'] }}</span>
                                <button
                                    class="theme-invert-button rounded-full bg-cocoa px-4 py-2 text-sm font-semibold text-white transition hover:bg-terracotta disabled:cursor-wait disabled:opacity-70 dark:bg-cream dark:text-ink dark:hover:bg-white"
                                    type="button"
                                    x-on:click="addToCart({{ $product['id'] }})"
                                    x-bind:disabled="cartMutating"
                                >
                                    {{ __('home.products.cta') }}
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="theme-card rounded-lg border border-cocoa/10 bg-linen p-6 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70 md:col-span-2 lg:col-span-3">
                        {{ __('home.products.empty') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="checkout" class="theme-band surface-transition bg-cream px-5 py-16 dark:bg-ink sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div>
                    <p class="theme-subtle text-sm font-semibold uppercase tracking-wide text-sage dark:text-cream/70">
                        {{ __('home.checkout.eyebrow') }}
                    </p>
                    <h2 class="theme-title mt-3 text-3xl font-semibold text-cocoa dark:text-cream">
                        {{ __('home.checkout.title') }}
                    </h2>
                    <p class="theme-muted mt-4 text-sm leading-7 text-cocoa/70 dark:text-cream/70">
                        {{ __('home.checkout.body') }}
                    </p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach (trans('home.checkout.steps') as $step)
                        <div class="theme-card rounded-lg border border-cocoa/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                            <span class="theme-title text-sm font-semibold text-terracotta dark:text-cream">{{ $step['number'] }}</span>
                            <h3 class="theme-title mt-3 font-semibold text-cocoa dark:text-cream">{{ $step['title'] }}</h3>
                            <p class="theme-muted mt-2 text-sm leading-6 text-cocoa/70 dark:text-cream/70">{{ $step['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
