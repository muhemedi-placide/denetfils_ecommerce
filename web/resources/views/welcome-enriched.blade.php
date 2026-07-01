@extends('layouts.shop')

@section('title', data_get($seoPayload ?? [], 'meta.title', __('home.meta.title')))
@section('description', data_get($seoPayload ?? [], 'meta.description', __('home.meta.description')))
@section('canonical', data_get($seoPayload ?? [], 'canonical', route('home.localized', ['locale' => $locale])))

@section('content')
    @php
        $brandName = config('app.name');
        $shopUrl = route('shop.index', ['locale' => $locale]);
        $images = [
            asset('assets/products/product-pikliz.jpg'),
            asset('assets/products/product-epis.jpg'),
            asset('assets/products/product-rice.jpg'),
            asset('assets/products/product-mango.jpg'),
            asset('assets/products/product-plantain.jpg'),
            asset('assets/products/product-spices.jpg'),
        ];
        $featuredProducts = array_values(array_slice($products ?? [], 0, 6));
        $fallbackProducts = $locale === 'fr' ? [
            ['name' => 'Pikliz Piment Bonda', 'formatted_price' => '8,90 €', 'short_description' => 'Le condiment qui réveille chaque assiette.', 'category' => ['name' => 'Best-seller']],
            ['name' => 'Épis Créole', 'formatted_price' => '7,50 €', 'short_description' => 'Une base aromatique riche et authentique.', 'category' => ['name' => 'Artisanal']],
            ['name' => 'Riz & Pois Rouges', 'formatted_price' => '5,20 €', 'short_description' => 'Une sélection de première qualité.', 'category' => ['name' => 'Épicerie']],
            ['name' => 'Nectar de Mangue', 'formatted_price' => '4,90 €', 'short_description' => 'Un nectar généreux au goût du fruit.', 'category' => ['name' => 'Nouveau']],
            ['name' => 'Bananes Plantains', 'formatted_price' => '3,40 €', 'short_description' => 'À frire, bouillir ou écraser.', 'category' => ['name' => 'Frais']],
            ['name' => 'Mélange Colombo', 'formatted_price' => '9,80 €', 'short_description' => 'Le curry parfumé des îles.', 'category' => ['name' => 'Épices']],
        ] : [
            ['name' => 'Bonda Pepper Pikliz', 'formatted_price' => '€8.90', 'short_description' => 'The condiment that wakes up every plate.', 'category' => ['name' => 'Best seller']],
            ['name' => 'Creole Epis', 'formatted_price' => '€7.50', 'short_description' => 'A rich and authentic aromatic base.', 'category' => ['name' => 'Artisan']],
            ['name' => 'Rice & Red Beans', 'formatted_price' => '€5.20', 'short_description' => 'A carefully selected pantry essential.', 'category' => ['name' => 'Grocery']],
            ['name' => 'Mango Nectar', 'formatted_price' => '€4.90', 'short_description' => 'A generous nectar with real fruit flavor.', 'category' => ['name' => 'New']],
            ['name' => 'Plantains', 'formatted_price' => '€3.40', 'short_description' => 'Perfect for frying, boiling or mashing.', 'category' => ['name' => 'Fresh']],
            ['name' => 'Colombo Spice Blend', 'formatted_price' => '€9.80', 'short_description' => 'The fragrant curry blend of the islands.', 'category' => ['name' => 'Spices']],
        ];
        if (count($featuredProducts) < 6) {
            $featuredProducts = array_replace($fallbackProducts, $featuredProducts);
        }
    @endphp

    <div class="store-container pb-2">
        <section class="store-promo" aria-label="Promotion">
            <div class="flex items-center gap-3">
                <x-icon name="gift" class="h-8 w-8 shrink-0" />
                <span>
                    {{ $locale === 'fr' ? 'Livraison flexible · Paiement sécurisé · Boutique' : 'Flexible delivery · Secure payment · Shop' }}
                    <strong class="store-promo-code">{{ $brandName }}</strong>
                </span>
            </div>
            <div class="flex items-center gap-3"><x-icon name="truck" class="h-8 w-8" /> {{ $locale === 'fr' ? 'Domicile, relais ou locker selon disponibilité' : 'Home, pickup point or locker depending on availability' }}</div>
        </section>

        <section class="store-hero">
            <div>
                <p class="section-kicker mb-4">{{ $locale === 'fr' ? 'Boutique agroalimentaire moderne' : 'Modern food store' }}</p>
                <h1>
                    {{ $locale === 'fr' ? 'Des saveurs' : 'Authentic' }}
                    <span class="store-hero-accent">{{ $locale === 'fr' ? 'authentiques' : 'flavors' }}</span>
                    {{ $locale === 'fr' ? 'simples à commander.' : 'made easy to order.' }}
                </h1>
                <p>{{ $locale === 'fr' ? $brandName.' aide le client à comprendre vite les produits, la livraison et le parcours de commande avant de passer à l’action.' : $brandName.' helps customers quickly understand products, delivery and the order journey before taking action.' }}</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ $shopUrl }}" class="store-button" wire:navigate.hover><x-icon name="sparkles" class="h-4 w-4" /> {{ $locale === 'fr' ? 'Explorer la boutique' : 'Explore the shop' }}</a>
                    <a href="#products" class="store-button store-button-outline"><x-icon name="store" class="h-4 w-4" /> {{ $locale === 'fr' ? 'Voir les produits populaires' : 'View popular products' }}</a>
                </div>
            </div>
            <div class="store-hero-media relative">
                <img class="ml-auto h-64 w-full max-w-md rounded-3xl object-cover shadow-tropical" src="{{ asset('assets/products/hero-basket.jpg') }}" alt="{{ $brandName }}">
                <span class="absolute -bottom-4 -left-3 rounded-full bg-white px-5 py-3 font-bold text-leaf shadow-xl dark:bg-cocoa">{{ $locale === 'fr' ? 'Expérience claire' : 'Clear experience' }}</span>
            </div>
        </section>

        <h2 class="store-section-heading" id="products"><x-icon name="store" class="store-accent h-8 w-8" /> {{ $locale === 'fr' ? 'Produits populaires' : 'Popular products' }}</h2>
        <section class="store-products-grid" aria-labelledby="products">
            @foreach ($featuredProducts as $index => $product)
                @php
                    $href = ! empty($product['slug'] ?? null)
                        ? route('products.show', ['locale' => $locale, 'slug' => $product['slug']])
                        : $shopUrl;
                    $image = data_get($product, 'primary_image.url') ?: data_get($product, 'image.url') ?: data_get($product, 'images.0.url') ?: $images[$index % count($images)];
                    $displayPrice = preg_replace('/\bEUR\b/u', '€', (string) ($product['formatted_price'] ?? ''));
                @endphp
                <article class="store-product-card group">
                    <a class="store-card-image block" href="{{ $href }}" wire:navigate.hover>
                        <img src="{{ $image }}" alt="{{ $product['name'] }}" loading="lazy" decoding="async">
                    </a>
                    <div class="store-card-body">
                        <span class="store-badge">{{ data_get($product, 'category.name', $locale === 'fr' ? 'Sélection' : 'Selection') }}</span>
                        <h3 class="store-card-title"><a href="{{ $href }}" wire:navigate.hover>{{ $product['name'] }}</a></h3>
                        <p class="store-card-copy">{{ data_get($product, 'short_description', $locale === 'fr' ? 'Produit sélectionné pour une commande simple et rapide.' : 'Selected product for a simple and fast order.') }}</p>
                        <div class="mb-4 mt-auto"><strong class="store-price">{{ $displayPrice }}</strong></div>
                        <a href="{{ $href }}" class="store-button store-button-outline w-full" wire:navigate.hover><x-icon name="plus" class="h-4 w-4" /> {{ $locale === 'fr' ? 'Voir le produit' : 'View product' }}</a>
                    </div>
                </article>
            @endforeach
        </section>

        @include('partials.home.conversion-sections', ['brandName' => $brandName, 'shopUrl' => $shopUrl])

        <h2 class="store-section-heading"><x-icon name="heart" class="store-accent h-8 w-8" /> {{ $locale === 'fr' ? 'Avis & confiance' : 'Reviews & trust' }}</h2>
        <section class="grid gap-6 md:grid-cols-2">
            <article class="store-review-card">
                <p class="text-xl text-leaf">★★★★★</p>
                <p class="my-4 italic text-cocoa/70 dark:text-cream/70">« {{ $locale === 'fr' ? 'La boutique est claire, rapide et rassurante avant de commander.' : 'The shop is clear, fast and reassuring before ordering.' }} »</p>
                <strong class="text-leaf">— Clara M.</strong>
            </article>
            <article class="store-review-card">
                <p class="text-xl text-leaf">★★★★☆</p>
                <p class="my-4 italic text-cocoa/70 dark:text-cream/70">« {{ $locale === 'fr' ? 'Je comprends mieux les produits, la livraison et le suivi.' : 'I better understand the products, delivery and tracking.' }} »</p>
                <strong class="text-leaf">— Lucas D.</strong>
            </article>
        </section>
    </div>
@endsection
