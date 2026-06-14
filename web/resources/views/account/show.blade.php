@extends('layouts.shop')

@php
    $roles = collect($user['roles'] ?? [])->implode(', ');
    $timezones = ['Europe/Paris', 'Europe/Brussels', 'Europe/Berlin', 'Europe/Amsterdam', 'Europe/Madrid', 'Europe/Rome', 'Europe/Luxembourg', 'Europe/Lisbon'];
@endphp

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

                <form method="POST" action="{{ route('account.logout', ['locale' => $locale]) }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-leaf/20 bg-white px-5 py-3 text-sm font-bold uppercase tracking-wide text-cocoa transition hover:border-leaf hover:text-leaf dark:border-white/10 dark:bg-white/5 dark:text-cream dark:hover:border-meadow dark:hover:text-meadow">{{ __('home.account.profile.logout') }}</button>
                </form>
            </div>

            @if (session('status'))
                <div class="mt-8 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-leaf dark:border-white/10 dark:bg-white/10 dark:text-meadow">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-8 rounded-xl border border-terracotta/25 bg-terracotta/10 px-4 py-3 text-sm text-cocoa dark:text-cream">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mt-8 grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                <aside class="space-y-4">
                    <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm font-bold uppercase tracking-wide text-leaf dark:text-meadow">{{ __('home.account.profile.status') }}</p>
                        <p class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $user['status'] ?? 'active' }}</p>
                        <p class="mt-1 text-sm text-cocoa/60 dark:text-cream/60">{{ $user['email'] ?? '' }}</p>
                    </div>
                    <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm font-bold uppercase tracking-wide text-leaf dark:text-meadow">{{ __('home.account.profile.role') }}</p>
                        <p class="mt-2 text-lg font-extrabold text-cocoa dark:text-cream">{{ $roles ?: 'customer' }}</p>
                    </div>
                </aside>

                <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-7">
                    <h2 class="text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.profile.section_title') }}</h2>
                    <form method="POST" action="{{ route('account.update', ['locale' => $locale]) }}" class="mt-6 space-y-5">
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.first_name') }}</label>
                                <input id="first_name" name="first_name" value="{{ old('first_name', $user['first_name'] ?? '') }}" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                            </div>
                            <div>
                                <label for="last_name" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.last_name') }}</label>
                                <input id="last_name" name="last_name" value="{{ old('last_name', $user['last_name'] ?? '') }}" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="phone" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.phone') }}</label>
                                <input id="phone" name="phone" value="{{ old('phone', $user['phone'] ?? '') }}" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                            </div>
                            <div>
                                <label for="country_code" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.country') }}</label>
                                <select id="country_code" name="country_code" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                                    @foreach ($countries as $country)
                                        <option value="{{ $country['code'] }}" @selected(old('country_code', $user['country_code'] ?? 'FR') === $country['code'])>{{ $country['name'] }} · {{ $country['currency'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="preferred_locale" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.profile.preferred_locale') }}</label>
                                <select id="preferred_locale" name="preferred_locale" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                                    <option value="fr" @selected(old('preferred_locale', $user['preferred_locale'] ?? $locale) === 'fr')>{{ __('home.locale.fr') }}</option>
                                    <option value="en" @selected(old('preferred_locale', $user['preferred_locale'] ?? $locale) === 'en')>{{ __('home.locale.en') }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="timezone" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.profile.timezone') }}</label>
                                <select id="timezone" name="timezone" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                                    @foreach ($timezones as $timezone)
                                        <option value="{{ $timezone }}" @selected(old('timezone', $user['timezone'] ?? 'Europe/Paris') === $timezone)>{{ $timezone }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay">{{ __('home.account.profile.save') }}</button>
                    </form>
                </div>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
                <section class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-7">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.account.addresses.title') }}</p>
                    <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.addresses.new_title') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.account.addresses.intro') }}</p>

                    <form method="POST" action="{{ route('account.addresses.store', ['locale' => $locale]) }}" class="mt-6 space-y-4">
                        @csrf
                        @include('account.address-form-fields', ['address' => [], 'countries' => $countries, 'prefix' => 'new'])
                        <button type="submit" class="w-full rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay">{{ __('home.account.addresses.save') }}</button>
                    </form>
                </section>

                <section class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.account.addresses.title') }}</p>
                        <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.addresses.title') }}</h2>
                    </div>

                    @forelse ($addresses as $address)
                        <details class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5" @if ($loop->first) open @endif>
                            <summary class="cursor-pointer list-none">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-lg font-black text-cocoa dark:text-cream">{{ $address['label'] ?: $address['recipient_name'] }}</h3>
                                            @if ($address['is_default'])
                                                <span class="rounded-full bg-mint px-3 py-1 text-xs font-bold uppercase tracking-wide text-leaf dark:bg-white/10 dark:text-meadow">{{ __('home.account.addresses.default_badge') }}</span>
                                            @endif
                                        </div>
                                        <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">
                                            {{ $address['street_line_1'] }}, {{ $address['postal_code'] }} {{ $address['city'] }} · {{ $address['country_code'] }}
                                        </p>
                                    </div>
                                    <span class="rounded-full border border-leaf/15 px-3 py-1 text-xs font-bold uppercase tracking-wide text-cocoa/70 dark:border-white/10 dark:text-cream/70">
                                        {{ __('home.account.addresses.' . $address['type']) }}
                                    </span>
                                </div>
                            </summary>

                            <div class="mt-5 border-t border-leaf/10 pt-5 dark:border-white/10">
                                <form method="POST" action="{{ route('account.addresses.update', ['locale' => $locale, 'address' => $address['id']]) }}" class="space-y-4">
                                    @csrf
                                    @method('PATCH')
                                    @include('account.address-form-fields', ['address' => $address, 'countries' => $countries, 'prefix' => 'address-' . $address['id']])
                                    <div class="flex flex-col gap-3 sm:flex-row">
                                        <button type="submit" class="rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay">{{ __('home.account.addresses.update') }}</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('account.addresses.delete', ['locale' => $locale, 'address' => $address['id']]) }}" class="mt-3">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-leaf/20 px-5 py-2.5 text-sm font-bold text-cocoa transition hover:border-terracotta hover:text-terracotta dark:border-white/10 dark:text-cream">{{ __('home.account.addresses.delete') }}</button>
                                </form>
                            </div>
                        </details>
                    @empty
                        <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-6 text-sm text-cocoa/65 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-cream/65">
                            {{ __('home.account.addresses.empty') }}
                        </div>
                    @endforelse
                </section>
            </div>
        </div>
    </section>
@endsection
