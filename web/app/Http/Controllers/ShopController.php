<?php

namespace App\Http\Controllers;

use App\Services\ShopApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ShopController extends Controller
{
    public function home(Request $request, ShopApiClient $api, ?string $locale = null): View
    {
        $locale = $this->setLocale($locale);
        $filters = $this->filters($request);
        $categories = $api->categories($locale);
        $products = $api->products($locale, $filters);

        return view('welcome', [
            'locale' => $locale,
            'categories' => $categories['data'],
            'products' => $products['data'],
            'blogPosts' => array_slice($this->blogPosts($locale), 0, 3),
            'apiError' => $products['error'],
            'filters' => $filters,
            'activeMenu' => 'home',
        ]);
    }

    public function about(string $locale): View
    {
        $locale = $this->setLocale($locale);

        return view('pages.about', [
            'locale' => $locale,
            'activeMenu' => 'about',
        ]);
    }

    public function blog(string $locale): View
    {
        $locale = $this->setLocale($locale);

        return view('blog.index', [
            'locale' => $locale,
            'posts' => $this->blogPosts($locale),
            'activeMenu' => 'blog',
        ]);
    }

    public function blogShow(string $locale, string $slug): View
    {
        $locale = $this->setLocale($locale);
        $posts = $this->blogPosts($locale);
        $post = collect($posts)->firstWhere('slug', $slug);

        abort_if(! $post, 404);

        $relatedPosts = collect($posts)
            ->reject(fn (array $item) => $item['slug'] === $slug)
            ->take(3)
            ->values()
            ->all();

        return view('blog.show', [
            'locale' => $locale,
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'activeMenu' => 'blog',
        ]);
    }

    public function delivery(string $locale): View
    {
        return $this->utilityPage($locale, 'delivery');
    }

    public function legalNotice(string $locale): View
    {
        return $this->utilityPage($locale, 'legal');
    }

    public function terms(string $locale): View
    {
        return $this->utilityPage($locale, 'terms');
    }

    public function securePayment(string $locale): View
    {
        return $this->utilityPage($locale, 'payment');
    }

    public function show(ShopApiClient $api, string $locale, string $slug): View
    {
        $locale = $this->setLocale($locale);
        $product = $api->product($slug, $locale);

        abort_if(! $product, 404);

        $relatedProducts = $this->relatedProducts($api, $locale, $product);

        return view('products.show', [
            'locale' => $locale,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'activeMenu' => 'products',
        ]);
    }

    private function utilityPage(string $locale, string $page): View
    {
        $locale = $this->setLocale($locale);

        abort_unless(array_key_exists($page, trans('home.utility_pages')), 404);

        return view('pages.utility', [
            'locale' => $locale,
            'page' => $page,
            'content' => trans('home.utility_pages.' . $page),
            'activeMenu' => $page === 'legal' || $page === 'terms' ? 'about' : 'home',
        ]);
    }

    private function setLocale(?string $locale): string
    {
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : config('app.locale', 'fr');

        app()->setLocale($locale);

        return $locale;
    }

    private function filters(Request $request): array
    {
        $sort = $request->query('sort', 'default');

        return [
            'category' => (string) $request->query('category', ''),
            'q' => trim((string) $request->query('q', '')),
            'sort' => in_array($sort, ['default', 'price_asc', 'price_desc', 'latest'], true) ? $sort : 'default',
        ];
    }

    private function relatedProducts(ShopApiClient $api, string $locale, array $product): array
    {
        $categorySlug = (string) data_get($product, 'category.slug', '');

        $response = $api->products($locale, [
            'category' => $categorySlug,
            'sort' => 'latest',
        ]);

        return collect($response['data'])
            ->reject(fn (array $item) => (int) ($item['id'] ?? 0) === (int) ($product['id'] ?? 0))
            ->take(3)
            ->values()
            ->all();
    }

    private function blogPosts(string $locale): array
    {
        $posts = [
            'fr' => [
                [
                    'slug' => 'epice-que-votre-cuisine-attendait',
                    'date' => '16/12/2025',
                    'category' => 'Nouveauté',
                    'read_time' => '3 min',
                    'title' => "L'Épice que votre cuisine attendait",
                    'description' => 'Une épice complète inspirée de l’héritage haïtien, pensée pour simplifier l’assaisonnement du quotidien sans perdre l’âme des recettes familiales.',
                    'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=1400&q=85',
                    'content' => [
                        'Dans une cuisine familiale, l’assaisonnement n’est jamais un détail. Il donne le ton du plat, réveille les parfums et rappelle souvent une mémoire précise : celle d’un repas partagé, d’une recette transmise et d’un geste répété avec soin.',
                        'Avec son épice complète, DEN & FILS met en avant une aide culinaire pratique pour gagner du temps tout en gardant une base aromatique généreuse. Le produit s’adresse aux clients qui veulent cuisiner vite, mais avec un goût qui reste profond et identifiable.',
                        'Cette logique respecte l’ADN de la maison : rendre les saveurs haïtiennes accessibles, mieux présentées et faciles à intégrer dans la cuisine du quotidien, en France comme en Europe.',
                    ],
                ],
                [
                    'slug' => 'histoire-pate-djondjon',
                    'date' => '20/11/2025',
                    'category' => 'Histoire',
                    'read_time' => '4 min',
                    'title' => 'Le goût suffit-il pour faire vivre une légende ? Histoire de la pâte de Djondjon',
                    'description' => 'Un récit autour d’Edenne, du djon djon et de la transmission familiale qui a permis de moderniser un classique de la cuisine haïtienne.',
                    'image' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=1400&q=85',
                    'content' => [
                        'Le djon djon occupe une place particulière dans la cuisine haïtienne. Il ne se limite pas à sa couleur sombre ni à son parfum : il évoque les repas de fête, les préparations patientes et le respect d’une tradition culinaire forte.',
                        'La pâte de djondjon SELAKAY répond à une demande simple : préserver ce goût tout en facilitant la préparation. Elle permet au client de retrouver l’esprit du riz djon djon sans passer par toutes les contraintes d’une préparation longue.',
                        'Derrière cette évolution, il y a une histoire familiale. DEN & FILS transforme une mémoire culinaire en produit moderne, tout en gardant un lien clair avec les recettes transmises par les générations précédentes.',
                    ],
                ],
                [
                    'slug' => 'pourquoi-choisir-denetfils',
                    'date' => '19/11/2025',
                    'category' => 'Marque',
                    'read_time' => '3 min',
                    'title' => 'Pourquoi tant de personnes choisissent Denetfils ?',
                    'description' => 'Authenticité, histoire, goût et confiance : les raisons qui donnent à DEN & FILS une place différente dans l’épicerie haïtienne.',
                    'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1400&q=85',
                    'content' => [
                        'Choisir une marque alimentaire, c’est choisir plus qu’un produit. Le client cherche un goût, mais aussi une origine claire, une histoire crédible et une régularité dans la qualité.',
                        'DEN & FILS met en avant des produits liés à la cuisine haïtienne et caribéenne, avec une présentation pensée pour inspirer confiance. L’objectif est de permettre aux clients de retrouver des saveurs familières dans une boutique moderne et lisible.',
                        'La différence se trouve dans l’équilibre entre tradition et exigence actuelle : des recettes inspirées d’un héritage familial, une distribution structurée et une communication claire autour des produits.',
                    ],
                ],
                [
                    'slug' => 'poisson-epice-piment-edenne-djondjon',
                    'date' => '17/11/2025',
                    'category' => 'Recette',
                    'read_time' => '5 min',
                    'title' => 'Poisson épicé au piment Edenne & pâte de djon djon',
                    'description' => 'Une idée recette pour associer la chaleur du piment Edenne et le parfum du djon djon dans un plat généreux.',
                    'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=1400&q=85',
                    'content' => [
                        'Le poisson se prête parfaitement aux assaisonnements intenses. Avec le piment Edenne, il gagne une chaleur maîtrisée, tandis que la pâte de djon djon apporte une profondeur aromatique caractéristique.',
                        'L’idée est de mariner le poisson avec une base d’épices, une petite touche de piment selon votre tolérance, puis d’ajouter la pâte de djon djon dans la sauce pour renforcer le goût.',
                        'Servez avec du riz, des bananes plantains ou un accompagnement simple. Le plat reste accessible, mais il garde une identité forte et un vrai caractère caribéen.',
                    ],
                ],
                [
                    'slug' => 'recette-riz-djondjon',
                    'date' => '05/11/2025',
                    'category' => 'Recette',
                    'read_time' => '4 min',
                    'title' => 'Recette de riz Djondjon',
                    'description' => 'Un classique haïtien à préparer plus simplement avec la pâte de djon djon SELAKAY.',
                    'image' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?auto=format&fit=crop&w=1400&q=85',
                    'content' => [
                        'Le riz djon djon est l’un des plats emblématiques de la cuisine haïtienne. Sa couleur, son parfum et sa profondeur en font un plat souvent associé aux grandes occasions.',
                        'Avec la pâte de djon djon, la préparation devient plus rapide. Il suffit d’intégrer la pâte dans la base de cuisson du riz pour obtenir une couleur soutenue et un goût caractéristique.',
                        'Cette version permet de préserver la tradition tout en répondant aux habitudes modernes : cuisiner plus simplement, sans sacrifier l’authenticité du résultat final.',
                    ],
                ],
            ],
            'en' => [
                [
                    'slug' => 'spice-your-kitchen-was-waiting-for',
                    'date' => '16/12/2025',
                    'category' => 'New',
                    'read_time' => '3 min',
                    'title' => 'The Spice Your Kitchen Was Waiting For',
                    'description' => 'A complete spice inspired by Haitian heritage, created to simplify everyday seasoning while keeping the spirit of family recipes.',
                    'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=1400&q=85',
                    'content' => [
                        'In a family kitchen, seasoning is never a detail. It gives direction to the dish, wakes up aromas and often brings back the memory of a shared meal or a recipe passed down with care.',
                        'With its complete spice, DEN & FILS offers a practical cooking aid for customers who want to save time while keeping a generous aromatic base.',
                        'This approach follows the brand’s DNA: making Haitian flavors accessible, better presented and easier to use in everyday cooking across France and Europe.',
                    ],
                ],
                [
                    'slug' => 'story-of-djon-djon-paste',
                    'date' => '20/11/2025',
                    'category' => 'Story',
                    'read_time' => '4 min',
                    'title' => 'Can taste alone keep a legend alive? The story of djon djon paste',
                    'description' => 'A story around Edenne, djon djon and the family transmission behind a modern version of a Haitian classic.',
                    'image' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=1400&q=85',
                    'content' => [
                        'Djon djon has a special place in Haitian cuisine. It is not only about its dark color or perfume; it evokes festive meals, patient preparation and respect for a strong culinary tradition.',
                        'SELAKAY djon djon paste answers a simple need: preserving the taste while making preparation easier for customers.',
                        'Behind this evolution is a family story. DEN & FILS turns culinary memory into a modern product while keeping a clear link with recipes passed down through generations.',
                    ],
                ],
                [
                    'slug' => 'why-choose-denetfils',
                    'date' => '19/11/2025',
                    'category' => 'Brand',
                    'read_time' => '3 min',
                    'title' => 'Why do so many people choose Denetfils?',
                    'description' => 'Authenticity, history, taste and trust: why DEN & FILS stands apart in Haitian grocery products.',
                    'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1400&q=85',
                    'content' => [
                        'Choosing a food brand means choosing more than a product. Customers look for taste, but also a clear origin, a credible story and consistent quality.',
                        'DEN & FILS highlights products connected to Haitian and Caribbean cooking, with a presentation designed to inspire trust.',
                        'The difference lies in the balance between tradition and modern standards: family-inspired recipes, structured distribution and clear product communication.',
                    ],
                ],
                [
                    'slug' => 'spicy-fish-edenne-pepper-djon-djon',
                    'date' => '17/11/2025',
                    'category' => 'Recipe',
                    'read_time' => '5 min',
                    'title' => 'Spicy fish with Edenne pepper & djon djon paste',
                    'description' => 'A recipe idea combining Edenne pepper heat and djon djon depth in a generous dish.',
                    'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=1400&q=85',
                    'content' => [
                        'Fish works beautifully with intense seasoning. Edenne pepper brings controlled heat while djon djon paste adds a distinctive aromatic depth.',
                        'The idea is to marinate the fish with a spice base, add a small touch of pepper according to taste, then use djon djon paste in the sauce to strengthen the flavor.',
                        'Serve with rice, plantain or a simple side. The dish remains accessible while keeping a strong Caribbean identity.',
                    ],
                ],
                [
                    'slug' => 'djon-djon-rice-recipe',
                    'date' => '05/11/2025',
                    'category' => 'Recipe',
                    'read_time' => '4 min',
                    'title' => 'Djon djon rice recipe',
                    'description' => 'A Haitian classic made easier with SELAKAY djon djon paste.',
                    'image' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?auto=format&fit=crop&w=1400&q=85',
                    'content' => [
                        'Djon djon rice is one of the emblematic dishes of Haitian cuisine. Its color, perfume and depth make it a dish often associated with special occasions.',
                        'With djon djon paste, preparation becomes faster. Add the paste into the rice cooking base to achieve a deep color and characteristic taste.',
                        'This version preserves tradition while responding to modern habits: cooking more simply without sacrificing authenticity.',
                    ],
                ],
            ],
        ];

        return Arr::get($posts, $locale, $posts['fr']);
    }
}
