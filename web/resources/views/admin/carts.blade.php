@extends('layouts.admin')

@section('title', $locale === 'en' ? 'Carts' : 'Paniers')
@section('page_title', $locale === 'en' ? 'Carts' : 'Paniers')
@section('page_subtitle', $locale === 'en' ? 'Active and abandoned carts, value and recovery.' : 'Paniers actifs et abandonnés, valeur et récupération.')

@section('admin_content')
    @php
        $isEnglish = $locale === 'en';
        $rows = $carts['data'] ?? [];
        $summary = $carts['summary'] ?? [];
        $statusLabels = [
            'active' => $isEnglish ? 'Active' : 'Actif',
            'abandoned' => $isEnglish ? 'Abandoned' : 'Abandonné',
            'converted' => $isEnglish ? 'Converted' : 'Converti',
            'expired' => $isEnglish ? 'Expired' : 'Expiré',
            'empty' => $isEnglish ? 'Empty' : 'Vide',
        ];
    @endphp

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="admin-card p-5"><p class="admin-kicker">{{ $isEnglish ? 'Displayed carts' : 'Paniers affichés' }}</p><p class="mt-2 text-3xl font-black">{{ data_get($carts, 'meta.total', count($rows)) }}</p></div>
        <div class="admin-card p-5"><p class="admin-kicker">{{ $isEnglish ? 'Cart value' : 'Valeur des paniers' }}</p><p class="mt-2 text-3xl font-black">{{ data_get($summary, 'formatted_value', '—') }}</p></div>
        <div class="admin-card p-5"><p class="admin-kicker">{{ $isEnglish ? 'Abandoned' : 'Abandonnés' }}</p><p class="mt-2 text-3xl font-black text-orange-600">{{ data_get($summary, 'abandoned_count', 0) }}</p></div>
    </div>

    <section class="admin-card mt-5 p-4 sm:p-5">
        <form method="GET" class="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_190px_160px_160px_auto]">
            <input class="admin-input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ $isEnglish ? 'Reference, customer or email' : 'Référence, client ou e-mail' }}">
            <select class="admin-select" name="status">
                <option value="">{{ $isEnglish ? 'All statuses' : 'Tous les statuts' }}</option>
                @foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach
            </select>
            <input class="admin-input" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" aria-label="{{ $isEnglish ? 'From' : 'Du' }}">
            <input class="admin-input" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" aria-label="{{ $isEnglish ? 'To' : 'Au' }}">
            <button class="admin-btn-primary" type="submit">{{ $isEnglish ? 'Filter' : 'Filtrer' }}</button>
        </form>

        @if (! ($carts['ok'] ?? false))
            <p class="mt-5 rounded-xl bg-red-50 p-4 text-sm font-semibold text-red-700">{{ $carts['message'] ?? ($isEnglish ? 'Carts are unavailable.' : 'Les paniers sont indisponibles.') }}</p>
        @else
            <div class="mt-5 overflow-x-auto rounded-xl border border-leaf/10 dark:border-white/10">
                <table class="admin-table min-w-[980px]">
                    <thead><tr><th class="px-4 py-3">{{ $isEnglish ? 'Cart' : 'Panier' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Customer' : 'Client' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Contents' : 'Contenu' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Value' : 'Valeur' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Last activity' : 'Dernière activité' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Status' : 'Statut' }}</th><th class="px-4 py-3"></th></tr></thead>
                    <tbody>
                        @forelse ($rows as $cart)
                            <tr>
                                <td class="px-4 py-3"><strong>{{ $cart['reference'] ?? '—' }}</strong><small class="mt-1 block admin-muted">#{{ $cart['id'] ?? '—' }}</small></td>
                                <td class="px-4 py-3"><strong>{{ data_get($cart, 'customer.name', $isEnglish ? 'Guest' : 'Visiteur') }}</strong><small class="mt-1 block admin-muted">{{ data_get($cart, 'customer.email', '—') }}</small></td>
                                <td class="px-4 py-3">{{ $cart['items_count'] ?? 0 }} {{ $isEnglish ? 'item(s)' : 'article(s)' }}<small class="mt-1 block admin-muted">{{ $cart['distinct_items_count'] ?? 0 }} {{ $isEnglish ? 'line(s)' : 'ligne(s)' }}</small></td>
                                <td class="px-4 py-3 font-black">{{ $cart['formatted_total'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ ! empty($cart['last_activity_at']) ? \Illuminate\Support\Carbon::parse($cart['last_activity_at'])->locale($locale)->diffForHumans() : '—' }}</td>
                                <td class="px-4 py-3"><span class="admin-pill">{{ $statusLabels[$cart['admin_status'] ?? ''] ?? ($cart['admin_status'] ?? '—') }}</span></td>
                                <td class="px-4 py-3 text-right"><a class="admin-btn-secondary" href="{{ route('admin.carts.show', ['locale' => $locale, 'cart' => $cart['id']]) }}">{{ $isEnglish ? 'Details' : 'Détails' }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center admin-muted">{{ $isEnglish ? 'No carts match these filters.' : 'Aucun panier ne correspond aux filtres.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
