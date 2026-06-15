<div class="mobile-scrollbarless flex gap-3 overflow-x-auto pb-1 sm:grid sm:grid-cols-2 sm:overflow-visible lg:grid-cols-4">
    @foreach ($categories as $category)
        <a
            href="#products"
            wire:click.prevent="selectCategory('{{ $category['slug'] }}')"
            class="group min-w-[190px] rounded-[1.15rem] border border-leaf/10 bg-white p-4 text-left transition hover:border-leaf/30 hover:shadow-lg dark:border-white/10 dark:bg-white/5 sm:min-w-0 sm:p-5"
        >
            <div class="flex items-center justify-between gap-4">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-mint text-base font-extrabold text-leaf transition group-hover:bg-terracotta group-hover:text-white">{{ str($category['name'])->substr(0, 1) }}</span>
                <span class="text-xs font-bold text-cocoa/50 dark:text-cream/50">{{ __('home.categories.count', ['count' => $category['products_count']]) }}</span>
            </div>
            <h3 class="mt-4 text-base font-extrabold text-cocoa dark:text-cream">{{ $category['name'] }}</h3>
        </a>
    @endforeach
</div>
