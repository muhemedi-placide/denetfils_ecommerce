@extends('layouts.admin')

@php
    $isEnglish = $locale === 'en';
    $order = $invoice['order_detail'] ?? $invoice['order'] ?? [];
    $customer = $order['customer'] ?? [];
    $addresses = $order['addresses'] ?? [];
    $items = $order['items'] ?? [];
@endphp

@section('title', $isEnglish ? 'Invoice details' : 'Détail de la facture')
@section('page_title', $invoice['invoice_number'] ?? ($isEnglish ? 'Invoice' : 'Facture'))
@section('page_subtitle', $isEnglish ? 'Invoice, customer and order details.' : 'Détails de la facture, du client et de la commande.')

@section('content')
    <div class="grid gap-5">
        <section class="admin-card p-4 sm:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div><p class="admin-kicker">{{ $isEnglish ? 'Invoice' : 'Facture' }}</p><h2 class="mt-2 text-3xl font-black">{{ $invoice['invoice_number'] ?? '—' }}</h2><p class="mt-2 admin-muted">{{ $isEnglish ? 'Related order:' : 'Commande associée :' }} {{ data_get($invoice, 'order.order_number', '—') }}</p></div>
                <div class="flex flex-wrap gap-2"><span class="admin-pill">{{ $invoice['status_label'] ?? '—' }}</span><a class="admin-btn-secondary" href="{{ route('admin.invoices', ['locale' => $locale]) }}">{{ $isEnglish ? 'Back' : 'Retour' }}</a><a class="admin-btn" href="{{ route('admin.orders.invoice', ['locale' => $locale, 'order' => data_get($invoice, 'order.id')]) }}">{{ $isEnglish ? 'Download PDF' : 'Télécharger le PDF' }}</a></div>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-4">
                @foreach([
                    [$isEnglish ? 'Total' : 'Total TTC', $invoice['formatted_total'] ?? '—'],
                    [$isEnglish ? 'Issued on' : 'Émise le', $invoice['issued_at'] ? \Illuminate\Support\Carbon::parse($invoice['issued_at'])->format($isEnglish ? 'Y-m-d' : 'd/m/Y') : '—'],
                    [$isEnglish ? 'Due on' : 'Échéance', $invoice['due_at'] ? \Illuminate\Support\Carbon::parse($invoice['due_at'])->format($isEnglish ? 'Y-m-d' : 'd/m/Y') : '—'],
                    [$isEnglish ? 'Paid on' : 'Payée le', $invoice['paid_at'] ? \Illuminate\Support\Carbon::parse($invoice['paid_at'])->format($isEnglish ? 'Y-m-d' : 'd/m/Y') : '—'],
                ] as [$label, $value])<article class="admin-panel p-4"><p class="admin-kicker">{{ $label }}</p><p class="mt-2 text-xl font-black">{{ $value }}</p></article>@endforeach
            </div>
        </section>

        <section class="grid gap-5 lg:grid-cols-2">
            <article class="admin-card p-4 sm:p-5"><h2 class="admin-heading">{{ $isEnglish ? 'Customer' : 'Client' }}</h2><p class="mt-4 font-black">{{ $customer['name'] ?? data_get($invoice, 'order.customer.name', '—') }}</p><p class="admin-muted">{{ $customer['email'] ?? data_get($invoice, 'order.customer.email', '—') }}</p><p class="admin-muted">{{ $customer['phone'] ?? data_get($invoice, 'order.customer.phone', '—') }}</p></article>
            <article class="admin-card p-4 sm:p-5">
                <h2 class="admin-heading">{{ $isEnglish ? 'Billing address' : 'Adresse de facturation' }}</h2>
                @php $billing = collect($addresses)->firstWhere('type', 'billing') ?? collect($addresses)->firstWhere('type', 'shipping'); @endphp
                @if($billing)<p class="mt-4 font-black">{{ $billing['recipient_name'] ?? '—' }}</p><p>{{ $billing['street_line_1'] ?? '' }} {{ $billing['street_line_2'] ?? '' }}</p><p>{{ $billing['postal_code'] ?? '' }} {{ $billing['city'] ?? '' }}, {{ $billing['country_code'] ?? '' }}</p>@else<p class="mt-4 admin-muted">{{ $isEnglish ? 'No billing address.' : 'Aucune adresse de facturation.' }}</p>@endif
            </article>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="p-4 sm:p-5"><h2 class="admin-heading">{{ $isEnglish ? 'Invoiced items' : 'Articles facturés' }}</h2></div>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>{{ $isEnglish ? 'Product' : 'Produit' }}</th><th>{{ $isEnglish ? 'Quantity' : 'Quantité' }}</th><th>{{ $isEnglish ? 'Unit price' : 'Prix unitaire' }}</th><th>{{ $isEnglish ? 'Tax' : 'TVA' }}</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr><td><p class="font-black">{{ data_get($item, 'product.name', '—') }}</p><p class="admin-muted">{{ data_get($item, 'product.sku', '—') }}</p></td><td>{{ $item['quantity'] ?? 0 }}</td><td>{{ $item['formatted_unit_price'] ?? '—' }}</td><td>{{ $item['formatted_tax'] ?? '—' }}</td><td class="font-black">{{ $item['formatted_line_total'] ?? '—' }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="p-8 text-center admin-muted">{{ $isEnglish ? 'No invoiced items.' : 'Aucun article facturé.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
