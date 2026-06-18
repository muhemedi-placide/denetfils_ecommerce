@php
    $cards = [
        ['class' => 'bg-coral text-cream', 'icon' => '🌶️'],
        ['class' => 'bg-flamingo text-cream', 'icon' => '🧂'],
        ['class' => 'bg-mango text-forest', 'icon' => '🥭'],
        ['class' => 'bg-forest text-cream', 'icon' => '🍌'],
        ['class' => 'bg-caribbean text-cream', 'icon' => '🫘'],
        ['class' => 'bg-sunshine text-forest', 'icon' => '🎁'],
    ];
@endphp

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($categories as $category)
        @php($style = $cards[$loop->index % count($cards)])
        <a
            href="#products"
            wire:click.prevent="selectCategory('{{ $category['slug'] }}')"
            class="group relative min-h-[190px] overflow-hidden rounded-[1.5rem] p-6 text-left shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-tropical {{ $style['class'] }}"
        >
            <span class="text-3xl" aria-hidden="true">{{ $style['icon'] }}</span>
            <div class="mt-7">
                <h3 class="text-2xl font-black leading-tight">{{ $category['name'] }}</h3>
                <p class="mt-3 max-w-sm text-sm font-semibold leading-6 opacity-80">
                    {{ $locale === 'fr' ? 'Découvrez ce rayon et ajoutez vos essentiels au panier.' : 'Explore this aisle and add your essentials to the cart.' }}
                </p>
                <span class="mt-5 inline-flex items-center gap-2 text-sm font-black uppercase tracking-wide">
                    {{ $locale === 'fr' ? 'Découvrir' : 'Explore' }} <span class="transition group-hover:translate-x-1">→</span>
                </span>
            </div>
            <span class="absolute right-5 top-5 rounded-full bg-cream/20 px-3 py-1 text-xs font-black uppercase tracking-wide backdrop-blur">
                {{ __('home.categories.count', ['count' => $category['products_count']]) }}
            </span>
        </a>
    @endforeach
</div>
