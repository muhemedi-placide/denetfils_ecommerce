@extends('layouts.admin')

@section('title', ($locale === 'en' ? 'Cart ' : 'Panier ').($cart['reference'] ?? ''))
@section('page_title', ($locale === 'en' ? 'Cart details' : 'Détail du panier'))
@section('page_subtitle', $cart['reference'] ?? '')

@section('content')
    @php($isEnglish = $locale === 'en')

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a class="admin-btn-secondary" href="{{ route('admin.carts', ['locale' => $locale]) }}">← {{ $isEnglish ? 'Back to carts' : 'Retour aux paniers' }}</a>
        @if (! in_array($cart['admin_status'] ?? '', ['converted', 'expired', 'empty'], true))
            <form method="POST" action="{{ route('admin.carts.recovery-link', ['locale' => $locale, 'cart' => $cart['id']]) }}">@csrf<button class="admin-btn-primary" type="submit">{{ $isEnglish ? 'Create recovery link' : 'Créer un lien de récupération' }}</button></form>
        @endif
    </div>

    @if (session('cart_recovery_url'))
        <div class="admin-card mb-5 p-5" x-data="{ copied: false }">
            <p class="admin-kicker">{{ $isEnglish ? 'Secure recovery link' : 'Lien sécurisé de récupération' }}</p>
            <div class="mt-3 flex gap-2"><input x-ref="url" class="admin-input" value="{{ session('cart_recovery_url') }}" readonly><button class="admin-btn-primary" type="button" x-on:click="navigator.clipboard.writeText($refs.url.value); copied = true"><span x-show="!copied">{{ $isEnglish ? 'Copy' : 'Copier' }}</span><span x-cloak x-show="copied">{{ $isEnglish ? 'Copied' : 'Copié' }}</span></button></div>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
        <section class="admin-card p-4 sm:p-5">
            <h2 class="text-xl font-black">{{ $isEnglish ? 'Products' : 'Produits' }}</h2>
            <div class="mt-4 overflow-x-auto rounded-xl border border-leaf/10 dark:border-white/10">
                <table class="admin-table min-w-[700px]">
                    <thead><tr><th class="px-4 py-3">{{ $isEnglish ? 'Product' : 'Produit' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Unit price' : 'Prix unitaire' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Quantity' : 'Quantité' }}</th><th class="px-4 py-3">{{ $isEnglish ? 'Total' : 'Total' }}</th></tr></thead>
                    <tbody>@foreach ($cart['items'] ?? [] as $item)<tr><td class="px-4 py-3"><strong>{{ data_get($item, 'product.name', '—') }}</strong><small class="mt-1 block admin-muted">{{ data_get($item, 'variant.sku', data_get($item, 'product.sku', '—')) }} · {{ data_get($item, 'variant.name', data_get($item, 'product.origin', '')) }}</small></td><td class="px-4 py-3">{{ $item['formatted_unit_price'] ?? '—' }}</td><td class="px-4 py-3 font-bold">{{ $item['quantity'] ?? 0 }}</td><td class="px-4 py-3 font-black">{{ $item['formatted_line_total'] ?? '—' }}</td></tr>@endforeach</tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-5">
            <section class="admin-card p-5">
                <h2 class="text-xl font-black">{{ $isEnglish ? 'Summary' : 'Résumé' }}</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="admin-muted">{{ $isEnglish ? 'Status' : 'Statut' }}</dt><dd class="font-bold">{{ $cart['admin_status'] ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="admin-muted">{{ $isEnglish ? 'Items' : 'Articles' }}</dt><dd class="font-bold">{{ $cart['items_count'] ?? 0 }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="admin-muted">{{ $isEnglish ? 'Weight' : 'Poids' }}</dt><dd class="font-bold">{{ number_format((int) ($cart['total_weight_grams'] ?? 0) / 1000, 2, ',', ' ') }} kg</dd></div>
                    <div class="flex justify-between gap-3 border-t border-leaf/10 pt-3 text-lg"><dt class="font-black">{{ $isEnglish ? 'Total' : 'Total' }}</dt><dd class="font-black">{{ $cart['formatted_total'] ?? '—' }}</dd></div>
                </dl>
            </section>
            <section class="admin-card p-5">
                <h2 class="text-xl font-black">{{ $isEnglish ? 'Customer and activity' : 'Client et activité' }}</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="admin-muted">{{ $isEnglish ? 'Customer' : 'Client' }}</dt><dd class="mt-1 font-bold">{{ data_get($cart, 'customer.name', $isEnglish ? 'Guest' : 'Visiteur') }}</dd></div>
                    <div><dt class="admin-muted">{{ $isEnglish ? 'Email' : 'E-mail' }}</dt><dd class="mt-1 font-bold">{{ data_get($cart, 'customer.email', '—') }}</dd></div>
                    <div><dt class="admin-muted">{{ $isEnglish ? 'Created' : 'Créé' }}</dt><dd class="mt-1">{{ ! empty($cart['created_at']) ? \Illuminate\Support\Carbon::parse($cart['created_at'])->locale($locale)->translatedFormat('d M Y, H:i') : '—' }}</dd></div>
                    <div><dt class="admin-muted">{{ $isEnglish ? 'Last activity' : 'Dernière activité' }}</dt><dd class="mt-1">{{ ! empty($cart['last_activity_at']) ? \Illuminate\Support\Carbon::parse($cart['last_activity_at'])->locale($locale)->diffForHumans() : '—' }}</dd></div>
                    <div><dt class="admin-muted">{{ $isEnglish ? 'Expires' : 'Expire' }}</dt><dd class="mt-1">{{ ! empty($cart['expires_at']) ? \Illuminate\Support\Carbon::parse($cart['expires_at'])->locale($locale)->translatedFormat('d M Y, H:i') : '—' }}</dd></div>
                    <div><dt class="admin-muted">{{ $isEnglish ? 'Recovery links' : 'Liens de récupération' }}</dt><dd class="mt-1 font-bold">{{ count($cart['recovery_links'] ?? []) }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
@endsection
