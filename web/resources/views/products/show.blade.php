@extends('layouts.shop')

@section('title', $product['name'] . ' | Denetfils')
@section('description', $product['description'])

@section('content')
    <section class="theme-band relative isolate overflow-hidden bg-cocoa pt-28 text-white dark:bg-black">
        <img
            class="absolute inset-0 -z-20 h-full w-full object-cover opacity-45"
            src="{{ $product['primary_image']['url'] ?? '' }}"
            alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}"
        >
        <div class="absolute inset-0 -z-10 bg-cocoa/72 dark:bg-black/76"></div>
        <div class="mx-auto grid max-w-7xl gap-10 px-5 pb-14 pt-10 sm:px-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-end">
            <div>
                <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="text-sm font-semibold text-cream/80 transition hover:text-white">
                    {{ __('home.product.back') }}
                </a>
                <p class="mt-8 text-sm font-semibold uppercase tracking-wide text-cream/75">
                    {{ $product['origin'] }}
                </p>
                <h1 class="mt-3 max-w-3xl text-4xl font-semibold leading-tight sm:text-6xl">
                    {{ $product['name'] }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-cream/88 sm:text-lg">
                    {{ $product['description'] }}
                </p>
            </div>
            <div class="theme-card rounded-lg border border-white/15 bg-white/10 p-5 backdrop-blur">
                <img
                    class="aspect-[4/3] w-full rounded-md object-cover"
                    src="{{ $product['primary_image']['url'] ?? '' }}"
                    alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}"
                >
            </div>
        </div>
    </section>

    <section class="theme-band-soft surface-transition bg-white px-5 py-14 dark:bg-[#211914] sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1fr_380px]">
            <div class="theme-card rounded-lg border border-cocoa/10 bg-linen p-6 dark:border-white/10 dark:bg-white/5">
                <h2 class="theme-title text-2xl font-semibold text-cocoa dark:text-cream">{{ __('home.product.details') }}</h2>
                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="theme-subtle text-xs font-semibold uppercase tracking-wide text-sage dark:text-cream/60">{{ __('home.product.category') }}</dt>
                        <dd class="theme-title mt-1 text-sm font-semibold text-cocoa dark:text-cream">{{ $product['category']['name'] ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="theme-subtle text-xs font-semibold uppercase tracking-wide text-sage dark:text-cream/60">{{ __('home.product.sku') }}</dt>
                        <dd class="theme-title mt-1 text-sm font-semibold text-cocoa dark:text-cream">{{ $product['sku'] }}</dd>
                    </div>
                    <div>
                        <dt class="theme-subtle text-xs font-semibold uppercase tracking-wide text-sage dark:text-cream/60">{{ __('home.product.stock') }}</dt>
                        <dd class="theme-title mt-1 text-sm font-semibold text-cocoa dark:text-cream">{{ $product['stock_quantity'] }}</dd>
                    </div>
                    <div>
                        <dt class="theme-subtle text-xs font-semibold uppercase tracking-wide text-sage dark:text-cream/60">{{ __('home.product.weight') }}</dt>
                        <dd class="theme-title mt-1 text-sm font-semibold text-cocoa dark:text-cream">{{ $product['weight_grams'] }} g</dd>
                    </div>
                </dl>
            </div>

            <aside class="theme-card h-fit rounded-lg border border-cocoa/10 bg-linen p-6 shadow-sm dark:border-white/10 dark:bg-white/5" x-data="{ variantId: @js($product['variants'][0]['id'] ?? null) }">
                <p class="theme-subtle text-sm font-semibold uppercase tracking-wide text-sage dark:text-cream/60">{{ __('home.product.price') }}</p>
                <p class="theme-title mt-2 text-3xl font-semibold text-terracotta dark:text-cream">{{ $product['formatted_price'] }}</p>

                @if (! empty($product['variants']))
                    <label class="theme-title mt-6 block text-sm font-semibold text-cocoa dark:text-cream" for="variant">
                        {{ __('home.product.variant') }}
                    </label>
                    <select
                        id="variant"
                        class="mt-2 w-full rounded-lg border border-cocoa/15 bg-white px-3 py-3 text-sm text-cocoa outline-none transition focus:border-terracotta dark:border-white/15 dark:bg-ink dark:text-cream"
                        x-model="variantId"
                    >
                        @foreach ($product['variants'] as $variant)
                            <option value="{{ $variant['id'] }}">
                                {{ $variant['name'] }} - {{ $variant['formatted_price'] }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <button
                    type="button"
                    class="mt-6 w-full rounded-full bg-terracotta px-5 py-3 text-sm font-semibold text-white transition hover:bg-clay disabled:cursor-wait disabled:opacity-70"
                    x-on:click="addToCart({{ $product['id'] }}, variantId)"
                    x-bind:disabled="cartMutating"
                >
                    {{ __('home.products.cta') }}
                </button>
            </aside>
        </div>
    </section>
@endsection
