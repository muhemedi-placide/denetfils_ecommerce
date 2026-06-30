@php
    $productName = $product['name'] ?? config('shop.name');
    $reviews = $currentLocale === 'fr'
        ? [
            ['name' => 'Client vérifié', 'title' => 'Très bon goût', 'body' => 'Produit bien présenté, facile à utiliser et fidèle à l’esprit de la cuisine haïtienne.', 'rating' => 5, 'date' => '2026-01-12'],
            ['name' => 'Client '.config('shop.name'), 'title' => 'Pratique en cuisine', 'body' => 'Le format est pratique pour assaisonner rapidement sans perdre le goût recherché.', 'rating' => 5, 'date' => '2026-01-08'],
            ['name' => 'Avis client', 'title' => 'Bonne découverte', 'body' => 'Je recommande pour les personnes qui veulent retrouver des saveurs authentiques à la maison.', 'rating' => 4, 'date' => '2025-12-28'],
        ]
        : [
            ['name' => 'Verified customer', 'title' => 'Very good taste', 'body' => 'Well presented, easy to use and faithful to the spirit of Haitian cooking.', 'rating' => 5, 'date' => '2026-01-12'],
            ['name' => config('shop.name').' customer', 'title' => 'Practical in the kitchen', 'body' => 'The format is practical for quick seasoning while keeping the expected taste.', 'rating' => 5, 'date' => '2026-01-08'],
            ['name' => 'Customer review', 'title' => 'Good discovery', 'body' => 'Recommended for anyone who wants authentic flavors at home.', 'rating' => 4, 'date' => '2025-12-28'],
        ];
    $average = (float) data_get($product, 'commerce.rating.average', collect($reviews)->avg('rating'));
    $reviewCount = (int) data_get($product, 'commerce.rating.count', count($reviews));
@endphp

<section class="bg-cream px-4 py-14 dark:bg-ink sm:px-8 lg:py-20" aria-labelledby="product-reviews-title">
    <div class="mx-auto max-w-7xl">
        <div class="grid gap-6 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
            <aside class="utility-section bg-linen dark:bg-white/5 lg:sticky lg:top-32">
                <p class="section-kicker">{{ $currentLocale === 'fr' ? 'Avis clients' : 'Customer reviews' }}</p>
                <h2 id="product-reviews-title" class="mt-3 text-3xl font-black text-forest dark:text-meadow">
                    {{ $currentLocale === 'fr' ? 'Avis sur ce produit' : 'Reviews for this product' }}
                </h2>
                <div class="mt-6 flex items-end gap-3">
                    <span class="brand-display text-6xl text-forest dark:text-meadow">{{ number_format($average, 1, ',', ' ') }}</span>
                    <div class="pb-2">
                        <div class="text-sm text-sunshine" aria-label="{{ number_format($average, 1) }}/5">★★★★★</div>
                        <p class="mt-1 text-xs font-black uppercase tracking-wide text-cocoa/55 dark:text-cream/55">
                            {{ $reviewCount }} {{ $currentLocale === 'fr' ? 'avis' : 'reviews' }}
                        </p>
                    </div>
                </div>
                <p class="mt-5 text-sm leading-7 text-cocoa/65 dark:text-cream/65">
                    {{ $currentLocale === 'fr' ? 'Cette zone est prête pour recevoir les avis réels liés aux commandes et au compte client.' : 'This area is ready to receive real reviews linked to orders and customer accounts.' }}
                </p>
            </aside>

            <div class="space-y-4">
                @foreach ($reviews as $review)
                    <article class="utility-section bg-white dark:bg-white/5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="text-sm text-sunshine" aria-label="{{ $review['rating'] }}/5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= $review['rating'] ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                                <h3 class="mt-2 text-xl font-black text-forest dark:text-meadow">{{ $review['title'] }}</h3>
                            </div>
                            <time datetime="{{ $review['date'] }}" class="text-xs font-black uppercase tracking-wide text-cocoa/50 dark:text-cream/50">
                                {{ \Carbon\Carbon::parse($review['date'])->format('d/m/Y') }}
                            </time>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-cocoa/70 dark:text-cream/70">“{{ $review['body'] }}”</p>
                        <p class="mt-4 text-sm font-black text-forest dark:text-meadow">{{ $review['name'] }}</p>
                    </article>
                @endforeach

                <div class="rounded-[1.25rem] border border-dashed border-leaf/25 bg-white p-6 dark:border-white/15 dark:bg-white/5">
                    <h3 class="text-xl font-black text-forest dark:text-meadow">
                        {{ $currentLocale === 'fr' ? 'Laisser un avis sur ' . $productName : 'Leave a review for ' . $productName }}
                    </h3>
                    <p class="mt-2 text-sm leading-7 text-cocoa/65 dark:text-cream/65">
                        {{ $currentLocale === 'fr' ? 'Emplacement préparé pour le futur formulaire : note, commentaire, nom client et validation après commande.' : 'Prepared space for the future form: rating, comment, customer name and validation after purchase.' }}
                    </p>
                    <button type="button" class="btn-secondary mt-5 w-full cursor-not-allowed opacity-70 sm:w-auto" disabled>
                        {{ $currentLocale === 'fr' ? 'Formulaire bientôt disponible' : 'Form coming soon' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
