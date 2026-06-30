@extends('layouts.shop')

@section('title', __('home.account.profile.title') . ' | ' . config('shop.name'))
@section('description', __('home.account.profile.intro'))
@section('robots', 'noindex,nofollow')

@section('content')
    <section class="soft-grid px-4 pb-20 pt-8 dark:bg-ink sm:px-6 lg:px-8 lg:pb-24 lg:pt-10">
        <div class="mx-auto max-w-7xl">
            <livewire:account.dashboard :locale="$locale" :user="$user" :addresses="$addresses" :orders="$orders" :countries="$countries" />
        </div>
    </section>
@endsection
