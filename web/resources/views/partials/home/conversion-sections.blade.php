@php
    $brandName = $brandName ?? config('app.name');
    $shopUrl = $shopUrl ?? route('shop.index', ['locale' => $locale]);
    $copy = $locale === 'fr' ? [
        'why_title' => 'Pourquoi acheter ici ?',
        'why_body' => 'Une bonne page d’accueil doit rassurer vite : produits lisibles, livraison expliquée, paiement sécurisé et action claire.',
        'delivery_title' => 'Livraison et suivi visibles avant la décision',
        'delivery_body' => 'La boutique met en avant les options de transport comme Chronopost, Mondial Relay, point relais, locker ou domicile selon les modules actifs.',
        'steps_title' => 'Commander en quatre étapes',
        'story_title' => 'Une boutique indépendante et professionnelle',
        'story_body' => $brandName.' garde sa propre identité avec une expérience moderne, mobile-first, claire et orientée conversion.',
        'faq_title' => 'Questions fréquentes',
        'shop_cta' => 'Découvrir la boutique',
        'delivery_cta' => 'Voir la livraison',
        'tracking_cta' => 'Suivre un colis',
        'contact_cta' => 'Nous contacter',
        'steps' => [
            ['title' => 'Choisir', 'body' => 'Explorer les rayons et ajouter les produits au panier.'],
            ['title' => 'Confirmer', 'body' => 'Vérifier les quantités, l’adresse et le pays.'],
            ['title' => 'Payer', 'body' => 'Finaliser avec un paiement sécurisé.'],
            ['title' => 'Suivre', 'body' => 'Recevoir les informations de livraison et de suivi.'],
        ],
        'faq' => [
            ['q' => 'Où livrez-vous ?', 'a' => 'Les pays et modes disponibles dépendent de l’adresse, du panier et des transporteurs actifs.'],
            ['q' => 'Puis-je suivre ma commande ?', 'a' => 'Oui, la page de suivi colis rassure le client après expédition.'],
            ['q' => 'Comment choisir vite ?', 'a' => 'Les rayons, produits populaires et CTA guident vers un premier panier.'],
            ['q' => 'Pourquoi créer un compte ?', 'a' => 'Le compte facilite les adresses, l’historique et les futures commandes.'],
        ],
    ] : [
        'why_title' => 'Why buy here?',
        'why_body' => 'A strong homepage must reassure quickly: readable products, explained delivery, secure payment and clear action.',
        'delivery_title' => 'Delivery and tracking visible before decision',
        'delivery_body' => 'The shop highlights transport options such as Chronopost, Mondial Relay, pickup point, locker or home delivery depending on active modules.',
        'steps_title' => 'Order in four steps',
        'story_title' => 'An independent and professional shop',
        'story_body' => $brandName.' keeps its own identity with a modern, mobile-first, clear and conversion-oriented experience.',
        'faq_title' => 'Frequently asked questions',
        'shop_cta' => 'Discover the shop',
        'delivery_cta' => 'View delivery',
        'tracking_cta' => 'Track a parcel',
        'contact_cta' => 'Contact us',
        'steps' => [
            ['title' => 'Choose', 'body' => 'Explore aisles and add products to cart.'],
            ['title' => 'Confirm', 'body' => 'Check quantities, address and country.'],
            ['title' => 'Pay', 'body' => 'Complete the purchase through secure payment.'],
            ['title' => 'Track', 'body' => 'Receive delivery and tracking information.'],
        ],
        'faq' => [
            ['q' => 'Where do you deliver?', 'a' => 'Available countries and modes depend on address, basket and active carriers.'],
            ['q' => 'Can I track my order?', 'a' => 'Yes, the tracking page reassures customers after shipment.'],
            ['q' => 'How can I choose quickly?', 'a' => 'Aisles, popular products and CTAs guide visitors toward a first basket.'],
            ['q' => 'Why create an account?', 'a' => 'The account simplifies addresses, history and future orders.'],
        ],
    ];
    $reasons = $locale === 'fr' ? [
        ['icon' => 'store', 'title' => 'Catalogue lisible', 'body' => 'Rayons, prix, stock et boutons d’achat sont visibles rapidement.'],
        ['icon' => 'credit-card', 'title' => 'Paiement sécurisé', 'body' => 'Le client comprend comment payer avant le checkout.'],
        ['icon' => 'truck', 'title' => 'Livraison visible', 'body' => 'Les options de transport sont expliquées avant la commande.'],
        ['icon' => 'heart', 'title' => 'Image de confiance', 'body' => 'Avis, FAQ et histoire renforcent la crédibilité.'],
    ] : [
        ['icon' => 'store', 'title' => 'Readable catalog', 'body' => 'Aisles, prices, stock and purchase buttons are easy to scan.'],
        ['icon' => 'credit-card', 'title' => 'Secure payment', 'body' => 'Customers understand how to pay before checkout.'],
        ['icon' => 'truck', 'title' => 'Visible delivery', 'body' => 'Transport options are explained before ordering.'],
        ['icon' => 'heart', 'title' => 'Trust image', 'body' => 'Reviews, FAQ and story strengthen credibility.'],
    ];
@endphp

<section class="py-12">
    <div class="mb-7 grid gap-4 lg:grid-cols-2 lg:items-end">
        <div>
            <p class="section-kicker">{{ $copy['why_title'] }}</p>
            <h2 class="section-title mt-3">{{ $copy['why_title'] }}</h2>
        </div>
        <p class="section-copy lg:ml-auto">{{ $copy['why_body'] }}</p>
    </div>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($reasons as $reason)
            <article class="market-card p-6">
                <span class="brand-mark mb-5"><x-icon :name="$reason['icon']" class="h-5 w-5" /></span>
                <h3 class="text-lg font-black text-cocoa dark:text-cream">{{ $reason['title'] }}</h3>
                <p class="mt-3 text-sm font-semibold leading-6 text-cocoa/70 dark:text-cream/70">{{ $reason['body'] }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="py-12">
    <h2 class="store-section-heading"><x-icon name="cart" class="store-accent h-8 w-8" /> {{ $copy['steps_title'] }}</h2>
    <div class="grid gap-5 md:grid-cols-4">
        @foreach ($copy['steps'] as $index => $step)
            <article class="premium-card p-6">
                <span class="text-4xl font-black text-leaf">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                <h3 class="mt-4 text-lg font-black text-cocoa dark:text-cream">{{ $step['title'] }}</h3>
                <p class="mt-3 text-sm font-semibold leading-6 text-cocoa/70 dark:text-cream/70">{{ $step['body'] }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="grid gap-8 rounded-3xl bg-cocoa p-6 text-white dark:bg-white/5 lg:grid-cols-2 lg:p-10">
    <div>
        <p class="text-xs font-black uppercase tracking-widest text-flamingo">{{ $copy['delivery_title'] }}</p>
        <h2 class="mt-3 text-3xl font-black lg:text-5xl">{{ $copy['delivery_title'] }}</h2>
        <p class="mt-4 text-sm font-semibold leading-7 text-white/75">{{ $copy['delivery_body'] }}</p>
        <div class="mt-7 flex flex-wrap gap-3">
            <a href="{{ route('pages.delivery', ['locale' => $locale]) }}" class="store-button" wire:navigate.hover><x-icon name="truck" class="h-4 w-4" /> {{ $copy['delivery_cta'] }}</a>
            <a href="{{ route('pages.tracking', ['locale' => $locale]) }}" class="store-button store-button-outline border-white text-white hover:border-leaf" wire:navigate.hover><x-icon name="location" class="h-4 w-4" /> {{ $copy['tracking_cta'] }}</a>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach (['Mondial Relay', 'Chronopost', 'Point relais', 'Domicile'] as $deliveryItem)
            <div class="rounded-3xl border border-white/10 bg-white/10 p-5">
                <x-icon name="truck" class="h-7 w-7 text-flamingo" />
                <strong class="mt-4 block">{{ $deliveryItem }}</strong>
            </div>
        @endforeach
    </div>
</section>

<section class="grid gap-8 py-12 lg:grid-cols-2 lg:items-center">
    <div class="overflow-hidden rounded-3xl shadow-tropical">
        <img src="{{ asset('assets/products/product-spices.jpg') }}" alt="{{ $brandName }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
    </div>
    <div>
        <p class="section-kicker">{{ $copy['story_title'] }}</p>
        <h2 class="section-title mt-3">{{ $copy['story_title'] }}</h2>
        <p class="section-copy mt-5">{{ $copy['story_body'] }}</p>
    </div>
</section>

<section class="rounded-3xl border border-leaf/10 bg-white p-6 shadow-sm dark:bg-white/5 lg:p-8">
    <h2 class="text-3xl font-black text-ink dark:text-cream lg:text-5xl">{{ $copy['faq_title'] }}</h2>
    <div class="mt-7 grid gap-4 md:grid-cols-2">
        @foreach ($copy['faq'] as $item)
            <article class="rounded-3xl border border-leaf/10 bg-linen p-5 dark:bg-white/5">
                <h3 class="font-black text-cocoa dark:text-cream">{{ $item['q'] }}</h3>
                <p class="mt-2 text-sm font-semibold leading-6 text-cocoa/70 dark:text-cream/70">{{ $item['a'] }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="my-12 rounded-3xl bg-mint p-8 text-center dark:bg-white/5">
    <h2 class="mx-auto max-w-3xl text-3xl font-black text-ink dark:text-cream lg:text-5xl">{{ $locale === 'fr' ? 'Prêt à remplir votre panier ?' : 'Ready to fill your basket?' }}</h2>
    <div class="mt-7 flex flex-wrap justify-center gap-3">
        <a href="{{ $shopUrl }}" class="store-button" wire:navigate.hover>{{ $copy['shop_cta'] }}</a>
        <a href="{{ route('pages.contact', ['locale' => $locale]) }}" class="store-button store-button-outline" wire:navigate.hover>{{ $copy['contact_cta'] }}</a>
    </div>
</section>
