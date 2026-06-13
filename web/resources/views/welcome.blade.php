@extends('layouts.shop')

@section('title', __('home.meta.title'))
@section('description', __('home.meta.description'))

@section('content')
    <section class="soft-grid relative isolate overflow-hidden px-5 pb-14 pt-28 dark:bg-ink sm:px-8 lg:pb-20 lg:pt-32">
        <div class="absolute inset-x-0 top-0 -z-10 h-80 bg-gradient-to-b from-white/60 to-transparent dark:from-white/5"></div>
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1fr_0.82fr] lg:items-center">
            <div class="animate-rise">
                <div class="inline-flex items-center gap-2 rounded-full border border-cocoa/10 bg-white/75 px-3 py-2 text-xs font-bold uppercase tracking-[0.2em] text-terracotta shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-cream">
                    <span class="h-2 w-2 rounded-full bg-sage"></span>
                    {{ __('home.hero.eyebrow') }}
                </div>

                <h1 class="mt-7 max-w-4xl text-5xl font-extrabold leading-[0.96] tracking-tight text-cocoa dark:text-cream sm:text-6xl lg:text-7xl">
                    {{ __('home.hero.title') }}
                </h1>

                <p class="mt-6 max-w-2xl text-base leading-8 text-cocoa/72 dark:text-cream/72 sm:text-lg">
                    {{ __('home.hero.body') }}
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#products" class="btn-primary">
                        {{ __('home.hero.primary_cta') }}
                    </a>
                    <button type="button" x-on:click="loadCart(true)" class="btn-secondary">
                        {{ __('home.hero.secondary_cta') }}
                    </button>
                </div>

                <div class="mt-8 grid max-w-2xl gap-3 sm:grid-cols-3">
                    @foreach (trans('home.hero.trust') as $item)
                        <div class="rounded-2xl border border-cocoa/10 bg-white/70 p-4 text-sm font-semibold text-cocoa/70 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-cream/70">
                            {{ $item }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="animate-rise relative">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-terracotta/20 blur-3xl"></div>
                <div class="premium-card overflow-hidden bg-white p-3 dark:bg-white/5">
                    <div class="relative overflow-hidden rounded-[1.25rem]">
                        <img
                            class="aspect-[4/5] w-full object-cover"
                            src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1300&q=84"
                            alt="{{ __('home.hero.image_alt') }}"
                            fetchpriority="high"
                        >
                        <div class="product-image-overlay absolute inset-0"></div>
                        <div class="absolute inset-x-0 bottom-0 p-5 text-white">
                            <p class="text-sm font-semibold text-cream/80">{{ __('home.hero.side_label') }}</p>
                            <p class="mt-2 max-w-xs text-2xl font-extrabold leading-tight">{{ __('home.hero.side_title') }}</p>
                        </div>
                    </div>
                    <div class="grid gap-3 p-3 sm:grid-cols-2">
                        @foreach (trans('home.stats') as $stat)
                            <div class="rounded-2xl bg-linen p-4 dark:bg-white/5">
                                <p class="text-2xl font-extrabold text-cocoa dark:text-cream">{{ $stat['value'] }}</p>
                                <p class="mt-1 text-xs leading-5 text-cocoa/62 dark:text-cream/62">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="products" class="theme-band-soft surface-transition bg-white px-5 py-14 dark:bg-[#211914] sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                <div>
                    <p class="theme-subtle text-sm font-bold uppercase tracking-[0.2em] text-sage dark:text-cream/60">
                        {{ __('home.products.eyebrow') }}
                    </p>
                    <h2 class="theme-title mt-3 max-w-3xl text-3xl font-extrabold tracking-tight text-cocoa dark:text-cream sm:text-5xl">
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
                class="glass-panel mt-8 grid gap-3 rounded-[1.5rem] p-3 md:grid-cols-[1fr_220px_180px_auto]"
            >
                <label class="sr-only" for="q">{{ __('home.filters.search') }}</label>
                <input
                    id="q"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="{{ __('home.filters.search_placeholder') }}"
                    class="input-premium"
                >

                <label class="sr-only" for="category">{{ __('home.filters.category') }}</label>
                <select id="category" name="category" class="input-premium">
                    <option value="">{{ __('home.filters.all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['slug'] }}" @selected(($filters['category'] ?? '') === $category['slug'])>
                            {{ $category['name'] }} ({{ $category['products_count'] }})
                        </option>
                    @endforeach
                </select>

                <label class="sr-only" for="sort">{{ __('home.filters.sort') }}</label>
                <select id="sort" name="sort" class="input-premium">
                    @foreach (trans('home.filters.sort_options') as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'default') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="btn-primary">
                        {{ __('home.filters.apply') }}
                    </button>
                    @if (($filters['q'] ?? '') !== '' || ($filters['category'] ?? '') !== '' || ($filters['sort'] ?? 'default') !== 'default')
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary px-4">
                            {{ __('home.filters.reset') }}
                        </a>
                    @endif
                </div>
            </form>

            @if ($apiError)
                <div class="mt-8 rounded-2xl border border-terracotta/25 bg-terracotta/10 px-5 py-4 text-sm font-semibold text-terracotta">
                    {{ $apiError }}
                </div>
            @endif

            <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($products as $product)
                    <article class="premium-card group overflow-hidden bg-linen dark:bg-white/5">
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="relative block overflow-hidden">
                            <img
                                class="h-64 w-full object-cover transition duration-500 group-hover:scale-[1.04]"
                                src="{{ $product['primary_image']['url'] ?? '' }}"
                                alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}"
                                loading="lazy"
                            >
                            <div class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1.5 text-xs font-extrabold text-cocoa shadow-sm backdrop-blur dark:bg-ink/80 dark:text-cream">
                                {{ $product['origin'] }}
                            </div>
                        </a>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="theme-title text-xl font-extrabold leading-snug text-cocoa dark:text-cream">
                                    <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="transition hover:text-terracotta">
                                        {{ $product['name'] }}
                                    </a>
                                </h3>
                                <span class="shrink-0 rounded-full bg-sage/10 px-3 py-1 text-xs font-bold text-leaf dark:bg-white/10 dark:text-cream">
                                    {{ __('home.products.stock_label', ['count' => $product['stock_quantity']]) }}
                                </span>
                            </div>
                            <p class="theme-muted mt-3 line-clamp-2 text-sm leading-6 text-cocoa/70 dark:text-cream/70">
                                {{ $product['description'] }}
                            </p>
                            <div class="mt-5 flex items-center justify-between gap-3">
                                <span class="theme-title text-2xl font-extrabold text-terracotta dark:text-cream">{{ $product['formatted_price'] }}</span>
                                <button
                                    class="btn-primary px-4 py-2.5"
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
                    <div class="theme-card rounded-[1.5rem] border border-cocoa/10 bg-linen p-6 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70 md:col-span-2 lg:col-span-3">
                        {{ __('home.products.empty') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="checkout" class="theme-band surface-transition bg-cream px-5 py-16 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl rounded-[2rem] bg-cocoa p-6 text-white shadow-2xl shadow-cocoa/20 dark:bg-black sm:p-8 lg:p-10">
            <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-cream/70">
                        {{ __('home.checkout.eyebrow') }}
                    </p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-5xl">
                        {{ __('home.checkout.title') }}
                    </h2>
                    <p class="mt-5 text-sm leading-7 text-cream/75">
                        {{ __('home.checkout.body') }}
                    </p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach (trans('home.checkout.steps') as $step)
                        <div class="rounded-[1.4rem] border border-white/12 bg-white/8 p-5 backdrop-blur">
                            <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-cream/60">{{ $step['number'] }}</span>
                            <h3 class="mt-4 font-extrabold text-white">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-cream/70">{{ $step['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
