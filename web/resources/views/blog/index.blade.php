@extends('layouts.shop')

@section('title', __('home.blog.title') . ' | ' . config('shop.name'))
@section('description', __('home.blog.body'))
@section('canonical', route('blog.index', ['locale' => $locale]))
@section('og_type', 'website')
@section('og_image', $posts[0]['image'] ?? '')

@push('structured-data')
    @php
        $blogSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            'name' => __('home.blog.title'),
            'description' => __('home.blog.body'),
            'url' => route('blog.index', ['locale' => $locale]),
            'inLanguage' => $locale,
            'blogPost' => collect($posts)->map(fn (array $post) => [
                '@type' => 'BlogPosting',
                'headline' => $post['title'],
                'description' => $post['description'],
                'image' => $post['image'],
                'url' => route('blog.show', ['locale' => $locale, 'slug' => $post['slug']]),
            ])->values()->all(),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($blogSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
    <section class="soft-grid px-4 py-12 dark:bg-ink sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:items-end">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.blog.eyebrow') }}</p>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight text-cocoa dark:text-cream sm:text-5xl">
                        {{ __('home.blog.title') }}
                    </h1>
                    <p class="mt-5 text-sm leading-7 text-cocoa/70 dark:text-cream/70 sm:text-base sm:leading-8">
                        {{ __('home.blog.body') }}
                    </p>
                </div>

                @if (! empty($posts))
                    <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $posts[0]['slug']]) }}" class="group relative overflow-hidden rounded-[1.6rem] bg-ink text-white shadow-sm" wire:navigate.hover>
                        <img class="h-80 w-full object-cover transition duration-500 group-hover:scale-[1.03]" src="{{ $posts[0]['image'] }}" alt="{{ $posts[0]['title'] }}" fetchpriority="high" decoding="async">
                        <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/65 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-5 sm:p-7">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-meadow px-3 py-1 text-xs font-extrabold uppercase tracking-wide text-ink">{{ $posts[0]['category'] }}</span>
                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white backdrop-blur">{{ $posts[0]['date'] }}</span>
                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white backdrop-blur">{{ $posts[0]['read_time'] }}</span>
                            </div>
                            <h2 class="mt-4 max-w-2xl text-2xl font-extrabold leading-tight sm:text-3xl">{{ $posts[0]['title'] }}</h2>
                            <p class="mt-3 line-clamp-2 max-w-2xl text-sm leading-6 text-white/75">{{ $posts[0]['description'] }}</p>
                        </div>
                    </a>
                @endif
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (array_slice($posts, 1) as $post)
                    <article class="group overflow-hidden rounded-[1.35rem] border border-leaf/10 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-white/5">
                        <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post['slug']]) }}" class="block overflow-hidden" wire:navigate.hover>
                            <img class="h-48 w-full object-cover transition duration-500 group-hover:scale-[1.04]" src="{{ $post['image'] }}" alt="{{ $post['title'] }}" loading="lazy" decoding="async">
                        </a>
                        <div class="p-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-mint px-3 py-1 text-xs font-extrabold uppercase tracking-wide text-leaf dark:bg-white/10 dark:text-meadow">{{ $post['category'] }}</span>
                                <time class="text-xs font-bold text-cocoa/50 dark:text-cream/50">{{ $post['date'] }}</time>
                            </div>
                            <h2 class="mt-4 text-xl font-extrabold leading-snug text-cocoa transition group-hover:text-leaf dark:text-cream">
                                <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post['slug']]) }}" wire:navigate.hover>{{ $post['title'] }}</a>
                            </h2>
                            <p class="mt-3 line-clamp-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ $post['description'] }}</p>
                            <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post['slug']]) }}" class="mt-5 inline-flex min-h-[42px] items-center justify-center rounded-full border border-leaf/15 px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-leaf transition hover:bg-mint dark:border-white/10 dark:text-meadow dark:hover:bg-white/10" wire:navigate.hover>
                                {{ $locale === 'fr' ? 'Lire l’article' : 'Read article' }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white px-4 py-12 dark:bg-[#111111] sm:px-8 lg:py-14">
        <div class="mx-auto grid max-w-7xl gap-6 rounded-[1.5rem] border border-leaf/10 bg-linen p-5 dark:border-white/10 dark:bg-white/5 sm:p-8 lg:grid-cols-[0.85fr_1.15fr]">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.products.eyebrow') }}</p>
                <h2 class="mt-3 text-2xl font-extrabold text-cocoa dark:text-cream">{{ __('home.products.title') }}</h2>
                <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.products.body') }}</p>
            </div>
            <div class="flex flex-col justify-center gap-3 sm:flex-row lg:justify-end">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary w-full sm:w-auto" wire:navigate.hover>{{ __('home.hero.primary_cta') }}</a>
                <livewire:shop.cart-open-button button-class="btn-secondary w-full sm:w-auto" />
            </div>
        </div>
    </section>
@endsection
