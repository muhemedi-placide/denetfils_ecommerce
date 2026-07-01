@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Boutique '.config('shop.name') : config('shop.name').' Shop'))
@section('description', $locale === 'fr' ? 'Explorez les rayons '.config('shop.name').' : sauces, pikliz, epices, boissons, produits frais et coffrets gourmands.' : 'Explore '.config('shop.name').' aisles: sauces, pikliz, spices, drinks, fresh products and gift boxes.')
@section('canonical', route('shop.index', ['locale' => $locale]))

@section('content')
    <section class="store-container pt-6">
        <div class="store-hero">
            <div>
                <p class="mb-3 font-bold uppercase tracking-wider text-[#f97316]">{{ $locale === 'fr' ? 'Boutique' : 'Shop' }}</p>
                <h1>{{ $locale === 'fr' ? 'Tout le' : 'The whole' }} <span class="store-hero-accent">{{ $locale === 'fr' ? 'marché' : 'market' }}</span>.</h1>
                <p>{{ $locale === 'fr' ? 'Parcourez nos rayons, filtrez par catégorie et retrouvez vos saveurs préférées.' : 'Browse our aisles, filter by category and find your favorite flavors.' }}</p>
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
@endsection
