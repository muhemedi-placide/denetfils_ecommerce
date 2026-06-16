@extends('layouts.admin')

@section('title', 'Catalogue')
@section('page_title', 'Catalogue produits')

@php
    $productRows = $products['data'] ?? [];
    $categoryRows = $categories['data'] ?? [];
@endphp

@section('content')
    @if (! ($products['ok'] ?? false))
        <div class="mb-5 rounded-3xl border border-red-200 bg-red-50 p-5 text-sm font-semibold text-red-700">{{ $products['message'] ?? 'Catalogue indisponible.' }}</div>
    @endif

    <section class="rounded-3xl border border-black/5 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#2f7d1b]">Catalogue</p>
                <h2 class="mt-2 text-2xl font-black text-[#12210f]">Produits et categories</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#1f2a1c]/60">Vue back-office consommee depuis l API admin. Elle permet de verifier rapidement publication, stock, categorie, prix et qualite de presentation.</p>
            </div>
            <div class="grid gap-2 sm:grid-cols-2">
                <button type="button" class="rounded-2xl bg-[#12210f] px-5 py-3 text-sm font-black text-white opacity-70" title="Action API a brancher ensuite">Nouveau produit</button>
                <button type="button" class="rounded-2xl border border-black/10 px-5 py-3 text-sm font-black text-[#12210f] opacity-70" title="Action API a brancher ensuite">Nouvelle categorie</button>
            </div>
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-5">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Rechercher produit, SKU..." class="min-h-[46px] rounded-2xl border border-black/10 bg-[#f7f5ef] px-4 text-sm font-semibold outline-none focus:border-[#2f7d1b] md:col-span-2">
            <select name="publication_status" class="min-h-[46px] rounded-2xl border border-black/10 bg-[#f7f5ef] px-4 text-sm font-semibold outline-none focus:border-[#2f7d1b]">
                <option value="">Publication</option>
                <option value="published" @selected(($filters['publication_status'] ?? '') === 'published')>Publies</option>
                <option value="draft" @selected(($filters['publication_status'] ?? '') === 'draft')>Brouillons</option>
            </select>
            <select name="stock_status" class="min-h-[46px] rounded-2xl border border-black/10 bg-[#f7f5ef] px-4 text-sm font-semibold outline-none focus:border-[#2f7d1b]">
                <option value="">Stock</option>
                <option value="in_stock" @selected(($filters['stock_status'] ?? '') === 'in_stock')>En stock</option>
                <option value="low_stock" @selected(($filters['stock_status'] ?? '') === 'low_stock')>Stock faible</option>
                <option value="out_of_stock" @selected(($filters['stock_status'] ?? '') === 'out_of_stock')>Rupture</option>
            </select>
            <button class="min-h-[46px] rounded-2xl bg-[#f15b2a] px-5 text-sm font-black uppercase tracking-wide text-white">Filtrer</button>
        </form>
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[1fr_360px]">
        <div class="rounded-3xl border border-black/5 bg-white p-4 shadow-sm sm:p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-xl font-black text-[#12210f]">Produits</h3>
                <span class="rounded-full bg-[#e8f6dd] px-3 py-1 text-xs font-black text-[#2f7d1b]">{{ data_get($products, 'meta.total', count($productRows)) }} items</span>
            </div>
            <div class="overflow-hidden rounded-2xl border border-black/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-black/5 text-left text-sm">
                        <thead class="bg-[#f7f5ef] text-xs font-black uppercase tracking-wide text-[#1f2a1c]/55">
                            <tr>
                                <th class="px-4 py-3">Produit</th>
                                <th class="px-4 py-3">Categorie</th>
                                <th class="px-4 py-3">Prix</th>
                                <th class="px-4 py-3">Stock</th>
                                <th class="px-4 py-3">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5">
                            @forelse ($productRows as $product)
                                @php
                                    $name = data_get($product, 'name.fr') ?: data_get($product, 'name.en') ?: $product['slug'] ?? 'Produit';
                                    $category = data_get($product, 'category.name.fr') ?: data_get($product, 'category.name.en') ?: data_get($product, 'category.slug', '-');
                                @endphp
                                <tr class="align-top">
                                    <td class="px-4 py-4">
                                        <p class="font-black text-[#12210f]">{{ $name }}</p>
                                        <p class="mt-1 text-xs font-semibold text-[#1f2a1c]/55">{{ $product['sku'] ?? '-' }} - {{ $product['slug'] ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-[#1f2a1c]/65">{{ $category }}</td>
                                    <td class="px-4 py-4 font-bold text-[#12210f]">{{ number_format((int) ($product['price_cents'] ?? 0) / 100, 2, ',', ' ') }} {{ $product['currency'] ?? 'EUR' }}</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full bg-[#f7f5ef] px-3 py-1 text-xs font-black text-[#12210f]">{{ $product['stock_quantity'] ?? 0 }}</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-black {{ ($product['is_active'] ?? false) ? 'bg-[#e8f6dd] text-[#2f7d1b]' : 'bg-[#fff0e8] text-[#f15b2a]' }}">
                                            {{ ($product['is_active'] ?? false) ? 'Publie' : 'Brouillon' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-[#1f2a1c]/55">Aucun produit trouve.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside class="rounded-3xl border border-black/5 bg-[#12210f] p-5 text-white shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#8ed957]">Categories</p>
                    <h3 class="mt-2 text-xl font-black">Organisation</h3>
                </div>
                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-black">{{ data_get($categories, 'meta.total', count($categoryRows)) }}</span>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($categoryRows as $category)
                    @php $name = data_get($category, 'name.fr') ?: data_get($category, 'name.en') ?: $category['slug'] ?? 'Categorie'; @endphp
                    <div class="rounded-2xl bg-white/10 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-black">{{ $name }}</p>
                                <p class="mt-1 text-xs text-white/55">/{{ $category['slug'] ?? '-' }}</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#12210f]">{{ $category['products_count'] ?? 0 }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-white/10 p-4 text-sm text-white/65">Aucune categorie disponible.</div>
                @endforelse
            </div>
        </aside>
    </section>
@endsection
