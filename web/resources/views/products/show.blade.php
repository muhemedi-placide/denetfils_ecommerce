@extends('layouts.shop')

@php
    $moodboardImages = [
        'https://moodboard-to-shop.lovable.app/assets/peppers-w9B84COi.jpg',
        'https://moodboard-to-shop.lovable.app/assets/product-epis-C1D47KI3.jpg',
        'https://moodboard-to-shop.lovable.app/assets/product-rice-BcMjvpU2.jpg',
        'https://moodboard-to-shop.lovable.app/assets/product-plantain-CnKnHz17.jpg',
        'https://moodboard-to-shop.lovable.app/assets/product-spices-DL1CC1Hd.jpg',
        'https://moodboard-to-shop.lovable.app/assets/leaves-D-dPOddf.jpg',
        'https://moodboard-to-shop.lovable.app/assets/hero-market-B__fWu60.jpg',
    ];
    $heroImage = $moodboardImages[abs(crc32((string) ($product['slug'] ?? $product['name']))) % count($moodboardImages)];
    $ratingAverage = (float) data_get($product, 'commerce.rating.average', 4.8);
    $ratingCount = (int) data_get($product, 'commerce.rating.count', 0);
    $ratingLabel = number_format($ratingAverage, 1, ',', ' ');
    $isAvailable = (bool) data_get($product, 'commerce.is_available', ((int) ($product['stock_quantity'] ?? 0)) > 0);
    $shipping = data_get($product, 'commerce.shipping', []);
@endphp

@section('title', data_get($product, 'seo.meta.title', $product['name'] . ' | Marché Peyi'))
@section('description', data_get($product, 'seo.meta.description', $product['description']))
@section('canonical', data_get($product, 'seo.canonical', route('products.show', ['locale' => $locale, 'slug' => $product['slug']])))
@section('og_type', data_get($product, 'seo.open_graph.type', 'product'))
@section('og_image', $heroImage)
@section('preload_image', $heroImage)

@section('content')
    <section class="tropical-pattern px-4 py-10 sm:px-8 lg:py-16">
        <div class="mx-auto max-w-7xl">
            <nav class="mobile-scrollbarless flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm font-semibold text-cocoa/60 dark:text-cream/60" aria-label="Breadcrumb">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-forest" wire:navigate.hover>{{ __('home.nav.home') }}</a><span>/</span>
                <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="transition hover:text-forest" wire:navigate.hover>{{ __('home.nav.shop') }}</a><span>/</span>
                <span class="text-forest">{{ $product['name'] }}</span>
            </nav>

            <div class="mt-8 grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-start">
                <div class="space-y-4">
                    <div class="relative overflow-hidden rounded-[2rem] border-[6px] border-forest bg-white shadow-tropical dark:bg-white/5">
                        <img class="aspect-[4/3] w-full object-cover" src="{{ $heroImage }}" alt="{{ $product['name'] }}" width="1200" height="900" fetchpriority="high" decoding="async">
                        <div class="absolute left-5 top-5 flex flex-wrap gap-2">
                            @if (! empty($product['origin']))<span class="badge-tropical bg-cream text-forest shadow-sm">{{ $product['origin'] }}</span>@endif
                            <span class="badge-tropical {{ $isAvailable ? 'bg-forest text-cream' : 'bg-coral text-cream' }}">{{ $isAvailable ? ($locale === 'fr' ? 'En stock' : 'In stock') : ($locale === 'fr' ? 'Rupture' : 'Out of stock') }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="utility-section p-4"><p class="text-[10px] font-black uppercase tracking-[0.16em] text-forest sm:text-xs">{{ __('home.product.category') }}</p><p class="mt-2 truncate text-xs font-black text-cocoa dark:text-cream sm:text-sm">{{ $product['category']['name'] ?? '-' }}</p></div>
                        <div class="utility-section p-4"><p class="text-[10px] font-black uppercase tracking-[0.16em] text-forest sm:text-xs">{{ __('home.product.stock') }}</p><p class="mt-2 truncate text-xs font-black text-cocoa dark:text-cream sm:text-sm">{{ $product['stock_quantity'] }}</p></div>
                        <div class="utility-section p-4"><p class="text-[10px] font-black uppercase tracking-[0.16em] text-forest sm:text-xs">{{ __('home.product.weight') }}</p><p class="mt-2 truncate text-xs font-black text-cocoa dark:text-cream sm:text-sm">{{ $product['weight_grams'] }} g</p></div>
                    </div>
                </div>

                <div class="lg:sticky lg:top-32">
                    <p class="section-kicker">{{ $product['category']['name'] ?? ($locale === 'fr' ? 'Marché tropical' : 'Tropical market') }}</p>
                    <h1 class="brand-display mt-4 text-4xl uppercase text-forest dark:text-meadow sm:text-6xl">{{ $product['name'] }}</h1>
                    <div class="mt-4 flex flex-wrap items-center gap-3 text-sm font-bold text-cocoa/65 dark:text-cream/65"><span class="text-sunshine" aria-label="{{ $ratingLabel }}/5">★★★★★</span><span>{{ $ratingLabel }}/5</span><a href="#product-reviews-title" class="underline decoration-leaf/30 underline-offset-4 transition hover:text-forest">{{ $ratingCount }} {{ $locale === 'fr' ? 'avis clients' : 'customer reviews' }}</a></div>
                    <p class="mt-5 text-base font-semibold leading-8 text-cocoa/70 dark:text-cream/70">{{ $product['short_description'] ?? $product['description'] }}</p>
                    @if (! empty(data_get($product, 'rich_content.badges')))<div class="mt-5 flex flex-wrap gap-2">@foreach (data_get($product, 'rich_content.badges', []) as $badge)<span class="rounded-full bg-mint px-3 py-1.5 text-xs font-black text-forest dark:bg-white/10 dark:text-meadow">{{ $badge }}</span>@endforeach</div>@endif
                    <livewire:shop.product-purchase-panel :locale="$locale" :product="$product" />
                </div>
            </div>
        </div>
    </section>

    <section class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-[1fr_0.8fr]">
            <div class="utility-section bg-linen dark:bg-white/5"><p class="section-kicker">{{ __('home.product.details') }}</p><h2 class="mt-3 text-3xl font-black text-forest dark:text-meadow">{{ __('home.product.details_body') }}</h2><p class="mt-5 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ $product['description'] }}</p></div>
            <dl class="utility-section grid gap-5 bg-linen dark:bg-white/5 sm:grid-cols-2"><div><dt class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ __('home.product.category') }}</dt><dd class="mt-2 text-sm font-black text-cocoa dark:text-cream">{{ $product['category']['name'] ?? '-' }}</dd></div><div><dt class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ __('home.product.sku') }}</dt><dd class="mt-2 text-sm font-black text-cocoa dark:text-cream">{{ $product['sku'] }}</dd></div><div><dt class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ __('home.product.stock') }}</dt><dd class="mt-2 text-sm font-black text-cocoa dark:text-cream">{{ $product['stock_quantity'] }}</dd></div><div><dt class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ __('home.product.weight') }}</dt><dd class="mt-2 text-sm font-black text-cocoa dark:text-cream">{{ $product['weight_grams'] }} g</dd></div></dl>
        </div>
        <div class="mx-auto mt-6 grid max-w-7xl gap-5 lg:grid-cols-3"><article class="utility-section"><h3 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Informations alimentaires' : 'Food information' }}</h3><dl class="mt-4 space-y-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70">@if (data_get($product, 'rich_content.ingredients'))<div><dt class="font-black text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Ingrédients' : 'Ingredients' }}</dt><dd>{{ data_get($product, 'rich_content.ingredients') }}</dd></div>@endif @if (! empty(data_get($product, 'rich_content.allergens')))<div><dt class="font-black text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'Allergènes' : 'Allergens' }}</dt><dd>{{ implode(', ', data_get($product, 'rich_content.allergens', [])) }}</dd></div>@endif</dl></article><article class="utility-section"><h3 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Conservation et usage' : 'Storage and use' }}</h3><div class="mt-4 space-y-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70">@if (data_get($product, 'rich_content.storage_instructions'))<p>{{ data_get($product, 'rich_content.storage_instructions') }}</p>@endif @if (data_get($product, 'rich_content.usage_instructions'))<p>{{ data_get($product, 'rich_content.usage_instructions') }}</p>@endif</div></article><article class="utility-section"><h3 class="text-xl font-black text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Livraison et garanties' : 'Delivery and guarantees' }}</h3><div class="mt-4 space-y-3 text-sm leading-6 text-cocoa/70 dark:text-cream/70">@if (data_get($shipping, 'delivery_zone'))<p>{{ data_get($shipping, 'delivery_zone') }}</p>@endif @if (data_get($product, 'commerce.return_policy'))<p>{{ data_get($product, 'commerce.return_policy') }}</p>@endif @if (data_get($product, 'commerce.guarantee'))<p>{{ data_get($product, 'commerce.guarantee') }}</p>@endif</div></article></div>
    </section>

    @if (! empty($relatedProducts))
        <section class="bg-linen px-4 py-14 dark:bg-[#163319] sm:px-8 lg:py-16"><div class="mx-auto max-w-7xl"><div class="mb-7 flex flex-col gap-3 md:flex-row md:items-end md:justify-between"><div><p class="section-kicker">{{ __('home.spotlight.eyebrow') }}</p><h2 class="mt-3 text-3xl font-black text-forest dark:text-meadow">{{ __('home.product.related_title') }}</h2></div><a href="{{ route('shop.index', ['locale' => $locale]) }}" class="btn-secondary w-full sm:w-fit" wire:navigate.hover>{{ __('home.spotlight.cta') }}</a></div><div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">@foreach ($relatedProducts as $related) @php $relatedImage = $moodboardImages[$loop->index % count($moodboardImages)]; $relatedRating = number_format((float) data_get($related, 'commerce.rating.average', 4.8), 1, ',', ' '); @endphp <article class="market-card group min-w-[260px] overflow-hidden bg-white dark:bg-white/5"><a href="{{ route('products.show', ['locale' => $locale, 'slug' => $related['slug']]) }}" wire:navigate.hover><img class="h-48 w-full object-cover transition duration-500 group-hover:scale-[1.04]" src="{{ $relatedImage }}" alt="{{ $related['name'] }}" loading="lazy" decoding="async"><div class="p-5"><h3 class="line-clamp-2 text-xl font-black text-forest">{{ $related['name'] }}</h3><p class="mt-3 text-sunshine">★★★★★ <span class="text-xs font-bold text-cocoa/55 dark:text-cream/55">{{ $relatedRating }}</span></p><div class="mt-4 flex items-center justify-between gap-3"><span class="brand-display text-2xl text-forest">{{ $related['formatted_price'] }}</span><span class="btn-secondary px-4 py-2 text-xs">{{ $locale === 'fr' ? 'Voir' : 'View' }}</span></div></div></a></article>@endforeach</div></div></section>
    @endif
@endsection
