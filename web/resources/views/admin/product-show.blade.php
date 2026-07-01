@extends('layouts.admin')

@php
    $isEnglish = $locale === 'en';
    $nameFr = data_get($product, 'name.fr', '');
    $nameEn = data_get($product, 'name.en', '');
    $displayName = $isEnglish ? ($nameEn ?: $nameFr) : ($nameFr ?: $nameEn);
    $health = $product['health'] ?? [];
    $images = $product['images'] ?? [];
    $categoryRows = $categories['data'] ?? [];
    $isActive = (bool) ($product['is_active'] ?? false);
    $statusLabels = [
        'excellent' => $isEnglish ? 'Excellent' : 'Excellent',
        'good' => $isEnglish ? 'Good' : 'Bon',
        'incomplete' => $isEnglish ? 'Incomplete' : 'Incomplet',
        'critical' => $isEnglish ? 'Critical' : 'Critique',
    ];
@endphp

@section('title', $displayName)
@section('page_title', $displayName)
@section('page_subtitle', ($product['sku'] ?? '').' · '.($isActive ? ($isEnglish ? 'Published' : 'Publié') : ($isEnglish ? 'Draft' : 'Brouillon')))

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.catalog.products', ['locale' => $locale]) }}" class="admin-btn-secondary">← {{ $isEnglish ? 'Back to products' : 'Retour aux produits' }}</a>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" target="_blank" rel="noopener" class="admin-btn-secondary">{{ $isEnglish ? 'Open storefront page' : 'Ouvrir la fiche boutique' }}</a>
            <form method="POST" action="{{ route('admin.catalog.products.publication', ['locale' => $locale, 'product' => $product['id']]) }}">
                @csrf
                <input type="hidden" name="action" value="{{ $isActive ? 'unpublish' : 'publish' }}">
                <button class="admin-btn" type="submit">{{ $isActive ? ($isEnglish ? 'Move to draft' : 'Passer en brouillon') : ($isEnglish ? 'Publish product' : 'Publier le produit') }}</button>
            </form>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
        <form method="POST" action="{{ route('admin.catalog.products.update', ['locale' => $locale, 'product' => $product['id']]) }}" enctype="multipart/form-data" class="space-y-5" x-data="{ newPreviews: [], primaryNew: '', selectNew(event) { this.newPreviews.forEach(item => URL.revokeObjectURL(item.url)); this.newPreviews = Array.from(event.target.files).map((file, index) => ({ index, name: file.name, url: URL.createObjectURL(file) })); } }">
            @csrf
            @method('PATCH')
            <input type="hidden" name="primary_new_index" x-model="primaryNew">

            <section class="admin-card p-5 sm:p-6">
                <div><p class="admin-kicker">{{ $isEnglish ? 'Identity' : 'Identité' }}</p><h2 class="mt-2 text-2xl font-black">{{ $isEnglish ? 'Product identification' : 'Identification du produit' }}</h2></div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'French name' : 'Nom français' }} *</span><input class="admin-input" name="name_fr" value="{{ old('name_fr', $nameFr) }}" required></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'English name' : 'Nom anglais' }} *</span><input class="admin-input" name="name_en" value="{{ old('name_en', $nameEn) }}" required></label>
                    <label><span class="admin-kicker mb-2 block">Slug *</span><input class="admin-input" name="slug" value="{{ old('slug', $product['slug']) }}" required></label>
                    <label><span class="admin-kicker mb-2 block">SKU *</span><input class="admin-input" name="sku" value="{{ old('sku', $product['sku']) }}" required></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Category' : 'Catégorie' }}</span><select class="admin-select" name="category_id">@foreach($categoryRows as $category)<option value="{{ $category['id'] }}" @selected((int) old('category_id', $product['category_id']) === (int) $category['id'])>{{ data_get($category, "name.{$locale}") ?: data_get($category, 'name.fr') ?: $category['slug'] }}</option>@endforeach</select></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Barcode / EAN' : 'Code-barres / EAN' }}</span><input class="admin-input" name="barcode" value="{{ old('barcode', $product['barcode'] ?? '') }}"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Brand' : 'Marque' }}</span><input class="admin-input" name="brand" value="{{ old('brand', $product['brand'] ?? '') }}"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Supplier reference' : 'Référence fournisseur' }}</span><input class="admin-input" name="supplier_reference" value="{{ old('supplier_reference', $product['supplier_reference'] ?? '') }}"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'French origin' : 'Origine FR' }}</span><input class="admin-input" name="origin_fr" value="{{ old('origin_fr', data_get($product, 'origin.fr')) }}"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'English origin' : 'Origine EN' }}</span><input class="admin-input" name="origin_en" value="{{ old('origin_en', data_get($product, 'origin.en')) }}"></label>
                </div>
            </section>

            <section class="admin-card p-5 sm:p-6">
                <div><p class="admin-kicker">{{ $isEnglish ? 'Commerce' : 'Commerce' }}</p><h2 class="mt-2 text-2xl font-black">{{ $isEnglish ? 'Prices, VAT and inventory' : 'Prix, TVA et stock' }}</h2></div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Purchase price (€)' : 'Prix d’achat (€)' }}</span><input class="admin-input" name="purchase_price_eur" type="number" min="0" step="0.01" value="{{ old('purchase_price_eur', isset($product['purchase_price_cents']) ? number_format($product['purchase_price_cents'] / 100, 2, '.', '') : '') }}"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Sale incl. VAT (€)' : 'Vente TTC (€)' }} *</span><input class="admin-input" name="sale_price_ttc_eur" type="number" min="0.01" step="0.01" value="{{ old('sale_price_ttc_eur', number_format(($product['price_cents'] ?? 0) / 100, 2, '.', '')) }}" required></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Compare-at (€)' : 'Prix barré (€)' }}</span><input class="admin-input" name="compare_at_price_eur" type="number" min="0.01" step="0.01" value="{{ old('compare_at_price_eur', isset($product['compare_at_price_cents']) ? number_format($product['compare_at_price_cents'] / 100, 2, '.', '') : '') }}"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'VAT class' : 'Classe TVA' }} *</span><select class="admin-select" name="tax_class" required><option value="food" @selected(old('tax_class', $product['tax_class']) === 'food')>{{ $isEnglish ? 'Food — reduced' : 'Alimentaire — réduit' }}</option><option value="standard" @selected(old('tax_class', $product['tax_class']) === 'standard')>{{ $isEnglish ? 'Standard' : 'Standard' }}</option></select></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Stock quantity' : 'Quantité en stock' }} *</span><input class="admin-input" name="stock_quantity" type="number" min="0" value="{{ old('stock_quantity', $product['stock_quantity']) }}" required></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Maximum per order' : 'Maximum par commande' }}</span><input class="admin-input" name="max_order_quantity" type="number" min="1" value="{{ old('max_order_quantity', $product['max_order_quantity'] ?? '') }}"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Weight (g)' : 'Poids (g)' }}</span><input class="admin-input" name="weight_grams" type="number" min="1" value="{{ old('weight_grams', $product['weight_grams'] ?? '') }}"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Sales unit' : 'Unité de vente' }}</span><input class="admin-input" name="unit_label" value="{{ old('unit_label', $product['unit_label'] ?? '') }}"></label>
                </div>
            </section>

            <section class="admin-card p-5 sm:p-6">
                <div><p class="admin-kicker">{{ $isEnglish ? 'Content' : 'Contenu' }}</p><h2 class="mt-2 text-2xl font-black">{{ $isEnglish ? 'Bilingual product copy' : 'Contenu produit bilingue' }}</h2></div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Short description FR' : 'Description courte FR' }}</span><textarea class="admin-textarea min-h-24" name="short_description_fr">{{ old('short_description_fr', data_get($product, 'short_description.fr')) }}</textarea></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Short description EN' : 'Description courte EN' }}</span><textarea class="admin-textarea min-h-24" name="short_description_en">{{ old('short_description_en', data_get($product, 'short_description.en')) }}</textarea></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Full description FR' : 'Description complète FR' }}</span><textarea class="admin-textarea min-h-44" name="description_fr">{{ old('description_fr', data_get($product, 'description.fr')) }}</textarea></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Full description EN' : 'Description complète EN' }}</span><textarea class="admin-textarea min-h-44" name="description_en">{{ old('description_en', data_get($product, 'description.en')) }}</textarea></label>
                </div>
            </section>

            <section class="admin-card p-5 sm:p-6">
                <div><p class="admin-kicker">SEO</p><h2 class="mt-2 text-2xl font-black">{{ $isEnglish ? 'Search engine optimization' : 'Référencement du produit' }}</h2><p class="mt-2 text-sm admin-muted">{{ $isEnglish ? 'Unique, descriptive metadata improves indexing and click-through rate.' : 'Des métadonnées uniques et descriptives améliorent l’indexation et le taux de clic.' }}</p></div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'SEO title FR' : 'Titre SEO FR' }}</span><input class="admin-input" name="seo_title_fr" maxlength="180" value="{{ old('seo_title_fr', data_get($product, 'seo_title.fr')) }}"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'SEO title EN' : 'Titre SEO EN' }}</span><input class="admin-input" name="seo_title_en" maxlength="180" value="{{ old('seo_title_en', data_get($product, 'seo_title.en')) }}"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Meta description FR' : 'Méta-description FR' }}</span><textarea class="admin-textarea min-h-28" name="seo_description_fr" maxlength="320">{{ old('seo_description_fr', data_get($product, 'seo_description.fr')) }}</textarea></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Meta description EN' : 'Méta-description EN' }}</span><textarea class="admin-textarea min-h-28" name="seo_description_en" maxlength="320">{{ old('seo_description_en', data_get($product, 'seo_description.en')) }}</textarea></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Keywords FR' : 'Mots-clés FR' }}</span><input class="admin-input" name="seo_keywords_fr" value="{{ old('seo_keywords_fr', implode(', ', data_get($product, 'seo_keywords.fr', []))) }}" placeholder="miel, épicerie, naturel"></label>
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Keywords EN' : 'Mots-clés EN' }}</span><input class="admin-input" name="seo_keywords_en" value="{{ old('seo_keywords_en', implode(', ', data_get($product, 'seo_keywords.en', []))) }}" placeholder="honey, grocery, natural"></label>
                    <label class="sm:col-span-2"><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Canonical path' : 'Chemin canonique' }}</span><input class="admin-input" name="canonical_path" value="{{ old('canonical_path', $product['canonical_path'] ?? '') }}" placeholder="/{locale}/products/{{ $product['slug'] }}"></label>
                </div>
                <div class="mt-5 rounded-xl border border-neutral-200 bg-white p-4 dark:border-white/10 dark:bg-black/20">
                    <p class="text-sm text-emerald-700">{{ url("/{$locale}/products/{$product['slug']}") }}</p>
                    <p class="mt-1 text-xl text-blue-700">{{ data_get($product, "seo_title.{$locale}") ?: $displayName }}</p>
                    <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">{{ data_get($product, "seo_description.{$locale}") ?: data_get($product, "short_description.{$locale}") ?: ($isEnglish ? 'Add a meta description to improve this search result.' : 'Ajoutez une méta-description pour améliorer ce résultat de recherche.') }}</p>
                </div>
            </section>

            <section class="admin-card p-5 sm:p-6">
                <div><p class="admin-kicker">{{ $isEnglish ? 'Media' : 'Médias' }}</p><h2 class="mt-2 text-2xl font-black">{{ $isEnglish ? 'Gallery, primary image and icon' : 'Galerie, image principale et icône' }}</h2></div>
                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($images as $image)
                        <div class="rounded-xl border border-leaf/10 p-2 dark:border-white/10">
                            <img src="{{ $image['url'] }}" alt="{{ data_get($image, "alt_text.{$locale}", $displayName) }}" class="aspect-square w-full rounded-lg object-cover">
                            <label class="mt-2 flex items-center gap-2 text-xs font-bold"><input type="radio" name="primary_existing_id" value="{{ $image['id'] }}" @checked($image['is_primary'] ?? false)> {{ $isEnglish ? 'Primary' : 'Principale' }}</label>
                            <label class="mt-2 flex items-center gap-2 text-xs text-red-600"><input type="checkbox" name="remove_image_ids[]" value="{{ $image['id'] }}"> {{ $isEnglish ? 'Remove' : 'Retirer' }}</label>
                        </div>
                    @endforeach
                </div>
                <label class="mt-5 block rounded-xl border-2 border-dashed border-orange-300 p-5 text-center"><span class="font-black text-orange-600">{{ $isEnglish ? 'Add gallery images' : 'Ajouter des images à la galerie' }}</span><input class="mt-3 block w-full text-sm" type="file" name="new_images[]" accept="image/jpeg,image/png,image/webp" multiple x-on:change="selectNew($event)"></label>
                <div x-show="newPreviews.length" class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <template x-for="item in newPreviews" :key="item.url"><button type="button" class="relative rounded-xl border-2 p-1" :class="String(primaryNew) === String(item.index) ? 'border-orange-500' : 'border-transparent'" x-on:click="primaryNew = item.index"><img :src="item.url" class="aspect-square w-full rounded-lg object-cover"><span class="absolute bottom-2 left-2 rounded-full bg-black/70 px-2 py-1 text-[10px] font-black text-white" x-text="String(primaryNew) === String(item.index) ? @js($isEnglish ? 'PRIMARY' : 'PRINCIPALE') : @js($isEnglish ? 'Set primary' : 'Définir')"></span></button></template>
                </div>
                <div class="mt-5 grid gap-4 rounded-xl border border-leaf/10 p-4 dark:border-white/10 sm:grid-cols-[90px_1fr] sm:items-center">
                    @if(data_get($product, 'icon_image.url'))<img src="{{ data_get($product, 'icon_image.url') }}" class="h-20 w-20 rounded-xl object-cover" alt="">@else<span class="grid h-20 w-20 place-items-center rounded-xl bg-neutral-100 text-xs font-black dark:bg-white/10">{{ $isEnglish ? 'No icon' : 'Sans icône' }}</span>@endif
                    <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Replace product icon' : 'Remplacer l’icône produit' }}</span><input type="file" name="new_icon" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm"></label>
                </div>
            </section>

            <div class="sticky bottom-4 z-20 flex justify-end rounded-2xl border border-orange-200 bg-white/95 p-4 shadow-xl backdrop-blur dark:border-orange-500/30 dark:bg-neutral-950/95">
                <button class="admin-btn px-8" type="submit">{{ $isEnglish ? 'Save all changes' : 'Enregistrer toutes les modifications' }}</button>
            </div>
        </form>

        <aside class="space-y-5 xl:sticky xl:top-28 xl:self-start">
            <section class="admin-card overflow-hidden">
                @if(data_get($product, 'primary_image.url'))<img src="{{ data_get($product, 'primary_image.url') }}" alt="{{ $displayName }}" class="aspect-[4/3] w-full object-cover">@endif
                <div class="p-5"><p class="admin-kicker">{{ $isEnglish ? 'Current product' : 'Produit actuel' }}</p><h2 class="mt-2 text-xl font-black">{{ $displayName }}</h2><dl class="mt-4 space-y-2 text-sm"><div class="flex justify-between gap-3"><dt class="admin-muted">SKU</dt><dd class="font-bold">{{ $product['sku'] }}</dd></div><div class="flex justify-between gap-3"><dt class="admin-muted">{{ $isEnglish ? 'Sale price' : 'Prix TTC' }}</dt><dd class="font-bold">{{ number_format(($product['price_cents'] ?? 0) / 100, 2, ',', ' ') }} EUR</dd></div><div class="flex justify-between gap-3"><dt class="admin-muted">{{ $isEnglish ? 'Stock' : 'Stock' }}</dt><dd class="font-bold">{{ $product['stock_quantity'] }}</dd></div></dl></div>
            </section>

            <section class="admin-card p-5">
                <div class="flex items-center justify-between"><p class="admin-kicker">{{ $isEnglish ? 'Product health' : 'Santé du produit' }}</p><strong class="text-2xl text-orange-600">{{ $health['score'] ?? 0 }}%</strong></div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-neutral-200 dark:bg-white/10"><div class="h-full rounded-full bg-orange-500" style="width: {{ $health['score'] ?? 0 }}%"></div></div>
                <p class="mt-3 font-black">{{ $statusLabels[$health['status'] ?? 'critical'] ?? '—' }}</p>
                <p class="mt-1 text-sm admin-muted">{{ $health['missing_count'] ?? 0 }} {{ $isEnglish ? 'element(s) missing for maximum visibility.' : 'élément(s) manquant(s) pour la visibilité maximale.' }}</p>
                <div class="mt-4 flex flex-wrap gap-1.5">@foreach(array_slice($health['missing'] ?? [], 0, 10) as $missing)<span class="rounded-full bg-neutral-100 px-2.5 py-1 text-xs dark:bg-white/10">{{ $missing['label'] }}</span>@endforeach</div>
                <a href="{{ route('admin.catalog.health', ['locale' => $locale, 'scan' => 1, 'q' => $product['sku']]) }}" class="admin-btn-secondary mt-4 w-full">{{ $isEnglish ? 'Open in catalog monitoring' : 'Ouvrir dans le suivi catalogue' }}</a>
            </section>
        </aside>
    </div>
@endsection
