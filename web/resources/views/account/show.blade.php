@extends('layouts.shop')

@section('title', __('home.account.profile.title') . ' | DEN & FILS')
@section('description', __('home.account.profile.intro'))
@section('robots', 'noindex,nofollow')

@section('content')
    <section class="soft-grid px-4 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="section-kicker">{{ __('home.account.nav') }}</p>
                    <h1 class="brand-display mt-4 text-5xl uppercase text-forest dark:text-meadow sm:text-6xl">{{ __('home.account.profile.title') }}</h1>
                    <p class="mt-5 text-base font-semibold leading-8 text-cocoa/70 dark:text-cream/70">{{ __('home.account.profile.intro') }}</p>
                </div>
            </div>

            <livewire:account.dashboard :locale="$locale" :user="$user" :addresses="$addresses" :orders="$orders" :countries="$countries" />
        </div>
    </section>
@endsection
