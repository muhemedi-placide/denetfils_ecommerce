@extends('layouts.shop')

@section('title', data_get($seoPayload ?? [], 'meta.title', __('home.meta.title')))
@section('description', data_get($seoPayload ?? [], 'meta.description', __('home.meta.description')))
@section('canonical', data_get($seoPayload ?? [], 'canonical', route('home.localized', ['locale' => $locale])))

@section('content')
    @php
        $spotlightProducts = array_slice($products, 0, 4);
        $featuredBlogPosts = $blogPosts ?? [];
        $marketImages = [
            'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=84',
            'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=1200&q=84',
            'https://images.unsplash.com/photo-1516684732162-798a0062be99?auto=format&fit=crop&w=1200&q=84',
        ];
        $categoryVisuals = [
            ['emoji' => '🌶️', 'color' => 'bg-coral/15 text-tomato'],
            ['emoji' => '🧄', 'color' => 'bg-sunshine/25 text-forest'],
            ['emoji' => '🥭', 'color' => 'bg-mango/20 text-clay'],
            ['emoji' => '🍌', 'color' => 'bg-meadow/25 text-forest'],
            ['emoji' => '🫘', 'color' => 'bg-caribbean/15 text-leaf'],
            ['emoji' => '🎁', 'color' => 'bg-flamingo/15 text-clay'],
        ];
        $recipes = $locale === 'fr'
            ? [
                ['title' => 'Riz djon djon maison', 'body' => 'Un classique parfumé pour retrouver les saveurs du pays.', 'image' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?auto=format&fit=crop&w=900&q=82'],
                ['title' => 'Comment utiliser le pikliz ?', 'body' => 'Viandes, riz, sandwichs : le condiment qui réveille tout.', 'image' => 'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?auto=format&fit=crop&w=900&q=82'],
                ['title' => 'Sauce créole épicée', 'body' => 'Une base simple pour cuisiner vite avec beaucoup de caractère.', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=900&q=82'],
            ]
            : [
                ['title' => 'Homemade djon djon rice', 'body' => 'A fragrant classic to bring island flavors home.', 'image' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?auto=format&fit=crop&w=900&q=82'],
                ['title' => 'How to use pikliz', 'body' => 'Meat, rice, sandwiches: the condiment that wakes up every dish.', 'image' => 'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?auto=format&fit=crop&w=900&q=82'],
                ['title' => 'Spicy creole sauce', 'body' => 'A simple base for fast, flavorful cooking.', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=900&q=82'],
            ];
        $reviews = $locale === 'fr'
            ? [
                ['name' => 'Mireille', 'product' => 'Pikliz', 'text' => 'J’ai retrouvé les saveurs de chez moi, avec une livraison soignée.'],
                ['name' => 'Samuel', 'product' => 'Épices créoles', 'text' => 'Les produits sont bien emballés, le goût est authentique.'],
                ['name' => 'Nadia', 'product' => 'Coffret créole', 'text' => 'Très belle boutique, simple à utiliser et rassurante.'],
            ]
            : [
                ['name' => 'Mireille', 'product' => 'Pikliz', 'text' => 'It brought back the flavors of home, with careful delivery.'],
                ['name' => 'Samuel', 'product' => 'Creole spices', 'text' => 'Products are well packed and the taste is authentic.'],
                ['name' => 'Nadia', 'product' => 'Creole box', 'text' => 'A beautiful shop, easy to use and reassuring.'],
            ];
    @endphp

    <section id="home" class="tropical-pattern tropical-leaf-pattern relative overflow-hidden px-4 py-10 sm:px-8 lg:py-16">
        <div class="relative z-10 mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div class="animate-rise">
                <p class="inline-flex rounded-full bg-sunshine px-4 py-2 text-xs font-black uppercase tracking-[0.22em] text-forest shadow-sm">
                    {{ $locale === 'fr' ? 'Exotic & Tropical Tastes' : 'Exotic & Tropical Tastes' }}
                </p>
                <h1 class="brand-display mt-5 max-w-3xl text-5xl uppercase leading-[0.92] tracking-tight text-leaf sm:text-7xl lg:text-8xl">
                    {{ $locale === 'fr' ? 'Le marché tropical livré chez vous' : 'The tropical market delivered home' }}
                </h1>
                <p class="mt-5 max-w-2xl text-base font-semibold leading-8 text-cocoa/75 sm:text-lg">
                    {{ $locale === 'fr' ? 'Retrouvez vos essentiels haïtiens, caribéens et africains : sauces, épices, boissons, produits frais et coffrets gourmands.' : 'Find Haitian, Caribbean and African essentials: sauces, spices, drinks, fresh products and gourmet boxes.' }}
                </p>
                <div class="mt-7 grid gap-3 sm:flex">
                    <a href="#products" class="btn-primary w-full sm:w-auto">{{ $locale === 'fr' ? 'Découvrir la boutique' : 'Discover the shop' }}</a>
                    <a href="#best-sellers" class="btn-sunshine w-full sm:w-auto">{{ $locale === 'fr' ? 'Voir les best-sellers' : 'View best sellers' }}</a>
                </div>
                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    @foreach ([['120+', $locale === 'fr' ? 'producteurs' : 'producers'], ['48h', $locale === 'fr' ? 'livraison' : 'delivery'], ['4,9★', $locale === 'fr' ? 'avis clients' : 'reviews']] as $stat)
                        <div class="rounded-2xl border border-leaf/10 bg-white/80 p-4 shadow-sm backdrop-blur">
                            <p class="brand-display text-3xl text-leaf">{{ $stat[0] }}</p>
                            <p class="mt-1 text-xs font-black uppercase tracking-wide text-cocoa/60">{{ $stat[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative animate-rise lg:pl-4">
                <div class="absolute -right-5 -top-5 h-28 w-28 rounded-full bg-caribbean/25 blur-2xl"></div>
                <div class="absolute -bottom-8 left-8 h-32 w-32 rounded-full bg-coral/25 blur-2xl"></div>
                <div class="relative overflow-hidden rounded-[2rem] border-[10px] border-white bg-white shadow-tropical">
                    <img class="aspect-[4/3] w-full object-cover" src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1400&q=86" alt="{{ $locale === 'fr' ? 'Panier de produits tropicaux' : 'Basket of tropical products' }}" fetchpriority="high" decoding="async">
                    <div class="absolute bottom-5 left-5 right-5 rounded-[1.25rem] bg-forest/90 p-5 text-white shadow-xl backdrop-blur">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-sunshine">{{ $locale === 'fr' ? 'Sélection du chef' : 'Chef selection' }}</p>
                        <div class="mt-2 flex items-end justify-between gap-4">
                            <h2 class="brand-display text-3xl uppercase leading-none">Coffret Créole #001</h2>
                            <p class="text-3xl font-black text-sunshine">39€</p>
                        </div>
                    </div>
                </div>
                <div class="absolute -left-3 top-8 rounded-2xl bg-tomato px-4 py-3 text-sm font-black uppercase tracking-wide text-white shadow-lg sm:-left-8">-15% {{ $locale === 'fr' ? '1ère cmd' : '1st order' }}</div>
            </div>
        </div>
    </section>

    <section class="border-y border-leaf/10 bg-forest px-4 py-3 text-cream sm:px-8">
        <div class="market-ticker flex gap-8 text-xs font-black uppercase tracking-[0.18em]">
            <span>Votre marché des saveurs exotiques 24h/24</span><span>•</span><span>Paiement sécurisé</span><span>•</span><span>Livraison offerte dès 49€</span><span>•</span><span>Produits authentiques des Antilles & d’Afrique</span><span>•</span><span>Votre marché des saveurs exotiques 24h/24</span><span>•</span><span>Paiement sécurisé</span><span>•</span><span>Livraison offerte dès 49€</span>
        </div>
    </section>

    <section class="bg-white px-4 py-5 dark:bg-ink sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (trans('home.hero.trust') as $item)
                <div class="rounded-2xl border border-leaf/10 bg-mint p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-sm font-black uppercase tracking-wide text-leaf dark:text-meadow">{{ $item['title'] }}</p>
                    <p class="mt-1 text-xs leading-5 text-cocoa/65 dark:text-cream/65">{{ $item['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="categories" class="theme-band-soft bg-linen px-4 py-12 dark:bg-[#163319] sm:px-8 lg:py-16">
        <div class="mx-auto max-w-7xl">
            <div class="mb-7 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.categories.eyebrow') }}</p>
                    <h2 class="brand-display mt-2 text-4xl uppercase leading-none text-leaf sm:text-5xl">{{ __('home.categories.title') }}</h2>
                </div>
                <p class="max-w-xl text-sm font-semibold leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.categories.body') }}</p>
            </div>
            <livewire:shop.category-grid :locale="$locale" :categories="$categories" />
        </div>
    </section>

    <section id="best-sellers" class="bg-white px-4 py-12 dark:bg-ink sm:px-8 lg:py-16">
        <div class="mx-auto max-w-7xl">
            <div class="mb-7 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-tomato">{{ $locale === 'fr' ? 'Sélection' : 'Selection' }}</p>
                    <h2 class="brand-display mt-2 text-4xl uppercase leading-none text-leaf sm:text-5xl">{{ $locale === 'fr' ? 'Les produits les plus aimés' : 'Most loved products' }}</h2>
                </div>
                <a href="#products" class="btn-secondary w-full sm:w-fit">{{ $locale === 'fr' ? 'Voir tout le marché' : 'View the market' }}</a>
            </div>

            <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-2 lg:grid lg:grid-cols-4 lg:overflow-visible">
                @forelse ($spotlightProducts as $index => $product)
                    @php
                        $primaryImage = $product['primary_image'] ?? [];
                        $ratingValue = number_format((float) data_get($product, 'commerce.rating.average', 4.8), 1, ',', ' ');
                    @endphp
                    <article class="premium-card group min-w-[260px] overflow-hidden bg-cream dark:bg-white/5 lg:min-w-0">
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="relative block overflow-hidden bg-white" wire:navigate.hover>
                            <img class="h-52 w-full object-cover transition duration-500 group-hover:scale-[1.05]" src="{{ $primaryImage['url'] ?? $marketImages[$index % count($marketImages)] }}" alt="{{ $primaryImage['alt_text'] ?? $product['name'] }}" loading="lazy" decoding="async">
                            <span class="badge-tropical absolute left-3 top-3 {{ $index % 2 === 0 ? 'bg-tomato text-white' : 'bg-sunshine text-forest' }}">{{ $index % 2 === 0 ? 'Best-seller' : 'Nouveau' }}</span>
                        </a>
                        <div class="p-4">
                            <p class="text-xs font-bold text-leaf/70">{{ $product['category']['name'] ?? ($locale === 'fr' ? 'Épicerie tropicale' : 'Tropical grocery') }} · {{ $product['origin'] ?? 'Peyi' }}</p>
                            <h3 class="mt-2 line-clamp-2 text-lg font-black text-cocoa dark:text-cream">{{ $product['name'] }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $product['short_description'] ?? $product['description'] }}</p>
                            <div class="mt-3 flex items-center justify-between text-xs font-bold text-cocoa/60 dark:text-cream/60">
                                <span class="text-sunshine">★★★★★</span><span>{{ $ratingValue }} · {{ $locale === 'fr' ? 'en stock' : 'in stock' }}</span>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-3">
                                <strong class="text-xl font-black text-leaf dark:text-meadow">{{ $product['formatted_price'] }}</strong>
                                <button class="btn-primary px-4 py-2 text-xs" type="button" x-on:click="window.dispatchEvent(new CustomEvent('cart-opening'))" wire:click="$dispatchTo('shop.cart-manager', 'cart:add', { productId: {{ (int) $product['id'] }} })" wire:loading.attr="disabled">{{ __('home.products.cta') }}</button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.5rem] border border-leaf/10 bg-linen p-6 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70 lg:col-span-4">{{ __('home.products.empty') }}</div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="offers" class="tropical-pattern px-4 py-12 sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-tomato">{{ __('home.offers.main_eyebrow') }}</p>
                <h2 class="brand-display mt-2 text-4xl uppercase leading-none text-leaf sm:text-5xl">{{ __('home.offers.main_title') }}</h2>
                <p class="mt-4 max-w-xl text-sm font-semibold leading-7 text-cocoa/70">{{ $locale === 'fr' ? 'Des produits sélectionnés chaque semaine pour retrouver les vraies saveurs du pays.' : 'Fresh selections every week to bring real island flavors home.' }}</p>
                <a href="#products" class="btn-primary mt-6">{{ __('home.offers.cta') }}</a>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach (trans('home.offers.cards') as $index => $card)
                    <article class="rounded-[1.5rem] border border-white/60 bg-white/80 p-5 shadow-sm backdrop-blur">
                        <p class="text-xs font-black uppercase tracking-[0.18em] {{ $index === 0 ? 'text-tomato' : ($index === 1 ? 'text-caribbean' : 'text-leaf') }}">{{ $card['eyebrow'] }}</p>
                        <h3 class="mt-3 text-xl font-black text-cocoa">{{ $card['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-cocoa/65">{{ $card['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <livewire:shop.product-catalog
        :locale="$locale"
        :categories="$categories"
        :products="$products"
        :filters="$filters"
        :api-error="$apiError"
    />

    <section id="recipes" class="bg-white px-4 py-12 dark:bg-ink sm:px-8 lg:py-16">
        <div class="mx-auto max-w-7xl">
            <div class="mb-7 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.blog.eyebrow') }}</p>
                    <h2 class="brand-display mt-2 text-4xl uppercase leading-none text-leaf sm:text-5xl">{{ __('home.blog.title') }}</h2>
                </div>
                <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="btn-secondary w-full sm:w-fit" wire:navigate.hover>{{ __('home.nav.blog') }}</a>
            </div>
            <div class="grid gap-4 lg:grid-cols-3">
                @foreach ($recipes as $recipe)
                    <article class="premium-card overflow-hidden bg-linen dark:bg-white/5">
                        <img class="h-48 w-full object-cover" src="{{ $recipe['image'] }}" alt="{{ $recipe['title'] }}" loading="lazy" decoding="async">
                        <div class="p-5">
                            <h3 class="text-xl font-black text-cocoa dark:text-cream">{{ $recipe['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $recipe['body'] }}</p>
                            <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="mt-4 inline-flex text-sm font-black uppercase tracking-wide text-leaf" wire:navigate.hover>{{ $locale === 'fr' ? 'Voir la recette' : 'View recipe' }} →</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="about" class="luxury-gradient px-4 py-12 text-white sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-sunshine">{{ $locale === 'fr' ? 'Manifeste' : 'Manifesto' }}</p>
                <h2 class="brand-display mt-3 text-5xl uppercase leading-none text-white sm:text-6xl">{{ __('home.about.title') }}</h2>
                <p class="mt-5 max-w-2xl text-base font-semibold leading-8 text-white/76">{{ __('home.about.body') }}</p>
                <a href="{{ route('pages.about', ['locale' => $locale]) }}" class="btn-sunshine mt-7" wire:navigate.hover>{{ $locale === 'fr' ? 'Découvrir notre histoire' : 'Discover our story' }}</a>
            </div>
            <div class="relative overflow-hidden rounded-[2rem] border-[8px] border-white/15 bg-white/10 p-2">
                <img class="aspect-[4/3] w-full rounded-[1.5rem] object-cover" src="https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=1200&q=84" alt="{{ __('home.about.image_alt') }}" loading="lazy" decoding="async">
            </div>
        </div>
    </section>

    <section class="theme-band-soft bg-linen px-4 py-12 dark:bg-[#163319] sm:px-8 lg:py-16">
        <div class="mx-auto max-w-7xl">
            <div class="mb-7">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-tomato">{{ $locale === 'fr' ? 'Avis clients' : 'Customer reviews' }}</p>
                <h2 class="brand-display mt-2 text-4xl uppercase leading-none text-leaf dark:text-meadow sm:text-5xl">{{ $locale === 'fr' ? 'Ce que disent nos clients' : 'What customers say' }}</h2>
            </div>
            <div class="grid gap-4 lg:grid-cols-3">
                @foreach ($reviews as $review)
                    <article class="rounded-[1.5rem] border border-leaf/10 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-sunshine">★★★★★</p>
                        <p class="mt-4 text-sm font-semibold leading-7 text-cocoa/75 dark:text-cream/75">“{{ $review['text'] }}”</p>
                        <p class="mt-4 text-sm font-black text-cocoa dark:text-cream">{{ $review['name'] }}</p>
                        <p class="text-xs font-bold text-leaf dark:text-meadow">{{ $review['product'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="checkout" class="bg-white px-4 py-12 dark:bg-ink sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-6 rounded-[2rem] border border-leaf/10 bg-cream p-6 shadow-tropical dark:border-white/10 dark:bg-white/5 lg:grid-cols-[0.8fr_1.2fr] lg:p-10">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.checkout.eyebrow') }}</p>
                <h2 class="brand-display mt-3 text-4xl uppercase leading-none text-leaf dark:text-meadow sm:text-5xl">{{ __('home.checkout.title') }}</h2>
                <p class="mt-4 text-sm font-semibold leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.checkout.body') }}</p>
                <livewire:shop.cart-open-button button-class="btn-primary mt-6 w-full sm:w-auto" />
            </div>
            <div class="grid gap-3 md:grid-cols-3">
                @foreach (trans('home.checkout.steps') as $item)
                    <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-tomato">{{ $item['number'] }}</p>
                        <h3 class="mt-3 font-black text-cocoa dark:text-cream">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $item['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
