<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            [
                'slug' => 'epicerie-fine',
                'name' => ['fr' => 'Epicerie fine', 'en' => 'Fine groceries'],
                'sort_order' => 1,
            ],
            [
                'slug' => 'produits-frais',
                'name' => ['fr' => 'Produits frais', 'en' => 'Fresh products'],
                'sort_order' => 2,
            ],
            [
                'slug' => 'boissons-naturelles',
                'name' => ['fr' => 'Boissons naturelles', 'en' => 'Natural drinks'],
                'sort_order' => 3,
            ],
        ])->mapWithKeys(function (array $category) {
            return [
                $category['slug'] => Category::updateOrCreate(
                    ['slug' => $category['slug']],
                    [
                        'name' => $category['name'],
                        'sort_order' => $category['sort_order'],
                        'is_active' => true,
                    ],
                ),
            ];
        });

        foreach ($this->products() as $productData) {
            $productData = array_replace_recursive($this->defaultProductExperience(), $productData);
            $images = $productData['images'];
            $variants = $productData['variants'];
            $categorySlug = $productData['category_slug'];
            unset($productData['category_slug'], $productData['images'], $productData['variants']);

            $product = Product::updateOrCreate(
                ['slug' => $productData['slug']],
                [
                    ...$productData,
                    'category_id' => $categories[$categorySlug]->id,
                    'is_active' => true,
                ],
            );

            $product->images()->delete();
            $product->variants()->delete();

            foreach ($images as $index => $image) {
                $product->images()->create([
                    ...$image,
                    'width' => $image['width'] ?? 1200,
                    'height' => $image['height'] ?? 900,
                    'dominant_color' => $image['dominant_color'] ?? '#f4efe7',
                    'sort_order' => $index + 1,
                ]);
            }

            foreach ($variants as $variant) {
                $product->variants()->create([
                    ...$variant,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function products(): array
    {
        return [
            [
                'category_slug' => 'epicerie-fine',
                'name' => ['fr' => 'Miel de montagne', 'en' => 'Mountain honey'],
                'slug' => 'miel-de-montagne',
                'description' => [
                    'fr' => 'Un miel floral et dense, prepare pour les petits-dejeuners, les desserts et les coffrets cadeaux.',
                    'en' => 'A dense floral honey prepared for breakfasts, desserts and gift boxes.',
                ],
                'origin' => ['fr' => 'Origine France', 'en' => 'French origin'],
                'sku' => 'DEN-MIEL-250',
                'price_cents' => 890,
                'currency' => 'EUR',
                'weight_grams' => 250,
                'stock_quantity' => 35,
                'images' => [[
                    'url' => 'https://images.unsplash.com/photo-1587049352851-8d4e89133924?auto=format&fit=crop&w=1200&q=80',
                    'alt_text' => ['fr' => 'Pot de miel artisanal.', 'en' => 'Jar of artisanal honey.'],
                ]],
                'variants' => [
                    ['name' => ['fr' => 'Pot 250 g', 'en' => '250 g jar'], 'sku' => 'DEN-MIEL-250-A', 'price_adjustment_cents' => 0, 'stock_quantity' => 35],
                    ['name' => ['fr' => 'Pot 500 g', 'en' => '500 g jar'], 'sku' => 'DEN-MIEL-500-A', 'price_adjustment_cents' => 650, 'stock_quantity' => 20],
                ],
            ],
            [
                'category_slug' => 'epicerie-fine',
                'name' => ['fr' => 'Epices maison', 'en' => 'House spices'],
                'slug' => 'epices-maison',
                'description' => [
                    'fr' => 'Un melange aromatique equilibre pour relever legumes, viandes et plats mijotes.',
                    'en' => 'A balanced aromatic blend for vegetables, meats and slow-cooked dishes.',
                ],
                'origin' => ['fr' => 'Recette '.config('shop.name'), 'en' => config('shop.name').' recipe'],
                'sku' => 'DEN-EPICES-120',
                'price_cents' => 1250,
                'currency' => 'EUR',
                'weight_grams' => 120,
                'stock_quantity' => 48,
                'images' => [[
                    'url' => 'https://images.unsplash.com/photo-1532336414038-cf19250c5757?auto=format&fit=crop&w=1200&q=80',
                    'alt_text' => ['fr' => 'Epices colorees en vrac.', 'en' => 'Colorful loose spices.'],
                ]],
                'variants' => [
                    ['name' => ['fr' => 'Doux', 'en' => 'Mild'], 'sku' => 'DEN-EPICES-DOUX', 'price_adjustment_cents' => 0, 'stock_quantity' => 24],
                    ['name' => ['fr' => 'Piquant', 'en' => 'Spicy'], 'sku' => 'DEN-EPICES-PIQ', 'price_adjustment_cents' => 100, 'stock_quantity' => 24],
                ],
            ],
            [
                'category_slug' => 'epicerie-fine',
                'name' => ['fr' => 'Huile d olive vierge', 'en' => 'Virgin olive oil'],
                'slug' => 'huile-olive-vierge',
                'description' => [
                    'fr' => 'Une huile souple et fruitée pour assaisonnements, marinades et cuisine quotidienne.',
                    'en' => 'A smooth fruity oil for dressings, marinades and everyday cooking.',
                ],
                'origin' => ['fr' => 'Origine Espagne', 'en' => 'Spanish origin'],
                'sku' => 'DEN-HUILE-500',
                'price_cents' => 1490,
                'currency' => 'EUR',
                'weight_grams' => 500,
                'stock_quantity' => 30,
                'images' => [[
                    'url' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=1200&q=80',
                    'alt_text' => ['fr' => 'Bouteille d huile d olive.', 'en' => 'Bottle of olive oil.'],
                ]],
                'variants' => [
                    ['name' => ['fr' => 'Bouteille 500 ml', 'en' => '500 ml bottle'], 'sku' => 'DEN-HUILE-500-A', 'price_adjustment_cents' => 0, 'stock_quantity' => 30],
                ],
            ],
            [
                'category_slug' => 'produits-frais',
                'name' => ['fr' => 'Assortiment bio', 'en' => 'Organic assortment'],
                'slug' => 'assortiment-bio',
                'description' => [
                    'fr' => 'Une selection de produits frais et secs pour composer un panier decouverte complet.',
                    'en' => 'A selection of fresh and dry products for a complete discovery basket.',
                ],
                'origin' => ['fr' => 'Origine Europe', 'en' => 'European origin'],
                'sku' => 'DEN-BIO-BOX',
                'price_cents' => 2990,
                'currency' => 'EUR',
                'weight_grams' => 1800,
                'stock_quantity' => 18,
                'images' => [[
                    'url' => 'https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=1200&q=80',
                    'alt_text' => ['fr' => 'Assortiment de legumes frais.', 'en' => 'Assorted fresh vegetables.'],
                ]],
                'variants' => [
                    ['name' => ['fr' => 'Panier standard', 'en' => 'Standard basket'], 'sku' => 'DEN-BIO-STD', 'price_adjustment_cents' => 0, 'stock_quantity' => 18],
                    ['name' => ['fr' => 'Panier familial', 'en' => 'Family basket'], 'sku' => 'DEN-BIO-FAM', 'price_adjustment_cents' => 1600, 'stock_quantity' => 10],
                ],
            ],
            [
                'category_slug' => 'produits-frais',
                'name' => ['fr' => 'Panier premium', 'en' => 'Premium basket'],
                'slug' => 'panier-premium',
                'description' => [
                    'fr' => 'Un format cadeau avec produits soignes, emballage propre et preparation prioritaire.',
                    'en' => 'A gift format with curated products, clean packaging and priority preparation.',
                ],
                'origin' => ['fr' => 'Origine Europe', 'en' => 'European origin'],
                'sku' => 'DEN-PREMIUM-BOX',
                'price_cents' => 4900,
                'currency' => 'EUR',
                'weight_grams' => 2500,
                'stock_quantity' => 12,
                'images' => [[
                    'url' => 'https://images.unsplash.com/photo-1606787366850-de6330128bfc?auto=format&fit=crop&w=1200&q=80',
                    'alt_text' => ['fr' => 'Panier de produits alimentaires premium.', 'en' => 'Basket of premium food products.'],
                ]],
                'variants' => [
                    ['name' => ['fr' => 'Classique', 'en' => 'Classic'], 'sku' => 'DEN-PREMIUM-CLS', 'price_adjustment_cents' => 0, 'stock_quantity' => 12],
                    ['name' => ['fr' => 'Avec carte cadeau', 'en' => 'With gift card'], 'sku' => 'DEN-PREMIUM-GFT', 'price_adjustment_cents' => 500, 'stock_quantity' => 12],
                ],
            ],
            [
                'category_slug' => 'produits-frais',
                'name' => ['fr' => 'Fromage affine', 'en' => 'Aged cheese'],
                'slug' => 'fromage-affine',
                'description' => [
                    'fr' => 'Un fromage de caractere pour plateaux, degustations et paniers gourmands.',
                    'en' => 'A characterful cheese for boards, tastings and gourmet baskets.',
                ],
                'origin' => ['fr' => 'Origine France', 'en' => 'French origin'],
                'sku' => 'DEN-FROMAGE-300',
                'price_cents' => 1690,
                'currency' => 'EUR',
                'weight_grams' => 300,
                'stock_quantity' => 16,
                'images' => [[
                    'url' => 'https://images.unsplash.com/photo-1452195100486-9cc805987862?auto=format&fit=crop&w=1200&q=80',
                    'alt_text' => ['fr' => 'Plateau de fromages affines.', 'en' => 'Board of aged cheeses.'],
                ]],
                'variants' => [
                    ['name' => ['fr' => 'Piece 300 g', 'en' => '300 g piece'], 'sku' => 'DEN-FROMAGE-300-A', 'price_adjustment_cents' => 0, 'stock_quantity' => 16],
                ],
            ],
            [
                'category_slug' => 'boissons-naturelles',
                'name' => ['fr' => 'Jus pomme gingembre', 'en' => 'Apple ginger juice'],
                'slug' => 'jus-pomme-gingembre',
                'description' => [
                    'fr' => 'Une boisson naturelle, vive et peu sucree, adaptee aux pauses et coffrets.',
                    'en' => 'A natural, bright and lightly sweet drink for breaks and boxes.',
                ],
                'origin' => ['fr' => 'Origine Belgique', 'en' => 'Belgian origin'],
                'sku' => 'DEN-JUS-PG-750',
                'price_cents' => 690,
                'currency' => 'EUR',
                'weight_grams' => 750,
                'stock_quantity' => 42,
                'images' => [[
                    'url' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=1200&q=80',
                    'alt_text' => ['fr' => 'Verres de jus naturel.', 'en' => 'Glasses of natural juice.'],
                ]],
                'variants' => [
                    ['name' => ['fr' => 'Bouteille 750 ml', 'en' => '750 ml bottle'], 'sku' => 'DEN-JUS-PG-750-A', 'price_adjustment_cents' => 0, 'stock_quantity' => 42],
                ],
            ],
            [
                'category_slug' => 'boissons-naturelles',
                'name' => ['fr' => 'Infusion hibiscus', 'en' => 'Hibiscus infusion'],
                'slug' => 'infusion-hibiscus',
                'description' => [
                    'fr' => 'Une infusion rouge intense, parfumee et simple a servir chaude ou froide.',
                    'en' => 'A deep red, fragrant infusion that is easy to serve hot or cold.',
                ],
                'origin' => ['fr' => 'Origine Senegal', 'en' => 'Senegalese origin'],
                'sku' => 'DEN-INF-HIB-80',
                'price_cents' => 790,
                'currency' => 'EUR',
                'weight_grams' => 80,
                'stock_quantity' => 40,
                'images' => [[
                    'url' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=1200&q=80',
                    'alt_text' => ['fr' => 'Tasse d infusion rouge.', 'en' => 'Cup of red infusion.'],
                ]],
                'variants' => [
                    ['name' => ['fr' => 'Sachet 80 g', 'en' => '80 g pouch'], 'sku' => 'DEN-INF-HIB-80-A', 'price_adjustment_cents' => 0, 'stock_quantity' => 40],
                ],
            ],
        ];
    }

    private function defaultProductExperience(): array
    {
        return [
            'short_description' => [
                'fr' => 'Produit selectionne par '.config('shop.name').' pour une experience alimentaire fiable, claire et adaptee au marche europeen.',
                'en' => 'A '.config('shop.name').' selected product for a clear, reliable food shopping experience tailored for Europe.',
            ],
            'highlights' => [
                'fr' => ['Selection premium', 'Prix en EUR', 'Preparation soignee', 'Fiche produit detaillee'],
                'en' => ['Premium selection', 'EUR pricing', 'Careful preparation', 'Detailed product page'],
            ],
            'badges' => [
                'fr' => ['Best seller', 'Stock suivi', 'Livraison UE'],
                'en' => ['Best seller', 'Tracked stock', 'EU delivery'],
            ],
            'tags' => [
                'fr' => ['epicerie', 'premium', \Illuminate\Support\Str::slug(config('shop.name'))],
                'en' => ['grocery', 'premium', \Illuminate\Support\Str::slug(config('shop.name'))],
            ],
            'ingredients' => [
                'fr' => 'Voir les informations detaillees sur l emballage fournisseur.',
                'en' => 'See detailed information on the supplier packaging.',
            ],
            'allergens' => [
                'fr' => ['Peut contenir des traces de fruits a coque ou sesame selon atelier.'],
                'en' => ['May contain traces of nuts or sesame depending on the workshop.'],
            ],
            'nutrition_facts' => [
                'serving_basis' => 'per_100g',
                'energy_kcal' => null,
                'fat_g' => null,
                'saturated_fat_g' => null,
                'carbohydrates_g' => null,
                'sugars_g' => null,
                'protein_g' => null,
                'salt_g' => null,
            ],
            'certifications' => [
                'fr' => ['Selection fournisseur verifiee'],
                'en' => ['Verified supplier selection'],
            ],
            'storage_instructions' => [
                'fr' => 'Conserver dans un endroit frais, sec et a l abri de la lumiere.',
                'en' => 'Store in a cool, dry place away from light.',
            ],
            'usage_instructions' => [
                'fr' => 'A consommer selon les indications du produit et la date de durabilite minimale.',
                'en' => 'Use according to product instructions and best-before date.',
            ],
            'shipping_profile' => [
                'dispatch_time' => [
                    'fr' => 'Preparation sous 24 a 48 h ouvrables.',
                    'en' => 'Prepared within 24 to 48 business hours.',
                ],
                'delivery_zone' => [
                    'fr' => 'France et pays europeens supportes.',
                    'en' => 'France and supported European countries.',
                ],
                'cold_chain' => false,
                'free_shipping_threshold_cents' => 6900,
            ],
            'return_policy' => [
                'fr' => 'Produit alimentaire non repris apres ouverture, sauf defaut ou erreur de preparation.',
                'en' => 'Food products cannot be returned after opening, except for defects or preparation errors.',
            ],
            'guarantee' => [
                'fr' => 'Controle qualite avant expedition et support client base en Europe.',
                'en' => 'Quality check before dispatch and Europe-based customer support.',
            ],
            'max_order_quantity' => 12,
            'rating_average' => 4.7,
            'rating_count' => 38,
            'sales_count' => 240,
            'seo_title' => [
                'fr' => 'Produit alimentaire premium '.config('shop.name'),
                'en' => config('shop.name').' premium food product',
            ],
            'seo_description' => [
                'fr' => 'Achetez ce produit alimentaire '.config('shop.name').' avec prix en EUR, details complets, livraison europeenne et fiche SEO structuree.',
                'en' => 'Shop this '.config('shop.name').' food product with EUR pricing, complete details, European delivery and structured SEO data.',
            ],
            'seo_keywords' => [
                'fr' => [\Illuminate\Support\Str::slug(config('shop.name')), 'boutique alimentaire', 'produit premium'],
                'en' => [\Illuminate\Support\Str::slug(config('shop.name')), 'food shop', 'premium product'],
            ],
            'published_at' => now(),
        ];
    }
}
