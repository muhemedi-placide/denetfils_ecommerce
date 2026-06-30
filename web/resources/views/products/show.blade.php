@extends('layouts.shop')

@php
    $productImages = [
        asset('assets/products/product-pikliz.jpg'),
        asset('assets/products/product-epis.jpg'),
        asset('assets/products/product-rice.jpg'),
        asset('assets/products/product-mango.jpg'),
        asset('assets/products/product-plantain.jpg'),
        asset('assets/products/product-spices.jpg'),
    ];

    $productSlug = (string) ($product['slug'] ?? '');
    $productName = (string) ($product['name'] ?? '');
    $imageIndex = abs(crc32($productSlug ?: $productName)) % count($productImages);

    if (str_contains($productSlug, 'pikliz') || str_contains(strtolower($productName), 'pikliz')) {
        $imageIndex = 0;
    }

    $apiHeroImage = data_get($product, 'primary_image.url') ?: data_get($product, 'image.url') ?: data_get($product, 'images.0.url');
    $heroImage = $apiHeroImage ?: $productImages[$imageIndex];
    $ratingAverage = (float) data_get($product, 'commerce.rating.average', 4.8);
    $ratingCount = (int) data_get($product, 'commerce.rating.count', 64);
    $ratingLabel = number_format($ratingAverage, 1, ',', ' ');
    $isAvailable = (bool) data_get($product, 'commerce.is_available', ((int) ($product['stock_quantity'] ?? 0)) > 0);
    $categoryName = data_get($product, 'category.name', $locale === 'fr' ? 'Sauces' : 'Sauces');
    $origin = $product['origin'] ?? ($locale === 'fr' ? 'Origine Haiti' : 'Haiti origin');
    $relatedFallbacks = [
        ['name' => 'Epis Creole Vert', 'formatted_price' => '7.50€', 'short_description' => 'La base de toutes les recettes', 'origin' => 'Martinique', 'category' => ['name' => 'Epices'], 'image' => $productImages[1]],
        ['name' => 'Riz & Pois Rouges', 'formatted_price' => '5.20€', 'short_description' => 'Selection 1ere qualite', 'origin' => 'Guadeloupe', 'category' => ['name' => 'Epicerie'], 'image' => $productImages[2]],
        ['name' => 'Nectar de Mangue', 'formatted_price' => '4.90€', 'short_description' => '100% fruit, 0% sucre ajoute', 'origin' => 'Cote d Ivoire', 'category' => ['name' => 'Boissons'], 'image' => $productImages[3]],
    ];
@endphp

@section('title', data_get($product, 'seo.meta.title', $product['name'] . ' | ' . config('shop.name')))
@section('description', data_get($product, 'seo.meta.description', $product['description'] ?? $product['short_description'] ?? ''))
@section('canonical', data_get($product, 'seo.canonical', route('products.show', ['locale' => $locale, 'slug' => $product['slug']])))
@section('og_type', data_get($product, 'seo.open_graph.type', 'product'))
@section('og_image', $heroImage)
@section('preload_image', $heroImage)

@section('content')
    <section class="bg-cream px-4 py-10 dark:bg-ink sm:px-8 lg:py-16">
        <div class="mx-auto max-w-7xl">
            <nav class="flex items-center gap-2 text-xs font-black uppercase tracking-wide text-forest/55 dark:text-cream/55" aria-label="Breadcrumb">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-forest" wire:navigate.hover>{{ __('home.nav.home') }}</a>
                <span>-</span>
                <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="transition hover:text-forest" wire:navigate.hover>{{ __('home.nav.shop') }}</a>
                <span>-</span>
                <span class="text-forest dark:text-meadow">{{ $product['name'] }}</span>
            </nav>

            <div class="mt-16 grid gap-12 lg:grid-cols-[1fr_1fr] lg:items-start">
                <div class="relative overflow-hidden rounded-[2rem] border-[5px] border-forest bg-sunshine shadow-sm">
                    <img class="aspect-square w-full object-cover" src="{{ $heroImage }}" alt="{{ $product['name'] }}" width="900" height="900" fetchpriority="high" decoding="async">
                    <span class="absolute left-5 top-5 rounded-full bg-forest px-4 py-2 text-[11px] font-black uppercase tracking-wide text-cream">Best-seller</span>
                </div>

                <div class="pt-1 lg:pt-0">
                    <p class="text-xs font-black uppercase tracking-[0.35em] text-coral">{{ $categoryName }} - {{ $origin }}</p>
                    <h1 class="mt-5 max-w-2xl text-5xl font-black leading-[0.98] tracking-tight text-forest dark:text-meadow sm:text-6xl lg:text-7xl">{{ $product['name'] }}</h1>
                    <p class="mt-4 text-lg font-semibold text-forest/62 dark:text-cream/68">{{ $product['short_description'] ?? ($locale === 'fr' ? 'Le condiment qui reveille tout' : 'The condiment that wakes everything up') }}</p>

                    <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-coral/10 px-4 py-2 text-xs font-black uppercase tracking-wide text-coral">
                        <span>🔥🔥🔥</span>
                        <span>{{ $locale === 'fr' ? 'Tres piquant' : 'Very spicy' }}</span>
                    </div>

                    <p class="mt-7 max-w-2xl text-base font-semibold leading-8 text-forest/75 dark:text-cream/75">
                        {{ $product['description'] ?? ($locale === 'fr' ? 'Notre pikliz traditionnel, fermente lentement avec piments bonda, chou et carottes. Une claque tropicale dans chaque cuilleree.' : 'Our traditional pikliz, slowly fermented with hot peppers, cabbage and carrots. A tropical kick in every spoon.') }}
                    </p>

                    <livewire:shop.product-purchase-panel :locale="$locale" :product="$product" />
                </div>
            </div>
        </div>
    </section>

    <section class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <h2 class="max-w-4xl text-4xl font-black tracking-tight text-forest dark:text-meadow sm:text-5xl">
                {{ $locale === 'fr' ? 'Ca pourrait aussi vous plaire' : 'You may also like' }}
            </h2>

            <div class="mt-9 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach (array_slice($relatedProducts ?: $relatedFallbacks, 0, 3) as $index => $related)
                    @php
                        $relatedImage = data_get($related, 'primary_image.url') ?: data_get($related, 'image.url') ?: data_get($related, 'images.0.url') ?: ($related['image'] ?? $productImages[($index + 1) % count($productImages)]);
                        $relatedHref = ! empty($related['slug'] ?? null) ? route('products.show', ['locale' => $locale, 'slug' => $related['slug']]) : route('shop.index', ['locale' => $locale]);
                        $relatedRating = number_format((float) data_get($related, 'commerce.rating.average', 4.8), 1, ',', ' ');
                    @endphp
                    <article class="group overflow-hidden rounded-[1.6rem] border border-forest/10 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-tropical dark:border-white/10 dark:bg-white/5">
                        <a href="{{ $relatedHref }}" class="relative block overflow-hidden" wire:navigate.hover>
                            <img class="h-80 w-full object-cover transition duration-500 group-hover:scale-[1.04]" src="{{ $relatedImage }}" alt="{{ $related['name'] }}" loading="lazy" decoding="async">
                            <span class="absolute left-4 top-4 rounded-full bg-forest px-4 py-2 text-[11px] font-black uppercase tracking-wide text-cream">{{ $index === 0 ? 'Best-seller' : ($locale === 'fr' ? 'Nouveau' : 'New') }}</span>
                            @if ($index === 0)<span class="absolute right-4 top-4 rounded-full bg-cream px-3 py-1 text-xs font-black text-coral">🔥</span>@endif
                        </a>
                        <div class="p-6">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-coral">{{ data_get($related, 'category.name', 'Epicerie') }} - {{ $related['origin'] ?? config('shop.name') }}</p>
                            <h3 class="mt-2 text-2xl font-black leading-tight text-forest dark:text-meadow">{{ $related['name'] }}</h3>
                            <p class="mt-1 line-clamp-1 text-sm font-semibold text-forest/65 dark:text-cream/70">{{ $related['short_description'] ?? $related['description'] ?? 'Produit authentique du marche.' }}</p>
                            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-forest/65 dark:text-cream/70"><span class="text-sunshine">★★★★★</span><span>{{ $relatedRating }} - {{ 64 + ($index * 44) }} {{ $locale === 'fr' ? 'avis' : 'reviews' }}</span></div>
                            <div class="mt-3 flex items-center gap-2 text-xs font-black uppercase tracking-wide text-forest dark:text-meadow"><span class="h-2 w-2 rounded-full bg-forest dark:bg-meadow"></span><span>{{ $locale === 'fr' ? 'En stock' : 'In stock' }}</span></div>
                            <div class="mt-7 flex items-center justify-between gap-4"><strong class="text-3xl font-black tracking-tight text-forest dark:text-meadow">{{ $related['formatted_price'] }}</strong><a href="{{ $relatedHref }}" class="rounded-full bg-forest px-5 py-3 text-xs font-black text-cream transition hover:bg-leaf" wire:navigate.hover>+ {{ $locale === 'fr' ? 'Ajouter' : 'Add' }}</a></div>
                            <a href="{{ $relatedHref }}" class="mt-5 block text-center text-xs font-black uppercase tracking-[0.22em] text-forest/65 transition hover:text-forest" wire:navigate.hover>{{ $locale === 'fr' ? 'Voir le produit' : 'View product' }}</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
