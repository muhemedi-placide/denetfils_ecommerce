@extends('layouts.shop')

@section('title', __('home.account.profile.title') . ' | DEN & FILS')
@section('description', __('home.account.profile.intro'))
@section('robots', 'noindex,nofollow')

@section('content')
    <section class="bg-linen px-4 py-12 dark:bg-[#172414] sm:px-8 lg:py-16">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.account.nav') }}</p>
                    <h1 class="mt-3 text-3xl font-black text-cocoa dark:text-cream sm:text-4xl">{{ __('home.account.profile.title') }}</h1>
                    <p class="mt-4 text-base leading-8 text-cocoa/70 dark:text-cream/70">{{ __('home.account.profile.intro') }}</p>
                </div>
            </div>

            <livewire:account.dashboard :locale="$locale" :user="$user" :addresses="$addresses" :orders="$orders" :countries="$countries" />
        </div>
    </section>
@endsection
