@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Panier' : 'Cart') . ' | ' . config('shop.name'))
@section('description', $locale === 'fr' ? 'Verifiez vos produits '.config('shop.name').' avant de passer a la commande.' : 'Review your '.config('shop.name').' products before checkout.')
@section('robots', 'noindex,nofollow')
@section('canonical', route('cart.show', ['locale' => $locale]))

@section('content')
    <livewire:shop.cart-page
        :locale="$locale"
        :recommended-products="$recommendedProducts"
        :country-code="$visitorContext['country_code'] ?? 'FR'"
    />
@endsection
