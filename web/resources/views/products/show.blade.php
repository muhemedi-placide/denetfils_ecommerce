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
    <section class="soft-grid px-4 py-10 dark:bg-ink sm:px-8 lg:py-16">
        <div class="mx-auto max-w-7xl">
            <nav class="mobile-scrollbarless flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm font-semibold text-cocoa/60 dark:text-cream/60" aria-label="Breadcrumb">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-forest" wire:navigate.hover>{{ __('home.nav.home') }}</a>
                <span>/</span>
                <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="transition hover:text-forest" wire:navigate.hover>{{ __('home.nav.shop') }}</a>
                <span>/</span>
                <span class="text-forest">{{ $product['name'] }}</span>
            </nav>

            <div class="mt-8 grid gap-8 lg:grid-cols-[1.04fr_0.96fr] lg:items-start">
                <div class="space-y-4">
                    <div class="relative overflow-hidden rounded-[2rem] border-[6px] border-forest bg-white shadow-tropical dark:bg-white/5">
                        @if (! empty($primaryImage['url']))
                            <img
                                class="aspect-[4/3] w-full object-cover"
                                src="{{ $primaryImage['url'] }}"
                                alt="{{ $primaryImage['alt_text'] ?? $product['name'] }}"
                                width="{{ $primaryImage['width'] ?? 1200 }}"
                                height="{{ $primaryImage['height'] ?? 900 }}"
                                fetchpriority="{{ $primaryImage['fetch_priority'] ?? 'high' }}"
                                decoding="async"
                            >
                        @else
                            <div class="grid aspect-[4/3] place-items-center bg-sunshine/35 text-5xl font-black text-forest">DF</div>
                        @endif
                        <div class="absolute left-5 top-5 flex flex-wrap gap-2">
                            @if (! empty($product['origin']))
                                <span class="badge-tropical bg-cream text-forest shadow-sm">{{ $product['origin'] }}</span>
                            @endif
                            <span class="badge-tropical {{ $isAvailable ? 'bg-forest text-cream' : 'bg-coral text-cream' }}">
                                {{ $isAvailable ? ($locale === 'fr' ? 'En stock' : 'In stock') : ($locale === 'fr' ? 'Rupture' : 'Out of stock') }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="utility-section p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-forest sm:text-xs">{{ __('home.product.category') }}</p>
                            <p class="mt-2 truncate text-xs font-black text-cocoa dark:text-cream sm:text-sm">{{ $product['category']['name'] ?? '-' }}</p>
                        </div>
                        <div class="utility-section p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-forest sm:text-xs">{{ __('home.product.stock') }}</p>
                            <p class="mt-2 truncate text-xs font-black text-cocoa dark:text-cream sm:text-sm">{{ $product['stock_quantity'] }}</p>
                        </div>
                        <div class="utility-section p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-forest sm:text-xs">{{ __('home.product.weight') }}</p>
                            <p class="mt-2 truncate text-xs font-black text-cocoa dark:text-cream sm:text-sm">{{ $product['weight_grams'] }} g</p>
                        </div>
                    </div>
                </div>

                <div class="lg:sticky lg:top-32">
                    <p class="section-kicker">{{ $product['origin'] }}</p>
                    <h1 class="brand-display mt-4 text-4xl uppercase text-forest dark:text-meadow sm:text-6xl">{{ $product['name'] }}</h1>
                    <div class="mt-4 flex flex-wrap items-center gap-3 text-sm font-bold text-cocoa/65 dark:text-cream/65">
                        <span class="text-sunshine" aria-label="{{ $ratingLabel }}/5">★★★★★</span>
                        <span>{{ $ratingLabel }}/5</span>
                        <a href="#product-reviews-title" class="underline decoration-leaf/30 underline-offset-4 transition hover:text-forest">
                            {{ $ratingCount }} {{ $locale === 'fr' ? 'avis clients' : 'customer reviews' }}
                        </a>
                    </div>
                    <p class="mt-5 text-base font-semibold leading-8 text-cocoa/70 dark:text-cream/70">{{ $product['short_description'] ?? $product['description'] }}</p>

                    @if (! empty(data_get($product, 'rich_content.badges')))
                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach (data_get($product, 'rich_content.badges', []) as $badge)
                                <span class="rounded-full bg-mint px-3 py-1.5 text-xs font-black text-forest dark:bg-white/10 dark:text-meadow">{{ $badge }}</span>
                            @endforeach
                        </div>
                    @endif

                    <livewire:shop.product-purchase-panel :locale="$locale" :product="$product" />
                </div>
            </div>
        </div>
    </section>

    <section class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-[1fr_0.8fr]">
            <div class="utility-section bg-linen dark:bg-white/5">
                <p class="section-kicker">{{ __('home.product.details') }}</p>
                <h2 class="mt-3 text-3xl font-black text-forest dark:text-meadow">{{ __('home.product.details_body') }}</h2>
                <p class="mt-5 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ $product['description'] }}</p>

                @if (! empty(data_get($product, 'rich_content.highlights')))
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach (data_get($product, 'rich_content.highlights', []) as $highlight)
                            <div class="rounded-[1rem] border border-leaf/10 bg-white px-4 py-3 text-sm font-bold text-cocoa dark:border-white/10 dark:bg-white/5 dark:text-cream">
                                <span class="mr-2 text-forest dark:text-meadow">✓</span>{{ $highlight }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <dl class="utility-section grid gap-5 bg-linen dark:bg-white/5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ __('home.product.category') }}</dt>
                    <dd class="mt-2 text-sm font-black text-cocoa dark:text-cream">{{ $product['category']['name'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ __('home.product.sku') }}</dt>
                    <dd class="mt-2 text-sm font-black text-cocoa dark:text-cream">{{ $product['sku'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ __('home.product.stock') }}</dt>
                    <dd class="mt-2 text-sm font-black text-cocoa dark:text-cream">{{ $product['stock_quantity'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ __('home.product.weight') }}</dt>
                    <dd class="mt-2 text-sm font-black text-cocoa dark:text-cream">{{ $product['weight_grams'] }} g</dd>
                </div>
                @if (data_get($product, 'commerce.max_order_quantity'))
                    <div>
                        <dt class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Quantité max.' : 'Max quantity' }}</dt>
                        <dd class="mt-2 text-sm font-black text-cocoa dark:text-cream">{{ data_get($product, 'commerce.max_order_quantity') }}</dd>
                    </div>
                @endif
                @if (data_get($product, 'commerce.sales_count'))
                    <div>
                        <dt class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Ventes' : 'Sales' }}</dt>
                        <dd class="mt-2 text-sm font-black text-cocoa dark:text-cream">{{ data_get($product, 'commerce.sales_count') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="mx-auto mt-6 grid max-w-7xl gap-5 lg:grid-cols-3">
            <article class="utility-section">
                <h3 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Informations alimentaires' : 'Food information' }}</h3>
                <dl class="mt-4 space-y-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70">
                    @if (data_get($product, 'rich_content.ingredients'))
                        <div><dt class="font-black text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Ingrédients' : 'Ingredients' }}</dt><dd>{{ data_get($product, 'rich_content.ingredients') }}</dd></div>
                    @endif
                    @if (! empty(data_get($product, 'rich_content.allergens')))
                        <div><dt class="font-black text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Allergènes' : 'Allergens' }}</dt><dd>{{ implode(', ', data_get($product, 'rich_content.allergens', [])) }}</dd></div>
                    @endif
                    @if (! empty(data_get($product, 'rich_content.certifications')))
                        <div><dt class="font-black text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Certifications' : 'Certifications' }}</dt><dd>{{ implode(', ', data_get($product, 'rich_content.certifications', [])) }}</dd></div>
                    @endif
                </dl>
            </article>

            <article class="utility-section">
                <h3 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Conservation et usage' : 'Storage and use' }}</h3>
                <div class="mt-4 space-y-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70">
                    @if (data_get($product, 'rich_content.storage_instructions'))<p>{{ data_get($product, 'rich_content.storage_instructions') }}</p>@endif
                    @if (data_get($product, 'rich_content.usage_instructions'))<p>{{ data_get($product, 'rich_content.usage_instructions') }}</p>@endif
                </div>
            </article>

            <article class="utility-section">
                <h3 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Livraison et garanties' : 'Delivery and guarantees' }}</h3>
                <div class="mt-4 space-y-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70">
                    @if (data_get($shipping, 'delivery_zone'))<p>{{ data_get($shipping, 'delivery_zone') }}</p>@endif
                    @if (data_get($product, 'commerce.return_policy'))<p>{{ data_get($product, 'commerce.return_policy') }}</p>@endif
                    @if (data_get($product, 'commerce.guarantee'))<p>{{ data_get($product, 'commerce.guarantee') }}</p>@endif
                </div>
            </article>
        </div>
    </section>

    @if (! empty($relatedProducts))
        <section class="bg-linen px-4 py-14 dark:bg-[#163319] sm:px-8 lg:py-16">
            <div class="mx-auto max-w-7xl">
                <div class="mb-7 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="section-kicker">{{ __('home.spotlight.eyebrow') }}</p>
                        <h2 class="mt-3 text-3xl font-black text-forest dark:text-meadow">{{ __('home.product.related_title') }}</h2>
                    </div>
                    <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary w-full sm:w-fit">{{ __('home.spotlight.cta') }}</a>
                </div>

                <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
                    @foreach ($relatedProducts as $related)
                        @php
                            $relatedRating = number_format((float) data_get($related, 'commerce.rating.average', 0), 1, ',', ' ');
                            $relatedReviewCount = (int) data_get($related, 'commerce.rating.count', 0);
                        @endphp
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $related['slug']]) }}" class="market-card group min-w-[260px] overflow-hidden bg-white dark:bg-white/5 lg:min-w-0" itemscope itemtype="https://schema.org/Product" wire:navigate.hover>
                            @if (! empty($related['primary_image']['url']))
                                <img class="h-52 w-full object-cover transition group-hover:scale-[1.04]" src="{{ $related['primary_image']['url'] }}" alt="{{ $related['primary_image']['alt_text'] ?? $related['name'] }}" loading="lazy" decoding="async" itemprop="image">
                            @else
                                <div class="grid h-52 place-items-center bg-sunshine/35 text-forest">DF</div>
                            @endif
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <h3 class="line-clamp-2 text-xl font-black text-forest transition group-hover:text-leaf dark:text-meadow" itemprop="name">{{ $related['name'] }}</h3>
                                    <span class="brand-display shrink-0 text-2xl text-forest dark:text-meadow">{{ $related['formatted_price'] }}</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3 text-xs font-bold text-cocoa/55 dark:text-cream/55" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                                    <span class="text-sunshine" aria-label="{{ $relatedRating }}/5">★★★★★</span>
                                    <span><span itemprop="ratingValue">{{ $relatedRating }}</span> · {{ $relatedReviewCount }} {{ $locale === 'fr' ? 'avis' : 'reviews' }}</span>
                                    <meta itemprop="reviewCount" content="{{ $relatedReviewCount }}">
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
