@extends('layouts.admin')

@section('title', 'Categories')
@section('page_title', 'Categories')
@section('page_subtitle', 'Organisation du catalogue, rayons et activation front-office.')

@php
    $categoryRows = $categories['data'] ?? [];
@endphp

@section('content')
    @if (! ($categories['ok'] ?? false))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">{{ $categories['message'] ?? 'Categories indisponibles.' }}</div>
    @endif

    <section class="admin-card p-5 sm:p-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="admin-kicker">Catalogue</p>
                <h2 class="mt-2 admin-heading">Gestion des categories</h2>
                <p class="mt-2 max-w-2xl admin-muted">Une page dediee aux rayons, ordres d affichage et activations.</p>
            </div>
            <button type="button" data-dialog-target="category-create-modal" class="admin-btn">Nouvelle categorie</button>
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Rechercher categorie, slug..." class="admin-input">
            <select name="is_active" class="admin-select">
                <option value="">Tous statuts</option>
                <option value="1" @selected((string) ($filters['is_active'] ?? '') === '1')>Actives</option>
                <option value="0" @selected((string) ($filters['is_active'] ?? '') === '0')>Inactives</option>
            </select>
            <button class="admin-btn">Filtrer</button>
        </form>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($categoryRows as $category)
            @php
                $categoryId = $category['id'] ?? null;
                $name = data_get($category, 'name.fr') ?: data_get($category, 'name.en') ?: $category['slug'] ?? 'Categorie';
                $isActive = (bool) ($category['is_active'] ?? false);
            @endphp
            <article class="admin-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="admin-kicker">Categorie</p>
                        <h3 class="mt-2 truncate text-xl font-black text-ink dark:text-cream">{{ $name }}</h3>
                        <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">/{{ $category['slug'] ?? '-' }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $isActive ? 'bg-mint text-leaf dark:bg-meadow/15 dark:text-meadow' : 'bg-amber-100 text-amber-700 dark:bg-amber-300/15 dark:text-amber-200' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="admin-panel p-3">
                        <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">Produits</p>
                        <p class="mt-2 text-2xl font-black text-ink dark:text-cream">{{ $category['products_count'] ?? 0 }}</p>
                    </div>
                    <div class="admin-panel p-3">
                        <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">Ordre</p>
                        <p class="mt-2 text-2xl font-black text-ink dark:text-cream">{{ $category['sort_order'] ?? 0 }}</p>
                    </div>
                </div>

                @if ($categoryId)
                    <div class="mt-5 flex gap-2">
                        <button type="button" data-dialog-target="category-show-{{ $categoryId }}" class="admin-btn-secondary flex-1">Voir</button>
                        <button type="button" data-dialog-target="category-activation-{{ $categoryId }}" class="admin-btn flex-1">{{ $isActive ? 'Desactiver' : 'Activer' }}</button>
                    </div>
                @endif
            </article>
        @empty
            <div class="admin-card p-8 text-center text-sm text-cocoa/55 dark:text-cream/55 md:col-span-2 xl:col-span-3">Aucune categorie disponible.</div>
        @endforelse
    </section>
@endsection

@push('admin_modals')
    <dialog id="category-create-modal" class="admin-dialog" @if(session('admin_modal') === 'category-create') data-open-on-load @endif>
        <form method="POST" action="{{ route('admin.catalog.categories.store', ['locale' => $locale]) }}" class="admin-modal-card">
            @csrf
            <div class="flex items-start justify-between border-b border-leaf/10 p-5 dark:border-white/10 sm:p-6">
                <div>
                    <p class="admin-kicker">Categorie</p>
                    <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">Nouvelle categorie</h2>
                </div>
                <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                </button>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                @if(session('admin_modal') === 'category-create' && $errors->any())
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
                    <span class="admin-kicker mb-2 block">Ordre</span>
                    <input name="sort_order" value="{{ old('sort_order', 0) }}" type="number" min="0" class="admin-input">
                </label>
                <label class="flex items-center gap-3 sm:col-span-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked((string) old('is_active', '1') === '1') class="h-5 w-5 rounded border-leaf/20 text-leaf focus:ring-leaf">
                    <span class="text-sm font-bold text-cocoa dark:text-cream">Categorie active</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 border-t border-leaf/10 p-5 dark:border-white/10 sm:p-6">
                <button type="button" data-dialog-close class="admin-btn-secondary">Annuler</button>
                <button class="admin-btn">Enregistrer</button>
            </div>
        </form>
    </dialog>

    @foreach ($categoryRows as $category)
        @php
            $categoryId = $category['id'] ?? null;
            $name = data_get($category, 'name.fr') ?: data_get($category, 'name.en') ?: $category['slug'] ?? 'Categorie';
            $isActive = (bool) ($category['is_active'] ?? false);
        @endphp
        @continue(! $categoryId)

        <dialog id="category-show-{{ $categoryId }}" class="admin-dialog">
            <div class="admin-modal-card p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="admin-kicker">Categorie</p>
                        <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $name }}</h2>
                        <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">/{{ $category['slug'] ?? '-' }}</p>
                    </div>
                    <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                    </button>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="admin-panel p-4"><p class="admin-kicker">Produits</p><p class="mt-2 text-xl font-black">{{ $category['products_count'] ?? 0 }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Ordre</p><p class="mt-2 text-xl font-black">{{ $category['sort_order'] ?? 0 }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Statut</p><p class="mt-2 text-xl font-black">{{ $isActive ? 'Active' : 'Inactive' }}</p></div>
                </div>
            </div>
        </dialog>

        <dialog id="category-activation-{{ $categoryId }}" class="admin-dialog" @if(session('admin_modal') === "category-activation-{$categoryId}") data-open-on-load @endif>
            <form method="POST" action="{{ route('admin.catalog.categories.activation', ['locale' => $locale, 'category' => $categoryId]) }}" class="admin-modal-card p-5 sm:p-6">
                @csrf
                <input type="hidden" name="action" value="{{ $isActive ? 'deactivate' : 'activate' }}">
                <p class="admin-kicker">Categorie</p>
                <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $isActive ? 'Desactiver la categorie' : 'Activer la categorie' }}</h2>
                <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $name }}</p>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" data-dialog-close class="admin-btn-secondary">Annuler</button>
                    <button class="admin-btn">{{ $isActive ? 'Desactiver' : 'Activer' }}</button>
                </div>
            </form>
        </dialog>
    @endforeach
@endpush
