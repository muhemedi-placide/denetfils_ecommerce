@extends('layouts.shop')

@section('title', $product['name'] . ' | Denetfils')
@section('description', $product['description'])

@section('content')
    <section class="soft-grid px-5 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <nav class="flex flex-wrap items-center gap-2 text-sm font-semibold text-cocoa/60 dark:text-cream/60">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-leaf">{{ __('home.nav.home') }}</a>
                <span>/</span>
                <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="transition hover:text-leaf">{{ __('home.nav.shop') }}</a>
                <span>/</span>
                <span class="text-leaf">{{ $product['name'] }}</span>
            </nav>

            <div class="mt-8 grid gap-8 lg:grid-cols-[1.02fr_0.98fr] lg:items-start">
                <div class="space-y-4">
                    <div class="premium-card overflow-hidden bg-white p-3 dark:bg-white/5">
                        <img
                            class="aspect-[4/3] w-full rounded-[1.25rem] object-cover"
                            src="{{ $product['primary_image']['url'] ?? '' }}"
                            alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}"
                            fetchpriority="high"
                        >
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf">{{ __('home.product.category') }}</p>
                            <p class="mt-2 text-sm font-extrabold text-cocoa dark:text-cream">{{ $product['category']['name'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf">{{ __('home.product.stock') }}</p>
                            <p class="mt-2 text-sm font-extrabold text-cocoa dark:text-cream">{{ __('home.product.available', ['count' => $product['stock_quantity']]) }}</p>
                        </div>
                        <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf">{{ __('home.product.weight') }}</p>
                            <p class="mt-2 text-sm font-extrabold text-cocoa dark:text-cream">{{ $product['weight_grams'] }} g</p>
                        </div>
                    </div>
                </div>

                <div class="lg:sticky lg:top-40">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-leaf dark:text-meadow">
                        {{ $product['origin'] }}
                    </p>
                    <h1 class="theme-title mt-4 text-4xl font-extrabold leading-tight tracking-tight text-cocoa dark:text-cream sm:text-5xl">
                        {{ $product['name'] }}
                    </h1>
                    <p class="theme-muted mt-5 text-base leading-8 text-cocoa/70 dark:text-cream/70">
                        {{ $product['description'] }}
                    </p>

                    <aside class="glass-panel mt-7 rounded-[1.6rem] p-5" x-data="{ variantId: @js($product['variants'][0]['id'] ?? null) }">
                        <div class="flex items-start justify-between gap-5">
                            <div>
                                <p class="theme-subtle text-xs font-bold uppercase tracking-[0.2em] text-leaf dark:text-cream/60">{{ __('home.product.price') }}</p>
                                <p class="theme-title mt-2 text-4xl font-extrabold text-forest dark:text-cream">{{ $product['formatted_price'] }}</p>
                            </div>
                            <span class="rounded-full bg-mint px-3 py-2 text-xs font-bold text-leaf dark:bg-white/10 dark:text-cream">
                                {{ __('home.product.available', ['count' => $product['stock_quantity']]) }}
                            </span>
                        </div>

                        @if (! empty($product['variants']))
                            <label class="theme-title mt-6 block text-sm font-bold text-cocoa dark:text-cream" for="variant">
                                {{ __('home.product.variant') }}
                            </label>
                            <select id="variant" class="input-premium mt-2 w-full" x-model="variantId">
                                @foreach ($product['variants'] as $variant)
                                    <option value="{{ $variant['id'] }}">
                                        {{ $variant['name'] }} - {{ $variant['formatted_price'] }}
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        <button
                            type="button"
                            class="btn-primary mt-6 w-full py-4 text-base"
                            x-on:click="addToCart({{ $product['id'] }}, variantId)"
                            x-bind:disabled="cartMutating"
                        >
                            {{ __('home.products.cta') }}
                        </button>

                        <p class="theme-muted mt-4 text-center text-xs leading-5 text-cocoa/60 dark:text-cream/60">
                            {{ __('home.product.shipping_note') }}
                        </p>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    <section class="theme-band-soft bg-white px-5 py-14 dark:bg-[#172414] sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[1fr_0.8fr]">
            <div class="premium-card bg-linen p-6 dark:bg-white/5">
                <h2 class="theme-title text-2xl font-extrabold text-cocoa dark:text-cream">{{ __('home.product.details') }}</h2>
                <p class="theme-muted mt-3 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.product.details_body') }}</p>
            </div>

            <dl class="premium-card grid gap-4 bg-linen p-6 dark:bg-white/5 sm:grid-cols-2">
                <div>
                    <dt class="theme-subtle text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-cream/60">{{ __('home.product.category') }}</dt>
                    <dd class="theme-title mt-2 text-sm font-extrabold text-cocoa dark:text-cream">{{ $product['category']['name'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="theme-subtle text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-cream/60">{{ __('home.product.sku') }}</dt>
                    <dd class="theme-title mt-2 text-sm font-extrabold text-cocoa dark:text-cream">{{ $product['sku'] }}</dd>
                </div>
                <div>
                    <dt class="theme-subtle text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-cream/60">{{ __('home.product.stock') }}</dt>
                    <dd class="theme-title mt-2 text-sm font-extrabold text-cocoa dark:text-cream">{{ $product['stock_quantity'] }}</dd>
                </div>
                <div>
                    <dt class="theme-subtle text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-cream/60">{{ __('home.product.weight') }}</dt>
                    <dd class="theme-title mt-2 text-sm font-extrabold text-cocoa dark:text-cream">{{ $product['weight_grams'] }} g</dd>
                </div>
            </dl>
        </div>
    </section>

    @if (! empty($relatedProducts))
        <section class="bg-linen px-5 py-14 dark:bg-ink sm:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.spotlight.eyebrow') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ __('home.product.related_title') }}</h2>
                    </div>
                    <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary w-fit">{{ __('home.spotlight.cta') }}</a>
                </div>

                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach ($relatedProducts as $related)
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $related['slug']]) }}" class="group rounded-[1.25rem] border border-leaf/10 bg-white p-4 transition hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-white/5">
                            <img class="h-48 w-full rounded-[1rem] object-cover" src="{{ $related['primary_image']['url'] ?? '' }}" alt="{{ $related['primary_image']['alt_text'] ?? $related['name'] }}" loading="lazy">
                            <div class="mt-4 flex items-start justify-between gap-4">
                                <h3 class="text-lg font-extrabold text-cocoa transition group-hover:text-leaf dark:text-cream">{{ $related['name'] }}</h3>
                                <span class="font-extrabold text-leaf">{{ $related['formatted_price'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
