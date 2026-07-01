@extends('layouts.admin')

@section('title', 'Stock')
@section('page_title', 'Stock et alertes')
@section('page_subtitle', 'Controle rapide des ruptures, seuils et references a mettre a jour.')

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
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">{{ $inventory['message'] ?? 'Inventaire indisponible.' }}</div>
    @endif

    <section class="admin-card p-5 sm:p-6">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="admin-kicker">Inventaire</p>
                <h2 class="mt-2 admin-heading">Surveillance du stock</h2>
                <p class="mt-2 max-w-2xl admin-muted">Produits en rupture, references faibles et variantes a controler.</p>
            </div>
            <span class="admin-pill">Seuil actuel : {{ $filters['threshold'] ?? 5 }}</span>
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-6">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Produit, SKU, slug..." class="admin-input md:col-span-2">
            <select name="category_id" class="admin-select">
                <option value="">Toutes categories</option>
                @foreach ($categoriesRows as $category)
                    @php $name = data_get($category, 'name.fr') ?: data_get($category, 'name.en') ?: $category['slug'] ?? 'Categorie'; @endphp
                    <option value="{{ $category['id'] }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category['id'])>{{ $name }}</option>
                @endforeach
            </select>
            <select name="status" class="admin-select">
                <option value="">Tous statuts</option>
                @foreach ($statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <input name="threshold" type="number" min="0" max="100" value="{{ $filters['threshold'] ?? 5 }}" class="admin-input" placeholder="Seuil">
            <button class="admin-btn">Analyser</button>
        </form>
    </section>

    <section class="mt-6 admin-card p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-xl font-black text-ink dark:text-cream">Produits surveilles</h3>
            <span class="admin-pill">{{ data_get($inventory, 'meta.total', count($rows)) }} lignes</span>
        </div>

        <div class="overflow-hidden rounded-xl border border-leaf/10 dark:border-white/10">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">Categorie</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Variantes</th>
                            <th class="px-4 py-3">Maj</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $product)
                            @php
                                $name = data_get($product, 'preview_name.fr') ?: data_get($product, 'preview_name.en') ?: $product['slug'] ?? 'Produit';
                                $status = $product['stock_status'] ?? 'in_stock';
                                $productId = $product['id'] ?? null;
                                $imageUrl = data_get($product, 'primary_image.url');
                                $imageAlt = data_get($product, 'primary_image.alt_text.fr') ?: data_get($product, 'primary_image.alt_text.en') ?: $name;
                                $statusClass = match ($status) {
                                    'out_of_stock' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-200',
                                    'low_stock' => 'bg-amber-100 text-amber-700 dark:bg-amber-300/15 dark:text-amber-200',
                                    'inactive' => 'bg-cocoa/10 text-cocoa/55 dark:bg-white/10 dark:text-cream/55',
                                    default => 'bg-mint text-leaf dark:bg-meadow/15 dark:text-meadow',
                                };
                            @endphp
                            <tr class="align-top transition hover:bg-linen dark:hover:bg-white/5">
                                <td class="px-4 py-4">
                                    <div class="flex min-w-[240px] items-center gap-3">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="h-14 w-14 shrink-0 rounded-xl object-cover ring-1 ring-leaf/10 dark:ring-white/10" loading="lazy" decoding="async">
                                        @else
                                            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-mint text-xs font-black text-forest dark:bg-white/10 dark:text-meadow">DF</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate font-black text-ink dark:text-cream">{{ $name }}</p>
                                            <p class="mt-1 truncate text-xs font-semibold text-cocoa/55 dark:text-cream/55">{{ $product['sku'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-cocoa/65 dark:text-cream/65">{{ data_get($product, 'category.slug', '-') }}</td>
                                <td class="px-4 py-4"><span class="text-2xl font-black text-ink dark:text-cream">{{ $product['stock_quantity'] ?? 0 }}</span></td>
                                <td class="px-4 py-4"><span class="rounded-full px-3 py-1 text-xs font-black {{ $statusClass }}">{{ $statusLabels[$status] ?? $status }}</span></td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse (($product['variants'] ?? []) as $variant)
                                            <span class="rounded-full bg-linen px-2 py-1 text-[11px] font-bold text-cocoa/65 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/65 dark:ring-white/10">{{ $variant['sku'] ?? '-' }}: {{ $variant['stock_quantity'] ?? 0 }}</span>
                                        @empty
                                            <span class="text-xs text-cocoa/45 dark:text-cream/45">Aucune</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-xs text-cocoa/55 dark:text-cream/55">{{ $product['updated_at'] ?? '-' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        @if ($productId)
                                            <button type="button" data-dialog-target="inventory-show-{{ $productId }}" class="admin-btn-secondary min-h-0 px-3 py-2 text-xs">Voir</button>
                                            <button type="button" data-dialog-target="inventory-stock-{{ $productId }}" class="admin-btn min-h-0 px-3 py-2 text-xs">Stock</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-cocoa/55 dark:text-cream/55">Aucun produit trouve dans l inventaire.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('admin_modals')
    @foreach ($rows as $product)
        @php
            $productId = $product['id'] ?? null;
            $name = data_get($product, 'preview_name.fr') ?: data_get($product, 'preview_name.en') ?: $product['slug'] ?? 'Produit';
            $status = $product['stock_status'] ?? 'in_stock';
            $imageUrl = data_get($product, 'primary_image.url');
            $imageAlt = data_get($product, 'primary_image.alt_text.fr') ?: data_get($product, 'primary_image.alt_text.en') ?: $name;
        @endphp
        @continue(! $productId)

        <dialog id="inventory-show-{{ $productId }}" class="admin-dialog admin-dialog-wide">
            <div class="admin-modal-card p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-4">
                        @if ($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="h-20 w-20 shrink-0 rounded-2xl object-cover ring-1 ring-leaf/10 dark:ring-white/10" loading="lazy" decoding="async">
                        @else
                            <span class="grid h-20 w-20 shrink-0 place-items-center rounded-2xl bg-mint text-sm font-black text-forest dark:bg-white/10 dark:text-meadow">DF</span>
                        @endif
                        <div class="min-w-0">
                            <p class="admin-kicker">Inventaire</p>
                            <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $name }}</h2>
                            <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">{{ $product['sku'] ?? '-' }} - {{ $product['slug'] ?? '-' }}</p>
                        </div>
                    </div>
                    <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                    </button>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-4">
                    <div class="admin-panel p-4"><p class="admin-kicker">Stock</p><p class="mt-2 text-2xl font-black">{{ $product['stock_quantity'] ?? 0 }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Seuil</p><p class="mt-2 text-2xl font-black">{{ $product['low_stock_threshold'] ?? ($filters['threshold'] ?? 5) }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Statut</p><p class="mt-2 text-lg font-black">{{ $statusLabels[$status] ?? $status }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Prix</p><p class="mt-2 text-lg font-black">{{ number_format((int) ($product['price_cents'] ?? 0) / 100, 2, ',', ' ') }} {{ $product['currency'] ?? 'EUR' }}</p></div>
                </div>
                <div class="mt-5 admin-panel p-4">
                    <p class="admin-kicker">Variantes</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse (($product['variants'] ?? []) as $variant)
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-cocoa ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream dark:ring-white/10">{{ $variant['sku'] ?? '-' }} : {{ $variant['stock_quantity'] ?? 0 }}</span>
                        @empty
                            <span class="text-sm text-cocoa/55 dark:text-cream/55">Aucune variante.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </dialog>

        <dialog id="inventory-stock-{{ $productId }}" class="admin-dialog" @if(session('admin_modal') === "product-stock-{$productId}") data-open-on-load @endif>
            <form method="POST" action="{{ route('admin.catalog.products.stock', ['locale' => $locale, 'product' => $productId]) }}" class="admin-modal-card p-5 sm:p-6">
                @csrf
                @method('PATCH')
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="admin-kicker">Mise a jour stock</p>
                        <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $name }}</h2>
                    </div>
                    <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                    </button>
                </div>
                <label class="mt-5 block">
                    <span class="admin-kicker mb-2 block">Quantite disponible</span>
                    <input name="stock_quantity" value="{{ old('stock_quantity', $product['stock_quantity'] ?? 0) }}" type="number" min="0" class="admin-input" required>
                </label>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" data-dialog-close class="admin-btn-secondary">Annuler</button>
                    <button class="admin-btn">Mettre a jour</button>
                </div>
            </form>
        </dialog>
    @endforeach
@endpush
