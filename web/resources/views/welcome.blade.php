@extends('layouts.shop')

@section('title', data_get($seoPayload ?? [], 'meta.title', __('home.meta.title')))
@section('description', data_get($seoPayload ?? [], 'meta.description', __('home.meta.description')))
@section('canonical', data_get($seoPayload ?? [], 'canonical', route('home.localized', ['locale' => $locale])))

@section('content')
    @php
        $productImages = [
            asset('assets/products/product-pikliz.jpg'),
            asset('assets/products/product-epis.jpg'),
            asset('assets/products/product-rice.jpg'),
            asset('assets/products/product-mango.jpg'),
            asset('assets/products/product-plantain.jpg'),
            asset('assets/products/product-spices.jpg'),
        ];

        $heroImage = asset('assets/products/hero-basket.jpg');
        $harvestImage = asset('assets/products/hero-market.jpg');
        $leavesImage = asset('assets/products/hero-market.jpg');
        $peppersImage = asset('assets/products/peppers.jpg');
        $recipeImages = [asset('assets/products/recipe-djondjon.jpg'), asset('assets/products/recipe-pikliz.jpg'), asset('assets/products/recipe-sauce.jpg')];

        $spotlightProducts = array_values(array_slice($products ?? [], 0, 6));
        $newProducts = array_values(array_slice($products ?? [], 2, 2));

        $fallbackProducts = [
            ['id' => null, 'slug' => null, 'name' => 'Pikliz Piment Bonda', 'formatted_price' => '8.90€', 'short_description' => 'Le condiment qui réveille tout', 'origin' => 'Haïti', 'category' => ['name' => 'Sauces']],
            ['id' => null, 'slug' => null, 'name' => 'Épis Créole Vert', 'formatted_price' => '7.50€', 'short_description' => 'La base de toutes les recettes', 'origin' => 'Martinique', 'category' => ['name' => 'Épices']],
            ['id' => null, 'slug' => null, 'name' => 'Riz & Pois Rouges', 'formatted_price' => '5.20€', 'short_description' => 'Sélection 1ère qualité', 'origin' => 'Guadeloupe', 'category' => ['name' => 'Épicerie']],
            ['id' => null, 'slug' => null, 'name' => 'Nectar de Mangue', 'formatted_price' => '4.90€', 'short_description' => '100% fruit, 0% sucre ajouté', 'origin' => "Côte d’Ivoire", 'category' => ['name' => 'Boissons']],
            ['id' => null, 'slug' => null, 'name' => 'Bananes Plantains', 'formatted_price' => '3.40€', 'short_description' => 'À frire, bouillir, écraser', 'origin' => 'Cameroun', 'category' => ['name' => 'Produits frais']],
            ['id' => null, 'slug' => null, 'name' => 'Mélange Colombo', 'formatted_price' => '9.80€', 'short_description' => 'Curry des îles', 'origin' => 'Guadeloupe', 'category' => ['name' => 'Épices']],
        ];

        if (count($spotlightProducts) < 6) {
            $spotlightProducts = array_replace($fallbackProducts, $spotlightProducts);
        }

        if (count($newProducts) < 2) {
            $newProducts = array_slice($fallbackProducts, 2, 2);
        }

        $categoryCards = [
            ['title' => 'Sauces & Pikliz', 'body' => 'Pikliz, sauces piquantes et condiments du pays.', 'icon' => '🌶️', 'class' => 'bg-[#fb5a4e] text-white'],
            ['title' => 'Épices Créoles', 'body' => 'Colombo, épis, mélanges traditionnels.', 'icon' => '🧂', 'class' => 'bg-[#f4668d] text-white'],
            ['title' => 'Boissons Tropicales', 'body' => 'Nectars, jus pressés et boissons des îles.', 'icon' => '🥭', 'class' => 'bg-[#ff9817] text-forest'],
            ['title' => 'Produits Frais', 'body' => 'Plantains, ignames, légumes pays de saison.', 'icon' => '🍌', 'class' => 'bg-forest text-white'],
            ['title' => 'Épicerie Sèche', 'body' => 'Riz, légumineuses, farines essentielles.', 'icon' => '🌾', 'class' => 'bg-caribbean text-white'],
            ['title' => 'Coffrets Cadeaux', 'body' => 'Sélections gourmandes à offrir.', 'icon' => '🎁', 'class' => 'bg-sunshine text-forest'],
        ];

        $recipes = [
            ['meta' => '45 min · facile', 'title' => 'Riz djon djon maison', 'body' => 'Le classique haïtien, parfumé aux champignons noirs.', 'image' => $recipeImages[0]],
            ['meta' => 'Astuces · 3 min', 'title' => 'Comment utiliser le pikliz ?', 'body' => '5 façons d’ajouter du peps à vos plats du quotidien.', 'image' => $recipeImages[1]],
            ['meta' => '20 min · facile', 'title' => 'Sauce créole épicée', 'body' => 'La sauce qui transforme un simple poisson grillé.', 'image' => $recipeImages[2]],
        ];

        $reviews = [
            ['name' => 'Naïma', 'product' => 'Pikliz Piment Bonda', 'text' => 'Les produits sont bien emballés et le goût est vraiment authentique. Je recommande à 100%.'],
            ['name' => 'Jean-Marc', 'product' => 'Mélange Colombo', 'text' => 'J’ai retrouvé les saveurs de chez moi. C’est exactement ce que je cherchais depuis des années.'],
            ['name' => 'Sandra', 'product' => 'Nectar de Mangue', 'text' => 'Livraison rapide et très bonne qualité. Le nectar de mangue est incroyable.'],
        ];
    @endphp

    <section id="home" class="relative overflow-hidden bg-cream px-4 py-14 sm:px-8 lg:py-24">
        <div class="absolute inset-0 opacity-45" style="background-image: radial-gradient(#124c20 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="absolute bottom-0 left-0 h-72 w-72 rounded-full bg-coral/20 blur-3xl"></div>
        <div class="absolute right-0 top-0 h-96 w-96 rounded-full bg-sunshine/25 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div>
                <p class="inline-flex rounded-full bg-forest/10 px-5 py-2 text-xs font-black uppercase tracking-[0.24em] text-forest">✣ Exotic & Tropical Tastes</p>
                <h1 class="mt-7 max-w-4xl text-6xl font-black leading-[0.95] tracking-tight text-forest sm:text-7xl lg:text-8xl">
                    Le marché
                    <span class="block text-coral">tropical</span>
                    <span class="block">livré chez vous.</span>
                </h1>
                <p class="mt-7 max-w-2xl text-lg font-semibold leading-8 text-forest/70">
                    Retrouvez vos essentiels haïtiens, caribéens et africains : sauces, épices, boissons, produits frais et coffrets gourmands.
                </p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="rounded-full bg-forest px-8 py-4 text-sm font-black uppercase tracking-wide text-cream transition hover:bg-leaf" wire:navigate.hover>Découvrir la boutique <span class="ml-2">→</span></a>
                    <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="rounded-full border-2 border-forest px-8 py-4 text-sm font-black uppercase tracking-wide text-forest transition hover:bg-forest hover:text-cream" wire:navigate.hover>Voir les best-sellers</a>
                </div>
                <div class="mt-12 grid gap-4 border-t border-forest/10 pt-7 sm:grid-cols-3">
                    <div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-full bg-forest text-cream">↦</span><p class="text-xs font-black uppercase tracking-wide text-forest">Livraison rapide</p></div>
                    <div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-full bg-forest text-cream">✓</span><p class="text-xs font-black uppercase tracking-wide text-forest">Paiement sécurisé</p></div>
                    <div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-full bg-forest text-cream">✣</span><p class="text-xs font-black uppercase tracking-wide text-forest">Produits authentiques</p></div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -left-8 -top-8 z-10 rotate-[-8deg] rounded-2xl bg-sunshine px-6 py-5 text-center text-forest shadow-xl">
                    <p class="text-3xl font-black leading-none">-15%</p>
                    <p class="mt-1 text-xs font-black uppercase tracking-wide">1ère cmd</p>
                </div>
                <div class="overflow-hidden rounded-[2rem] border-[6px] border-forest bg-white shadow-tropical">
                    <img class="aspect-[4/3] w-full object-cover" src="{{ $heroImage }}" alt="Panier de produits tropicaux Marché Peyi" fetchpriority="high" decoding="async">
                    <div class="m-4 -mt-24 relative rounded-[1.25rem] bg-cream/95 p-5 shadow-xl backdrop-blur">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-coral">Sélection du chef</p>
                        <div class="mt-2 flex items-end justify-between gap-4"><h2 class="text-xl font-black text-forest sm:text-2xl">Coffret Créole #001</h2><p class="text-3xl font-black text-forest">39€</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="categories" class="bg-cream px-4 py-16 sm:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div><p class="text-xs font-black uppercase tracking-[0.35em] text-coral">Rayons</p><h2 class="mt-3 text-5xl font-black tracking-tight text-forest sm:text-6xl">Explorez le marché</h2><p class="mt-4 text-base font-semibold text-forest/65">Choisissez votre rayon et retrouvez les saveurs du pays en quelques clics.</p></div>
                <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="text-sm font-black uppercase tracking-wide text-forest" wire:navigate.hover>Voir tout →</a>
            </div>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($categoryCards as $card)
                    <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="group min-h-[180px] rounded-[1.5rem] p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-tropical {{ $card['class'] }}" wire:navigate.hover>
                        <span class="text-3xl">{{ $card['icon'] }}</span>
                        <h3 class="mt-8 text-2xl font-black leading-tight">{{ $card['title'] }}</h3>
                        <p class="mt-3 text-sm font-semibold leading-6 opacity-80">{{ $card['body'] }}</p>
                        <span class="mt-5 inline-flex text-sm font-black uppercase tracking-wide">Découvrir →</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="best-sellers" class="bg-cream px-4 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div><p class="text-xs font-black uppercase tracking-[0.35em] text-coral">Sélection</p><h2 class="mt-3 text-5xl font-black tracking-tight text-forest sm:text-6xl">Les produits les plus aimés</h2><p class="mt-4 text-base font-semibold text-forest/65">Les essentiels que nos clients commandent encore et encore.</p></div>
                <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="text-sm font-black uppercase tracking-wide text-forest" wire:navigate.hover>Toute la boutique →</a>
            </div>
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach (array_slice($spotlightProducts, 0, 6) as $index => $product)
                    @php
                        $href = ! empty($product['slug'] ?? null) ? route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) : route('shop.index', ['locale' => $locale]);
                        $productId = (int) ($product['id'] ?? 0);
                    @endphp
                    <article class="group overflow-hidden rounded-[1.6rem] border border-forest/10 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-tropical">
                        <a href="{{ $href }}" class="relative block overflow-hidden bg-sunshine" wire:navigate.hover>
                            <img class="h-80 w-full object-cover transition duration-500 group-hover:scale-[1.04]" src="{{ $productImages[$index % count($productImages)] }}" alt="{{ $product['name'] }}" loading="lazy" decoding="async">
                            <span class="absolute left-4 top-4 rounded-full bg-forest px-4 py-2 text-[11px] font-black uppercase tracking-wide text-cream">{{ $index < 3 ? 'Best-seller' : 'Nouveau' }}</span>
                            @if ($index === 0 || $index === 1 || $index === 5)<span class="absolute right-4 top-4 rounded-full bg-cream px-3 py-1 text-xs font-black text-coral">🔥🔥</span>@endif
                        </a>
                        <div class="p-6">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-coral">{{ data_get($product, 'category.name', 'Épicerie') }} · {{ $product['origin'] ?? 'Marché Peyi' }}</p>
                            <h3 class="mt-2 text-2xl font-black leading-tight text-forest">{{ $product['name'] }}</h3>
                            <p class="mt-1 line-clamp-1 text-sm font-semibold text-forest/65">{{ $product['short_description'] ?? $product['description'] ?? 'Produit authentique du marché.' }}</p>
                            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-forest/65"><span class="text-sunshine">★★★★★</span><span>4.{{ 9 - ($index % 3) }} · {{ 98 + ($index * 23) }} avis</span></div>
                            <div class="mt-3 flex items-center gap-2 text-xs font-black uppercase tracking-wide text-forest"><span class="h-2 w-2 rounded-full bg-forest"></span><span>En stock</span></div>
                            <div class="mt-7 flex items-center justify-between gap-4"><strong class="text-3xl font-black tracking-tight text-forest">{{ $product['formatted_price'] }}</strong><button class="rounded-full bg-forest px-5 py-3 text-xs font-black text-cream transition hover:bg-leaf disabled:opacity-50" type="button" @if($productId > 0) x-on:click="window.Livewire?.dispatch('cart:add', { productId: {{ $productId }} }); window.dispatchEvent(new CustomEvent('cart-opening'))" @else disabled @endif>+ Ajouter</button></div>
                            <a href="{{ $href }}" class="mt-5 block text-center text-xs font-black uppercase tracking-[0.22em] text-forest/65 transition hover:text-forest" wire:navigate.hover>Voir le produit</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="offers" class="bg-cream px-4 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-10 max-w-2xl"><p class="text-xs font-black uppercase tracking-[0.35em] text-coral">Nouveautés</p><h2 class="mt-3 text-5xl font-black tracking-tight text-forest sm:text-6xl">Les arrivages du moment</h2><p class="mt-4 text-base font-semibold leading-7 text-forest/65">Des produits sélectionnés chaque semaine pour retrouver les vraies saveurs du pays.</p></div>
            <div class="grid gap-8 lg:grid-cols-3">
                <article class="relative min-h-[520px] overflow-hidden rounded-[1.6rem] bg-coral p-8 text-cream shadow-sm">
                    <img class="absolute inset-0 h-full w-full object-cover opacity-35 mix-blend-multiply" src="{{ $harvestImage }}" alt="Récolte de la semaine" loading="lazy" decoding="async">
                    <div class="relative z-10 flex h-full flex-col justify-between"><div><span class="rounded-full bg-cream px-4 py-2 text-xs font-black uppercase tracking-wide text-coral">Drop hebdo</span><h3 class="mt-8 text-4xl font-black leading-tight text-cream">Récolte de la semaine</h3><p class="mt-4 text-base font-semibold leading-7">Mangues, ignames, gombo, piments bonda et plus encore.</p></div><a href="{{ route('shop.index', ['locale' => $locale]) }}" class="mt-8 w-fit rounded-full bg-cream px-6 py-3 text-sm font-black uppercase tracking-wide text-forest" wire:navigate.hover>Voir les arrivages →</a></div>
                </article>
                @foreach ($newProducts as $index => $product)
                    @php
                        $href = ! empty($product['slug'] ?? null) ? route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) : route('shop.index', ['locale' => $locale]);
                        $productId = (int) ($product['id'] ?? 0);
                    @endphp
                    <article class="group overflow-hidden rounded-[1.6rem] border border-forest/10 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-tropical">
                        <a href="{{ $href }}" class="relative block overflow-hidden" wire:navigate.hover><img class="h-80 w-full object-cover transition duration-500 group-hover:scale-[1.04]" src="{{ $productImages[($index + 2) % count($productImages)] }}" alt="{{ $product['name'] }}" loading="lazy" decoding="async"><span class="absolute left-4 top-4 rounded-full bg-forest px-4 py-2 text-[11px] font-black uppercase tracking-wide text-cream">Nouveau</span></a>
                        <div class="p-6"><p class="text-xs font-black uppercase tracking-[0.22em] text-coral">{{ data_get($product, 'category.name', 'Épicerie') }} · {{ $product['origin'] ?? 'Marché Peyi' }}</p><h3 class="mt-2 text-2xl font-black leading-tight text-forest">{{ $product['name'] }}</h3><p class="mt-1 line-clamp-1 text-sm font-semibold text-forest/65">{{ $product['short_description'] ?? $product['description'] ?? 'Produit authentique du marché.' }}</p><div class="mt-4 flex items-center gap-2 text-xs font-semibold text-forest/65"><span class="text-sunshine">★★★★★</span><span>4.{{ 7 + $index }} · {{ 64 + ($index * 34) }} avis</span></div><div class="mt-3 flex items-center gap-2 text-xs font-black uppercase tracking-wide text-forest"><span class="h-2 w-2 rounded-full bg-forest"></span><span>En stock</span></div><div class="mt-7 flex items-center justify-between gap-4"><strong class="text-3xl font-black tracking-tight text-forest">{{ $product['formatted_price'] }}</strong><button class="rounded-full bg-forest px-5 py-3 text-xs font-black text-cream transition hover:bg-leaf disabled:opacity-50" type="button" @if($productId > 0) x-on:click="window.Livewire?.dispatch('cart:add', { productId: {{ $productId }} }); window.dispatchEvent(new CustomEvent('cart-opening'))" @else disabled @endif>+ Ajouter</button></div><a href="{{ $href }}" class="mt-5 block text-center text-xs font-black uppercase tracking-[0.22em] text-forest/65" wire:navigate.hover>Voir le produit</a></div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="recipes" class="bg-cream px-4 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl"><div class="mb-10 max-w-2xl"><p class="text-xs font-black uppercase tracking-[0.35em] text-coral">Cuisine</p><h2 class="mt-3 text-5xl font-black tracking-tight text-forest sm:text-6xl">Recettes & inspirations</h2><p class="mt-4 text-base font-semibold leading-7 text-forest/65">Découvrez comment utiliser nos produits dans des plats simples, généreux et pleins de goût.</p></div><div class="grid gap-8 lg:grid-cols-3">@foreach ($recipes as $recipe)<article class="overflow-hidden rounded-[1.6rem] border border-forest/10 bg-white shadow-sm"><img class="h-64 w-full object-cover" src="{{ $recipe['image'] }}" alt="{{ $recipe['title'] }}" loading="lazy" decoding="async"><div class="p-6"><p class="text-xs font-black uppercase tracking-[0.22em] text-coral">{{ $recipe['meta'] }}</p><h3 class="mt-3 text-2xl font-black leading-tight text-forest">{{ $recipe['title'] }}</h3><p class="mt-3 text-sm font-semibold leading-6 text-forest/65">{{ $recipe['body'] }}</p><a href="{{ route('blog.index', ['locale' => $locale]) }}" class="mt-5 inline-flex text-sm font-black uppercase tracking-wide text-forest" wire:navigate.hover>Voir la recette →</a></div></article>@endforeach</div></div>
    </section>

    <section id="about" class="bg-cream px-4 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto grid max-w-[96rem] gap-8 overflow-hidden rounded-[2rem] bg-forest px-6 py-12 text-cream shadow-tropical sm:px-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:px-24 lg:py-20" style="background-image: linear-gradient(90deg, rgba(18,76,32,.88), rgba(18,76,32,.92)), url('{{ $leavesImage }}'); background-size: cover; background-position: center;">
            <div><p class="text-xs font-black uppercase tracking-[0.35em] text-sunshine">Manifeste</p><h2 class="mt-5 text-5xl font-black leading-tight text-cream sm:text-6xl">Le goût vrai,<br>sans détour.</h2></div>
            <div><p class="max-w-2xl text-lg font-semibold leading-9 text-cream/90">Marché Peyi est une épicerie en ligne pleine de couleurs, de parfums et d’histoires. Chaque bocal, chaque sachet et chaque produit racontent un savoir-faire, une origine et une manière de partager la cuisine.</p><p class="mt-6 max-w-2xl text-lg font-semibold leading-9 text-cream/90">Sourcés auprès de producteurs et artisans, livrés chez vous rapidement.</p><a href="{{ route('pages.about', ['locale' => $locale]) }}" class="mt-8 inline-flex rounded-full bg-[#ff9817] px-7 py-4 text-sm font-black uppercase tracking-wide text-forest" wire:navigate.hover>Découvrir notre histoire →</a></div>
        </div>
    </section>

    <section class="bg-cream px-4 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl"><div class="mb-10"><p class="text-xs font-black uppercase tracking-[0.35em] text-coral">Avis vérifiés</p><h2 class="mt-3 text-5xl font-black tracking-tight text-forest sm:text-6xl">Ce que disent nos clients</h2></div><div class="grid gap-8 lg:grid-cols-3">@foreach ($reviews as $review)<article class="relative rounded-[1.6rem] border border-forest/10 bg-white p-8 shadow-sm"><span class="absolute -top-4 left-8 grid h-10 w-10 place-items-center rounded-full bg-coral text-lg font-black text-cream">”</span><p class="text-sunshine">★★★★★</p><p class="mt-4 text-base font-semibold leading-8 text-forest/70">“{{ $review['text'] }}”</p><div class="mt-6 border-t border-forest/10 pt-5"><p class="text-xl font-black text-forest">{{ $review['name'] }}</p><p class="text-xs font-black uppercase tracking-wide text-forest/35">{{ $review['product'] }}</p></div></article>@endforeach</div></div>
    </section>

    <section class="bg-cream px-4 py-10 sm:px-8 lg:py-14">
        <div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-4">
            <article class="rounded-[1.5rem] border border-forest/10 bg-white p-7"><span class="grid h-12 w-12 place-items-center rounded-full bg-sunshine text-forest">↦</span><h3 class="mt-6 text-xl font-black text-forest">Livraison rapide</h3><p class="mt-3 text-sm font-semibold leading-6 text-forest/65">Partout en France, offerte dès 49€ d’achats.</p></article>
            <article class="rounded-[1.5rem] border border-forest/10 bg-white p-7"><span class="grid h-12 w-12 place-items-center rounded-full bg-sunshine text-forest">✓</span><h3 class="mt-6 text-xl font-black text-forest">Paiement sécurisé</h3><p class="mt-3 text-sm font-semibold leading-6 text-forest/65">Carte, Apple Pay, Google Pay. Données chiffrées.</p></article>
            <article class="rounded-[1.5rem] border border-forest/10 bg-white p-7"><span class="grid h-12 w-12 place-items-center rounded-full bg-sunshine text-forest">✣</span><h3 class="mt-6 text-xl font-black text-forest">Produits authentiques</h3><p class="mt-3 text-sm font-semibold leading-6 text-forest/65">Sélectionnés auprès de producteurs et artisans.</p></article>
            <article class="rounded-[1.5rem] border border-forest/10 bg-white p-7"><span class="grid h-12 w-12 place-items-center rounded-full bg-sunshine text-forest">☏</span><h3 class="mt-6 text-xl font-black text-forest">Service client</h3><p class="mt-3 text-sm font-semibold leading-6 text-forest/65">Une équipe disponible du lundi au samedi.</p></article>
        </div>
    </section>

    <section class="bg-cream px-4 pb-20 pt-12 sm:px-8 lg:pb-28">
        <div class="mx-auto grid max-w-7xl overflow-hidden rounded-[2rem] bg-coral text-cream shadow-tropical lg:grid-cols-[1fr_1fr]"><div class="p-8 sm:p-12"><p class="text-xs font-black uppercase tracking-[0.35em] text-sunshine">Newsletter</p><h2 class="mt-4 max-w-xl text-4xl font-black leading-tight sm:text-5xl">Recevez nos recettes, nouveautés et offres du marché.</h2><p class="mt-4 text-base font-semibold text-cream/90">Une fois par mois. Pas de spam, uniquement des saveurs.</p><form class="mt-7 flex max-w-lg overflow-hidden rounded-full bg-cream p-1"><input class="min-w-0 flex-1 bg-transparent px-5 text-sm font-semibold text-forest outline-none placeholder:text-forest/45" type="email" placeholder="votre@email.com"><button class="rounded-full bg-forest px-6 py-3 text-xs font-black uppercase tracking-wide text-cream" type="button">Je m’inscris</button></form></div><img class="h-full min-h-[320px] w-full object-cover" src="{{ $peppersImage }}" alt="Piments tropicaux" loading="lazy" decoding="async"></div>
    </section>
@endsection
