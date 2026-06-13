@php
    $labels = $currentLocale === 'fr'
        ? [
            'shop' => 'Boutique',
            'cart' => 'Panier',
            'checkout' => 'Livraison',
            'success' => 'Validation',
            'helper' => 'Commande en 3 actions simples',
        ]
        : [
            'shop' => 'Shop',
            'cart' => 'Cart',
            'checkout' => 'Delivery',
            'success' => 'Confirmation',
            'helper' => 'Order in 3 simple actions',
        ];

    $steps = [
        ['key' => 'shop', 'icon' => '🛍️', 'label' => $labels['shop'], 'url' => route('home.localized', ['locale' => $currentLocale]) . '#products'],
        ['key' => 'cart', 'icon' => '🧺', 'label' => $labels['cart'], 'url' => route('cart.show', ['locale' => $currentLocale])],
        ['key' => 'checkout', 'icon' => '🚚', 'label' => $labels['checkout'], 'url' => route('checkout.show', ['locale' => $currentLocale])],
        ['key' => 'success', 'icon' => '🏅', 'label' => $labels['success'], 'url' => '#'],
    ];

    $activeIndex = collect($steps)->search(fn (array $step) => $step['key'] === $currentStep);
    $activeIndex = $activeIndex === false ? 0 : $activeIndex;
@endphp

<div class="mx-auto mb-5 max-w-4xl rounded-[1.5rem] border border-leaf/10 bg-white/90 p-3 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5 sm:p-4" aria-label="{{ $labels['helper'] }}">
    <p class="mb-3 text-center text-[11px] font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow sm:text-xs">
        {{ $labels['helper'] }}
    </p>
    <div class="relative">
        <div class="absolute left-8 right-8 top-5 hidden h-1 rounded-full bg-leaf/10 dark:bg-white/10 sm:block"></div>
        <div class="absolute left-8 top-5 hidden h-1 rounded-full bg-leaf transition-all duration-500 dark:bg-meadow sm:block" style="width: calc((100% - 4rem) * {{ $activeIndex }} / 3);"></div>

        <ol class="grid grid-cols-4 gap-2">
            @foreach ($steps as $index => $step)
                @php
                    $isDone = $index < $activeIndex;
                    $isActive = $index === $activeIndex;
                    $isMedal = $step['key'] === 'success';
                @endphp
                <li class="relative z-10 text-center">
                    @if ($step['key'] !== 'success')
                        <a href="{{ $step['url'] }}" class="group block">
                    @else
                        <span class="block">
                    @endif
                        <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full border text-lg transition sm:h-11 sm:w-11 {{ $isActive ? 'border-leaf bg-leaf text-white shadow-lg dark:border-meadow dark:bg-meadow dark:text-ink' : ($isDone ? 'border-leaf bg-mint text-leaf dark:border-meadow dark:bg-white/10 dark:text-meadow' : 'border-leaf/10 bg-linen text-cocoa/55 dark:border-white/10 dark:bg-white/5 dark:text-cream/55') }} {{ $isMedal && $isActive ? 'celebration-medal' : '' }}">
                            {{ $isDone ? '✓' : $step['icon'] }}
                        </span>
                        <span class="mt-2 block truncate text-[11px] font-extrabold uppercase tracking-wide {{ $isActive ? 'text-leaf dark:text-meadow' : 'text-cocoa/55 dark:text-cream/55' }} sm:text-xs">
                            {{ $step['label'] }}
                        </span>
                    @if ($step['key'] !== 'success')
                        </a>
                    @else
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</div>
