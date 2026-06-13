@extends('layouts.shop')

@section('title', __('home.meta.title'))
@section('description', __('home.meta.description'))

@section('content')
    @php
        $featuredProducts = array_slice($products, 0, 6);
        $spotlightProducts = array_slice($products, 0, 3);
    @endphp

    <section class="soft-grid relative isolate overflow-hidden px-5 pb-16 pt-28 dark:bg-ink sm:px-8 lg:pb-24 lg:pt-32">
        <div class="absolute inset-x-0 top-24 -z-10 mx-auto h-72 max-w-5xl rounded-full bg-meadow/20 blur-3xl"></div>

        <div class="mx-auto max-w-7xl">
            <div class="mb-8 rounded-full border border-leaf/10 bg-white/85 px-4 py-3 text-center text-sm font-bold text-leaf shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-cream">
                {{ __('home.announcement') }}
            </div>

            <div class="grid gap-10 lg:grid-cols-[0.92fr_1.08fr] lg:items-center">
                <div class="animate-rise">
                    <div class="inline-flex items-center gap-2 rounded-full border border-leaf/10 bg-white/80 px-3 py-2 text-xs font-bold uppercase tracking-[0.2em] text-leaf shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-cream">
                        <span class="h-2 w-2 rounded-full bg-terracotta"></span>
                        {{ __('home.hero.eyebrow') }}
                    </div>

                    <h1 class="mt-7 max-w-4xl text-5xl font-extrabold leading-[0.96] tracking-tight text-cocoa dark:text-cream sm:text-6xl lg:text-7xl">
                        {{ __('home.hero.title') }}
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-8 text-cocoa/70 dark:text-cream/70 sm:text-lg">
                        {{ __('home.hero.body') }}
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="#products" class="btn-primary">
                            {{ __('home.hero.primary_cta') }}
                        </a>
                        <a href="#offers" class="btn-secondary">
                            {{ __('home.hero.secondary_cta') }}
                        </a>
                    </div>

                    <div class="mt-9 grid max-w-2xl gap-3 sm:grid-cols-3">
                        @foreach (trans('home.hero.trust') as $item)
                            <div class="rounded-2xl border border-leaf/10 bg-white/70 p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                                <p class="text-sm font-extrabold text-leaf dark:text-cream">{{ $item['title'] }}</p>
                                <p class="mt-1 text-xs leading-5 text-cocoa/60 dark:text-cream/60">{{ $item['body'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div
                    class="animate-rise relative"
                    x-data="{ active: 0, slides: @js(trans('home.slider.items')) }"
                    x-init="setInterval(() => active = (active + 1) % slides.length, 5200)"
                >
                    <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-meadow/30 blur-3xl"></div>
                    <div class="premium-card overflow-hidden bg-white p-3 dark:bg-white/5">
                        <div class="relative min-h-[500px] overflow-hidden rounded-[1.25rem] bg-forest">
                            <template x-for="(slide, index) in slides" x-bind:key="slide.title">
                                <div x-show="active === index" x-transition.opacity.duration.500ms class="absolute inset-0">
                                    <img class="h-full w-full object-cover" x-bind:src="slide.image" x-bind:alt="slide.title" fetchpriority="high">
                                    <div class="product-image-overlay absolute inset-0"></div>
                                    <div class="absolute inset-x-0 bottom-0 p-6 text-white sm:p-8">
                                        <p class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-meadow backdrop-blur" x-text="slide.label"></p>
                                        <h2 class="mt-4 max-w-xl text-3xl font-extrabold leading-tight sm:text-5xl" x-text="slide.title"></h2>
                                        <p class="mt-3 max-w-lg text-sm leading-7 text-white/78" x-text="slide.body"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-between gap-4 p-4">
                            <div class="flex gap-2">
                                <template x-for="(slide, index) in slides" x-bind:key="index">
                                    <button type="button" class="h-2.5 rounded-full transition-all" x-bind:class="active === index ? 'w-9 bg-terracotta' : 'w-2.5 bg-leaf/20'" x-on:click="active = index"></button>
                                </template>
                            </div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf">{{ __('home.slider.badge') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white px-5 py-8 dark:bg-[#172414] sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-4 md:grid-cols-3">
            @foreach (trans('home.stats') as $stat)
                <div class="rounded-[1.25rem] border border-leaf/10 bg-mint/70 p-5 dark:border-white/10 dark:bg-white/5">
                    <p class="text-3xl font-extrabold text-leaf dark:text-cream">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm leading-6 text-cocoa/70 dark:text-cream/70">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="theme-band-soft bg-linen px-5 py-16 dark:bg-ink sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-leaf">{{ __('home.categories.eyebrow') }}</p>
                    <h2 class="mt-3 text-3xl font-extrabold text-cocoa dark:text-cream sm:text-4xl">{{ __('home.categories.title') }}</h2>
                </div>
                <p class="max-w-xl text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.categories.body') }}</p>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ route('home.localized', ['locale' => $locale, 'category' => $category['slug']]) }}#products" class="group rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-leaf/30 hover:shadow-xl dark:border-white/10 dark:bg-white/5">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-mint text-xl font-extrabold text-leaf transition group-hover:bg-terracotta group-hover:text-white">{{ str($category['name'])->substr(0, 1) }}</span>
                        <h3 class="mt-5 text-xl font-extrabold text-cocoa dark:text-cream">{{ $category['name'] }}</h3>
                        <p class="mt-2 text-sm text-cocoa/60 dark:text-cream/60">{{ __('home.categories.count', ['count' => $category['products_count']]) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="offers" class="bg-white px-5 py-16 dark:bg-[#172414] sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="relative overflow-hidden rounded-[2rem] bg-forest p-8 text-white shadow-2xl shadow-leaf/20 lg:p-10">
                    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-meadow/20 blur-3xl"></div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-meadow">{{ __('home.offers.main_eyebrow') }}</p>
                    <h2 class="mt-4 max-w-2xl text-3xl font-extrabold leading-tight sm:text-5xl">{{ __('home.offers.main_title') }}</h2>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-white/75">{{ __('home.offers.main_body') }}</p>
                    <a href="#products" class="mt-7 inline-flex rounded-full bg-white px-5 py-3 text-sm font-bold uppercase tracking-wide text-leaf transition hover:bg-mint">{{ __('home.offers.cta') }}</a>
                </div>

                <div class="grid gap-5">
                    @foreach (trans('home.offers.cards') as $card)
                        <div class="rounded-[1.5rem] border border-leaf/10 bg-mint p-6 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $card['eyebrow'] }}</p>
                            <h3 class="mt-3 text-xl font-extrabold text-cocoa dark:text-cream">{{ $card['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $card['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="products" class="theme-band-soft surface-transition bg-linen px-5 py-16 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                <div>
                    <p class="theme-subtle text-sm font-bold uppercase tracking-[0.2em] text-leaf dark:text-cream/60">
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

            <form method="GET" action="{{ route('home.localized', ['locale' => $locale]) }}" class="glass-panel mt-8 grid gap-3 rounded-[1.5rem] p-3 md:grid-cols-[1fr_220px_180px_auto]">
                <label class="sr-only" for="q">{{ __('home.filters.search') }}</label>
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('home.filters.search_placeholder') }}" class="input-premium">

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
                    <button type="submit" class="btn-primary">{{ __('home.filters.apply') }}</button>
                    @if (($filters['q'] ?? '') !== '' || ($filters['category'] ?? '') !== '' || ($filters['sort'] ?? 'default') !== 'default')
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary px-4">{{ __('home.filters.reset') }}</a>
                    @endif
                </div>
            </form>

            @if ($apiError)
                <div class="mt-8 rounded-2xl border border-leaf/25 bg-mint px-5 py-4 text-sm font-semibold text-leaf dark:bg-white/5">
                    {{ $apiError }}
                </div>
            @endif

            <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($products as $product)
                    <article class="premium-card group overflow-hidden bg-white dark:bg-white/5">
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="relative block overflow-hidden bg-white">
                            <img class="h-64 w-full object-cover transition duration-500 group-hover:scale-[1.04]" src="{{ $product['primary_image']['url'] ?? '' }}" alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}" loading="lazy">
                            <div class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1.5 text-xs font-extrabold text-leaf shadow-sm backdrop-blur dark:bg-ink/80 dark:text-cream">
                                {{ $product['origin'] }}
                            </div>
                        </a>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="theme-title text-xl font-extrabold leading-snug text-cocoa dark:text-cream">
                                    <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="transition hover:text-leaf">{{ $product['name'] }}</a>
                                </h3>
                                <span class="shrink-0 rounded-full bg-mint px-3 py-1 text-xs font-bold text-leaf dark:bg-white/10 dark:text-cream">
                                    {{ __('home.products.stock_label', ['count' => $product['stock_quantity']]) }}
                                </span>
                            </div>
                            <p class="theme-muted mt-3 line-clamp-2 text-sm leading-6 text-cocoa/70 dark:text-cream/70">{{ $product['description'] }}</p>
                            <div class="mt-5 flex items-center justify-between gap-3">
                                <span class="theme-title text-2xl font-extrabold text-leaf dark:text-cream">{{ $product['formatted_price'] }}</span>
                                <button class="btn-primary px-4 py-2.5" type="button" x-on:click="addToCart({{ $product['id'] }})" x-bind:disabled="cartMutating">
                                    {{ __('home.products.cta') }}
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="theme-card rounded-[1.5rem] border border-leaf/10 bg-white p-6 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70 md:col-span-2 lg:col-span-3">
                        {{ __('home.products.empty') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="bg-white px-5 py-16 dark:bg-[#172414] sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-leaf">{{ __('home.spotlight.eyebrow') }}</p>
                    <h2 class="mt-3 text-3xl font-extrabold text-cocoa dark:text-cream sm:text-4xl">{{ __('home.spotlight.title') }}</h2>
                </div>
                <a href="#products" class="btn-secondary">{{ __('home.spotlight.cta') }}</a>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                @forelse ($spotlightProducts as $product)
                    <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="group rounded-[1.5rem] border border-leaf/10 bg-linen p-4 transition hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-white/5">
                        <img class="h-52 w-full rounded-[1rem] object-cover" src="{{ $product['primary_image']['url'] ?? '' }}" alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}" loading="lazy">
                        <div class="mt-4 flex items-start justify-between gap-4">
                            <h3 class="text-lg font-extrabold text-cocoa transition group-hover:text-leaf dark:text-cream">{{ $product['name'] }}</h3>
                            <span class="font-extrabold text-leaf">{{ $product['formatted_price'] }}</span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-[1.5rem] border border-leaf/10 bg-linen p-6 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70 lg:col-span-3">{{ __('home.products.empty') }}</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="bg-linen px-5 py-16 dark:bg-ink sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-4">
            @foreach (trans('home.commitments') as $item)
                <div class="rounded-[1.5rem] border border-leaf/10 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-mint text-lg font-extrabold text-leaf">{{ $item['number'] }}</span>
                    <h3 class="mt-5 text-lg font-extrabold text-cocoa dark:text-cream">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $item['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="checkout" class="theme-band surface-transition bg-white px-5 py-16 dark:bg-[#172414] sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl rounded-[2rem] bg-forest p-6 text-white shadow-2xl shadow-leaf/20 dark:bg-black sm:p-8 lg:p-10">
            <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-meadow">{{ __('home.checkout.eyebrow') }}</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-5xl">{{ __('home.checkout.title') }}</h2>
                    <p class="mt-5 text-sm leading-7 text-white/75">{{ __('home.checkout.body') }}</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach (trans('home.checkout.steps') as $step)
                        <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-meadow">{{ $step['number'] }}</span>
                            <h3 class="mt-4 font-extrabold text-white">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-white/70">{{ $step['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
