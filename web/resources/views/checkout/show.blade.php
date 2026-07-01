@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Validation de commande' : 'Checkout review') . ' | ' . config('shop.name'))
@section('description', $locale === 'fr' ? 'Verifiez le panier, le compte client et l adresse de livraison avant le paiement.' : 'Review cart, customer account and delivery address before payment.')
@section('robots', 'noindex,nofollow')
@section('canonical', route('checkout.show', ['locale' => $locale]))

@section('content')
    @if (session('payment_notice') || $errors->has('payment'))
        <div class="store-container pt-4">
            <div class="rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-semibold text-black">
                {{ session('payment_notice') ?: $errors->first('payment') }}
            </div>
        </div>
    @endif
    <livewire:shop.checkout-review
        :locale="$locale"
        :user="$user"
        :addresses="$addresses"
        :countries="$countries"
        :completed-order="$completedOrder"
        :visitor-country-code="$visitorContext['country_code'] ?? 'FR'"
    />
@endsection
