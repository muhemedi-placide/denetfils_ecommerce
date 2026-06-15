@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Panier' : 'Cart') . ' | Denetfils')
@section('description', $locale === 'fr' ? 'Vérifiez vos produits DEN & FILS avant de passer à la commande.' : 'Review your DEN & FILS products before checkout.')
@section('robots', 'noindex,nofollow')
@section('canonical', route('cart.show', ['locale' => $locale]))

@section('content')
    <livewire:shop.cart-page :locale="$locale" :recommended-products="$recommendedProducts" />
@endsection
