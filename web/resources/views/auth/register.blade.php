@extends('layouts.shop')

@section('title', __('home.account.auth.register_title') . ' | DEN & FILS')
@section('description', __('home.account.auth.register_intro'))
@section('robots', 'noindex,nofollow')

@section('content')
    <section class="bg-linen px-4 py-12 dark:bg-[#172414] sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div class="max-w-xl">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.account.nav') }}</p>
                <h1 class="mt-3 text-3xl font-black text-cocoa dark:text-cream sm:text-4xl">{{ __('home.account.auth.register_title') }}</h1>
                <p class="mt-4 text-base leading-8 text-cocoa/70 dark:text-cream/70">{{ __('home.account.auth.register_intro') }}</p>
            </div>

            <livewire:account.register-form :locale="$locale" />
        </div>
    </section>
@endsection
