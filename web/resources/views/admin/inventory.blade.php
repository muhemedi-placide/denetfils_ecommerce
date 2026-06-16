@extends('layouts.admin')

@section('title', 'Stock')
@section('page_title', 'Stock et alertes')

@php
    $rows = $inventory['data'] ?? [];
    $categoriesRows = $categories['data'] ?? [];
    $statusLabels = [
        'in_stock' => 'En stock',
        'low_stock' => 'Stock faible',
        'out_of_stock' => 'Rupture',
        'inactive' => 'Inactif',
    ];
@endphp

@section('content')
    @if (! ($inventory['ok'] ?? false))
        <div class="mb-5 rounded-3xl border border-red-200 bg-red-50 p-5 text-sm font-semibold text-red-700">{{ $inventory['message'] ?? 'Inventaire indisponible.' }}</div>
    @endif

    <section class="rounded-3xl border border-black/5 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#2f7d1b]">Inventaire</p>
                <h2 class="mt-2 text-2xl font-black text-[#12210f]">Surveillance du stock</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#1f2a1c]/60">Identifiez les produits en rupture, les references faibles et les variantes a controler avant les ventes ou campagnes marketing.</p>
            </div>
            <span class="rounded-full bg-[#e8f6dd] px-4 py-2 text-sm font-black text-[#2f7d1b]">Seuil actuel : {{ $filters['threshold'] ?? 5 }}</span>
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-6">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Produit, SKU, slug..." class="min-h-[46px] rounded-2xl border border-black/10 bg-[#f7f5ef] px-4 text-sm font-semibold outline-none focus:border-[#2f7d1b] md:col-span-2">
            <select name="category_id" class="min-h-[46px] rounded-2xl border border-black/10 bg-[#f7f5ef] px-4 text-sm font-semibold outline-none focus:border-[#2f7d1b]">
                <option value="">Toutes categories</option>
                @foreach ($categoriesRows as $category)
                    @php $name = data_get($category, 'name.fr') ?: data_get($category, 'name.en') ?: $category['slug'] ?? 'Categorie'; @endphp
                    <option value="{{ $category['id'] }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category['id'])>{{ $name }}</option>
                @endforeach
            </select>
            <select name="status" class="min-h-[46px] rounded-2xl border border-black/10 bg-[#f7f5ef] px-4 text-sm font-semibold outline-none focus:border-[#2f7d1b]">
                <option value="">Tous statuts</option>
                @foreach ($statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <input name="threshold" type="number" min="0" max="100" value="{{ $filters['threshold'] ?? 5 }}" class="min-h-[46px] rounded-2xl border border-black/10 bg-[#f7f5ef] px-4 text-sm font-semibold outline-none focus:border-[#2f7d1b]" placeholder="Seuil">
            <button class="min-h-[46px] rounded-2xl bg-[#f15b2a] px-5 text-sm font-black uppercase tracking-wide text-white">Analyser</button>
        </form>
    </section>

    <section class="mt-6 rounded-3xl border border-black/5 bg-white p-4 shadow-sm sm:p-5">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-xl font-black text-[#12210f]">Produits surveilles</h3>
            <span class="rounded-full bg-[#f7f5ef] px-3 py-1 text-xs font-black text-[#1f2a1c]/60">{{ data_get($inventory, 'meta.total', count($rows)) }} lignes</span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-black/5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-black/5 text-left text-sm">
                    <thead class="bg-[#f7f5ef] text-xs font-black uppercase tracking-wide text-[#1f2a1c]/55">
                        <tr>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">Categorie</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Variantes</th>
                            <th class="px-4 py-3">Maj</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        @forelse ($rows as $product)
                            @php
                                $name = data_get($product, 'preview_name.fr') ?: data_get($product, 'preview_name.en') ?: $product['slug'] ?? 'Produit';
                                $status = $product['stock_status'] ?? 'in_stock';
                                $statusClass = match ($status) {
                                    'out_of_stock' => 'bg-red-50 text-red-700',
                                    'low_stock' => 'bg-[#fff0e8] text-[#f15b2a]',
                                    'inactive' => 'bg-black/5 text-[#1f2a1c]/55',
                                    default => 'bg-[#e8f6dd] text-[#2f7d1b]',
                                };
                            @endphp
                            <tr class="align-top">
                                <td class="px-4 py-4">
                                    <p class="font-black text-[#12210f]">{{ $name }}</p>
                                    <p class="mt-1 text-xs font-semibold text-[#1f2a1c]/55">{{ $product['sku'] ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-4 text-[#1f2a1c]/65">{{ data_get($product, 'category.slug', '-') }}</td>
                                <td class="px-4 py-4"><span class="text-2xl font-black text-[#12210f]">{{ $product['stock_quantity'] ?? 0 }}</span></td>
                                <td class="px-4 py-4"><span class="rounded-full px-3 py-1 text-xs font-black {{ $statusClass }}">{{ $statusLabels[$status] ?? $status }}</span></td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse (($product['variants'] ?? []) as $variant)
                                            <span class="rounded-full bg-[#f7f5ef] px-2 py-1 text-[11px] font-bold text-[#1f2a1c]/65">{{ $variant['sku'] ?? '-' }}: {{ $variant['stock_quantity'] ?? 0 }}</span>
                                        @empty
                                            <span class="text-xs text-[#1f2a1c]/45">Aucune</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-xs text-[#1f2a1c]/55">{{ $product['updated_at'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-[#1f2a1c]/55">Aucun produit trouve dans l inventaire.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
