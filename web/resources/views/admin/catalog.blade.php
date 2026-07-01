@extends('layouts.admin')

@section('title', $locale === 'en' ? 'Products' : 'Produits')
@section('page_title', $locale === 'en' ? 'Products' : 'Produits')
@section('page_subtitle', $locale === 'en' ? 'Product catalog, health, prices and sales inventory.' : 'Catalogue produit, santé, prix et stock de vente.')

@php
    $isEnglish = $locale === 'en';
    $productRows = $products['data'] ?? [];
    $categoryRows = $categories['data'] ?? [];
@endphp

@section('content')
    @if (! ($products['ok'] ?? false))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">{{ $products['message'] ?? ($isEnglish ? 'Catalog unavailable.' : 'Catalogue indisponible.') }}</div>
    @endif

    <section class="admin-card p-5 sm:p-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="admin-kicker">{{ $isEnglish ? 'Catalog' : 'Catalogue' }}</p>
                <h2 class="mt-2 admin-heading">{{ $isEnglish ? 'Product management' : 'Gestion des produits' }}</h2>
                <p class="mt-2 max-w-2xl admin-muted">{{ $isEnglish ? 'Search products, review their health and update stock directly.' : 'Recherchez les produits, contrôlez leur santé et modifiez directement leur stock.' }}</p>
            </div>
            <button type="button" data-dialog-target="product-create-wizard" class="admin-btn">{{ $isEnglish ? 'New product' : 'Nouveau produit' }}</button>
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-6">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ $isEnglish ? 'Search product or SKU…' : 'Rechercher un produit ou un SKU…' }}" class="admin-input md:col-span-2">
            <select name="category_id" class="admin-select">
                <option value="">{{ $isEnglish ? 'All categories' : 'Toutes les catégories' }}</option>
                @foreach ($categoryRows as $category)
                    @php $name = data_get($category, 'name.fr') ?: data_get($category, 'name.en') ?: $category['slug'] ?? 'Categorie'; @endphp
                    <option value="{{ $category['id'] }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category['id'])>{{ $name }}</option>
                @endforeach
            </select>
            <select name="publication_status" class="admin-select">
                <option value="">{{ $isEnglish ? 'Publication' : 'Publication' }}</option>
                <option value="published" @selected(($filters['publication_status'] ?? '') === 'published')>{{ $isEnglish ? 'Published' : 'Publiés' }}</option>
                <option value="draft" @selected(($filters['publication_status'] ?? '') === 'draft')>{{ $isEnglish ? 'Drafts' : 'Brouillons' }}</option>
            </select>
            <select name="stock_status" class="admin-select">
                <option value="">{{ $isEnglish ? 'Stock level' : 'Niveau de stock' }}</option>
                <option value="in_stock" @selected(($filters['stock_status'] ?? '') === 'in_stock')>{{ $isEnglish ? 'In stock' : 'En stock' }}</option>
                <option value="low_stock" @selected(($filters['stock_status'] ?? '') === 'low_stock')>{{ $isEnglish ? 'Low stock' : 'Stock faible' }}</option>
                <option value="out_of_stock" @selected(($filters['stock_status'] ?? '') === 'out_of_stock')>{{ $isEnglish ? 'Out of stock' : 'Rupture' }}</option>
            </select>
            <button class="admin-btn">{{ $isEnglish ? 'Filter' : 'Filtrer' }}</button>
        </form>
    </section>

    <section class="mt-6 admin-card p-4 sm:p-5">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-xl font-black text-ink dark:text-cream">{{ $isEnglish ? 'Product list' : 'Liste des produits' }}</h3>
            <span class="admin-pill">{{ data_get($products, 'meta.total', count($productRows)) }} {{ $isEnglish ? 'items' : 'éléments' }}</span>
        </div>
        <div class="overflow-hidden rounded-xl border border-leaf/10 dark:border-white/10">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ $isEnglish ? 'Product' : 'Produit' }}</th>
                            <th class="px-4 py-3">{{ $isEnglish ? 'Category' : 'Catégorie' }}</th>
                            <th class="px-4 py-3">{{ $isEnglish ? 'Prices' : 'Prix' }}</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">{{ $isEnglish ? 'Health' : 'Santé' }}</th>
                            <th class="px-4 py-3">{{ $isEnglish ? 'Status' : 'Statut' }}</th>
                            <th class="px-4 py-3 text-right">{{ $isEnglish ? 'Action' : 'Action' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productRows as $product)
                            @php
                                $name = data_get($product, 'name.fr') ?: data_get($product, 'name.en') ?: $product['slug'] ?? 'Produit';
                                $category = data_get($product, 'category.name.fr') ?: data_get($product, 'category.name.en') ?: data_get($product, 'category.slug', '-');
                                $isActive = (bool) ($product['is_active'] ?? false);
                                $productId = $product['id'] ?? null;
                                $imageUrl = data_get($product, 'primary_image.url') ?: data_get($product, 'images.0.url');
                                $imageAlt = data_get($product, 'primary_image.alt_text.fr') ?: data_get($product, 'primary_image.alt_text.en') ?: $name;
                            @endphp
                            <tr class="align-top transition hover:bg-linen dark:hover:bg-white/5">
                                <td class="px-4 py-4">
                                    <div class="flex min-w-[260px] items-center gap-3">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="h-14 w-14 shrink-0 rounded-xl object-cover ring-1 ring-leaf/10 dark:ring-white/10" loading="lazy" decoding="async">
                                        @else
                                            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-mint text-xs font-black text-forest dark:bg-white/10 dark:text-meadow">DF</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate font-black text-ink dark:text-cream">{{ $name }}</p>
                                            <p class="mt-1 truncate text-xs font-semibold text-cocoa/55 dark:text-cream/55">{{ $product['sku'] ?? '-' }} - {{ $product['slug'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-cocoa/65 dark:text-cream/65">{{ $category }}</td>
                                <td class="px-4 py-4 text-ink dark:text-cream">
                                    <strong>{{ number_format((int) ($product['price_cents'] ?? 0) / 100, 2, ',', ' ') }} {{ $product['currency'] ?? 'EUR' }} {{ $isEnglish ? 'incl. VAT' : 'TTC' }}</strong>
                                    <small class="mt-1 block admin-muted">{{ $isEnglish ? 'Purchase' : 'Achat' }}: {{ isset($product['purchase_price_cents']) ? number_format((int) $product['purchase_price_cents'] / 100, 2, ',', ' ').' EUR' : '—' }}</small>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($productId)
                                        <form method="POST" action="{{ route('admin.catalog.products.stock', ['locale' => $locale, 'product' => $productId]) }}" class="flex min-w-[125px] items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input name="stock_quantity" value="{{ $product['stock_quantity'] ?? 0 }}" type="number" min="0" class="admin-input h-10 w-20 px-3 py-2 text-center" aria-label="{{ $isEnglish ? 'Stock quantity' : 'Quantité en stock' }}">
                                            <button type="submit" class="admin-icon-btn h-10 w-10" title="{{ $isEnglish ? 'Update stock' : 'Mettre à jour le stock' }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg></button>
                                        </form>
                                    @else
                                        <span>{{ $product['stock_quantity'] ?? 0 }}</span>
                                    @endif
                                </td>
                                @php
                                    $health = $product['health'] ?? [];
                                    $healthScore = (int) ($health['score'] ?? 0);
                                    $healthLabel = match ($health['status'] ?? 'critical') {
                                        'excellent' => $isEnglish ? 'Excellent' : 'Excellent',
                                        'good' => $isEnglish ? 'Good' : 'Bon',
                                        'incomplete' => $isEnglish ? 'Incomplete' : 'Incomplet',
                                        default => $isEnglish ? 'Critical' : 'Critique',
                                    };
                                @endphp
                                <td class="px-4 py-4">
                                    <div class="min-w-[150px]">
                                        <div class="flex items-center justify-between gap-3 text-xs font-black"><span>{{ $healthLabel }}</span><span>{{ $healthScore }}%</span></div>
                                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-neutral-200 dark:bg-white/10"><div class="h-full rounded-full bg-orange-500" style="width: {{ $healthScore }}%"></div></div>
                                        <p class="mt-2 text-xs admin-muted"><strong class="text-orange-600">{{ $health['missing_count'] ?? 0 }}</strong> {{ $isEnglish ? 'missing for maximum visibility' : 'manquant(s) pour la visibilité maximale' }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $isActive ? 'bg-mint text-leaf dark:bg-meadow/15 dark:text-meadow' : 'bg-amber-100 text-amber-700 dark:bg-amber-300/15 dark:text-amber-200' }}">
                                        {{ $isActive ? ($isEnglish ? 'Published' : 'Publié') : ($isEnglish ? 'Draft' : 'Brouillon') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        @if ($productId)
                                            <a href="{{ route('admin.catalog.products.show', ['locale' => $locale, 'product' => $productId]) }}" class="admin-btn-secondary min-h-0 px-3 py-2 text-xs">{{ $isEnglish ? 'View details' : 'Voir le détail' }}</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-cocoa/55 dark:text-cream/55">{{ $isEnglish ? 'No products found.' : 'Aucun produit trouvé.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('admin_modals')
    @include('admin.partials.product-create-wizard')

    @if (false)
    <dialog id="product-create-modal" class="admin-dialog admin-dialog-wide">
        <form method="POST" action="{{ route('admin.catalog.products.store', ['locale' => $locale]) }}" class="admin-modal-card">
            @csrf
            <div class="flex items-start justify-between border-b border-leaf/10 p-5 dark:border-white/10 sm:p-6">
                <div>
                    <p class="admin-kicker">Produit</p>
                    <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">Nouveau produit</h2>
                </div>
                <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                </button>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                @if(session('admin_modal') === 'product-create' && $errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700 sm:col-span-2">{{ $errors->first() }}</div>
                @endif
                <label class="block">
                    <span class="admin-kicker mb-2 block">Nom FR</span>
                    <input name="name_fr" value="{{ old('name_fr') }}" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Nom EN</span>
                    <input name="name_en" value="{{ old('name_en') }}" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Slug</span>
                    <input name="slug" value="{{ old('slug') }}" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">SKU</span>
                    <input name="sku" value="{{ old('sku') }}" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Categorie</span>
                    <select name="category_id" class="admin-select" required>
                        <option value="">Choisir</option>
                        @foreach ($categoryRows as $category)
                            @php $name = data_get($category, 'name.fr') ?: data_get($category, 'name.en') ?: $category['slug'] ?? 'Categorie'; @endphp
                            <option value="{{ $category['id'] }}" @selected((string) old('category_id') === (string) $category['id'])>{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Prix EUR</span>
                    <input name="price_eur" value="{{ old('price_eur') }}" type="number" min="0.01" step="0.01" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Classe TVA</span>
                    <select name="tax_class" class="admin-select" required>
                        <option value="food" @selected(old('tax_class', 'food') === 'food')>Alimentaire (taux réduit)</option>
                        <option value="standard" @selected(old('tax_class') === 'standard')>Standard</option>
                    </select>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Stock</span>
                    <input name="stock_quantity" value="{{ old('stock_quantity', 0) }}" type="number" min="0" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Poids grammes</span>
                    <input name="weight_grams" value="{{ old('weight_grams') }}" type="number" min="1" class="admin-input">
                </label>
                <label class="block sm:col-span-2">
                    <span class="admin-kicker mb-2 block">Description FR</span>
                    <textarea name="description_fr" class="admin-textarea" required>{{ old('description_fr') }}</textarea>
                </label>
                <label class="block sm:col-span-2">
                    <span class="admin-kicker mb-2 block">Description EN</span>
                    <textarea name="description_en" class="admin-textarea" required>{{ old('description_en') }}</textarea>
                </label>
                <label class="flex items-center gap-3 sm:col-span-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked((string) old('is_active', '1') === '1') class="h-5 w-5 rounded border-leaf/20 text-leaf focus:ring-leaf">
                    <span class="text-sm font-bold text-cocoa dark:text-cream">Publier directement</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 border-t border-leaf/10 p-5 dark:border-white/10 sm:p-6">
                <button type="button" data-dialog-close class="admin-btn-secondary">Annuler</button>
                <button class="admin-btn">Enregistrer</button>
            </div>
        </form>
    </dialog>

    @foreach ($productRows as $product)
        @php
            $productId = $product['id'] ?? null;
            $name = data_get($product, 'name.fr') ?: data_get($product, 'name.en') ?: $product['slug'] ?? 'Produit';
            $isActive = (bool) ($product['is_active'] ?? false);
            $imageUrl = data_get($product, 'primary_image.url') ?: data_get($product, 'images.0.url');
            $imageAlt = data_get($product, 'primary_image.alt_text.fr') ?: data_get($product, 'primary_image.alt_text.en') ?: $name;
        @endphp
        @continue(! $productId)

        <dialog id="product-show-{{ $productId }}" class="admin-dialog">
            <div class="admin-modal-card p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-4">
                        @if ($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="h-20 w-20 shrink-0 rounded-2xl object-cover ring-1 ring-leaf/10 dark:ring-white/10" loading="lazy" decoding="async">
                        @else
                            <span class="grid h-20 w-20 shrink-0 place-items-center rounded-2xl bg-mint text-sm font-black text-forest dark:bg-white/10 dark:text-meadow">DF</span>
                        @endif
                        <div class="min-w-0">
                            <p class="admin-kicker">Produit</p>
                            <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $name }}</h2>
                            <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">{{ $product['sku'] ?? '-' }} - {{ $product['slug'] ?? '-' }}</p>
                        </div>
                    </div>
                    <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                    </button>
                </div>
                @php
                    $purchaseCents = (int) ($product['purchase_price_cents'] ?? 0);
                    $saleCents = (int) ($product['price_cents'] ?? 0);
                    $marginPercent = $saleCents > 0 && $purchaseCents > 0 ? round((($saleCents - $purchaseCents) / $saleCents) * 100, 1) : null;
                @endphp
                <div class="mt-5 grid gap-3 sm:grid-cols-5">
                    <div class="admin-panel p-4"><p class="admin-kicker">{{ $isEnglish ? 'Purchase' : 'Prix d’achat' }}</p><p class="mt-2 text-xl font-black">{{ $purchaseCents > 0 ? number_format($purchaseCents / 100, 2, ',', ' ').' EUR' : '—' }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">{{ $isEnglish ? 'Sale incl. VAT' : 'Vente TTC' }}</p><p class="mt-2 text-xl font-black">{{ number_format($saleCents / 100, 2, ',', ' ') }} {{ $product['currency'] ?? 'EUR' }}</p><small class="admin-muted">{{ $marginPercent !== null ? ($isEnglish ? 'Gross margin ' : 'Marge brute ').$marginPercent.' %' : '' }}</small></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Stock</p><p class="mt-2 text-xl font-black">{{ $product['stock_quantity'] ?? 0 }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Classe TVA</p><p class="mt-2 text-xl font-black">{{ ($product['tax_class'] ?? 'food') === 'standard' ? 'Standard' : 'Alimentaire' }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Statut</p><p class="mt-2 text-xl font-black">{{ $isActive ? 'Publie' : 'Brouillon' }}</p></div>
                </div>
                <div class="mt-5 admin-panel p-4">
                    <p class="admin-kicker">Description</p>
                    <p class="mt-2 text-sm leading-6 text-cocoa/70 dark:text-cream/70">{{ Str::limit(data_get($product, 'description.fr') ?: data_get($product, 'description.en') ?: 'Aucune description.', 420) }}</p>
                </div>
            </div>
        </dialog>

        <dialog id="product-tax-{{ $productId }}" class="admin-dialog" @if(session('admin_modal') === "product-tax-{$productId}") data-open-on-load @endif>
            <form method="POST" action="{{ route('admin.catalog.products.tax-class', ['locale' => $locale, 'product' => $productId]) }}" class="admin-modal-card p-5 sm:p-6">
                @csrf
                @method('PATCH')
                <p class="admin-kicker">Fiscalité</p>
                <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $name }}</h2>
                <label class="mt-5 block">
                    <span class="admin-kicker mb-2 block">Classe TVA</span>
                    <select name="tax_class" class="admin-select" required>
                        <option value="food" @selected(($product['tax_class'] ?? 'food') === 'food')>Alimentaire (taux réduit)</option>
                        <option value="standard" @selected(($product['tax_class'] ?? 'food') === 'standard')>Standard</option>
                    </select>
                </label>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" data-dialog-close class="admin-btn-secondary">Annuler</button>
                    <button class="admin-btn">Mettre à jour</button>
                </div>
            </form>
        </dialog>

        <dialog id="product-stock-{{ $productId }}" class="admin-dialog" @if(session('admin_modal') === "product-stock-{$productId}") data-open-on-load @endif>
            <form method="POST" action="{{ route('admin.catalog.products.stock', ['locale' => $locale, 'product' => $productId]) }}" class="admin-modal-card p-5 sm:p-6">
                @csrf
                @method('PATCH')
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="admin-kicker">Stock</p>
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

        <dialog id="product-publication-{{ $productId }}" class="admin-dialog" @if(session('admin_modal') === "product-publication-{$productId}") data-open-on-load @endif>
            <form method="POST" action="{{ route('admin.catalog.products.publication', ['locale' => $locale, 'product' => $productId]) }}" class="admin-modal-card p-5 sm:p-6">
                @csrf
                <input type="hidden" name="action" value="{{ $isActive ? 'unpublish' : 'publish' }}">
                <p class="admin-kicker">Publication</p>
                <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $isActive ? 'Retirer du front-office' : 'Publier le produit' }}</h2>
                <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $name }}</p>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" data-dialog-close class="admin-btn-secondary">Annuler</button>
                    <button class="admin-btn">{{ $isActive ? 'Retirer' : 'Publier' }}</button>
                </div>
            </form>
        </dialog>
    @endforeach
    @endif
@endpush
