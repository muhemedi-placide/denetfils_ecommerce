@extends('layouts.shop')

@php
    $isEnglish = $locale === 'en';
    $gallery = collect($product['images'] ?? [])->filter(fn ($image) => filled($image['url'] ?? null))->values();
    if ($gallery->isEmpty() && filled(data_get($product, 'primary_image.url'))) {
        $gallery = collect([$product['primary_image']]);
    }
    if ($gallery->isEmpty()) {
        $gallery = collect([['url' => asset('assets/products/hero-basket.jpg'), 'alt_text' => $product['name']]]);
    }
    $heroImage = $gallery->first()['url'];
    $heroAlt = $gallery->first()['alt_text'] ?? $product['name'];
    $commerce = $product['commerce'] ?? [];
    $rich = $product['rich_content'] ?? [];
    $ratingAverage = (float) data_get($commerce, 'rating.average', 0);
    $ratingCount = (int) data_get($commerce, 'rating.count', 0);
    $isAvailable = (bool) data_get($commerce, 'is_available', false);
    $availability = data_get($commerce, 'availability', 'out_of_stock');
    $badges = collect($rich['badges'] ?? [])->filter()->take(4);
    $highlights = collect($rich['highlights'] ?? [])->filter();
    $certifications = collect($rich['certifications'] ?? [])->filter();
    $allergens = collect($rich['allergens'] ?? [])->filter();
    $nutrition = collect($rich['nutrition_facts'] ?? [])->filter(fn ($value) => ! is_array($value));
@endphp

@section('title', data_get($product, 'seo.meta.title', $product['name'].' | '.config('shop.name')))
@section('description', data_get($product, 'seo.meta.description', $product['short_description'] ?: $product['description']))
@section('canonical', data_get($product, 'seo.canonical', route('products.show', ['locale' => $locale, 'slug' => $product['slug']])))
@section('og_type', 'product')
@section('og_image', $heroImage)
@section('preload_image', $heroImage)

@section('content')
    <section class="store-page py-8 lg:py-12">
        <div class="store-container">
            <nav class="mobile-scrollbarless flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400" aria-label="{{ $isEnglish ? 'Breadcrumb' : 'Fil d’Ariane' }}">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-orange-600" wire:navigate>{{ __('home.nav.home') }}</a>
                <span>/</span>
                <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="transition hover:text-orange-600" wire:navigate>{{ __('home.nav.shop') }}</a>
                @if(data_get($product, 'category.name'))<span>/</span><a href="{{ route('shop.index', ['locale' => $locale, 'category' => data_get($product, 'category.slug')]) }}" class="transition hover:text-orange-600" wire:navigate>{{ data_get($product, 'category.name') }}</a>@endif
                <span>/</span>
                <span class="font-semibold text-neutral-950 dark:text-white">{{ $product['name'] }}</span>
            </nav>

            <div class="mt-7 grid gap-8 lg:grid-cols-[minmax(0,1.04fr)_minmax(420px,.96fr)] lg:gap-12">
                <div x-data="{ active: @js(['url' => $heroImage, 'alt' => $heroAlt]) }" class="min-w-0">
                    <div class="relative overflow-hidden rounded-2xl border border-neutral-200 bg-white dark:border-white/10 dark:bg-white/5">
                        <img :src="active.url" :alt="active.alt" class="aspect-square w-full object-cover" width="900" height="900" fetchpriority="high" decoding="async">
                        @if (($product['discount_percent'] ?? 0) > 0)
                            <span class="absolute left-4 top-4 rounded-full bg-orange-600 px-4 py-2 text-xs font-black text-white">−{{ $product['discount_percent'] }}%</span>
                        @endif
                        @if (! $isAvailable)
                            <span class="absolute inset-x-4 bottom-4 rounded-xl bg-black/80 px-4 py-3 text-center text-sm font-black text-white">{{ $isEnglish ? 'Currently unavailable' : 'Actuellement indisponible' }}</span>
                        @endif
                    </div>

                    @if ($gallery->count() > 1)
                        <div class="mobile-scrollbarless mt-3 flex gap-3 overflow-x-auto pb-1" aria-label="{{ $isEnglish ? 'Product gallery' : 'Galerie du produit' }}">
                            @foreach ($gallery as $image)
                                <button type="button" class="shrink-0 overflow-hidden rounded-xl border-2 border-transparent transition hover:border-orange-500 focus:border-orange-500 focus:outline-none" x-on:click="active = @js(['url' => $image['url'], 'alt' => $image['alt_text'] ?? $product['name']])">
                                    <img src="{{ $image['url'] }}" alt="{{ $image['alt_text'] ?? $product['name'] }}" class="h-20 w-20 object-cover sm:h-24 sm:w-24" loading="lazy" decoding="async">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        @if(data_get($product, 'category.name'))<span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-orange-700 dark:bg-orange-500/15 dark:text-orange-300">{{ data_get($product, 'category.name') }}</span>@endif
                        @foreach ($badges as $badge)<span class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-bold text-neutral-700 dark:border-white/15 dark:text-neutral-200">{{ $badge }}</span>@endforeach
                    </div>

                    @if($product['brand'] ?? null)<p class="mt-5 text-sm font-black uppercase tracking-[0.16em] text-orange-600">{{ $product['brand'] }}</p>@endif
                    <h1 class="mt-3 text-4xl font-black leading-tight tracking-tight text-neutral-950 dark:text-white sm:text-5xl">{{ $product['name'] }}</h1>
                    @if($product['short_description'])<p class="mt-4 text-lg leading-8 text-neutral-600 dark:text-neutral-300">{{ $product['short_description'] }}</p>@endif

                    @if ($ratingCount > 0)
                        <div class="mt-4 flex items-center gap-2 text-sm">
                            <span class="text-lg tracking-tight text-orange-500" aria-hidden="true">★★★★★</span>
                            <strong>{{ number_format($ratingAverage, 1, ',', ' ') }}</strong>
                            <span class="text-neutral-500">({{ $ratingCount }} {{ $isEnglish ? 'reviews' : 'avis' }})</span>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-wrap items-end gap-x-4 gap-y-2 border-y border-neutral-200 py-5 dark:border-white/10">
                        <strong class="text-4xl font-black text-neutral-950 dark:text-white">{{ $product['formatted_price'] }}</strong>
                        @if($product['formatted_compare_at_price'] ?? null)<span class="pb-1 text-lg font-semibold text-neutral-400 line-through">{{ $product['formatted_compare_at_price'] }}</span>@endif
                        <span class="pb-1 text-xs font-bold uppercase tracking-wide text-neutral-500">{{ $isEnglish ? 'VAT included' : 'TTC' }}</span>
                    </div>

                    <div class="mt-5 grid gap-2 text-sm sm:grid-cols-2">
                        <div class="flex items-center gap-2 font-semibold {{ $isAvailable ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600' }}"><span class="h-2.5 w-2.5 rounded-full {{ $isAvailable ? 'bg-emerald-500' : 'bg-red-500' }}"></span>{{ match($availability) { 'in_stock' => $isEnglish ? 'In stock' : 'En stock', 'low_stock' => $isEnglish ? 'Low stock' : 'Stock faible', default => $isEnglish ? 'Out of stock' : 'Rupture de stock' } }}</div>
                        <div class="text-neutral-600 dark:text-neutral-300">{{ $isEnglish ? 'Reference' : 'Référence' }} : <strong>{{ $product['sku'] }}</strong></div>
                        @if($product['origin'])<div class="text-neutral-600 dark:text-neutral-300">{{ $isEnglish ? 'Origin' : 'Origine' }} : <strong>{{ $product['origin'] }}</strong></div>@endif
                        @if($product['weight_grams'])<div class="text-neutral-600 dark:text-neutral-300">{{ $isEnglish ? 'Net weight' : 'Poids net' }} : <strong>{{ $product['weight_grams'] }} g</strong></div>@endif
                        @if($product['unit_label'] ?? null)<div class="text-neutral-600 dark:text-neutral-300">{{ $isEnglish ? 'Sold by' : 'Vendu à l’unité' }} : <strong>{{ $product['unit_label'] }}</strong></div>@endif
                    </div>

                    <livewire:shop.product-purchase-panel :locale="$locale" :product="$product" />

                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-neutral-50 p-4 text-sm dark:bg-white/5"><strong class="block text-neutral-950 dark:text-white">{{ $isEnglish ? 'Secure payment' : 'Paiement sécurisé' }}</strong><span class="mt-1 block text-neutral-500">Visa, Mastercard, PayPal</span></div>
                        <div class="rounded-xl bg-neutral-50 p-4 text-sm dark:bg-white/5"><strong class="block text-neutral-950 dark:text-white">{{ $isEnglish ? 'Tracked delivery' : 'Livraison suivie' }}</strong><span class="mt-1 block text-neutral-500">{{ data_get($commerce, 'shipping.dispatch_time', $isEnglish ? 'Prepared promptly' : 'Préparation rapide') }}</span></div>
                        <div class="rounded-xl bg-neutral-50 p-4 text-sm dark:bg-white/5"><strong class="block text-neutral-950 dark:text-white">{{ $isEnglish ? 'Quality guarantee' : 'Qualité contrôlée' }}</strong><span class="mt-1 block text-neutral-500">{{ data_get($commerce, 'guarantee', config('shop.name')) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-neutral-200 bg-neutral-50 py-12 dark:border-white/10 dark:bg-white/[0.03] lg:py-16">
        <div class="store-container grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-8">
                <article>
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-orange-600">{{ $isEnglish ? 'Product details' : 'Détails du produit' }}</p>
                    <h2 class="mt-3 text-3xl font-black text-neutral-950 dark:text-white">{{ $isEnglish ? 'Description' : 'Description' }}</h2>
                    <div class="mt-4 whitespace-pre-line text-base leading-8 text-neutral-700 dark:text-neutral-300">{{ $product['description'] ?: $product['short_description'] }}</div>
                </article>

                @if ($highlights->isNotEmpty())
                    <article><h2 class="text-2xl font-black text-neutral-950 dark:text-white">{{ $isEnglish ? 'Why choose it' : 'Pourquoi le choisir' }}</h2><ul class="mt-4 grid gap-3 sm:grid-cols-2">@foreach($highlights as $highlight)<li class="flex gap-3 rounded-xl bg-white p-4 text-sm font-semibold dark:bg-white/5"><span class="text-orange-600">✓</span>{{ $highlight }}</li>@endforeach</ul></article>
                @endif

                @if (filled($rich['ingredients'] ?? null) || $allergens->isNotEmpty())
                    <article class="grid gap-5 sm:grid-cols-2">
                        @if(filled($rich['ingredients'] ?? null))<div><h3 class="font-black text-neutral-950 dark:text-white">{{ $isEnglish ? 'Ingredients' : 'Ingrédients' }}</h3><p class="mt-2 text-sm leading-7 text-neutral-600 dark:text-neutral-300">{{ $rich['ingredients'] }}</p></div>@endif
                        @if($allergens->isNotEmpty())<div><h3 class="font-black text-neutral-950 dark:text-white">{{ $isEnglish ? 'Allergens' : 'Allergènes' }}</h3><p class="mt-2 text-sm leading-7 text-neutral-600 dark:text-neutral-300">{{ $allergens->join(', ') }}</p></div>@endif
                    </article>
                @endif

                @if ($nutrition->isNotEmpty())
                    <article><h2 class="text-2xl font-black text-neutral-950 dark:text-white">{{ $isEnglish ? 'Nutrition information' : 'Informations nutritionnelles' }}</h2><dl class="mt-4 divide-y divide-neutral-200 rounded-xl border border-neutral-200 bg-white dark:divide-white/10 dark:border-white/10 dark:bg-white/5">@foreach($nutrition as $key => $value)<div class="flex justify-between gap-4 px-4 py-3 text-sm"><dt>{{ \Illuminate\Support\Str::headline($key) }}</dt><dd class="font-bold">{{ $value }}</dd></div>@endforeach</dl></article>
                @endif
            </div>

            <aside class="space-y-4">
                @if(filled($rich['storage_instructions'] ?? null))<div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-white/10 dark:bg-white/5"><h3 class="font-black">{{ $isEnglish ? 'Storage' : 'Conservation' }}</h3><p class="mt-2 text-sm leading-7 text-neutral-600 dark:text-neutral-300">{{ $rich['storage_instructions'] }}</p></div>@endif
                @if(filled($rich['usage_instructions'] ?? null))<div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-white/10 dark:bg-white/5"><h3 class="font-black">{{ $isEnglish ? 'How to use' : 'Conseils d’utilisation' }}</h3><p class="mt-2 text-sm leading-7 text-neutral-600 dark:text-neutral-300">{{ $rich['usage_instructions'] }}</p></div>@endif
                @if($certifications->isNotEmpty())<div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-white/10 dark:bg-white/5"><h3 class="font-black">{{ $isEnglish ? 'Certifications' : 'Certifications' }}</h3><div class="mt-3 flex flex-wrap gap-2">@foreach($certifications as $certification)<span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">{{ $certification }}</span>@endforeach</div></div>@endif
                @if(filled(data_get($commerce, 'return_policy')))<div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-white/10 dark:bg-white/5"><h3 class="font-black">{{ $isEnglish ? 'Returns' : 'Retours' }}</h3><p class="mt-2 text-sm leading-7 text-neutral-600 dark:text-neutral-300">{{ data_get($commerce, 'return_policy') }}</p></div>@endif
            </aside>
        </div>
    </section>

    @if (! empty($relatedProducts))
        <section class="store-page py-12 lg:py-16">
            <div class="store-container">
                <div class="flex items-end justify-between gap-4"><div><p class="text-xs font-black uppercase tracking-[0.16em] text-orange-600">{{ $isEnglish ? 'Discover more' : 'À découvrir' }}</p><h2 class="mt-2 text-3xl font-black text-neutral-950 dark:text-white">{{ $isEnglish ? 'You may also like' : 'Vous aimerez aussi' }}</h2></div><a href="{{ route('shop.index', ['locale' => $locale]) }}" class="btn-secondary hidden sm:inline-flex" wire:navigate>{{ $isEnglish ? 'View all' : 'Tout voir' }}</a></div>
                <div class="mobile-scrollbarless mt-7 flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
                    @foreach(array_slice($relatedProducts, 0, 3) as $related)
                        @php($relatedUrl = route('products.show', ['locale' => $locale, 'slug' => $related['slug']]))
                        <article class="group min-w-[270px] overflow-hidden rounded-2xl border border-neutral-200 bg-white transition hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-white/5 lg:min-w-0">
                            <a href="{{ $relatedUrl }}" class="block overflow-hidden" wire:navigate>@if(data_get($related, 'primary_image.url'))<img src="{{ data_get($related, 'primary_image.url') }}" alt="{{ $related['name'] }}" class="h-64 w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">@else<div class="grid h-64 place-items-center bg-orange-50 text-orange-600">MP</div>@endif</a>
                            <div class="p-5"><p class="text-xs font-black uppercase tracking-wide text-orange-600">{{ data_get($related, 'category.name') }}</p><a href="{{ $relatedUrl }}" wire:navigate><h3 class="mt-2 text-xl font-black text-neutral-950 dark:text-white">{{ $related['name'] }}</h3></a><p class="mt-2 line-clamp-2 text-sm text-neutral-600 dark:text-neutral-300">{{ $related['short_description'] ?: $related['description'] }}</p><div class="mt-5 flex items-center justify-between"><strong class="text-2xl font-black">{{ $related['formatted_price'] }}</strong><a href="{{ $relatedUrl }}" class="rounded-full bg-orange-600 px-4 py-2 text-xs font-black text-white" wire:navigate>{{ $isEnglish ? 'View' : 'Voir' }}</a></div></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
