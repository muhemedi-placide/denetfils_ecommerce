@php
    $testimonials = $currentLocale === 'fr'
        ? [
            ['name' => 'Marie C.', 'location' => 'Île-de-France', 'title' => 'Goût authentique', 'body' => 'Les produits rappellent vraiment la cuisine familiale. La pâte de djon djon donne une belle couleur et un parfum profond au riz.', 'rating' => 5],
            ['name' => 'Jean R.', 'location' => 'Lyon', 'title' => 'Commande simple', 'body' => 'Le parcours est clair, les produits sont bien expliqués et on comprend rapidement quoi ajouter au panier.', 'rating' => 5],
            ['name' => 'Nathalie B.', 'location' => 'Belgique', 'title' => 'Saveurs bien équilibrées', 'body' => 'Le piment Edenne relève les plats sans masquer le goût. C’est pratique pour cuisiner vite avec une vraie identité créole.', 'rating' => 4],
        ]
        : [
            ['name' => 'Marie C.', 'location' => 'Paris region', 'title' => 'Authentic taste', 'body' => 'The products really bring back family cooking. Djon djon paste gives rice a deep color and a rich aroma.', 'rating' => 5],
            ['name' => 'Jean R.', 'location' => 'Lyon', 'title' => 'Simple ordering', 'body' => 'The journey is clear, products are well explained and it is easy to decide what to add to the cart.', 'rating' => 5],
            ['name' => 'Nathalie B.', 'location' => 'Belgium', 'title' => 'Balanced flavors', 'body' => 'Edenne pepper adds heat without hiding the taste. It is practical for quick cooking with a real Creole identity.', 'rating' => 4],
        ];
@endphp

<section class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-20" aria-labelledby="testimonials-title">
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="section-kicker">{{ $currentLocale === 'fr' ? 'Témoignages' : 'Testimonials' }}</p>
                <h2 id="testimonials-title" class="section-title mt-3">
                    {{ $currentLocale === 'fr' ? 'Ce que les clients apprécient' : 'What customers appreciate' }}
                </h2>
            </div>
            <p class="section-copy">
                {{ $currentLocale === 'fr' ? 'Des retours courts pour rassurer, réduire l’hésitation et aider le client à commander plus rapidement.' : 'Short feedback blocks to build trust, reduce hesitation and help customers order faster.' }}
            </p>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            @foreach ($testimonials as $testimonial)
                <article class="utility-section relative bg-white pt-8 dark:bg-white/5">
                    <span class="absolute -top-4 left-6 grid h-10 w-10 place-items-center rounded-full bg-coral text-lg font-black text-cream">”</span>
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex text-sm text-sunshine" aria-label="{{ $testimonial['rating'] }}/5">
                            @for ($i = 1; $i <= 5; $i++)
                                <span>{{ $i <= $testimonial['rating'] ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                        <span class="rounded-full bg-linen px-3 py-1 text-[11px] font-black uppercase tracking-wide text-cocoa/60 dark:bg-white/10 dark:text-cream/60">
                            {{ $testimonial['location'] }}
                        </span>
                    </div>
                    <h3 class="mt-4 text-xl font-black text-forest dark:text-meadow">{{ $testimonial['title'] }}</h3>
                    <p class="mt-3 text-sm leading-7 text-cocoa/70 dark:text-cream/70">“{{ $testimonial['body'] }}”</p>
                    <p class="mt-5 text-sm font-black text-forest dark:text-meadow">{{ $testimonial['name'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
