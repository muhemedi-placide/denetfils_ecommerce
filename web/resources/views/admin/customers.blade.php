@extends('layouts.admin')

@section('title', 'Clients')
@section('page_title', 'Clients')
@section('page_subtitle', 'Comptes clients, activite commerciale et acces aux fiches detaillees.')

@php
    $rows = data_get($customers, 'data', []);
    $meta = data_get($customers, 'meta', []);
    $statuses = ['active' => 'Actif', 'suspended' => 'Suspendu', 'deleted_pending' => 'Suppression demandee'];
@endphp

@section('content')
    <section class="grid gap-5">
        <div class="admin-card p-4 sm:p-5">
            <form method="GET" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_140px_auto]">
                <label>
                    <span class="admin-kicker mb-2 block">Recherche</span>
                    <input class="admin-input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom, email ou telephone">
                </label>
                <label>
                    <span class="admin-kicker mb-2 block">Statut</span>
                    <select class="admin-select" name="status">
                        <option value="">Tous</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="admin-kicker mb-2 block">Pays</span>
                    <input class="admin-input uppercase" name="country_code" value="{{ $filters['country_code'] ?? '' }}" placeholder="FR">
                </label>
                <button class="admin-btn self-end">Filtrer</button>
            </form>
        </div>

        @if(!data_get($customers, 'ok', true))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">{{ data_get($customers, 'message', 'Impossible de charger les clients.') }}</div>
        @endif

        <div class="admin-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-leaf/10 p-4 dark:border-white/10">
                <div>
                    <h2 class="admin-heading">Liste des clients</h2>
                    <p class="admin-muted mt-1">{{ $meta['total'] ?? count($rows) }} compte(s)</p>
                </div>
                <span class="admin-pill">Customers</span>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Client</th><th>Statut</th><th>Pays</th><th>Commandes</th><th>Depense</th><th>Adresses</th><th></th></tr></thead>
                    <tbody>
                    @forelse($rows as $customer)
                        @php $summary = $customer['summary'] ?? []; @endphp
                        <tr>
                            <td><p class="font-black">{{ $customer['name'] ?? 'Client' }}</p><p class="admin-muted">{{ $customer['email'] ?? '-' }}</p></td>
                            <td><span class="admin-pill">{{ $statuses[$customer['status'] ?? 'active'] ?? ($customer['status'] ?? '-') }}</span></td>
                            <td>{{ $customer['country_code'] ?? '-' }}</td>
                            <td>{{ $summary['orders_count'] ?? 0 }}</td>
                            <td>{{ number_format(($summary['total_spent_cents'] ?? 0) / 100, 2, ',', ' ') }} €</td>
                            <td>{{ $summary['addresses_count'] ?? 0 }}</td>
                            <td class="text-right"><a class="admin-btn-secondary" href="{{ route('admin.customers.show', ['locale' => $locale, 'customer' => $customer['id']]) }}">Voir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center admin-muted">Aucun client ne correspond aux filtres.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if(($meta['last_page'] ?? 1) > 1)
                <div class="flex items-center justify-between border-t border-leaf/10 p-4 dark:border-white/10">
                    <p class="admin-muted">Page {{ $meta['current_page'] ?? 1 }} sur {{ $meta['last_page'] }}</p>
                    <div class="flex gap-2">
                        @if(($meta['current_page'] ?? 1) > 1)
                            <a class="admin-btn-secondary" href="{{ route('admin.customers', ['locale' => $locale, ...array_filter([...$filters, 'page' => $meta['current_page'] - 1])]) }}">Precedent</a>
                        @endif
                        @if(($meta['current_page'] ?? 1) < $meta['last_page'])
                            <a class="admin-btn-secondary" href="{{ route('admin.customers', ['locale' => $locale, ...array_filter([...$filters, 'page' => $meta['current_page'] + 1])]) }}">Suivant</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
