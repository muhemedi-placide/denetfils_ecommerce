@php
    $buttonLabel = $buttonLabel ?? ($locale === 'fr' ? 'Utiliser ce point' : 'Use this point');
    $panelTitle = $panelTitle ?? ($locale === 'fr' ? 'Sélectionnez votre point de retrait' : 'Select your pickup point');
    $isModal = $isModal ?? false;
@endphp

<div class="overflow-hidden rounded-[1rem] border border-cocoa/10 bg-white dark:border-white/10 dark:bg-white/5">
    <div class="border-b border-cocoa/10 bg-neutral-100 px-4 py-2 text-center text-sm font-black text-cocoa/70 dark:border-white/10 dark:bg-white/10 dark:text-cream/80">
        {{ $panelTitle }}
    </div>

    <div class="grid gap-0 lg:grid-cols-[370px_1fr]">
        <div class="border-r border-cocoa/10 bg-white dark:border-white/10 dark:bg-ink/80">
            <div class="flex items-center gap-3 border-b border-cocoa/10 p-4 dark:border-white/10">
                <div class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-[#a01455] text-2xl font-black text-white">r</div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ $locale === 'fr' ? 'Recherche relais' : 'Pickup search' }}</p>
                    <input class="mt-2 w-full rounded-lg border border-cocoa/10 bg-white px-3 py-2 text-sm font-semibold outline-none transition focus:border-[#a01455] focus:ring-4 focus:ring-[#a01455]/10 dark:border-white/10 dark:bg-white/5 dark:text-cream" type="search" wire:model.live.debounce.350ms="pickupQuery" placeholder="{{ $locale === 'fr' ? 'Ville, CP ou relais' : 'City, ZIP or pickup' }}">
                </div>
            </div>

            <div class="max-h-[390px] overflow-y-auto p-3">
                @forelse ($pickupPoints as $point)
                    <button type="button" class="w-full border-l-4 py-2 pl-3 pr-2 text-left transition {{ $selectedPickupPoint === $point['code'] ? 'border-[#a01455] bg-[#a01455]/5' : 'border-[#f4b0ca] hover:bg-neutral-50 dark:hover:bg-white/5' }}" wire:click="selectPickupPoint('{{ $point['code'] }}')" wire:key="{{ $isModal ? 'modal' : 'inline' }}-pickup-row-{{ $point['code'] }}">
                        <span class="block text-sm font-black uppercase text-[#a01455]">{{ $loop->iteration }} - {{ $point['name'] }}</span>
                        <span class="mt-1 block text-sm leading-5 text-cocoa/75 dark:text-cream/75">{{ $point['address'] }}</span>
                        <span class="mt-1 block text-xs font-semibold text-cocoa/55 dark:text-cream/55">{{ $point['hours'] }} · {{ $point['distance'] }}</span>
                    </button>
                @empty
                    <div class="rounded-xl bg-linen px-4 py-3 text-sm text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                        {{ $locale === 'fr' ? 'Aucun point trouvé.' : 'No pickup point found.' }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="relative min-h-[430px] overflow-hidden bg-[linear-gradient(135deg,#eef5df_0%,#f9f1d2_48%,#e7efd7_100%)] dark:bg-[linear-gradient(135deg,#172414_0%,#121a10_100%)]">
            <div class="absolute inset-0 opacity-70">
                <div class="absolute left-[5%] top-[22%] h-[2px] w-[96%] rotate-[-8deg] bg-white/80"></div>
                <div class="absolute left-[-10%] top-[55%] h-[8px] w-[120%] rotate-[9deg] bg-[#f5c87c]/80"></div>
                <div class="absolute left-[15%] top-[10%] h-[2px] w-[95%] rotate-[29deg] bg-white/80"></div>
                <div class="absolute left-[18%] top-0 h-full w-[2px] rotate-[18deg] bg-white/70"></div>
                <div class="absolute left-[55%] top-0 h-full w-[2px] rotate-[-28deg] bg-white/70"></div>
                <div class="absolute bottom-[18%] left-0 h-[42px] w-full bg-[#aed38a]/45"></div>
            </div>

            <div class="absolute left-4 top-4 z-20 overflow-hidden rounded-lg bg-white shadow">
                <button type="button" class="grid h-9 w-9 place-items-center border-b text-xl font-black text-cocoa">+</button>
                <button type="button" class="grid h-9 w-9 place-items-center text-xl font-black text-cocoa">−</button>
            </div>

            @foreach ($pickupPoints as $point)
                <button
                    type="button"
                    class="absolute z-10 grid -translate-x-1/2 -translate-y-full place-items-center rounded-t-full rounded-bl-full border-2 border-white shadow-lg transition hover:scale-110 {{ $selectedPickupPoint === $point['code'] ? 'h-12 w-12 bg-[#a01455] text-lg text-white ring-8 ring-[#a01455]/20' : 'h-10 w-10 bg-[#a01455] text-sm text-white' }}"
                    style="left: {{ (int) ($point['map_x'] ?? 50) }}%; top: {{ (int) ($point['map_y'] ?? 50) }}%;"
                    wire:click="selectPickupPoint('{{ $point['code'] }}')"
                    wire:key="{{ $isModal ? 'modal' : 'inline' }}-pickup-marker-{{ $point['code'] }}"
                    title="{{ $point['name'] }}"
                >
                    {{ $loop->iteration }}
                </button>
            @endforeach

            <div class="absolute bottom-0 left-0 right-0 z-20 flex items-center justify-between bg-white/90 px-3 py-1 text-[11px] font-semibold text-cocoa/70 dark:bg-ink/90 dark:text-cream/70">
                <span>MapLibre | © Boxtal © OpenMapTiles © OpenStreetMap</span>
                <span>{{ number_format((float) ($pickupMapCenter['latitude'] ?? 0), 4) }}, {{ number_format((float) ($pickupMapCenter['longitude'] ?? 0), 4) }}</span>
            </div>
        </div>
    </div>

    @if (! $isModal)
        <div class="flex justify-end border-t border-cocoa/10 bg-white px-4 py-4 dark:border-white/10 dark:bg-white/5">
            <button type="button" class="rounded-full bg-[#45ad4d] px-8 py-3 text-sm font-black text-white shadow-[0_14px_32px_rgba(69,173,77,.25)]" wire:click="selectPickupPoint('{{ $selectedPickupPoint }}')">
                {{ $buttonLabel }}
            </button>
        </div>
    @endif
</div>
