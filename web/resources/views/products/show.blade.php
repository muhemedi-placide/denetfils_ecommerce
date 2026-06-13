@extends('layouts.shop')

@section('title', $product['name'] . ' | Denetfils')
@section('description', $product['description'])

@section('content')
    <section class="soft-grid px-5 pb-14 pt-28 dark:bg-ink sm:px-8 lg:pb-20 lg:pt-32">
        <div class="mx-auto max-w-7xl">
            <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="inline-flex items-center rounded-full border border-leaf/10 bg-white/75 px-4 py-2 text-sm font-bold text-cocoa/70 transition hover:border-leaf/40 hover:bg-mint hover:text-leaf dark:border-white/10 dark:bg-white/5 dark:text-cream/70">
                {{ __('home.product.back') }}
            </a>

            <div class="mt-8 grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-start">
                <div class="premium-card overflow-hidden bg-white p-3 dark:bg-white/5">
                    <img
                        class="aspect-[4/3] w-full rounded-[1.25rem] object-cover"
                        src="{{ $product['primary_image']['url'] ?? '' }}"
                        alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}"
                        fetchpriority="high"
                    >
                </div>

                <div class="lg:sticky lg:top-28">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-leaf dark:text-cream/60">
                        {{ $product['origin'] }}
                    </p>
                    <h1 class="theme-title mt-4 text-4xl font-extrabold leading-tight tracking-tight text-cocoa dark:text-cream sm:text-6xl">
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
@endsection
