@extends('layouts.shop')

@section('title', data_get($seoPayload ?? [], 'meta.title', __('home.meta.title')))
@section('description', data_get($seoPayload ?? [], 'meta.description', __('home.meta.description')))
@section('canonical', data_get($seoPayload ?? [], 'canonical', route('home.localized', ['locale' => $locale])))

@section('content')
    @php
        $spotlightProducts = array_slice($products, 0, 3);
        $featuredBlogPosts = $blogPosts ?? [];
    @endphp

    <section
        id="home"
        class="relative min-h-[68svh] overflow-hidden bg-ink text-white lg:min-h-[calc(100vh-132px)]"
        x-data="{ active: 0, slides: @js(trans('home.slider.items')) }"
        x-init="setInterval(() => active = (active + 1) % slides.length, 5600)"
    >
        <template x-for="(slide, index) in slides" x-bind:key="slide.title">
            <div x-show="active === index" x-transition.opacity.duration.700ms class="absolute inset-0">
                <img class="h-full w-full object-cover" x-bind:src="slide.image" x-bind:alt="slide.title" fetchpriority="high" decoding="async">
                <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/75 to-ink/25 lg:bg-gradient-to-r lg:from-ink/90 lg:via-ink/65 lg:to-transparent"></div>
            </div>
        </template>

        <div class="relative z-10 mx-auto flex min-h-[68svh] max-w-7xl flex-col justify-end px-4 pb-8 pt-20 sm:px-8 lg:min-h-[calc(100vh-132px)] lg:pb-14">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-meadow sm:text-sm" x-text="slides[active].label"></p>
                <h1 class="mt-3 text-3xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-6xl" x-text="slides[active].title"></h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/75 sm:text-base sm:leading-8" x-text="slides[active].body"></p>

                <div class="mt-6 grid gap-3 sm:flex sm:flex-row">
                    <a href="#products" class="btn-primary w-full sm:w-auto">{{ __('home.hero.primary_cta') }}</a>
                    <a href="#categories" class="inline-flex min-h-[44px] items-center justify-center rounded-full border border-white/25 bg-white/10 px-5 py-3 text-sm font-bold uppercase tracking-wide text-white backdrop-blur transition hover:bg-white hover:text-leaf sm:w-auto">
                        {{ __('home.hero.secondary_cta') }}
                    </a>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between gap-4 border-t border-white/15 pt-5">
                <div class="flex gap-2">
                    <template x-for="(slide, index) in slides" x-bind:key="index">
                        <button type="button" class="h-2.5 rounded-full transition-all" x-bind:class="active === index ? 'w-10 bg-meadow' : 'w-2.5 bg-white/35'" x-on:click="active = index"></button>
                    </template>
                </div>
                <p class="hidden text-xs font-bold uppercase tracking-[0.18em] text-white/60 sm:block">{{ __('home.slider.badge') }}</p>
            </div>
        </div>
    </section>

    <section class="border-b border-leaf/10 bg-white px-4 py-4 dark:border-white/10 dark:bg-ink sm:px-8">
        <div class="mobile-scrollbarless mx-auto flex max-w-7xl gap-3 overflow-x-auto md:grid md:grid-cols-3 md:overflow-visible">
            @foreach (trans('home.hero.trust') as $item)
                <div class="min-w-[260px] rounded-2xl border border-leaf/10 bg-mint px-4 py-3 dark:border-white/10 dark:bg-white/5 md:min-w-0">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-terracotta"></span>
                        <div>
                            <p class="text-sm font-extrabold text-cocoa dark:text-cream">{{ $item['title'] }}</p>
                            <p class="mt-1 text-xs leading-5 text-cocoa/60 dark:text-cream/60">{{ $item['body'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section id="about" class="bg-white px-4 py-12 dark:bg-ink sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div class="relative overflow-hidden rounded-[1.5rem] bg-linen p-2 dark:bg-white/5 sm:p-3">
                <img class="aspect-[4/3] w-full rounded-[1.15rem] object-cover" src="https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=1200&q=84" alt="{{ __('home.about.image_alt') }}" loading="lazy" decoding="async">
                <div class="absolute bottom-4 left-4 right-4 rounded-[1rem] border border-white/20 bg-ink/80 p-4 text-white shadow-xl backdrop-blur sm:bottom-6 sm:left-6 sm:right-6 sm:p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-meadow sm:text-xs">DEN & FILS</p>
                    <p class="mt-2 text-sm font-extrabold leading-snug sm:text-lg">{{ __('home.about.image_caption') }}</p>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.about.eyebrow') }}</p>
                <h2 class="mt-3 max-w-2xl text-2xl font-extrabold leading-tight text-cocoa dark:text-cream sm:text-4xl">{{ __('home.about.title') }}</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-cocoa/70 dark:text-cream/70 sm:text-base sm:leading-8">{{ __('home.about.body') }}</p>
                <a href="{{ route('pages.about', ['locale' => $locale]) }}" class="btn-secondary mt-6 w-full sm:w-auto" wire:navigate.hover>{{ __('home.nav.about') }}</a>

                <div class="mt-6 grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    @foreach (array_slice(trans('home.about.points'), 0, 3) as $point)
                        <article class="rounded-2xl border border-leaf/10 bg-linen p-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $point['eyebrow'] }}</p>
                            <h3 class="mt-2 text-sm font-extrabold text-cocoa dark:text-cream">{{ $point['title'] }}</h3>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="categories" class="theme-band-soft bg-linen px-4 py-12 dark:bg-[#172414] sm:px-8 lg:py-14">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.categories.eyebrow') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ __('home.categories.title') }}</h2>
                </div>
                <p class="max-w-lg text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.categories.body') }}</p>
            </div>

            <livewire:shop.category-grid :locale="$locale" :categories="$categories" />
        </div>
    </section>

    <section id="offers" class="bg-white px-4 py-12 dark:bg-ink sm:px-8 lg:py-14">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.offers.main_eyebrow') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ __('home.offers.main_title') }}</h2>
                </div>
                <a href="#products" class="btn-secondary w-full sm:w-fit">{{ __('home.offers.cta') }}</a>
            </div>

            <div class="grid gap-3 lg:grid-cols-3">
                @foreach (trans('home.offers.cards') as $card)
                    <article class="rounded-[1.15rem] border border-leaf/10 bg-linen p-5 dark:border-white/10 dark:bg-white/5 sm:p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $card['eyebrow'] }}</p>
                        <h3 class="mt-3 text-lg font-extrabold text-cocoa dark:text-cream sm:text-xl">{{ $card['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $card['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <livewire:shop.product-catalog
        :locale="$locale"
        :categories="$categories"
        :products="$products"
        :filters="$filters"
        :api-error="$apiError"
    />
    <section class="bg-white px-4 py-12 dark:bg-ink sm:px-8 lg:py-14">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.spotlight.eyebrow') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ __('home.spotlight.title') }}</h2>
                </div>
                <a href="#products" class="btn-secondary w-full sm:w-fit">{{ __('home.spotlight.cta') }}</a>
            </div>

            <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
                @forelse ($spotlightProducts as $product)
                    @php
                        $ratingValue = number_format((float) data_get($product, 'commerce.rating.average', 0), 1, ',', ' ');
                        $reviewCount = (int) data_get($product, 'commerce.rating.count', 0);
                        $primaryImage = $product['primary_image'] ?? [];
                    @endphp
                    <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="group min-w-[250px] rounded-[1.25rem] border border-leaf/10 bg-linen p-4 transition hover:shadow-xl dark:border-white/10 dark:bg-white/5 lg:min-w-0" itemscope itemtype="https://schema.org/Product" wire:navigate.hover>
                        <img class="h-40 w-full rounded-[1rem] object-cover sm:h-48" src="{{ $primaryImage['url'] ?? '' }}" alt="{{ $primaryImage['alt_text'] ?? $product['name'] }}" width="{{ $primaryImage['width'] ?? 600 }}" height="{{ $primaryImage['height'] ?? 450 }}" loading="{{ $primaryImage['loading'] ?? 'lazy' }}" decoding="async" itemprop="image">
                        <div class="mt-4 flex items-start justify-between gap-4">
                            <h3 class="line-clamp-2 text-base font-extrabold text-cocoa transition group-hover:text-leaf dark:text-cream sm:text-lg" itemprop="name">{{ $product['name'] }}</h3>
                            <span class="shrink-0 font-extrabold text-leaf">{{ $product['formatted_price'] }}</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-3 text-xs font-bold text-cocoa/55 dark:text-cream/55" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                            <span class="text-leaf dark:text-meadow" aria-label="{{ $ratingValue }}/5">★★★★★</span>
                            <span><span itemprop="ratingValue">{{ $ratingValue }}</span> · {{ $reviewCount }} {{ $locale === 'fr' ? 'avis' : 'reviews' }}</span>
                            <meta itemprop="reviewCount" content="{{ $reviewCount }}">
                        </div>
                    </a>
                @empty
                    <div class="rounded-[1.5rem] border border-leaf/10 bg-linen p-6 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70 lg:col-span-3">{{ __('home.products.empty') }}</div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="blog" class="theme-band-soft bg-linen px-4 py-12 dark:bg-[#172414] sm:px-8 lg:py-14">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.blog.eyebrow') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ __('home.blog.title') }}</h2>
                </div>
                <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="btn-secondary w-full sm:w-fit" wire:navigate.hover>{{ __('home.nav.blog') }}</a>
            </div>
            <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
                @foreach ($featuredBlogPosts as $post)
                    <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post['slug']]) }}" class="group min-w-[260px] overflow-hidden rounded-[1.25rem] border border-leaf/10 bg-white transition hover:shadow-xl dark:border-white/10 dark:bg-white/5 lg:min-w-0" wire:navigate.hover>
                        <img class="h-40 w-full object-cover" src="{{ $post['image'] }}" alt="{{ $post['title'] }}" loading="lazy" decoding="async">
                        <div class="p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $post['category'] }}</p>
                                <span class="text-xs font-semibold text-cocoa/50 dark:text-cream/50">{{ $post['date'] }}</span>
                            </div>
                            <h3 class="mt-3 line-clamp-2 text-base font-extrabold text-cocoa transition group-hover:text-leaf dark:text-cream sm:text-lg">{{ $post['title'] }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $post['description'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="checkout" class="theme-band surface-transition bg-linen px-4 py-12 dark:bg-[#172414] sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-6 rounded-[1.5rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-8 lg:grid-cols-[0.8fr_1.2fr] lg:gap-8 lg:p-10">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.checkout.eyebrow') }}</p>
                <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-cocoa dark:text-cream sm:text-3xl">{{ __('home.checkout.title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.checkout.body') }}</p>
                <livewire:shop.cart-open-button button-class="btn-primary mt-6 w-full sm:w-auto" />
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                @foreach (trans('home.checkout.steps') as $item)
                    <div class="rounded-[1.15rem] border border-leaf/10 bg-mint/70 p-4 dark:border-white/10 dark:bg-white/5 sm:p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $item['number'] }}</p>
                        <h3 class="mt-3 font-extrabold text-cocoa dark:text-cream">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $item['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
