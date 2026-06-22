@extends('layouts.shop')

@section('title', ($locale === 'fr' ? 'Contact' : 'Contact') . ' | Marche Peyi')
@section('description', $locale === 'fr' ? 'Contactez Marche Peyi pour une commande speciale, une question produit ou une collaboration.' : 'Contact Marche Peyi for a special order, product question or collaboration.')
@section('canonical', route('pages.contact', ['locale' => $locale]))

@section('content')
    <section class="relative overflow-hidden bg-cream px-4 py-20 dark:bg-ink sm:px-8 lg:py-28">
        <div class="absolute inset-0 opacity-35" style="background-image: radial-gradient(#124c20 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="relative mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.35em] text-coral">{{ $locale === 'fr' ? 'Contact' : 'Contact' }}</p>
                <h1 class="mt-5 max-w-xl text-6xl font-black leading-[0.98] tracking-tight text-forest dark:text-meadow sm:text-7xl lg:text-8xl">
                    {{ $locale === 'fr' ? 'Parlons cuisine.' : 'Let’s talk food.' }}
                </h1>
                <p class="mt-7 max-w-xl text-base font-semibold leading-8 text-forest/70 dark:text-cream/75">
                    {{ $locale === 'fr' ? 'Une commande speciale, une question sur un produit, une idee de collaboration ? Notre equipe repond sous 24h ouvrees.' : 'A special order, a product question, a collaboration idea? Our team replies within 24 business hours.' }}
                </p>

                <div class="mt-10 space-y-6">
                    <div class="flex items-center gap-5">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-sunshine text-forest">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="6" width="16" height="12" rx="2"></rect><path d="m4 8 8 6 8-6"></path></svg>
                        </span>
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-forest/40 dark:text-cream/45">Email</p>
                            <a href="mailto:bonjour@marche-peyi.com" class="mt-1 block text-xl font-black text-forest dark:text-meadow">bonjour@marche-peyi.com</a>
                        </div>
                    </div>
                    <div class="flex items-center gap-5">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-flamingo text-cream">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.11 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.72c.13.96.35 1.9.66 2.8a2 2 0 0 1-.45 2.11L8.1 9.9a16 16 0 0 0 6 6l1.27-1.2a2 2 0 0 1 2.11-.45c.9.31 1.84.53 2.8.66A2 2 0 0 1 22 16.92Z"></path></svg>
                        </span>
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-forest/40 dark:text-cream/45">{{ $locale === 'fr' ? 'Telephone' : 'Phone' }}</p>
                            <a href="tel:+33123456789" class="mt-1 block text-xl font-black text-forest dark:text-meadow">+33 1 23 45 67 89</a>
                        </div>
                    </div>
                    <div class="flex items-center gap-5">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-[#ff9817] text-forest">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-5.2 7-11a7 7 0 0 0-14 0c0 5.8 7 11 7 11Z"></path><circle cx="12" cy="10" r="2"></circle></svg>
                        </span>
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-forest/40 dark:text-cream/45">{{ $locale === 'fr' ? 'Entrepot' : 'Warehouse' }}</p>
                            <p class="mt-1 text-xl font-black text-forest dark:text-meadow">12 rue des Tropiques, 93500 Pantin</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border-[3px] border-forest bg-white p-7 shadow-[12px_12px_0_#124c20] dark:bg-white/5 sm:p-10 lg:ml-auto lg:w-[min(100%,620px)]">
                <h2 class="text-4xl font-black tracking-tight text-forest dark:text-meadow">{{ $locale === 'fr' ? 'Ecrivez-nous' : 'Write to us' }}</h2>
                <form class="mt-8 space-y-5" method="POST" action="#">
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.22em] text-forest/60 dark:text-cream/55" for="contact-name">{{ $locale === 'fr' ? 'Nom complet' : 'Full name' }}</label>
                        <input id="contact-name" name="name" type="text" class="mt-3 min-h-[56px] w-full rounded-full border-2 border-forest/15 bg-cream px-5 text-forest outline-none transition focus:border-forest dark:bg-ink dark:text-cream" autocomplete="name">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.22em] text-forest/60 dark:text-cream/55" for="contact-email">Email</label>
                        <input id="contact-email" name="email" type="email" class="mt-3 min-h-[56px] w-full rounded-full border-2 border-forest/15 bg-cream px-5 text-forest outline-none transition focus:border-forest dark:bg-ink dark:text-cream" autocomplete="email">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.22em] text-forest/60 dark:text-cream/55" for="contact-message">Message</label>
                        <textarea id="contact-message" name="message" rows="6" class="mt-3 w-full rounded-[1.25rem] border-2 border-forest/15 bg-cream px-5 py-4 text-forest outline-none transition focus:border-forest dark:bg-ink dark:text-cream"></textarea>
                    </div>
                    <button type="button" class="min-h-[58px] w-full rounded-full bg-forest px-6 py-4 text-sm font-black uppercase tracking-wide text-cream transition hover:bg-leaf">
                        {{ $locale === 'fr' ? 'Envoyer le message' : 'Send message' }}
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
