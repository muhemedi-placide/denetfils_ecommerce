@extends('layouts.shop')

@section('title', data_get($seoPayload ?? [], 'meta.title', __('home.meta.title')))
@section('description', data_get($seoPayload ?? [], 'meta.description', __('home.meta.description')))
@section('canonical', data_get($seoPayload ?? [], 'canonical', route('home.localized', ['locale' => $locale])))

@section('content')
    @php
        $spotlightProducts = array_slice($products ?? [], 0, 3);
        $newProducts = array_slice($products ?? [], 3, 2);
        $marketImages = [
            'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1400&q=86',
            'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=1100&q=84',
            'https://images.unsplash.com/photo-1516684732162-798a0062be99?auto=format&fit=crop&w=1100&q=84',
            'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?auto=format&fit=crop&w=1100&q=84',
        ];
        $recipes = $locale === 'fr'
            ? [
                ['meta' => '45 min · facile', 'title' => 'Riz djon djon maison', 'body' => 'Le classique haïtien, parfumé aux champignons noirs.', 'image' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?auto=format&fit=crop&w=900&q=82'],
                ['meta' => 'Astuces · 3 min', 'title' => 'Comment utiliser le pikliz ?', 'body' => 'Cinq façons d’ajouter du peps à vos plats du quotidien.', 'image' => 'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?auto=format&fit=crop&w=900&q=82'],
                ['meta' => '20 min · facile', 'title' => 'Sauce créole épicée', 'body' => 'La sauce qui transforme un simple poisson grillé.', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=900&q=82'],
            ]
            : [
                ['meta' => '45 min · easy', 'title' => 'Homemade djon djon rice', 'body' => 'The Haitian classic, fragrant with black mushrooms.', 'image' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?auto=format&fit=crop&w=900&q=82'],
                ['meta' => 'Tips · 3 min', 'title' => 'How to use pikliz', 'body' => 'Five ways to add a bright kick to everyday dishes.', 'image' => 'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?auto=format&fit=crop&w=900&q=82'],
                ['meta' => '20 min · easy', 'title' => 'Spicy Creole sauce', 'body' => 'The sauce that transforms simple grilled fish.', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=900&q=82'],
            ];
        $reviews = $locale === 'fr'
            ? [
                ['name' => 'Mireille', 'text' => 'Les produits sont bien emballés et le goût est vraiment authentique. Je recommande à 100%.'],
                ['name' => 'Samuel', 'text' => 'J’ai retrouvé les saveurs de chez moi. C’est exactement ce que je cherchais depuis des années.'],
                ['name' => 'Nadia', 'text' => 'Livraison rapide et très bonne qualité. Le piment Edenne est incroyable.'],
            ]
            : [
                ['name' => 'Mireille', 'text' => 'Products are well packed and the taste is truly authentic. I recommend it 100%.'],
                ['name' => 'Samuel', 'text' => 'I found the flavors of home again. This is exactly what I had been looking for.'],
                ['name' => 'Nadia', 'text' => 'Fast delivery and very good quality. The Edenne pepper is incredible.'],
            ];
    @endphp

    <section id="home" class="tropical-pattern relative overflow-hidden px-4 py-12 sm:px-8 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div class="animate-rise">
                <p class="inline-flex rounded-full bg-forest/10 px-4 py-2 text-xs font-black uppercase tracking-[0.24em] text-forest dark:bg-white/10 dark:text-meadow">
                    {{ __('home.hero.eyebrow') }}
                </p>
                <h1 class="brand-display mt-6 max-w-4xl text-5xl uppercase leading-none text-forest sm:text-7xl lg:text-8xl">
                    {{ __('home.hero.title_1') }}
                    <span class="block text-coral">{{ __('home.hero.title_accent') }}</span>
                    <span class="block">{{ __('home.hero.title_2') }}</span>
                </h1>
                <p class="mt-6 max-w-2xl text-base font-semibold leading-8 text-cocoa/70 dark:text-cream/74 sm:text-lg">
                    {{ __('home.hero.body') }}
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#products" class="btn-primary">{{ __('home.hero.primary_cta') }} <span class="ml-2">→</span></a>
                    <a href="#best-sellers" class="btn-secondary">{{ __('home.hero.secondary_cta') }}</a>
                </div>
                <div class="mt-10 grid gap-4 border-t border-leaf/10 pt-6 sm:grid-cols-3">
                    @foreach (trans('home.hero.trust') as $item)
                        <div class="flex items-center gap-3">
                            <span class="inline-grid h-10 w-10 shrink-0 place-items-center rounded-full bg-forest text-cream">{{ $loop->iteration }}</span>
                            <div>
                                <p class="text-xs font-black uppercase tracking-wide text-forest dark:text-meadow">{{ $item['title'] }}</p>
                                <p class="mt-1 text-xs leading-5 text-cocoa/60 dark:text-cream/60">{{ $item['body'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative animate-rise">
                <div class="relative overflow-hidden rounded-[2rem] border-[6px] border-forest bg-white shadow-tropical">
                    <img class="aspect-[4/3] w-full object-cover" src="{{ $marketImages[0] }}" alt="{{ __('home.hero.image_alt') }}" fetchpriority="high" decoding="async">
                    <div class="absolute bottom-4 left-4 right-4 rounded-[1.25rem] bg-cream/95 p-5 shadow-xl backdrop-blur">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-coral">{{ __('home.hero.chef_pick') }}</p>
                        <div class="mt-2 flex items-end justify-between gap-4">
                            <h2 class="text-xl font-black text-forest sm:text-2xl">{{ __('home.hero.chef_title') }}</h2>
                            <p class="brand-display text-3xl text-forest">39€</p>
                        </div>
                    </div>
                </div>
                <div class="brand-display absolute -left-3 top-8 rotate-[-8deg] rounded-2xl bg-sunshine px-5 py-4 text-2xl uppercase text-forest shadow-lg sm:-left-8">
                    {{ __('home.hero.discount') }}
                </div>
            </div>
        </div>
    </section>

    <section id="categories" class="bg-linen px-4 py-14 dark:bg-[#163319] sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="section-kicker">{{ __('home.categories.eyebrow') }}</p>
                    <h2 class="section-title mt-3">{{ __('home.categories.title') }}</h2>
                </div>
                <p class="section-copy">{{ __('home.categories.body') }}</p>
            </div>
            <livewire:shop.category-grid :locale="$locale" :categories="$categories" />
        </div>
    </section>

    <section id="best-sellers" class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="section-kicker">{{ __('home.spotlight.eyebrow') }}</p>
                    <h2 class="section-title mt-3">{{ $locale === 'fr' ? 'Les produits les plus aimés' : 'Most loved products' }}</h2>
                    <p class="section-copy mt-3">{{ $locale === 'fr' ? 'Les essentiels que nos clients commandent encore et encore.' : 'Essentials customers come back to again and again.' }}</p>
                </div>
                <a href="#products" class="btn-secondary w-full sm:w-fit">{{ $locale === 'fr' ? 'Toute la boutique' : 'All products' }} <span class="ml-2">→</span></a>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                @forelse ($spotlightProducts as $index => $product)
                    @php
                        $primaryImage = $product['primary_image'] ?? [];
                        $imageUrl = $primaryImage['url'] ?? $marketImages[($index + 1) % count($marketImages)];
                        $ratingValue = number_format((float) data_get($product, 'commerce.rating.average', 4.8), 1, ',', ' ');
                    @endphp
                    <article class="market-card group overflow-hidden bg-white dark:bg-white/5">
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="relative block overflow-hidden" wire:navigate.hover>
                            <img class="product-card-image" src="{{ $imageUrl }}" alt="{{ $primaryImage['alt_text'] ?? $product['name'] }}" loading="lazy" decoding="async">
                            <span class="badge-tropical absolute left-4 top-4 bg-forest text-cream">{{ $index === 0 ? 'Best-seller' : ($locale === 'fr' ? 'Sélection' : 'Selection') }}</span>
                        </a>
                        <div class="p-5">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-coral">{{ $product['category']['name'] ?? ($locale === 'fr' ? 'Épicerie' : 'Grocery') }} · {{ $product['origin'] ?? 'DEN & FILS' }}</p>
                            <h3 class="mt-2 line-clamp-2 text-2xl font-black leading-tight text-forest">{{ $product['name'] }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-cocoa/65 dark:text-cream/68">{{ $product['short_description'] ?? $product['description'] }}</p>
                            <div class="mt-4 flex items-center gap-2 text-xs font-bold text-cocoa/60 dark:text-cream/60">
                                <span class="text-sunshine">★★★★★</span>
                                <span>{{ $ratingValue }} · {{ $locale === 'fr' ? 'en stock' : 'in stock' }}</span>
                            </div>
                            <div class="mt-5 flex items-center justify-between gap-3">
                                <strong class="brand-display text-3xl text-forest">{{ $product['formatted_price'] }}</strong>
                                <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="btn-primary px-4 py-2 text-xs" wire:navigate.hover>{{ $locale === 'fr' ? 'Voir' : 'View' }}</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="utility-section lg:col-span-3">{{ __('home.products.empty') }}</div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="offers" class="bg-linen px-4 py-14 dark:bg-[#163319] sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 max-w-2xl">
                <p class="section-kicker">{{ __('home.offers.main_eyebrow') }}</p>
                <h2 class="section-title mt-3">{{ __('home.offers.main_title') }}</h2>
                <p class="section-copy mt-4">{{ __('home.offers.body') }}</p>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                <article class="relative overflow-hidden rounded-[1.5rem] bg-coral p-8 text-cream shadow-tropical lg:min-h-[420px]">
                    <img class="absolute inset-0 h-full w-full object-cover opacity-35 mix-blend-multiply" src="https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?auto=format&fit=crop&w=900&q=82" alt="" loading="lazy" decoding="async">
                    <div class="relative z-10 flex h-full flex-col justify-between">
                        <div>
                            <span class="rounded-full bg-cream px-4 py-2 text-xs font-black uppercase tracking-wide text-coral">{{ data_get(trans('home.offers.cards'), '0.eyebrow') }}</span>
                            <h3 class="brand-display mt-8 text-4xl uppercase text-cream">{{ data_get(trans('home.offers.cards'), '0.title') }}</h3>
                            <p class="mt-4 max-w-sm text-base font-semibold leading-7">{{ data_get(trans('home.offers.cards'), '0.body') }}</p>
                        </div>
                        <a href="#products" class="btn-sunshine mt-8 w-fit">{{ __('home.offers.cta') }} <span class="ml-2">→</span></a>
                    </div>
                </article>

                @foreach ($newProducts as $product)
                    @php
                        $primaryImage = $product['primary_image'] ?? [];
                        $imageUrl = $primaryImage['url'] ?? $marketImages[($loop->index + 2) % count($marketImages)];
                    @endphp
                    <article class="market-card group overflow-hidden bg-white dark:bg-white/5">
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="relative block overflow-hidden" wire:navigate.hover>
                            <img class="product-card-image" src="{{ $imageUrl }}" alt="{{ $primaryImage['alt_text'] ?? $product['name'] }}" loading="lazy" decoding="async">
                            <span class="badge-tropical absolute left-4 top-4 bg-forest text-cream">{{ $locale === 'fr' ? 'Nouveau' : 'New' }}</span>
                        </a>
                        <div class="p-5">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-coral">{{ $product['category']['name'] ?? ($locale === 'fr' ? 'Épicerie' : 'Grocery') }}</p>
                            <h3 class="mt-2 line-clamp-2 text-2xl font-black leading-tight text-forest">{{ $product['name'] }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-cocoa/65 dark:text-cream/68">{{ $product['short_description'] ?? $product['description'] }}</p>
                            <div class="mt-5 flex items-center justify-between gap-3">
                                <strong class="brand-display text-3xl text-forest">{{ $product['formatted_price'] }}</strong>
                                <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="btn-primary px-4 py-2 text-xs" wire:navigate.hover>{{ $locale === 'fr' ? 'Voir' : 'View' }}</a>
                            </div>
                        </div>
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

    <section id="recipes" class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 max-w-2xl">
                <p class="section-kicker">{{ __('home.blog.eyebrow') }}</p>
                <h2 class="section-title mt-3">{{ __('home.blog.title') }}</h2>
                <p class="section-copy mt-4">{{ __('home.blog.body') }}</p>
            </div>
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($recipes as $recipe)
                    <article class="market-card overflow-hidden bg-white dark:bg-white/5">
                        <img class="h-60 w-full object-cover" src="{{ $recipe['image'] }}" alt="{{ $recipe['title'] }}" loading="lazy" decoding="async">
                        <div class="p-6">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-coral">{{ $recipe['meta'] }}</p>
                            <h3 class="mt-3 text-2xl font-black leading-tight text-forest">{{ $recipe['title'] }}</h3>
                            <p class="mt-3 text-sm leading-6 text-cocoa/65 dark:text-cream/68">{{ $recipe['body'] }}</p>
                            <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="mt-5 inline-flex font-black uppercase tracking-wide text-forest" wire:navigate.hover>{{ $locale === 'fr' ? 'Voir la recette' : 'View recipe' }} <span class="ml-2">→</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="about" class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="luxury-gradient mx-auto grid max-w-[96rem] gap-8 rounded-[2rem] px-6 py-12 text-cream sm:px-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:px-24 lg:py-20">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-sunshine">{{ $locale === 'fr' ? 'Manifeste' : 'Manifesto' }}</p>
                <h2 class="brand-display mt-5 text-5xl uppercase text-cream sm:text-6xl lg:text-7xl">{{ __('home.about.title') }}</h2>
            </div>
            <div>
                <p class="max-w-2xl text-lg font-semibold leading-9 text-cream/85">{{ __('home.about.body') }}</p>
                <a href="{{ route('pages.about', ['locale' => $locale]) }}" class="btn-sunshine mt-8" wire:navigate.hover>{{ $locale === 'fr' ? 'Découvrir notre histoire' : 'Discover our story' }} <span class="ml-2">→</span></a>
            </div>
        </div>
    </section>

    <section class="bg-linen px-4 py-14 dark:bg-[#163319] sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8">
                <p class="section-kicker">{{ $locale === 'fr' ? 'Avis vérifiés' : 'Verified reviews' }}</p>
                <h2 class="section-title mt-3">{{ $locale === 'fr' ? 'Ce que disent nos clients' : 'What customers say' }}</h2>
            </div>
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($reviews as $review)
                    <article class="utility-section relative pt-8">
                        <span class="absolute -top-4 left-6 inline-grid h-10 w-10 place-items-center rounded-full bg-coral text-lg font-black text-cream">”</span>
                        <p class="text-sunshine">★★★★★</p>
                        <p class="mt-4 text-base font-semibold leading-8 text-cocoa/75 dark:text-cream/75">“{{ $review['text'] }}”</p>
                        <p class="mt-5 font-black text-forest dark:text-meadow">{{ $review['name'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-4">
            @foreach (trans('home.hero.trust') as $item)
                <article class="utility-section">
                    <span class="inline-grid h-11 w-11 place-items-center rounded-full bg-sunshine text-forest">{{ $loop->iteration }}</span>
                    <h3 class="mt-6 text-xl font-black text-forest">{{ $item['title'] }}</h3>
                    <p class="mt-3 text-sm leading-6 text-cocoa/65 dark:text-cream/68">{{ $item['body'] }}</p>
                </article>
            @endforeach
            <article class="utility-section">
                <span class="inline-grid h-11 w-11 place-items-center rounded-full bg-sunshine text-forest">4</span>
                <h3 class="mt-6 text-xl font-black text-forest">{{ $locale === 'fr' ? 'Service client' : 'Customer service' }}</h3>
                <p class="mt-3 text-sm leading-6 text-cocoa/65 dark:text-cream/68">{{ $locale === 'fr' ? 'Une équipe disponible pour vous accompagner avant et après la commande.' : 'A team available to support you before and after your order.' }}</p>
            </article>
        </div>

        <div class="mx-auto mt-12 grid max-w-7xl overflow-hidden rounded-[1.75rem] bg-coral text-cream shadow-tropical lg:grid-cols-[1fr_1fr]">
            <div class="p-8 sm:p-12">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-sunshine">Newsletter</p>
                <h2 class="brand-display mt-4 max-w-xl text-4xl uppercase sm:text-5xl">{{ $locale === 'fr' ? 'Recevez nos recettes, nouveautés et offres du marché.' : 'Get our recipes, new arrivals and market offers.' }}</h2>
                <p class="mt-4 text-base font-semibold text-cream/85">{{ $locale === 'fr' ? 'Une fois par mois. Pas de spam, uniquement des saveurs.' : 'Once a month. No spam, only flavor.' }}</p>
                <form class="mt-7 flex max-w-lg overflow-hidden rounded-full bg-cream p-1">
                    <input class="min-w-0 flex-1 bg-transparent px-5 text-sm font-semibold text-cocoa outline-none placeholder:text-cocoa/45" type="email" placeholder="{{ $locale === 'fr' ? 'votre@email.com' : 'your@email.com' }}">
                    <button class="btn-primary min-h-[42px] px-5 py-2 text-xs" type="button">{{ $locale === 'fr' ? 'Je m’inscris' : 'Sign me up' }}</button>
                </form>
            </div>
            <img class="h-full min-h-[320px] w-full object-cover" src="https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?auto=format&fit=crop&w=1000&q=84" alt="{{ $locale === 'fr' ? 'Piments tropicaux colorés' : 'Colorful tropical peppers' }}" loading="lazy" decoding="async">
        </div>
    </section>
@endsection
