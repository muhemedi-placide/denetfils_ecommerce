@extends('layouts.shop')

@section('title', __('home.account.auth.login_title') . ' | ' . config('shop.name'))
@section('description', __('home.account.auth.login_intro'))
@section('robots', 'noindex,nofollow')

@section('content')
    <section class="soft-grid px-4 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div class="max-w-xl">
                <p class="section-kicker">{{ __('home.account.nav') }}</p>
                <h1 class="brand-display mt-4 text-5xl uppercase text-forest dark:text-meadow sm:text-6xl">{{ __('home.account.auth.login_title') }}</h1>
                <p class="mt-5 text-base font-semibold leading-8 text-cocoa/70 dark:text-cream/70">{{ __('home.account.auth.login_intro') }}</p>
            </div>

            <livewire:account.login-form :locale="$locale" />
        </div>
    </section>
@endsection
