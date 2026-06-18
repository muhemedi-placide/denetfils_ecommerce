@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Boutique Marche Peyi' : 'Marche Peyi Shop'))
@section('description', $locale === 'fr' ? 'Explorez les rayons Marche Peyi : sauces, pikliz, epices, boissons, produits frais et coffrets gourmands.' : 'Explore Marche Peyi aisles: sauces, pikliz, spices, drinks, fresh products and gift boxes.')
@section('canonical', route('shop.index', ['locale' => $locale]))

@section('content')
    <section class="bg-sunshine px-4 py-20 sm:px-8 lg:py-28">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.35em] text-coral">{{ $locale === 'fr' ? 'Boutique' : 'Shop' }}</p>
            <h1 class="mt-4 max-w-4xl text-6xl font-black leading-none tracking-tight text-forest sm:text-7xl lg:text-8xl">
                {{ $locale === 'fr' ? 'Tout le marché.' : 'The whole market.' }}
            </h1>
            <p class="mt-6 max-w-xl text-base font-semibold leading-7 text-forest/80">
                {{ $locale === 'fr' ? 'Parcourez nos rayons. Filtrez par catégorie, triez par prix, ajoutez au panier — vos saveurs préférées arrivent en 48h.' : 'Browse our aisles. Filter by category, sort by price, add to cart — your favorite flavors arrive within 48h.' }}
            </p>
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
