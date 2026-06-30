@extends('layouts.shop')

@section('title', data_get($seoPayload ?? [], 'meta.title', __('home.meta.title')))
@section('description', data_get($seoPayload ?? [], 'meta.description', __('home.meta.description')))
@section('canonical', data_get($seoPayload ?? [], 'canonical', route('home.localized', ['locale' => $locale])))

@section('content')
    @php
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
                    {{ $locale === 'fr' ? 'Livraison offerte dès 49€ · Code' : 'Free shipping from €49 · Code' }}
                    <strong class="store-promo-code">PEYI10</strong>
                    -10%
                </span>
            </div>
            <div class="flex items-center gap-3"><x-icon name="truck" class="h-8 w-8" /> {{ $locale === 'fr' ? 'Expédition sous 24h' : 'Ships within 24h' }}</div>
        </section>

        <section class="store-hero">
            <div>
                <h1>
                    {{ $locale === 'fr' ? 'Saveurs' : 'Authentic' }}
                    <span class="store-hero-accent">{{ $locale === 'fr' ? 'artisanales' : 'artisan' }}</span>
                    {{ $locale === 'fr' ? 'du pays' : 'flavors' }}
                </h1>
                <p>{{ $locale === 'fr' ? 'Découvrez notre sélection de sauces, épices, boissons et douceurs authentiques.' : 'Discover our selection of authentic sauces, spices, drinks and treats.' }}</p>
                <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="store-button" wire:navigate.hover><x-icon name="sparkles" class="h-4 w-4" /> {{ $locale === 'fr' ? 'Explorer' : 'Explore' }}</a>
            </div>
            <div class="store-hero-media relative">
                <img class="ml-auto h-64 w-full max-w-md rounded-[32px] object-cover shadow-2xl" src="{{ asset('assets/products/hero-basket.jpg') }}" alt="{{ config('shop.name') }}">
                <span class="absolute -bottom-4 -left-3 rounded-full bg-white px-5 py-3 font-bold text-[#f97316] shadow-xl dark:bg-[#2c2824]">{{ $locale === 'fr' ? '100% authentique' : '100% authentic' }}</span>
            </div>
        </section>

        <h2 class="store-section-heading" id="products"><x-icon name="store" class="store-accent h-8 w-8" /> {{ $locale === 'fr' ? 'Nos délices' : 'Our favorites' }}</h2>
        <section class="store-products-grid" aria-labelledby="products">
            @foreach ($featuredProducts as $index => $product)
                @php
                    $href = ! empty($product['slug'] ?? null)
                        ? route('products.show', ['locale' => $locale, 'slug' => $product['slug']])
                        : route('shop.index', ['locale' => $locale]);
                    $image = data_get($product, 'primary_image.url')
                        ?: data_get($product, 'image.url')
                        ?: data_get($product, 'images.0.url')
                        ?: $images[$index % count($images)];
                    $gallery = collect([$image])
                        ->merge(collect($product['images'] ?? [])->pluck('url'))
                        ->push($image.(str_contains($image, '?') ? '&' : '?').'view=detail&crop=left')
                        ->push($image.(str_contains($image, '?') ? '&' : '?').'view=detail&crop=right')
                        ->filter()
                        ->unique()
                        ->take(3)
                        ->values();
                    $productId = (int) ($product['id'] ?? 0);
                    $displayPrice = preg_replace('/\bEUR\b/u', '€', (string) ($product['formatted_price'] ?? ''));
                @endphp
                <article class="store-product-card group">
                    <a class="store-card-image block" href="{{ $href }}" wire:navigate.hover>
                        @foreach ($gallery as $galleryImage)
                            <img src="{{ $galleryImage }}" alt="{{ $product['name'] }}" loading="lazy" decoding="async">
                        @endforeach
                    </a>
                    <div class="store-card-body">
                        <span class="store-badge">{{ data_get($product, 'category.name', $locale === 'fr' ? 'Sélection' : 'Selection') }}</span>
                        <h3 class="store-card-title"><a href="{{ $href }}" wire:navigate.hover>{{ $product['name'] }}</a></h3>
                        @if(! empty($product['origin']))<span class="sr-only">{{ $product['origin'] }}</span>@endif
                        <div class="mb-4 mt-auto"><strong class="store-price">{{ $displayPrice }}</strong></div>
                        <button
                            type="button"
                            class="store-button store-button-outline w-full"
                            @if($productId > 0) x-on:click="window.Livewire?.dispatch('cart:add', { productId: {{ $productId }} })" @else disabled @endif
                        ><x-icon name="plus" class="h-4 w-4" /> {{ $locale === 'fr' ? 'Ajouter' : 'Add to cart' }}</button>
                    </div>
                </article>
            @endforeach
        </section>

        <h2 class="store-section-heading"><x-icon name="heart" class="store-accent h-8 w-8" /> {{ $locale === 'fr' ? 'Avis gourmands' : 'Customer favorites' }}</h2>
        <section class="grid gap-6 md:grid-cols-2">
            <article class="store-review-card">
                <p class="text-xl text-[#f97316]">★★★★★</p>
                <p class="my-4 italic" style="color:var(--store-muted)">« {{ $locale === 'fr' ? 'Les produits sont incroyables et la livraison très rapide.' : 'The products are amazing and delivery was very fast.' }} »</p>
                <strong class="text-[#f97316]">— Clara M.</strong>
            </article>
            <article class="store-review-card">
                <p class="text-xl text-[#f97316]">★★★★☆</p>
                <p class="my-4 italic" style="color:var(--store-muted)">« {{ $locale === 'fr' ? 'J’ai retrouvé les saveurs de chez moi. Un vrai régal.' : 'I found the flavors of home again. A real treat.' }} »</p>
                <strong class="text-[#f97316]">— Lucas D.</strong>
            </article>
        </section>
    </div>
@endsection
