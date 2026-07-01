@extends('layouts.admin')

@php
    $isEnglish = $locale === 'en';
    $rows = $diagnostics['data'] ?? [];
    $summary = $diagnostics['summary'] ?? [];
    $meta = $diagnostics['meta'] ?? [];
    $statusLabels = [
        'excellent' => $isEnglish ? 'Excellent' : 'Excellent',
        'good' => $isEnglish ? 'Good' : 'Bon',
        'incomplete' => $isEnglish ? 'Incomplete' : 'Incomplet',
        'critical' => $isEnglish ? 'Critical' : 'Critique',
    ];
    $visibilityLabels = [
        'maximum' => $isEnglish ? 'Maximum visibility' : 'Visibilité maximale',
        'high' => $isEnglish ? 'High visibility' : 'Visibilité élevée',
        'limited' => $isEnglish ? 'Limited visibility' : 'Visibilité limitée',
        'minimal' => $isEnglish ? 'Minimal visibility' : 'Visibilité minimale',
    ];
@endphp

@section('title', $isEnglish ? 'Catalog health' : 'Santé du catalogue')
@section('page_title', $isEnglish ? 'Catalog health monitoring' : 'Suivi de la santé du catalogue')
@section('page_subtitle', $isEnglish ? 'Scan product files and identify what limits their visibility.' : 'Scannez les fiches produits et identifiez ce qui limite leur visibilité.')

@section('content')
    @if (! $scanRequested)
        <section class="admin-card overflow-hidden">
            <div class="grid gap-8 p-6 lg:grid-cols-[1fr_360px] lg:items-center lg:p-10">
                <div>
                    <p class="admin-kicker">{{ $isEnglish ? 'Catalog scanner' : 'Scanner du catalogue' }}</p>
                    <h2 class="mt-3 text-3xl font-black text-ink dark:text-white">{{ $isEnglish ? 'Measure every product file' : 'Mesurez chaque fiche produit' }}</h2>
                    <p class="mt-4 max-w-2xl text-sm leading-7 admin-muted">{{ $isEnglish ? 'The scan checks bilingual content, pricing, stock, logistics, images and SEO. It then lists every missing element required to reach maximum visibility.' : 'Le scan contrôle le contenu bilingue, les prix, le stock, la logistique, les images et le SEO. Il liste ensuite chaque élément manquant pour atteindre la visibilité maximale.' }}</p>
                    <a href="{{ route('admin.catalog.health', ['locale' => $locale, 'scan' => 1]) }}" class="admin-btn mt-6">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h3M17 4h3v3M20 17v3h-3M7 20H4v-3"/><circle cx="12" cy="12" r="3"/><path d="M7 12h2M15 12h2"/></svg>
                        {{ $isEnglish ? 'Start catalog scan' : 'Démarrer le scan du catalogue' }}
                    </a>
                </div>
                <div class="rounded-2xl bg-orange-50 p-6 text-neutral-950 dark:bg-orange-500/10 dark:text-white">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-orange-600">{{ $isEnglish ? 'Analyzed areas' : 'Zones analysées' }}</p>
                    <ul class="mt-4 grid gap-3 text-sm font-bold">
                        @foreach (($isEnglish ? ['Bilingual content', 'Commerce and inventory', 'Images and icon', 'Logistics', 'SEO visibility'] : ['Contenu bilingue', 'Commerce et stock', 'Images et icône', 'Logistique', 'Visibilité SEO']) as $area)
                            <li class="flex items-center gap-2"><span class="grid h-6 w-6 place-items-center rounded-full bg-orange-500 text-xs text-white">✓</span>{{ $area }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    @else
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="admin-kicker">{{ $isEnglish ? 'Scan complete' : 'Scan terminé' }}</p><p class="mt-2 text-sm admin-muted">{{ ! empty($summary['scanned_at']) ? \Illuminate\Support\Carbon::parse($summary['scanned_at'])->locale($locale)->translatedFormat('d M Y, H:i:s') : '' }}</p></div>
            <a href="{{ route('admin.catalog.health', ['locale' => $locale, 'scan' => 1]) }}" class="admin-btn-secondary">{{ $isEnglish ? 'Scan again' : 'Relancer le scan' }}</a>
        </div>

        @if (! ($diagnostics['ok'] ?? false))
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">{{ $diagnostics['message'] ?? ($isEnglish ? 'Scan unavailable.' : 'Scan indisponible.') }}</div>
        @else
            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <div class="admin-card p-5"><p class="admin-kicker">{{ $isEnglish ? 'Products' : 'Produits' }}</p><p class="mt-2 text-3xl font-black">{{ $summary['products_count'] ?? 0 }}</p></div>
                <div class="admin-card p-5"><p class="admin-kicker">{{ $isEnglish ? 'Average score' : 'Score moyen' }}</p><p class="mt-2 text-3xl font-black text-orange-600">{{ $summary['average_score'] ?? 0 }}%</p></div>
                <div class="admin-card p-5"><p class="admin-kicker">{{ $isEnglish ? 'Missing elements' : 'Éléments manquants' }}</p><p class="mt-2 text-3xl font-black">{{ $summary['missing_total'] ?? 0 }}</p></div>
                <div class="admin-card p-5"><p class="admin-kicker">{{ $isEnglish ? 'Maximum ready' : 'Prêts au maximum' }}</p><p class="mt-2 text-3xl font-black text-emerald-600">{{ $summary['excellent_count'] ?? 0 }}</p></div>
                <div class="admin-card p-5"><p class="admin-kicker">{{ $isEnglish ? 'Critical' : 'Critiques' }}</p><p class="mt-2 text-3xl font-black text-red-600">{{ $summary['critical_count'] ?? 0 }}</p></div>
            </div>

            <section class="admin-card mt-5 p-4 sm:p-5">
                <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                    <input type="hidden" name="scan" value="1">
                    <input name="q" value="{{ $filters['q'] ?? '' }}" class="admin-input" placeholder="{{ $isEnglish ? 'Product name or SKU' : 'Nom du produit ou SKU' }}">
                    <select name="status" class="admin-select"><option value="">{{ $isEnglish ? 'All health levels' : 'Tous les niveaux' }}</option>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
                    <button class="admin-btn" type="submit">{{ $isEnglish ? 'Filter' : 'Filtrer' }}</button>
                </form>

                <div class="mt-5 overflow-x-auto rounded-xl border border-leaf/10 dark:border-white/10">
                    <table class="admin-table min-w-[1050px]">
                        <thead><tr><th class="px-4 py-3">{{ $isEnglish ? 'Product' : 'Produit' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Health' : 'Santé' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Visibility' : 'Visibilité' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Missing to reach maximum' : 'Manquants pour atteindre le maximum' }}</th></tr></thead>
                        <tbody>
                            @forelse ($rows as $product)
                                @php
                                    $health = $product['health'] ?? [];
                                    $score = (int) ($health['score'] ?? 0);
                                    $name = data_get($product, "name.{$locale}") ?: data_get($product, 'name.fr') ?: data_get($product, 'name.en') ?: $product['slug'];
                                @endphp
                                <tr class="align-top">
                                    <td class="px-4 py-4"><div class="flex min-w-[230px] items-center gap-3">@if($product['primary_image'] ?? null)<img src="{{ $product['primary_image'] }}" class="h-12 w-12 rounded-xl object-cover" alt="">@else<span class="grid h-12 w-12 place-items-center rounded-xl bg-orange-50 font-black text-orange-600">P</span>@endif<div><strong>{{ $name }}</strong><small class="mt-1 block admin-muted">{{ $product['sku'] }}</small></div></div></td>
                                    <td class="px-4 py-4"><div class="min-w-[150px]"><div class="flex items-center justify-between text-xs font-black"><span>{{ $statusLabels[$health['status'] ?? 'critical'] ?? '—' }}</span><span>{{ $score }}%</span></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-neutral-200 dark:bg-white/10"><div class="h-full rounded-full bg-orange-500" style="width: {{ $score }}%"></div></div><small class="mt-2 block admin-muted">{{ $health['completed_count'] ?? 0 }}/{{ $health['checks_count'] ?? 0 }} {{ $isEnglish ? 'checks' : 'contrôles' }}</small></div></td>
                                    <td class="px-4 py-4"><span class="admin-pill">{{ $visibilityLabels[$health['visibility'] ?? 'minimal'] ?? '—' }}</span></td>
                                    <td class="px-4 py-4"><strong>{{ $health['missing_count'] ?? 0 }} {{ $isEnglish ? 'element(s)' : 'élément(s)' }}</strong><div class="mt-2 flex max-w-[470px] flex-wrap gap-1.5">@foreach (array_slice($health['missing'] ?? [], 0, 8) as $missing)<span class="rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-700 dark:bg-white/10 dark:text-neutral-200">{{ $missing['label'] }}</span>@endforeach @if(($health['missing_count'] ?? 0) > 8)<span class="rounded-full bg-orange-50 px-2.5 py-1 text-xs font-black text-orange-700">+{{ $health['missing_count'] - 8 }}</span>@endif</div></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-10 text-center admin-muted">{{ $isEnglish ? 'No products match this filter.' : 'Aucun produit ne correspond à ce filtre.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (($meta['last_page'] ?? 1) > 1)
                    <div class="mt-4 flex justify-end gap-2">
                        @if (($meta['current_page'] ?? 1) > 1)<a class="admin-btn-secondary" href="{{ route('admin.catalog.health', ['locale' => $locale, 'scan' => 1, ...$filters, 'page' => $meta['current_page'] - 1]) }}">{{ $isEnglish ? 'Previous' : 'Précédent' }}</a>@endif
                        @if (($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1))<a class="admin-btn-secondary" href="{{ route('admin.catalog.health', ['locale' => $locale, 'scan' => 1, ...$filters, 'page' => $meta['current_page'] + 1]) }}">{{ $isEnglish ? 'Next' : 'Suivant' }}</a>@endif
                    </div>
                @endif
            </section>
        @endif
    @endif
@endsection
