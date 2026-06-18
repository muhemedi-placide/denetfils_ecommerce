@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Boutique Marché Peyi' : 'Marché Peyi Shop'))
@section('description', $locale === 'fr' ? 'Explorez les rayons Marché Peyi : sauces, pikliz, épices, boissons, produits frais et coffrets gourmands.' : 'Explore Marché Peyi aisles: sauces, pikliz, spices, drinks, fresh products and gift boxes.')
@section('canonical', route('shop.index', ['locale' => $locale]))

@section('content')
    <section class="tropical-pattern px-4 py-12 sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
                <p class="inline-flex rounded-full bg-sunshine px-4 py-2 text-xs font-black uppercase tracking-[0.22em] text-forest">{{ $locale === 'fr' ? 'Boutique' : 'Shop' }}</p>
                <h1 class="brand-display mt-5 text-5xl uppercase leading-none text-leaf sm:text-7xl">{{ $locale === 'fr' ? 'Explorer le marché' : 'Explore the market' }}</h1>
                <p class="mt-5 max-w-2xl text-base font-semibold leading-8 text-cocoa/75">{{ $locale === 'fr' ? 'Tous les rayons Marché Peyi au même endroit : produits du quotidien, best-sellers, arrivages et essentiels tropicaux.' : 'All Marché Peyi aisles in one place: everyday products, best sellers, new arrivals and tropical essentials.' }}</p>
            </div>
            <div class="overflow-hidden rounded-[2rem] border-[10px] border-white bg-white shadow-tropical">
                <img class="aspect-[16/10] w-full object-cover" src="https://moodboard-to-shop.lovable.app/assets/hero-market-B__fWu60.jpg" alt="Marché Peyi" fetchpriority="high" decoding="async">
            </div>
        </div>
    </section>

    <section class="bg-white px-4 py-8 dark:bg-ink sm:px-8">
        <div class="mx-auto max-w-7xl">
            <livewire:shop.category-grid :locale="$locale" :categories="$categories" />
        </div>
    </section>

    <livewire:shop.product-catalog
        :locale="$locale"
        :categories="$categories"
        :products="$products"
        :filters="$filters"
        :api-error="$apiError"
    />
@endsection
