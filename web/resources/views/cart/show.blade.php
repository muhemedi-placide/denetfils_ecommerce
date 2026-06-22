@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Panier' : 'Cart') . ' | Marche Peyi')
@section('description', $locale === 'fr' ? 'Verifiez vos produits Marche Peyi avant de passer a la commande.' : 'Review your Marche Peyi products before checkout.')
@section('robots', 'noindex,nofollow')
@section('canonical', route('cart.show', ['locale' => $locale]))

@section('content')
    <livewire:shop.cart-page :locale="$locale" :recommended-products="$recommendedProducts" />
@endsection
