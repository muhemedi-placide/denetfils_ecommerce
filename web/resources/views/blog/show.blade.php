@extends('layouts.shop')

@section('title', $post['title'] . ' | Denetfils')
@section('description', $post['description'])

@section('content')
    <article>
        <section class="soft-grid px-4 py-10 dark:bg-ink sm:px-8 lg:py-18">
            <div class="mx-auto max-w-5xl">
                <nav class="mobile-scrollbarless flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm font-semibold text-cocoa/60 dark:text-cream/60">
                    <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-leaf">{{ __('home.nav.home') }}</a>
                    <span>/</span>
                    <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="transition hover:text-leaf">{{ __('home.nav.blog') }}</a>
                    <span>/</span>
                    <span class="text-leaf">{{ $post['category'] }}</span>
                </nav>

                <div class="mt-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-mint px-3 py-1 text-xs font-extrabold uppercase tracking-wide text-leaf dark:bg-white/10 dark:text-meadow">{{ $post['category'] }}</span>
                        <time class="rounded-full border border-leaf/10 bg-white px-3 py-1 text-xs font-bold text-cocoa/60 dark:border-white/10 dark:bg-white/5 dark:text-cream/60">{{ $post['date'] }}</time>
                        <span class="rounded-full border border-leaf/10 bg-white px-3 py-1 text-xs font-bold text-cocoa/60 dark:border-white/10 dark:bg-white/5 dark:text-cream/60">{{ $post['read_time'] }}</span>
                    </div>

                    <h1 class="mt-5 max-w-4xl text-3xl font-extrabold leading-tight text-cocoa dark:text-cream sm:text-5xl">
                        {{ $post['title'] }}
                    </h1>
                    <p class="mt-5 max-w-3xl text-base leading-8 text-cocoa/70 dark:text-cream/70">
                        {{ $post['description'] }}
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-white px-4 pb-12 dark:bg-ink sm:px-8 lg:pb-16">
            <div class="mx-auto max-w-5xl">
                <img class="h-[280px] w-full rounded-[1.5rem] object-cover shadow-sm sm:h-[430px]" src="{{ $post['image'] }}" alt="{{ $post['title'] }}" fetchpriority="high">

                <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_280px] lg:items-start">
                    <div class="rounded-[1.5rem] border border-leaf/10 bg-linen p-5 dark:border-white/10 dark:bg-white/5 sm:p-8">
                        <div class="space-y-6 text-base leading-8 text-cocoa/75 dark:text-cream/75">
                            @foreach ($post['content'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>

                        <div class="mt-8 flex flex-col gap-3 border-t border-leaf/10 pt-6 dark:border-white/10 sm:flex-row">
                            <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-primary w-full sm:w-auto">{{ __('home.hero.primary_cta') }}</a>
                            <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="btn-secondary w-full sm:w-auto">{{ $locale === 'fr' ? 'Retour au blog' : 'Back to blog' }}</a>
                        </div>
                    </div>

                    <aside class="lg:sticky lg:top-36">
                        <div class="rounded-[1.5rem] border border-leaf/10 bg-linen p-5 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">DEN & FILS</p>
                            <h2 class="mt-3 text-xl font-extrabold text-cocoa dark:text-cream">{{ $locale === 'fr' ? 'À retenir' : 'Key takeaway' }}</h2>
                            <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ $post['description'] }}</p>
                            <button type="button" x-on:click="openCart()" class="btn-secondary mt-5 w-full">{{ __('home.cart.title') }}</button>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        @if (! empty($relatedPosts))
            <section class="bg-linen px-4 py-12 dark:bg-[#172414] sm:px-8 lg:py-14">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.blog.eyebrow') }}</p>
                            <h2 class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">{{ $locale === 'fr' ? 'À lire aussi' : 'Read also' }}</h2>
                        </div>
                        <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="btn-secondary w-full sm:w-fit">{{ __('home.nav.blog') }}</a>
                    </div>

                    <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
                        @foreach ($relatedPosts as $related)
                            <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $related['slug']]) }}" class="group min-w-[260px] overflow-hidden rounded-[1.25rem] border border-leaf/10 bg-white transition hover:shadow-xl dark:border-white/10 dark:bg-white/5 lg:min-w-0">
                                <img class="h-40 w-full object-cover" src="{{ $related['image'] }}" alt="{{ $related['title'] }}" loading="lazy">
                                <div class="p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ $related['category'] }}</p>
                                    <h3 class="mt-2 line-clamp-2 text-base font-extrabold text-cocoa transition group-hover:text-leaf dark:text-cream">{{ $related['title'] }}</h3>
                                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $related['description'] }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>
@endsection
