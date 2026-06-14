@extends('layouts.shop')

@section('title', __('home.account.auth.login_title') . ' | DEN & FILS')
@section('description', __('home.account.auth.login_intro'))
@section('robots', 'noindex,nofollow')

@section('content')
    <section class="bg-linen px-4 py-12 dark:bg-[#172414] sm:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div class="max-w-xl">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.account.nav') }}</p>
                <h1 class="mt-3 text-3xl font-black text-cocoa dark:text-cream sm:text-4xl">{{ __('home.account.auth.login_title') }}</h1>
                <p class="mt-4 text-base leading-8 text-cocoa/70 dark:text-cream/70">{{ __('home.account.auth.login_intro') }}</p>
            </div>

            <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-7">
                @if (session('status'))
                    <div class="mb-5 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-leaf dark:border-white/10 dark:bg-white/10 dark:text-meadow">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-terracotta/25 bg-terracotta/10 px-4 py-3 text-sm text-cocoa dark:text-cream">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('account.login.store', ['locale' => $locale]) }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.email') }}</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                    </div>

                    <div>
                        <label for="password" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.password') }}</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                    </div>

                    <button type="submit" class="w-full rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay">{{ __('home.account.auth.sign_in') }}</button>
                </form>

                <div class="mt-6 flex flex-col gap-2 border-t border-leaf/10 pt-5 text-sm text-cocoa/70 dark:border-white/10 dark:text-cream/70 sm:flex-row sm:items-center sm:justify-between">
                    <span>{{ __('home.account.auth.no_account') }}</span>
                    <a class="font-bold text-leaf transition hover:text-forest dark:text-meadow" href="{{ route('account.register', ['locale' => $locale]) }}">{{ __('home.account.auth.go_register') }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
