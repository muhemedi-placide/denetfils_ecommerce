@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Notre histoire' : 'Our story') . ' | ' . config('shop.name'))
@section('description', $locale === 'fr' ? 'Découvrez l’histoire et la mission de '.config('shop.name').' : un marché de saveurs authentiques, directement lié aux producteurs.' : 'Discover '.config('shop.name').' story and mission: a market of authentic flavors directly connected to producers.')
@section('canonical', route('pages.about', ['locale' => $locale]))

@section('content')
    @php
        $leavesImage = asset('assets/products/hero-market.jpg');
        $peppersImage = asset('assets/products/peppers.jpg');
    @endphp

    <section class="relative overflow-hidden bg-forest px-4 py-24 text-cream sm:px-8 lg:py-36" style="background-image: linear-gradient(90deg, rgba(18,76,32,.88), rgba(18,76,32,.92)), url('{{ $leavesImage }}'); background-size: cover; background-position: center;">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.35em] text-[#ff9817]">{{ $locale === 'fr' ? 'Notre histoire' : 'Our story' }}</p>
            <h1 class="mt-6 max-w-5xl text-6xl font-black leading-[0.98] tracking-tight text-cream sm:text-7xl lg:text-8xl">
                {{ $locale === 'fr' ? 'Un marché. Mille saveurs. Une mission.' : 'One market. A thousand flavors. One mission.' }}
            </h1>
            <p class="mt-8 max-w-3xl text-lg font-semibold leading-9 text-cream/90">
                {{ $locale === 'fr' ? 'Nous avons grandi entre deux cuisines : celle de la grand-mère qui mijote l’épis, et celle du supermarché aux rayons fades. '.config('shop.name').' est né pour relier les deux.' : 'We grew up between two kitchens: the grandmother’s slow-cooked seasoning and the supermarket aisle without flavor. '.config('shop.name').' was born to reconnect both worlds.' }}
            </p>
        </div>
    </section>

    <section class="bg-cream px-4 py-20 dark:bg-ink sm:px-8 lg:py-28">
        <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.35em] text-coral">{{ $locale === 'fr' ? 'Manifeste' : 'Manifesto' }}</p>
                <h2 class="mt-5 max-w-xl text-5xl font-black leading-[1.04] tracking-tight text-forest dark:text-meadow sm:text-6xl">
                    {{ $locale === 'fr' ? 'Les vrais goûts, les vrais gens.' : 'Real flavors, real people.' }}
                </h2>
            </div>
            <div class="max-w-2xl space-y-7 text-base font-semibold leading-8 text-forest/75 dark:text-cream/75">
                <p>{{ $locale === 'fr' ? 'Chez '.config('shop.name').', chaque produit a un visage. Un nom. Une parcelle. Une recette.' : 'At '.config('shop.name').', every product has a face. A name. A field. A recipe.' }}</p>
                <p>{{ $locale === 'fr' ? 'Nous travaillons directement avec des coopératives en Haïti, en Guadeloupe, en Martinique, au Cameroun, en Côte d’Ivoire. Pas d’intermédiaire, pas de marque blanche.' : 'We work directly with cooperatives in Haiti, Guadeloupe, Martinique, Cameroon and Côte d’Ivoire. No unnecessary middlemen, no white-label shortcut.' }}</p>
                <p>{{ $locale === 'fr' ? 'Notre engagement : un prix juste pour le producteur, un produit authentique pour vous, et un colis qui voyage le moins possible.' : 'Our commitment: a fair price for producers, an authentic product for customers, and a parcel that travels as little as possible.' }}</p>
            </div>
        </div>

        <div class="mx-auto mt-20 grid max-w-7xl gap-5 lg:grid-cols-3">
            <article class="rounded-[1.5rem] bg-sunshine p-8 text-forest shadow-sm">
                <p class="text-5xl font-black tracking-tight">120+</p>
                <p class="mt-3 text-xs font-black uppercase tracking-[0.22em] text-forest/70">{{ $locale === 'fr' ? 'Producteurs partenaires' : 'Partner producers' }}</p>
            </article>
            <article class="rounded-[1.5rem] bg-sunshine p-8 text-forest shadow-sm">
                <p class="text-5xl font-black tracking-tight">8</p>
                <p class="mt-3 text-xs font-black uppercase tracking-[0.22em] text-forest/70">{{ $locale === 'fr' ? 'Pays sourcés' : 'Sourced countries' }}</p>
            </article>
            <article class="rounded-[1.5rem] bg-sunshine p-8 text-forest shadow-sm">
                <p class="text-5xl font-black tracking-tight">0</p>
                <p class="mt-3 text-xs font-black uppercase tracking-[0.22em] text-forest/70">{{ $locale === 'fr' ? 'Intermédiaires' : 'Middlemen' }}</p>
            </article>
        </div>
    </section>

    <section class="bg-cream px-4 pb-16 dark:bg-ink sm:px-8 lg:pb-20">
        <div class="mx-auto grid max-w-[112rem] overflow-hidden rounded-[2rem] bg-coral text-cream shadow-tropical lg:grid-cols-2">
            <div class="flex min-h-[300px] flex-col justify-center p-8 sm:min-h-[340px] sm:p-12 lg:h-[390px] lg:p-16">
                <h2 class="max-w-xl text-4xl font-black leading-tight text-cream sm:text-5xl lg:text-6xl">
                    {{ $locale === 'fr' ? 'Envie de cuisiner avec nous ?' : 'Want to cook with us?' }}
                </h2>
                <p class="mt-5 max-w-xl text-base font-semibold leading-8 text-cream/90">
                    {{ $locale === 'fr' ? 'Découvrez la boutique et trouvez vos prochains essentiels.' : 'Explore the shop and find your next essentials.' }}
                </p>
                <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="mt-8 inline-flex w-fit rounded-full bg-cream px-7 py-4 text-sm font-black uppercase tracking-wide text-forest transition hover:bg-sunshine" wire:navigate.hover>
                    {{ $locale === 'fr' ? 'Aller à la boutique' : 'Go to the shop' }}
                </a>
            </div>
            <div class="h-[300px] overflow-hidden sm:h-[340px] lg:h-[390px]">
                <img class="h-full w-full object-cover" src="{{ $peppersImage }}" alt="{{ $locale === 'fr' ? 'Piments tropicaux '.config('shop.name') : config('shop.name').' tropical peppers' }}" loading="lazy" decoding="async">
            </div>
        </div>
    </section>
@endsection
