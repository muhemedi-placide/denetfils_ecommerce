@extends('layouts.shop')

@section('title', __('home.about.title') . ' | Marche Peyi')
@section('description', __('home.about.body'))

@section('content')
    <section class="soft-grid px-4 py-14 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div>
                <p class="section-kicker">{{ __('home.about.eyebrow') }}</p>
                <h1 class="brand-display mt-4 max-w-3xl text-5xl uppercase text-forest dark:text-meadow sm:text-7xl">{{ __('home.about.title') }}</h1>
                <p class="mt-6 max-w-2xl text-base font-semibold leading-8 text-cocoa/70 dark:text-cream/70">{{ __('home.about.body') }}</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="btn-primary" wire:navigate.hover>{{ __('home.hero.primary_cta') }}</a>
                    <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="btn-secondary" wire:navigate.hover>{{ __('home.nav.blog') }}</a>
                </div>
            </div>
            <div class="relative overflow-hidden rounded-[2rem] border-[6px] border-forest bg-white shadow-tropical dark:bg-white/5">
                <img class="aspect-[4/3] w-full object-cover" src="https://moodboard-to-shop.lovable.app/assets/leaves-D-dPOddf.jpg" alt="{{ __('home.about.image_alt') }}" loading="lazy" decoding="async">
                <div class="absolute bottom-5 left-5 right-5 rounded-[1.25rem] bg-cream/95 p-5 text-forest shadow-xl backdrop-blur"><p class="text-xs font-black uppercase tracking-[0.22em] text-coral">Marché Peyi</p><p class="mt-2 text-lg font-black leading-snug">{{ __('home.about.image_caption') }}</p></div>
            </div>
        </div>
    </section>

    <section class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-16"><div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-3"><article class="utility-section bg-linen dark:bg-white/5 lg:col-span-2"><p class="section-kicker">01</p><h2 class="mt-3 text-3xl font-black text-forest dark:text-meadow">{{ __('home.about.story_title') }}</h2><p class="mt-5 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.about.story_body') }}</p></article><article class="rounded-[1.5rem] bg-forest p-6 text-cream shadow-sm"><p class="text-xs font-black uppercase tracking-[0.18em] text-sunshine">02</p><h2 class="mt-3 text-3xl font-black">{{ __('home.about.reach_title') }}</h2><ul class="mt-5 space-y-3 text-sm leading-6 text-cream/78">@foreach (trans('home.about.reach_items') as $item)<li class="flex gap-3"><span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-sunshine"></span><span>{{ $item }}</span></li>@endforeach</ul></article></div></section>
    <section class="bg-linen px-4 py-14 dark:bg-[#163319] sm:px-8 lg:py-16"><div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-[0.9fr_1.1fr]"><article class="utility-section"><p class="section-kicker">03</p><h2 class="mt-3 text-3xl font-black text-forest dark:text-meadow">{{ __('home.about.vision_title') }}</h2><p class="mt-5 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.about.vision_body') }}</p></article><article class="utility-section"><p class="section-kicker">04</p><h2 class="mt-3 text-3xl font-black text-forest dark:text-meadow">{{ __('home.about.commitment_title') }}</h2><p class="mt-5 text-sm leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.about.commitment_body') }}</p></article></div></section>
    <section class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-16"><div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-3">@foreach (trans('home.about.points') as $point)<article class="utility-section bg-linen dark:bg-white/5"><p class="section-kicker">{{ $point['eyebrow'] }}</p><h2 class="mt-3 text-2xl font-black text-forest dark:text-meadow">{{ $point['title'] }}</h2><p class="mt-3 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $point['body'] }}</p></article>@endforeach</div></section>
    <section class="bg-linen px-4 py-14 dark:bg-[#163319] sm:px-8 lg:py-16"><div class="utility-section mx-auto grid max-w-7xl gap-6 lg:grid-cols-[0.8fr_1.2fr]"><div><p class="section-kicker">{{ __('home.contact.eyebrow') }}</p><h2 class="mt-3 text-3xl font-black text-forest dark:text-meadow">{{ __('home.contact.title') }}</h2><p class="mt-4 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.footer.line') }}</p></div><div class="grid gap-4 sm:grid-cols-2"><div class="rounded-[1.25rem] bg-linen p-5 dark:bg-white/5"><p class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">{{ __('home.contact.company') }}</p><p class="mt-2 text-sm font-semibold text-cocoa dark:text-cream">{{ __('home.contact.address') }}</p></div><div class="rounded-[1.25rem] bg-linen p-5 dark:bg-white/5"><p class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">Contact</p><p class="mt-2 text-sm font-semibold text-cocoa dark:text-cream">{{ __('home.contact.phone') }}</p><p class="mt-1 text-sm text-cocoa/65 dark:text-cream/65">{{ __('home.contact.email') }}</p></div><div class="rounded-[1.25rem] bg-linen p-5 dark:bg-white/5 sm:col-span-2"><p class="text-xs font-black uppercase tracking-[0.18em] text-forest dark:text-meadow">Info</p><p class="mt-2 text-sm font-semibold text-cocoa dark:text-cream">{{ __('home.contact.vat') }}</p></div></div></div></section>
@endsection
