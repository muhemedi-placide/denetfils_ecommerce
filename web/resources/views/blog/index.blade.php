@extends('layouts.shop')

@section('title', __('home.blog.title') . ' | Denetfils')
@section('description', __('home.blog.body'))

@section('content')
    <section class="soft-grid px-5 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.blog.eyebrow') }}</p>
                <h1 class="mt-3 text-4xl font-extrabold leading-tight text-cocoa dark:text-cream sm:text-5xl">
                    {{ __('home.blog.title') }}
                </h1>
                <p class="mt-5 text-base leading-8 text-cocoa/70 dark:text-cream/70">
                    {{ __('home.blog.body') }}
                </p>
            </div>

            <div class="mt-10 grid gap-5 lg:grid-cols-3">
                @foreach (trans('home.blog.posts') as $post)
                    <article class="group overflow-hidden rounded-[1.5rem] border border-leaf/10 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-white/5">
                        <div class="h-2 bg-terracotta"></div>
                        <div class="p-6">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $post['category'] }}</p>
                            <h2 class="mt-4 text-xl font-extrabold leading-snug text-cocoa transition group-hover:text-leaf dark:text-cream">
                                {{ $post['title'] }}
                            </h2>
                            <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">
                                {{ $post['body'] }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white px-5 py-14 dark:bg-[#172414] sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 rounded-[1.75rem] border border-leaf/10 bg-linen p-6 dark:border-white/10 dark:bg-white/5 lg:grid-cols-[0.85fr_1.15fr] lg:p-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.products.eyebrow') }}</p>
                <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ __('home.products.title') }}</h2>
                <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.products.body') }}</p>
            </div>
            <div class="flex flex-col justify-center gap-3 sm:flex-row lg:justify-end">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary">{{ __('home.hero.primary_cta') }}</a>
                <button type="button" x-on:click="loadCart(true)" class="btn-secondary">{{ __('home.cart.title') }}</button>
            </div>
        </div>
    </section>
@endsection
