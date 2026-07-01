@extends('layouts.admin')

@php
    $isEnglish = $locale === 'en';
    $rows = data_get($invoices, 'data', []);
    $meta = data_get($invoices, 'meta', []);
    $summary = data_get($invoices, 'summary', []);
    $statusOptions = [
        'draft' => $isEnglish ? 'Draft' : 'Brouillon',
        'issued' => $isEnglish ? 'Issued' : 'Émise',
        'paid' => $isEnglish ? 'Paid' : 'Payée',
        'refunded' => $isEnglish ? 'Refunded' : 'Remboursée',
        'void' => $isEnglish ? 'Void' : 'Annulée',
    ];
@endphp

@section('title', $isEnglish ? 'Invoices' : 'Factures')
@section('page_title', $isEnglish ? 'Invoices' : 'Factures')
@section('page_subtitle', $isEnglish ? 'Search, review and download customer invoices.' : 'Recherchez, contrôlez et téléchargez les factures clients.')

@section('content')
    <section class="grid gap-5">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                [$isEnglish ? 'Invoices' : 'Factures', $summary['total_invoices'] ?? 0],
                [$isEnglish ? 'Drafts' : 'Brouillons', $summary['draft_invoices'] ?? 0],
                [$isEnglish ? 'Issued' : 'Émises', $summary['issued_invoices'] ?? 0],
                [$isEnglish ? 'Paid' : 'Payées', $summary['paid_invoices'] ?? 0],
                [$isEnglish ? 'Total value' : 'Valeur totale', $summary['formatted_total'] ?? '—'],
            ] as [$label, $value])
                <article class="admin-card p-4"><p class="admin-kicker">{{ $label }}</p><strong class="mt-3 block text-2xl font-black">{{ $value }}</strong></article>
            @endforeach
        </div>

        <div class="admin-card p-4 sm:p-5">
            <form method="GET" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_170px_170px_150px_150px_auto]">
                <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Search' : 'Recherche' }}</span><input class="admin-input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ $isEnglish ? 'Invoice, order or customer…' : 'Facture, commande ou client…' }}"></label>
                <label>
                    <span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Invoice status' : 'Statut facture' }}</span>
                    <select class="admin-select" name="status"><option value="">{{ $isEnglish ? 'All' : 'Tous' }}</option>@foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
                </label>
                <label>
                    <span class="admin-kicker mb-2 block">{{ $isEnglish ? 'Payment' : 'Paiement' }}</span>
                    <select class="admin-select" name="payment_status">
                        <option value="">{{ $isEnglish ? 'All' : 'Tous' }}</option>
                        <option value="unpaid" @selected(($filters['payment_status'] ?? '') === 'unpaid')>{{ $isEnglish ? 'Unpaid' : 'Non payé' }}</option>
                        <option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>{{ $isEnglish ? 'Paid' : 'Payé' }}</option>
                        <option value="refunded" @selected(($filters['payment_status'] ?? '') === 'refunded')>{{ $isEnglish ? 'Refunded' : 'Remboursé' }}</option>
                    </select>
                </label>
                <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'From' : 'Du' }}</span><input class="admin-input" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></label>
                <label><span class="admin-kicker mb-2 block">{{ $isEnglish ? 'To' : 'Au' }}</span><input class="admin-input" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></label>
                <button class="admin-btn self-end">{{ $isEnglish ? 'Filter' : 'Filtrer' }}</button>
            </form>
        </div>

        @if(!data_get($invoices, 'ok', true))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">{{ data_get($invoices, 'message', $isEnglish ? 'Invoices could not be loaded.' : 'Impossible de charger les factures.') }}</div>
        @endif

        <div class="admin-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>{{ $isEnglish ? 'Invoice' : 'Facture' }}</th><th>{{ $isEnglish ? 'Customer' : 'Client' }}</th><th>{{ $isEnglish ? 'Order' : 'Commande' }}</th><th>{{ $isEnglish ? 'Issue date' : 'Date d’émission' }}</th><th>{{ $isEnglish ? 'Status' : 'Statut' }}</th><th>{{ $isEnglish ? 'Total' : 'Total TTC' }}</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @forelse($rows as $invoice)
                            @php $order = $invoice['order'] ?? []; @endphp
                            <tr>
                                <td><p class="font-black">{{ $invoice['invoice_number'] ?? '—' }}</p><p class="admin-muted">#{{ $invoice['id'] ?? '—' }}</p></td>
                                <td><p class="font-bold">{{ data_get($order, 'customer.name', '—') }}</p><p class="admin-muted">{{ data_get($order, 'customer.email', '—') }}</p></td>
                                <td>{{ $order['order_number'] ?? '—' }}</td>
                                <td>{{ $invoice['issued_at'] ? \Illuminate\Support\Carbon::parse($invoice['issued_at'])->format($isEnglish ? 'Y-m-d H:i' : 'd/m/Y H:i') : '—' }}</td>
                                <td><span class="admin-pill">{{ $invoice['status_label'] ?? ($statusOptions[$invoice['status'] ?? ''] ?? '—') }}</span></td>
                                <td class="font-black">{{ $invoice['formatted_total'] ?? '—' }}</td>
                                <td><div class="flex justify-end gap-2"><a class="admin-btn-secondary" href="{{ route('admin.invoices.show', ['locale' => $locale, 'invoice' => $invoice['id']]) }}">{{ $isEnglish ? 'View' : 'Voir' }}</a><a class="admin-btn-secondary" href="{{ route('admin.orders.invoice', ['locale' => $locale, 'order' => $order['id']]) }}">PDF</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-8 text-center admin-muted">{{ $isEnglish ? 'No invoices match these filters.' : 'Aucune facture ne correspond à ces filtres.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(($meta['last_page'] ?? 1) > 1)
                <div class="flex items-center justify-between border-t border-leaf/10 p-4 dark:border-white/10">
                    <p class="admin-muted">Page {{ $meta['current_page'] }} / {{ $meta['last_page'] }}</p>
                    <div class="flex gap-2">
                        @if($meta['current_page'] > 1)<a class="admin-btn-secondary" href="{{ route('admin.invoices', ['locale' => $locale, ...array_filter([...$filters, 'page' => $meta['current_page'] - 1])]) }}">{{ $isEnglish ? 'Previous' : 'Précédent' }}</a>@endif
                        @if($meta['current_page'] < $meta['last_page'])<a class="admin-btn-secondary" href="{{ route('admin.invoices', ['locale' => $locale, ...array_filter([...$filters, 'page' => $meta['current_page'] + 1])]) }}">{{ $isEnglish ? 'Next' : 'Suivant' }}</a>@endif
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
