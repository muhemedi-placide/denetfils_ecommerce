@php
    $testimonials = $currentLocale === 'fr'
        ? [
            [
                'name' => 'Marie C.',
                'location' => 'Île-de-France',
                'title' => 'Goût authentique',
                'body' => 'Les produits rappellent vraiment la cuisine familiale. La pâte de djon djon donne une belle couleur et un parfum profond au riz.',
                'rating' => 5,
            ],
            [
                'name' => 'Jean R.',
                'location' => 'Lyon',
                'title' => 'Commande simple',
                'body' => 'Le parcours est clair, les produits sont bien expliqués et on comprend rapidement quoi ajouter au panier.',
                'rating' => 5,
            ],
            [
                'name' => 'Nathalie B.',
                'location' => 'Belgique',
                'title' => 'Saveurs bien équilibrées',
                'body' => 'Le piment Edenne relève les plats sans masquer le goût. C’est pratique pour cuisiner vite avec une vraie identité créole.',
                'rating' => 4,
            ],
        ]
        : [
            [
                'name' => 'Marie C.',
                'location' => 'Paris region',
                'title' => 'Authentic taste',
                'body' => 'The products really bring back family cooking. Djon djon paste gives rice a deep color and a rich aroma.',
                'rating' => 5,
            ],
            [
                'name' => 'Jean R.',
                'location' => 'Lyon',
                'title' => 'Simple ordering',
                'body' => 'The journey is clear, products are well explained and it is easy to decide what to add to the cart.',
                'rating' => 5,
            ],
            [
                'name' => 'Nathalie B.',
                'location' => 'Belgium',
                'title' => 'Balanced flavors',
                'body' => 'Edenne pepper adds heat without hiding the taste. It is practical for quick cooking with a real Creole identity.',
                'rating' => 4,
            ],
        ];
@endphp

<section class="bg-white px-4 py-12 dark:bg-ink sm:px-8 lg:py-16" aria-labelledby="testimonials-title">
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">
                    {{ $currentLocale === 'fr' ? 'Témoignages' : 'Testimonials' }}
                </p>
                <h2 id="testimonials-title" class="mt-2 text-2xl font-extrabold text-cocoa dark:text-cream sm:text-3xl">
                    {{ $currentLocale === 'fr' ? 'Ce que les clients apprécient chez DEN & FILS.' : 'What customers appreciate about DEN & FILS.' }}
                </h2>
            </div>
            <p class="max-w-xl text-sm leading-7 text-cocoa/65 dark:text-cream/65">
                {{ $currentLocale === 'fr' ? 'Des retours courts pour rassurer, réduire l’hésitation et aider le client à commander plus rapidement.' : 'Short feedback blocks to build trust, reduce hesitation and help customers order faster.' }}
            </p>
        </div>

        <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
            @foreach ($testimonials as $testimonial)
                <article class="min-w-[280px] rounded-[1.35rem] border border-leaf/10 bg-linen p-5 shadow-sm dark:border-white/10 dark:bg-white/5 lg:min-w-0 sm:p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex text-sm text-leaf dark:text-meadow" aria-label="{{ $testimonial['rating'] }}/5">
                            @for ($i = 1; $i <= 5; $i++)
                                <span>{{ $i <= $testimonial['rating'] ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-cocoa/60 dark:bg-white/10 dark:text-cream/60">
                            {{ $testimonial['location'] }}
                        </span>
                    </div>
                    <h3 class="mt-4 text-lg font-extrabold text-cocoa dark:text-cream">{{ $testimonial['title'] }}</h3>
                    <p class="mt-3 text-sm leading-7 text-cocoa/70 dark:text-cream/70">“{{ $testimonial['body'] }}”</p>
                    <p class="mt-5 text-sm font-extrabold text-leaf dark:text-meadow">{{ $testimonial['name'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
