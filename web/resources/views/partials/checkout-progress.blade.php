@php
    $labels = $currentLocale === 'fr'
        ? ['shop' => 'Boutique', 'cart' => 'Panier', 'checkout' => 'Livraison', 'success' => 'Validation']
        : ['shop' => 'Shop', 'cart' => 'Cart', 'checkout' => 'Delivery', 'success' => 'Confirmation'];

    $steps = [
        ['key' => 'shop', 'label' => $labels['shop'], 'url' => route('home.localized', ['locale' => $currentLocale]) . '#products'],
        ['key' => 'cart', 'label' => $labels['cart'], 'url' => route('cart.show', ['locale' => $currentLocale])],
        ['key' => 'checkout', 'label' => $labels['checkout'], 'url' => route('checkout.show', ['locale' => $currentLocale])],
        ['key' => 'success', 'label' => $labels['success'], 'url' => '#'],
    ];

    $activeIndex = collect($steps)->search(fn (array $step) => $step['key'] === $currentStep);
    $activeIndex = $activeIndex === false ? 0 : $activeIndex;
@endphp

<nav class="mx-auto mb-3 w-full max-w-[340px] lg:w-[20vw] lg:max-w-none" aria-label="Checkout progress">
    <ol class="grid h-5 grid-cols-4 items-center rounded-full border border-leaf/10 bg-white/85 px-2 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
        @foreach ($steps as $index => $step)
            @php
                $isDone = $index < $activeIndex;
                $isActive = $index === $activeIndex;
                $isLocked = $step['key'] === 'success' && ! $isActive;
            @endphp
            <li class="min-w-0">
                @if (! $isLocked)
                    <a href="{{ $step['url'] }}" class="group flex items-center justify-center gap-1.5" @if ($isActive) aria-current="step" @endif wire:navigate>
                @else
                    <span class="flex items-center justify-center gap-1.5">
                @endif
                    <span class="h-2 w-2 rounded-full {{ $isActive ? 'bg-leaf ring-4 ring-leaf/15 dark:bg-meadow dark:ring-meadow/20' : ($isDone ? 'bg-leaf/80 dark:bg-meadow/80' : 'bg-cocoa/20 dark:bg-cream/20') }}"></span>
                    <span class="hidden truncate text-[9px] font-black uppercase tracking-wide {{ $isActive ? 'text-leaf dark:text-meadow' : 'text-cocoa/45 dark:text-cream/45' }} xl:block">
                        {{ $step['label'] }}
                    </span>
                @if (! $isLocked)
                    </a>
                @else
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
