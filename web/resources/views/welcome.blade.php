@extends('layouts.shop')

@section('title', __('home.meta.title'))
@section('description', __('home.meta.description'))

@section('content')
    @php
        $spotlightProducts = array_slice($products, 0, 3);
    @endphp

    <section
        id="home"
        class="relative min-h-[calc(100vh-132px)] overflow-hidden bg-forest text-white"
        x-data="{ active: 0, slides: @js(trans('home.slider.items')) }"
        x-init="setInterval(() => active = (active + 1) % slides.length, 5600)"
    >
        <template x-for="(slide, index) in slides" x-bind:key="slide.title">
            <div x-show="active === index" x-transition.opacity.duration.700ms class="absolute inset-0">
                <img class="h-full w-full object-cover" x-bind:src="slide.image" x-bind:alt="slide.title" fetchpriority="high">
                <div class="absolute inset-0 bg-gradient-to-r from-ink/90 via-forest/72 to-forest/15"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-ink/70 via-transparent to-ink/25"></div>
            </div>
        </template>

        <div class="relative z-10 mx-auto flex min-h-[calc(100vh-132px)] max-w-7xl flex-col justify-end px-5 pb-10 pt-24 sm:px-8 lg:pb-14">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-[0.22em] text-meadow" x-text="slides[active].label"></p>
                <h1 class="mt-4 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-6xl" x-text="slides[active].title"></h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-white/78" x-text="slides[active].body"></p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#products" class="btn-primary">{{ __('home.hero.primary_cta') }}</a>
                    <a href="#categories" class="inline-flex items-center justify-center rounded-full border border-white/25 bg-white/10 px-5 py-3 text-sm font-bold uppercase tracking-wide text-white backdrop-blur transition hover:bg-white hover:text-leaf">
                        {{ __('home.hero.secondary_cta') }}
                    </a>
                </div>
            </div>

            <div class="mt-10 flex items-center justify-between gap-4 border-t border-white/15 pt-5">
                <div class="flex gap-2">
                    <template x-for="(slide, index) in slides" x-bind:key="index">
                        <button type="button" class="h-2.5 rounded-full transition-all" x-bind:class="active === index ? 'w-10 bg-meadow' : 'w-2.5 bg-white/35'" x-on:click="active = index"></button>
                    </template>
                </div>
                <p class="hidden text-xs font-bold uppercase tracking-[0.18em] text-white/60 sm:block">{{ __('home.slider.badge') }}</p>
            </div>
        </div>
    </section>

    <section class="border-b border-leaf/10 bg-white px-5 py-5 dark:border-white/10 dark:bg-ink sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-3 md:grid-cols-3">
            @foreach (trans('home.hero.trust') as $item)
                <div class="flex items-center gap-3 rounded-full bg-mint px-4 py-3 dark:bg-white/5">
                    <span class="h-2.5 w-2.5 rounded-full bg-terracotta"></span>
                    <div>
                        <p class="text-sm font-extrabold text-cocoa dark:text-cream">{{ $item['title'] }}</p>
                        <p class="text-xs text-cocoa/60 dark:text-cream/60">{{ $item['body'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section id="about" class="bg-white px-5 py-16 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div class="relative overflow-hidden rounded-[1.75rem] bg-linen p-3 dark:bg-white/5">
                <img
                    class="aspect-[4/3] w-full rounded-[1.25rem] object-cover"
                    src="https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=1200&q=84"
                    alt="{{ __('home.about.image_alt') }}"
                    loading="lazy"
                >
                <div class="absolute bottom-6 left-6 right-6 rounded-[1.25rem] border border-white/20 bg-forest/85 p-5 text-white shadow-xl backdrop-blur">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-meadow">Denetfils</p>
                    <p class="mt-2 text-lg font-extrabold leading-snug">{{ __('home.about.image_caption') }}</p>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.about.eyebrow') }}</p>
                <h2 class="mt-3 max-w-2xl text-3xl font-extrabold leading-tight text-cocoa dark:text-cream sm:text-4xl">{{ __('home.about.title') }}</h2>
                <p class="mt-5 max-w-2xl text-base leading-8 text-cocoa/70 dark:text-cream/70">{{ __('home.about.body') }}</p>

                <div class="mt-8 space-y-4">
                    @foreach (trans('home.about.points') as $point)
                        <article class="flex gap-4 rounded-[1.25rem] border border-leaf/10 bg-linen p-5 dark:border-white/10 dark:bg-white/5">
                            <span class="mt-1 h-3 w-3 shrink-0 rounded-full bg-terracotta"></span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $point['eyebrow'] }}</p>
                                <h3 class="mt-2 font-extrabold text-cocoa dark:text-cream">{{ $point['title'] }}</h3>
                                <p class="mt-1 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $point['body'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="categories" class="theme-band-soft bg-linen px-5 py-14 dark:bg-[#172414] sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf">{{ __('home.categories.eyebrow') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ __('home.categories.title') }}</h2>
                </div>
                <p class="max-w-lg text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.categories.body') }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ route('home.localized', ['locale' => $locale, 'category' => $category['slug']]) }}#products" class="group rounded-[1.25rem] border border-leaf/10 bg-white p-5 transition hover:-translate-y-1 hover:border-leaf/30 hover:shadow-lg dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-center justify-between gap-4">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-mint text-lg font-extrabold text-leaf transition group-hover:bg-terracotta group-hover:text-white">{{ str($category['name'])->substr(0, 1) }}</span>
                            <span class="text-xs font-bold text-cocoa/45 dark:text-cream/45">{{ __('home.categories.count', ['count' => $category['products_count']]) }}</span>
                        </div>
                        <h3 class="mt-5 text-lg font-extrabold text-cocoa dark:text-cream">{{ $category['name'] }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="offers" class="bg-white px-5 py-14 dark:bg-ink sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf">{{ __('home.offers.main_eyebrow') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ __('home.offers.main_title') }}</h2>
                </div>
                <a href="#products" class="btn-secondary w-fit">{{ __('home.offers.cta') }}</a>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                @foreach (trans('home.offers.cards') as $card)
                    <article class="rounded-[1.25rem] border border-leaf/10 bg-linen p-6 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $card['eyebrow'] }}</p>
                        <h3 class="mt-3 text-xl font-extrabold text-cocoa dark:text-cream">{{ $card['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $card['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="products" class="theme-band-soft surface-transition bg-linen px-5 py-14 dark:bg-[#172414] sm:px-8 lg:py-16">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="theme-subtle text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-cream/60">{{ __('home.products.eyebrow') }}</p>
                    <h2 class="theme-title mt-2 max-w-2xl text-2xl font-extrabold tracking-tight text-cocoa dark:text-cream sm:text-3xl">{{ __('home.products.title') }}</h2>
                </div>
                <p class="theme-muted max-w-xl text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.products.body') }}</p>
            </div>

            <form method="GET" action="{{ route('home.localized', ['locale' => $locale]) }}" class="glass-panel grid gap-3 rounded-[1.25rem] p-3 md:grid-cols-[1fr_220px_180px_auto]">
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

            <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($products as $product)
                    <article class="premium-card group overflow-hidden bg-white dark:bg-white/5">
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="relative block overflow-hidden bg-white dark:bg-white/5">
                            <img class="h-64 w-full object-cover transition duration-500 group-hover:scale-[1.04]" src="{{ $product['primary_image']['url'] ?? '' }}" alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}" loading="lazy">
                            <div class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1.5 text-xs font-extrabold text-leaf shadow-sm backdrop-blur dark:bg-ink/80 dark:text-cream">{{ $product['origin'] }}</div>
                        </a>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="theme-title text-lg font-extrabold leading-snug text-cocoa dark:text-cream">
                                    <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="transition hover:text-leaf">{{ $product['name'] }}</a>
                                </h3>
                                <span class="shrink-0 rounded-full bg-mint px-3 py-1 text-xs font-bold text-leaf dark:bg-white/10 dark:text-cream">{{ __('home.products.stock_label', ['count' => $product['stock_quantity']]) }}</span>
                            </div>
                            <p class="theme-muted mt-3 line-clamp-2 text-sm leading-6 text-cocoa/70 dark:text-cream/70">{{ $product['description'] }}</p>
                            <div class="mt-5 flex items-center justify-between gap-3">
                                <span class="theme-title text-xl font-extrabold text-leaf dark:text-cream">{{ $product['formatted_price'] }}</span>
                                <button class="btn-primary px-4 py-2.5" type="button" x-on:click="addToCart({{ $product['id'] }})" x-bind:disabled="cartMutating">{{ __('home.products.cta') }}</button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="theme-card rounded-[1.5rem] border border-leaf/10 bg-white p-6 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70 md:col-span-2 lg:col-span-3">{{ __('home.products.empty') }}</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="bg-white px-5 py-14 dark:bg-ink sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf">{{ __('home.spotlight.eyebrow') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ __('home.spotlight.title') }}</h2>
                </div>
                <a href="#products" class="btn-secondary w-fit">{{ __('home.spotlight.cta') }}</a>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                @forelse ($spotlightProducts as $product)
                    <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="group rounded-[1.25rem] border border-leaf/10 bg-linen p-4 transition hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-white/5">
                        <img class="h-48 w-full rounded-[1rem] object-cover" src="{{ $product['primary_image']['url'] ?? '' }}" alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}" loading="lazy">
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

    <section id="blog" class="theme-band-soft bg-linen px-5 py-14 dark:bg-[#172414] sm:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf">{{ __('home.blog.eyebrow') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ __('home.blog.title') }}</h2>
                </div>
                <p class="max-w-lg text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.blog.body') }}</p>
            </div>
            <div class="grid gap-4 lg:grid-cols-3">
                @foreach (trans('home.blog.posts') as $post)
                    <article class="rounded-[1.25rem] border border-leaf/10 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $post['category'] }}</p>
                        <h3 class="mt-3 text-lg font-extrabold text-cocoa dark:text-cream">{{ $post['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $post['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="checkout" class="theme-band surface-transition bg-linen px-5 py-14 dark:bg-[#172414] sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-8 rounded-[1.75rem] border border-leaf/10 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-8 lg:grid-cols-[0.8fr_1.2fr] lg:p-10">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.checkout.eyebrow') }}</p>
                <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-cocoa dark:text-cream sm:text-3xl">{{ __('home.checkout.title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.checkout.body') }}</p>
                <button type="button" x-on:click="loadCart(true)" class="btn-primary mt-6">{{ __('home.cart.title') }}</button>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach (trans('home.checkout.steps') as $item)
                    <div class="rounded-[1.25rem] border border-leaf/10 bg-mint/70 p-5 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $item['number'] }}</p>
                        <h3 class="mt-4 font-extrabold text-cocoa dark:text-cream">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $item['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
