@extends('layouts.shop')

@section('title', __('home.about.title') . ' | Denetfils')
@section('description', __('home.about.body'))

@section('content')
    <section class="soft-grid px-5 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.about.eyebrow') }}</p>
                <h1 class="mt-3 max-w-3xl text-4xl font-extrabold leading-tight text-cocoa dark:text-cream sm:text-5xl">
                    {{ __('home.about.title') }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-cocoa/70 dark:text-cream/70">
                    {{ __('home.about.body') }}
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary">{{ __('home.hero.primary_cta') }}</a>
                    <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="btn-secondary">{{ __('home.nav.blog') }}</a>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-[1.75rem] bg-linen p-3 shadow-sm dark:bg-white/5">
                <img
                    class="aspect-[4/3] w-full rounded-[1.25rem] object-cover"
                    src="https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=1200&q=84"
                    alt="{{ __('home.about.image_alt') }}"
                    loading="lazy"
                >
                <div class="absolute bottom-6 left-6 right-6 rounded-[1.25rem] border border-white/20 bg-forest/85 p-5 text-white shadow-xl backdrop-blur">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-meadow">DEN & FILS</p>
                    <p class="mt-2 text-lg font-extrabold leading-snug">{{ __('home.about.image_caption') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white px-5 py-14 dark:bg-[#172414] sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-3">
            <article class="rounded-[1.5rem] border border-leaf/10 bg-linen p-6 dark:border-white/10 dark:bg-white/5 lg:col-span-2">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">01</p>
                <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ __('home.about.story_title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.about.story_body') }}</p>
            </article>

            <article class="rounded-[1.5rem] border border-leaf/10 bg-forest p-6 text-white dark:border-white/10">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-meadow">02</p>
                <h2 class="mt-3 text-2xl font-extrabold">{{ __('home.about.reach_title') }}</h2>
                <ul class="mt-5 space-y-3 text-sm leading-6 text-white/78">
                    @foreach (trans('home.about.reach_items') as $item)
                        <li class="flex gap-3"><span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-meadow"></span><span>{{ $item }}</span></li>
                    @endforeach
                </ul>
            </article>
        </div>
    </section>

    <section class="bg-linen px-5 py-14 dark:bg-ink sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <article class="rounded-[1.5rem] border border-leaf/10 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">03</p>
                <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ __('home.about.vision_title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.about.vision_body') }}</p>
            </article>

            <article class="rounded-[1.5rem] border border-leaf/10 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">04</p>
                <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ __('home.about.commitment_title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.about.commitment_body') }}</p>
            </article>
        </div>
    </section>

    <section class="bg-white px-5 py-14 dark:bg-[#172414] sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-3">
            @foreach (trans('home.about.points') as $point)
                <article class="rounded-[1.25rem] border border-leaf/10 bg-linen p-6 dark:border-white/10 dark:bg-white/5">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $point['eyebrow'] }}</p>
                    <h2 class="mt-3 text-xl font-extrabold text-cocoa dark:text-cream">{{ $point['title'] }}</h2>
                    <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $point['body'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="bg-linen px-5 py-14 dark:bg-ink sm:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 rounded-[1.75rem] border border-leaf/10 bg-white p-6 dark:border-white/10 dark:bg-white/5 lg:grid-cols-[0.8fr_1.2fr] lg:p-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.contact.eyebrow') }}</p>
                <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ __('home.contact.title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.footer.line') }}</p>
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
                <div class="rounded-[1.25rem] bg-linen p-5 dark:bg-white/5 sm:col-span-2">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">TVA</p>
                    <p class="mt-2 text-sm font-semibold text-cocoa dark:text-cream">{{ __('home.contact.vat') }}</p>
                </div>
            </div>
        </div>
    </section>
@endsection
