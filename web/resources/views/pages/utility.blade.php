@extends('layouts.shop')

@section('title', $content['title'] . ' | Denetfils')
@section('description', $content['intro'])

@section('content')
    <section class="soft-grid px-5 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto max-w-5xl">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $content['eyebrow'] }}</p>
            <h1 class="mt-3 text-4xl font-extrabold leading-tight text-cocoa dark:text-cream sm:text-5xl">
                {{ $content['title'] }}
            </h1>
            <p class="mt-5 max-w-3xl text-base leading-8 text-cocoa/70 dark:text-cream/70">
                {{ $content['intro'] }}
            </p>
        </div>
    </section>

    <section class="bg-white px-5 py-14 dark:bg-[#172414] sm:px-8">
        <div class="mx-auto grid max-w-5xl gap-5">
            @foreach ($content['sections'] as $section)
                <article class="rounded-[1.5rem] border border-leaf/10 bg-linen p-6 dark:border-white/10 dark:bg-white/5">
                    <h2 class="text-xl font-extrabold text-cocoa dark:text-cream">{{ $section['title'] }}</h2>
                    <p class="mt-3 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ $section['body'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="bg-linen px-5 py-14 dark:bg-ink sm:px-8">
        <div class="mx-auto grid max-w-5xl gap-6 rounded-[1.75rem] border border-leaf/10 bg-white p-6 dark:border-white/10 dark:bg-white/5 lg:grid-cols-[0.85fr_1.15fr] lg:p-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.contact.eyebrow') }}</p>
                <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ __('home.contact.title') }}</h2>
                <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.footer.line') }}</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-[1.25rem] bg-linen p-5 dark:bg-white/5">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ __('home.contact.company') }}</p>
                    <p class="mt-2 text-sm font-semibold text-cocoa dark:text-cream">{{ __('home.contact.address') }}</p>
                </div>
                <div class="rounded-[1.25rem] bg-linen p-5 dark:bg-white/5">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">Contact</p>
                    <p class="mt-2 text-sm font-semibold text-cocoa dark:text-cream">{{ __('home.contact.phone') }}</p>
                    <p class="mt-1 text-sm text-cocoa/65 dark:text-cream/65">{{ __('home.contact.email') }}</p>
                </div>
            </div>
        </div>
    </section>
@endsection
