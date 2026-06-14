@extends('layouts.shop')

@php
    $primaryImage = $product['primary_image'] ?? [];
    $ratingAverage = (float) data_get($product, 'commerce.rating.average', 0);
    $ratingCount = (int) data_get($product, 'commerce.rating.count', 0);
    $ratingLabel = number_format($ratingAverage, 1, ',', ' ');
    $isAvailable = (bool) data_get($product, 'commerce.is_available', ((int) ($product['stock_quantity'] ?? 0)) > 0);
    $shipping = data_get($product, 'commerce.shipping', []);
@endphp

@section('title', data_get($product, 'seo.meta.title', $product['name'] . ' | Denetfils'))
@section('description', data_get($product, 'seo.meta.description', $product['description']))
@section('canonical', data_get($product, 'seo.canonical', route('products.show', ['locale' => $locale, 'slug' => $product['slug']])))
@section('og_type', data_get($product, 'seo.open_graph.type', 'product'))
@section('og_image', $primaryImage['url'] ?? '')
@section('preload_image', $primaryImage['url'] ?? '')

@section('content')
    <section class="soft-grid px-4 py-10 dark:bg-ink sm:px-8 lg:py-18">
        <div class="mx-auto max-w-7xl">
            <nav class="mobile-scrollbarless flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm font-semibold text-cocoa/60 dark:text-cream/60" aria-label="Breadcrumb">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-leaf">{{ __('home.nav.home') }}</a>
                <span>/</span>
                <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="transition hover:text-leaf">{{ __('home.nav.shop') }}</a>
                <span>/</span>
                <span class="text-leaf">{{ $product['name'] }}</span>
            </nav>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.02fr_0.98fr] lg:items-start">
                <div class="space-y-3 lg:space-y-4">
                    <div class="premium-card overflow-hidden bg-white p-2 dark:bg-white/5 sm:p-3">
                        <img
                            class="aspect-[4/3] w-full rounded-[1rem] object-cover sm:rounded-[1.25rem]"
                            src="{{ $primaryImage['url'] ?? '' }}"
                            alt="{{ $primaryImage['alt_text'] ?? $product['name'] }}"
                            width="{{ $primaryImage['width'] ?? 1200 }}"
                            height="{{ $primaryImage['height'] ?? 900 }}"
                            fetchpriority="{{ $primaryImage['fetch_priority'] ?? 'high' }}"
                            decoding="async"
                        >
                    </div>

                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        <div class="rounded-[1rem] border border-leaf/10 bg-white p-3 dark:border-white/10 dark:bg-white/5 sm:p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-leaf sm:text-xs">{{ __('home.product.category') }}</p>
                            <p class="mt-2 truncate text-xs font-extrabold text-cocoa dark:text-cream sm:text-sm">{{ $product['category']['name'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-[1rem] border border-leaf/10 bg-white p-3 dark:border-white/10 dark:bg-white/5 sm:p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-leaf sm:text-xs">{{ __('home.product.stock') }}</p>
                            <p class="mt-2 truncate text-xs font-extrabold text-cocoa dark:text-cream sm:text-sm">{{ $product['stock_quantity'] }}</p>
                        </div>
                        <div class="rounded-[1rem] border border-leaf/10 bg-white p-3 dark:border-white/10 dark:bg-white/5 sm:p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-leaf sm:text-xs">{{ __('home.product.weight') }}</p>
                            <p class="mt-2 truncate text-xs font-extrabold text-cocoa dark:text-cream sm:text-sm">{{ $product['weight_grams'] }} g</p>
                        </div>
                    </div>
                </div>

                <div class="lg:sticky lg:top-40">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-leaf dark:text-meadow sm:text-sm">{{ $product['origin'] }}</p>
                    <h1 class="theme-title mt-3 text-3xl font-extrabold leading-tight tracking-tight text-cocoa dark:text-cream sm:text-5xl">{{ $product['name'] }}</h1>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm font-bold text-cocoa/65 dark:text-cream/65">
                        <span class="text-leaf dark:text-meadow" aria-label="{{ $ratingLabel }}/5">★★★★★</span>
                        <span>{{ $ratingLabel }}/5</span>
                        <a href="#product-reviews-title" class="underline decoration-leaf/30 underline-offset-4 transition hover:text-leaf">
                            {{ $ratingCount }} {{ $locale === 'fr' ? 'avis clients' : 'customer reviews' }}
                        </a>
                    </div>
                    <p class="theme-muted mt-4 text-sm leading-7 text-cocoa/70 dark:text-cream/70 sm:text-base sm:leading-8">{{ $product['short_description'] ?? $product['description'] }}</p>

                    @if (! empty(data_get($product, 'rich_content.badges')))
                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach (data_get($product, 'rich_content.badges', []) as $badge)
                                <span class="rounded-full bg-mint px-3 py-1.5 text-xs font-extrabold text-leaf dark:bg-white/10 dark:text-meadow">{{ $badge }}</span>
                            @endforeach
                        </div>
                    @endif

                    <aside class="glass-panel mt-6 rounded-[1.35rem] p-4 sm:rounded-[1.6rem] sm:p-5" x-data="{ variantId: @js($product['variants'][0]['id'] ?? null) }">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="theme-subtle text-xs font-bold uppercase tracking-[0.2em] text-leaf dark:text-cream/60">{{ __('home.product.price') }}</p>
                                <p class="theme-title mt-2 text-3xl font-extrabold text-forest dark:text-cream sm:text-4xl">{{ $product['formatted_price'] }}</p>
                                <p class="mt-2 text-xs font-bold text-cocoa/55 dark:text-cream/55">
                                    <span class="text-leaf dark:text-meadow">★★★★★</span>
                                    <span class="ml-1">{{ $ratingLabel }} · {{ $ratingCount }} {{ $locale === 'fr' ? 'avis' : 'reviews' }}</span>
                                </p>
                            </div>
                            <span class="rounded-full bg-mint px-3 py-2 text-xs font-bold text-leaf dark:bg-white/10 dark:text-cream">
                                {{ $isAvailable ? __('home.product.available', ['count' => $product['stock_quantity']]) : ($locale === 'fr' ? 'Rupture de stock' : 'Out of stock') }}
                            </span>
                        </div>

                        @if (! empty($product['variants']))
                            <label class="theme-title mt-5 block text-sm font-bold text-cocoa dark:text-cream" for="variant">{{ __('home.product.variant') }}</label>
                            <select id="variant" class="input-premium mt-2 w-full" x-model="variantId">
                                @foreach ($product['variants'] as $variant)
                                    <option value="{{ $variant['id'] }}">{{ $variant['name'] }} - {{ $variant['formatted_price'] }}</option>
                                @endforeach
                            </select>
                        @endif

                        <button type="button" class="btn-primary mt-5 w-full py-4 text-base" x-on:click="addToCart({{ $product['id'] }}, variantId)" x-bind:disabled="cartMutating || @js(! $isAvailable)">{{ __('home.products.cta') }}</button>
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary mt-3 w-full">{{ __('home.product.back') }}</a>

                        <p class="theme-muted mt-4 text-center text-xs leading-5 text-cocoa/60 dark:text-cream/60">
                            {{ data_get($shipping, 'dispatch_time', __('home.product.shipping_note')) }}
                        </p>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    <section class="theme-band-soft bg-white px-4 py-12 dark:bg-[#172414] sm:px-8 lg:py-14">
        <div class="mx-auto grid max-w-7xl gap-4 lg:grid-cols-[1fr_0.8fr] lg:gap-6">
            <div class="premium-card bg-linen p-5 dark:bg-white/5 sm:p-6">
                <h2 class="theme-title text-2xl font-extrabold text-cocoa dark:text-cream">{{ __('home.product.details') }}</h2>
                <p class="theme-muted mt-3 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ $product['description'] }}</p>

                @if (! empty(data_get($product, 'rich_content.highlights')))
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach (data_get($product, 'rich_content.highlights', []) as $highlight)
                            <div class="rounded-[1rem] border border-leaf/10 bg-white px-4 py-3 text-sm font-bold text-cocoa dark:border-white/10 dark:bg-white/5 dark:text-cream">
                                <span class="mr-2 text-leaf dark:text-meadow">✓</span>{{ $highlight }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <dl class="premium-card grid gap-4 bg-linen p-5 dark:bg-white/5 sm:grid-cols-2 sm:p-6">
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
                @if (data_get($product, 'commerce.max_order_quantity'))
                    <div>
                        <dt class="theme-subtle text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-cream/60">{{ $locale === 'fr' ? 'Quantité max.' : 'Max quantity' }}</dt>
                        <dd class="theme-title mt-2 text-sm font-extrabold text-cocoa dark:text-cream">{{ data_get($product, 'commerce.max_order_quantity') }}</dd>
                    </div>
                @endif
                @if (data_get($product, 'commerce.sales_count'))
                    <div>
                        <dt class="theme-subtle text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-cream/60">{{ $locale === 'fr' ? 'Ventes' : 'Sales' }}</dt>
                        <dd class="theme-title mt-2 text-sm font-extrabold text-cocoa dark:text-cream">{{ data_get($product, 'commerce.sales_count') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="mx-auto mt-6 grid max-w-7xl gap-4 lg:grid-cols-3">
            <article class="rounded-[1.25rem] border border-leaf/10 bg-linen p-5 dark:border-white/10 dark:bg-white/5">
                <h3 class="text-base font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Informations alimentaires' : 'Food information' }}</h3>
                <dl class="mt-4 space-y-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70">
                    @if (data_get($product, 'rich_content.ingredients'))
                        <div>
                            <dt class="font-bold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Ingrédients' : 'Ingredients' }}</dt>
                            <dd>{{ data_get($product, 'rich_content.ingredients') }}</dd>
                        </div>
                    @endif
                    @if (! empty(data_get($product, 'rich_content.allergens')))
                        <div>
                            <dt class="font-bold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Allergènes' : 'Allergens' }}</dt>
                            <dd>{{ implode(', ', data_get($product, 'rich_content.allergens', [])) }}</dd>
                        </div>
                    @endif
                    @if (! empty(data_get($product, 'rich_content.certifications')))
                        <div>
                            <dt class="font-bold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Certifications' : 'Certifications' }}</dt>
                            <dd>{{ implode(', ', data_get($product, 'rich_content.certifications', [])) }}</dd>
                        </div>
                    @endif
                </dl>
            </article>

            <article class="rounded-[1.25rem] border border-leaf/10 bg-linen p-5 dark:border-white/10 dark:bg-white/5">
                <h3 class="text-base font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Conservation et usage' : 'Storage and use' }}</h3>
                <div class="mt-4 space-y-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70">
                    @if (data_get($product, 'rich_content.storage_instructions'))
                        <p>{{ data_get($product, 'rich_content.storage_instructions') }}</p>
                    @endif
                    @if (data_get($product, 'rich_content.usage_instructions'))
                        <p>{{ data_get($product, 'rich_content.usage_instructions') }}</p>
                    @endif
                </div>
            </article>

            <article class="rounded-[1.25rem] border border-leaf/10 bg-linen p-5 dark:border-white/10 dark:bg-white/5">
                <h3 class="text-base font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Livraison et garanties' : 'Delivery and guarantees' }}</h3>
                <div class="mt-4 space-y-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70">
                    @if (data_get($shipping, 'delivery_zone'))
                        <p>{{ data_get($shipping, 'delivery_zone') }}</p>
                    @endif
                    @if (data_get($product, 'commerce.return_policy'))
                        <p>{{ data_get($product, 'commerce.return_policy') }}</p>
                    @endif
                    @if (data_get($product, 'commerce.guarantee'))
                        <p>{{ data_get($product, 'commerce.guarantee') }}</p>
                    @endif
                </div>
            </article>
        </div>
    </section>

    @if (! empty($relatedProducts))
        <section class="bg-linen px-4 py-12 dark:bg-ink sm:px-8 lg:py-14">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.spotlight.eyebrow') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ __('home.product.related_title') }}</h2>
                    </div>
                    <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary w-full sm:w-fit">{{ __('home.spotlight.cta') }}</a>
                </div>

                <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
                    @foreach ($relatedProducts as $related)
                        @php
                            $relatedRating = number_format((float) data_get($related, 'commerce.rating.average', 0), 1, ',', ' ');
                            $relatedReviewCount = (int) data_get($related, 'commerce.rating.count', 0);
                        @endphp
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $related['slug']]) }}" class="group min-w-[250px] rounded-[1.25rem] border border-leaf/10 bg-white p-4 transition hover:shadow-xl dark:border-white/10 dark:bg-white/5 lg:min-w-0" itemscope itemtype="https://schema.org/Product">
                            <img class="h-40 w-full rounded-[1rem] object-cover sm:h-48" src="{{ $related['primary_image']['url'] ?? '' }}" alt="{{ $related['primary_image']['alt_text'] ?? $related['name'] }}" loading="lazy" decoding="async" itemprop="image">
                            <div class="mt-4 flex items-start justify-between gap-4">
                                <h3 class="line-clamp-2 text-base font-extrabold text-cocoa transition group-hover:text-leaf dark:text-cream sm:text-lg" itemprop="name">{{ $related['name'] }}</h3>
                                <span class="shrink-0 font-extrabold text-leaf">{{ $related['formatted_price'] }}</span>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3 text-xs font-bold text-cocoa/55 dark:text-cream/55" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                                <span class="text-leaf dark:text-meadow" aria-label="{{ $relatedRating }}/5">★★★★★</span>
                                <span><span itemprop="ratingValue">{{ $relatedRating }}</span> · {{ $relatedReviewCount }} {{ $locale === 'fr' ? 'avis' : 'reviews' }}</span>
                                <meta itemprop="reviewCount" content="{{ $relatedReviewCount }}">
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
