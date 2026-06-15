@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Validation de commande' : 'Checkout review') . ' | Denetfils')
@section('description', $locale === 'fr' ? 'Vérifiez le panier, le compte client et l’adresse de livraison avant le paiement.' : 'Review cart, customer account and delivery address before payment.')
@section('robots', 'noindex,nofollow')
@section('canonical', route('checkout.show', ['locale' => $locale]))

@section('content')
    <livewire:shop.checkout-review :locale="$locale" :user="$user" :addresses="$addresses" :countries="$countries" />
@endsection
