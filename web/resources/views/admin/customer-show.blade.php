@extends('layouts.admin')

@section('title', 'Fiche client')
@section('page_title', $customer['name'] ?? 'Fiche client')
@section('page_subtitle', 'Informations, adresses, commandes, paiements et conversations.')

@php
    $summary = $customer['summary'] ?? [];
    $addresses = $customer['addresses'] ?? [];
    $orders = $customer['orders'] ?? [];
    $statuses = ['active' => 'Actif', 'suspended' => 'Suspendu', 'deleted_pending' => 'Suppression demandee'];
@endphp

@section('content')
    <div class="grid gap-5">
        <section class="admin-card p-4 sm:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="admin-kicker">Client #{{ $customer['id'] }}</p>
                    <h2 class="mt-2 admin-heading">{{ $customer['name'] ?? '-' }}</h2>
                    <p class="admin-muted mt-1">{{ $customer['email'] ?? '-' }} · {{ $customer['phone'] ?? 'Telephone non renseigne' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.customers.update', ['locale' => $locale, 'customer' => $customer['id']]) }}" class="flex gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="admin-select">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($customer['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="admin-btn">Enregistrer</button>
                </form>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-4">
                @foreach([
                    ['Commandes', $summary['orders_count'] ?? 0],
                    ['Depense', number_format(($summary['total_spent_cents'] ?? 0) / 100, 2, ',', ' ').' €'],
                    ['Adresses', $summary['addresses_count'] ?? 0],
                    ['Conversations ouvertes', $summary['open_conversations_count'] ?? 0],
                ] as [$label, $value])
                    <div class="admin-panel p-4"><p class="admin-kicker">{{ $label }}</p><p class="mt-2 text-2xl font-black">{{ $value }}</p></div>
                @endforeach
            </div>
        </section>

        <section class="admin-card p-4 sm:p-5">
            <h2 class="admin-heading">Adresses du client</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @forelse($addresses as $address)
                    <article class="admin-panel p-4">
                        <div class="flex justify-between"><p class="font-black">{{ $address['label'] ?? ucfirst($address['type'] ?? 'Adresse') }}</p>@if($address['is_default'] ?? false)<span class="admin-pill">Par defaut</span>@endif</div>
                        <p class="admin-muted mt-2">{{ $address['recipient_name'] ?? '-' }}</p>
                        <p class="mt-1">{{ $address['street_line_1'] ?? '' }} {{ $address['street_line_2'] ?? '' }}</p>
                        <p>{{ $address['postal_code'] ?? '' }} {{ $address['city'] ?? '' }}, {{ $address['country_code'] ?? '' }}</p>
                    </article>
                @empty
                    <p class="admin-muted">Aucune adresse enregistree.</p>
                @endforelse
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="p-4 sm:p-5"><h2 class="admin-heading">Commandes, paiements et conversations</h2></div>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Commande</th><th>Total</th><th>Paiement</th><th>Conversation</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                    @forelse($orders as $order)
                        @php
                            $payments = collect($order['payments'] ?? []);
                            $conversation = $order['conversation'] ?? null;
                        @endphp
                        <tr>
                            <td><p class="font-black">{{ $order['order_number'] ?? '-' }}</p><p class="admin-muted">{{ $order['status_label'] ?? ($order['status'] ?? '-') }}</p></td>
                            <td>{{ $order['formatted_total'] ?? number_format(($order['total_cents'] ?? 0) / 100, 2, ',', ' ').' €' }}</td>
                            <td>
                                @forelse($payments as $payment)
                                    <p>{{ strtoupper($payment['provider'] ?? '-') }} · {{ $payment['status'] ?? '-' }}</p>
                                @empty
                                    <span class="admin-muted">{{ $order['payment_status_label'] ?? ($order['payment_status'] ?? '-') }}</span>
                                @endforelse
                            </td>
                            <td>{{ $conversation['status'] ?? 'Non ouverte' }} @if(($conversation['staff_unread_count'] ?? 0) > 0)· {{ $conversation['staff_unread_count'] }} non lu(s)@endif</td>
                            <td>{{ $order['placed_at'] ?? $order['created_at'] ?? '-' }}</td>
                            <td class="text-right"><a class="admin-btn-secondary" href="{{ route('admin.orders.show', ['locale' => $locale, 'order' => $order['id']]) }}">Ouvrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center admin-muted">Aucune commande pour ce client.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
