<?php

namespace App\Services\Seo;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SeoPayloadBuilder
{
    public function site(string $locale): array
    {
        $url = $this->localizedUrl('/{locale}', $locale);

        return [
            'meta' => [
                'title' => config('shop.name').' - '.($locale === 'en'
                    ? 'Premium food shop'
                    : 'Boutique alimentaire premium'),
                'description' => $locale === 'en'
                    ? 'Discover curated grocery products, natural drinks and premium food boxes for Europe.'
                    : 'Decouvrez des produits alimentaires selectionnes, boissons naturelles et coffrets premium pour l Europe.',
                'robots' => 'index,follow',
            ],
            'canonical' => $url,
            'hreflang' => $this->hreflang('/{locale}'),
            'open_graph' => $this->openGraph('website', $url, config('shop.name'), null, null, $locale),
            'twitter_card' => $this->twitterCard(config('shop.name'), null, null),
            'json_ld' => [
                'organization' => $this->organizationSchema(),
                'website' => $this->websiteSchema($locale),
                'articles' => $this->articleSchemas($locale),
            ],
        ];
    }

    public function product(Product $product, string $locale): array
    {
        $product->loadMissing(['category', 'images']);

        $title = $product->localized('seo_title', $locale)
            ?: $product->localized('name', $locale) . ' | ' . config('seo.brand_name');
        $description = $product->localized('seo_description', $locale)
            ?: Str::limit((string) ($product->localized('short_description', $locale) ?: $product->localized('description', $locale)), 155, '');
        $url = $this->productUrl($product, $locale);
        $image = $product->images->first();
        $imageUrl = $image?->url;

        return [
            'meta' => [
                'title' => $title,
                'description' => $description,
                'keywords' => $this->localizedArray($product->seo_keywords, $locale),
                'robots' => $product->is_active ? 'index,follow' : 'noindex,nofollow',
            ],
            'canonical' => $url,
            'hreflang' => $this->hreflang('/{locale}/products/' . $product->slug),
            'open_graph' => $this->openGraph('product', $url, $title, $description, $imageUrl, $locale),
            'twitter_card' => $this->twitterCard($title, $description, $imageUrl),
            'json_ld' => [
                'organization' => $this->organizationSchema(),
                'website' => $this->websiteSchema($locale),
                'breadcrumb' => $this->breadcrumbSchema($product, $locale),
                'product' => $this->productSchema($product, $locale, $url),
            ],
        ];
    }

    public function robots(): string
    {
        $lines = ['User-agent: *'];

        foreach (config('seo.robots.allow', ['/']) as $path) {
            $lines[] = 'Allow: ' . $path;
        }

        foreach (config('seo.robots.disallow', []) as $path) {
            $lines[] = 'Disallow: ' . $path;
        }

        $lines[] = 'Sitemap: ' . $this->apiUrl('/sitemap.xml');

        return implode("\n", $lines) . "\n";
    }

    public function sitemap(): string
    {
        $urls = collect($this->staticSitemapUrls())
            ->merge($this->categorySitemapUrls())
            ->merge($this->productSitemapUrls())
            ->merge($this->articleSitemapUrls())
            ->values();

        return $this->sitemapXml($urls);
    }

    private function staticSitemapUrls(): array
    {
        $urls = [];

        foreach (config('seo.static_routes', []) as $route) {
            foreach ($this->locales() as $locale) {
                $urls[] = [
                    'loc' => $this->localizedUrl($route['path'], $locale),
                    'lastmod' => now()->toDateString(),
                    'changefreq' => $route['changefreq'],
                    'priority' => $route['priority'],
                    'alternates' => $this->hreflang($route['path']),
                ];
            }
        }

        return $urls;
    }

    private function categorySitemapUrls(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->flatMap(function (Category $category) {
                return collect($this->locales())->map(fn (string $locale) => [
                    'loc' => $this->localizedUrl('/{locale}?category=' . $category->slug, $locale),
                    'lastmod' => $category->updated_at?->toDateString() ?? now()->toDateString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                    'alternates' => $this->hreflang('/{locale}?category=' . $category->slug),
                ]);
            });
    }

    private function productSitemapUrls(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderBy('id')
            ->get()
            ->flatMap(function (Product $product) {
                $path = '/{locale}/products/' . $product->slug;

                return collect($this->locales())->map(fn (string $locale) => [
                    'loc' => $this->localizedUrl($path, $locale),
                    'lastmod' => ($product->updated_at ?? $product->created_at)?->toDateString() ?? now()->toDateString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.9',
                    'alternates' => $this->hreflang($path),
                ]);
            });
    }

    private function articleSitemapUrls(): array
    {
        $urls = [];

        foreach (config('seo.articles', []) as $article) {
            foreach ($this->locales() as $locale) {
                $path = '/{locale}/blog#' . $article['slug'];
                $urls[] = [
                    'loc' => $this->localizedUrl($path, $locale),
                    'lastmod' => $article['published_at'],
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                    'alternates' => $this->hreflang($path),
                ];
            }
        }

        return $urls;
    }

    private function sitemapXml(Collection $urls): string
    {
        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">',
        ];

        foreach ($urls as $url) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>' . $this->escape($url['loc']) . '</loc>';
            $xml[] = '    <lastmod>' . $this->escape($url['lastmod']) . '</lastmod>';
            $xml[] = '    <changefreq>' . $this->escape($url['changefreq']) . '</changefreq>';
            $xml[] = '    <priority>' . $this->escape($url['priority']) . '</priority>';

            foreach ($url['alternates'] as $alternate) {
                $xml[] = sprintf(
                    '    <xhtml:link rel="alternate" hreflang="%s" href="%s" />',
                    $this->escape($alternate['hreflang']),
                    $this->escape($alternate['url']),
                );
            }

            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return implode("\n", $xml);
    }

    private function organizationSchema(): array
    {
        $organization = config('seo.organization');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $organization['name'],
            'legalName' => $organization['legal_name'],
            'url' => $this->siteUrl('/'),
            'logo' => $this->siteUrl($organization['logo_path']),
            'email' => $organization['email'],
            'sameAs' => $organization['same_as'],
        ];
    }

    private function websiteSchema(string $locale): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.brand_name'),
            'url' => $this->localizedUrl('/{locale}', $locale),
            'inLanguage' => config('seo.hreflang.' . $locale, $locale),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $this->localizedUrl('/{locale}', $locale) . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    private function articleSchemas(string $locale): array
    {
        return collect(config('seo.articles', []))
            ->map(function (array $article) use ($locale) {
                $url = $this->localizedUrl('/{locale}/blog#' . $article['slug'], $locale);

                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => $article['title'][$locale] ?? $article['title']['fr'],
                    'description' => $article['description'][$locale] ?? $article['description']['fr'],
                    'datePublished' => $article['published_at'],
                    'dateModified' => $article['published_at'],
                    'mainEntityOfPage' => $url,
                    'url' => $url,
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => config('seo.brand_name'),
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => $this->siteUrl(config('seo.organization.logo_path')),
                        ],
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function breadcrumbSchema(Product $product, string $locale): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => $locale === 'en' ? 'Home' : 'Accueil',
                    'item' => $this->localizedUrl('/{locale}', $locale),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $product->category?->localized('name', $locale),
                    'item' => $this->localizedUrl('/{locale}?category=' . $product->category?->slug, $locale),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $product->localized('name', $locale),
                    'item' => $this->productUrl($product, $locale),
                ],
            ],
        ];
    }

    private function productSchema(Product $product, string $locale, string $url): array
    {
        $imageUrls = $product->images->pluck('url')->values()->all();
        $availability = $product->stock_quantity > 0
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock';

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->localized('name', $locale),
            'description' => $product->localized('description', $locale),
            'sku' => $product->sku,
            'brand' => [
                '@type' => 'Brand',
                'name' => config('seo.brand_name'),
            ],
            'category' => $product->category?->localized('name', $locale),
            'image' => $imageUrls,
            'url' => $url,
            'offers' => [
                '@type' => 'Offer',
                'url' => $url,
                'priceCurrency' => $product->currency,
                'price' => number_format($product->price_cents / 100, 2, '.', ''),
                'availability' => $availability,
                'itemCondition' => 'https://schema.org/NewCondition',
                'priceValidUntil' => now()->addMonths(6)->toDateString(),
            ],
        ];

        if ($product->rating_count > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (float) $product->rating_average,
                'reviewCount' => $product->rating_count,
            ];
        }

        return $schema;
    }

    private function openGraph(string $type, string $url, string $title, ?string $description, ?string $image, string $locale): array
    {
        return [
            'type' => $type,
            'site_name' => config('seo.brand_name'),
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'locale' => str_replace('-', '_', config('seo.hreflang.' . $locale, $locale)),
            'image' => $image,
        ];
    }

    private function twitterCard(string $title, ?string $description, ?string $image): array
    {
        return [
            'card' => $image ? 'summary_large_image' : 'summary',
            'title' => $title,
            'description' => $description,
            'image' => $image,
        ];
    }

    public function imagePayload(ProductImage $image, string $locale, bool $isPrimary = false): array
    {
        return [
            'id' => $image->id,
            'url' => $image->url,
            'alt_text' => $image->localized('alt_text', $locale),
            'width' => $image->width,
            'height' => $image->height,
            'aspect_ratio' => $image->width && $image->height ? round($image->width / $image->height, 4) : null,
            'dominant_color' => $image->dominant_color,
            'loading' => $isPrimary ? 'eager' : 'lazy',
            'fetch_priority' => $isPrimary ? 'high' : 'auto',
            'sources' => collect([480, 768, 1200, 1600])
                ->map(fn (int $width) => [
                    'width' => $width,
                    'url' => $this->resizedImageUrl($image->url, $width),
                ])
                ->all(),
        ];
    }

    private function resizedImageUrl(string $url, int $width): string
    {
        if (! str_contains($url, 'images.unsplash.com')) {
            return $url;
        }

        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);

        $query['auto'] = 'format';
        $query['fit'] = 'crop';
        $query['w'] = $width;
        $query['q'] = $query['q'] ?? 80;

        return ($parts['scheme'] ?? 'https') . '://' . $parts['host'] . ($parts['path'] ?? '') . '?' . http_build_query($query);
    }

    private function productUrl(Product $product, string $locale): string
    {
        if ($product->canonical_path) {
            return $this->localizedUrl($product->canonical_path, $locale);
        }

        return $this->localizedUrl('/{locale}/products/' . $product->slug, $locale);
    }

    private function hreflang(string $path): array
    {
        $links = collect($this->locales())
            ->map(fn (string $locale) => [
                'locale' => $locale,
                'hreflang' => config('seo.hreflang.' . $locale, $locale),
                'url' => $this->localizedUrl($path, $locale),
            ])
            ->values()
            ->all();

        $links[] = [
            'locale' => 'x-default',
            'hreflang' => 'x-default',
            'url' => $this->localizedUrl($path, config('seo.default_locale', 'fr')),
        ];

        return $links;
    }

    private function localizedUrl(string $path, string $locale): string
    {
        return $this->siteUrl(str_replace('{locale}', $locale, $path));
    }

    private function siteUrl(string $path): string
    {
        return rtrim(config('seo.site_url'), '/') . '/' . ltrim($path, '/');
    }

    private function apiUrl(string $path): string
    {
        return rtrim(config('seo.api_url'), '/') . '/' . ltrim($path, '/');
    }

    private function locales(): array
    {
        return config('seo.locales', ['fr', 'en']);
    }

    private function localizedArray(?array $values, string $locale): array
    {
        if (! $values) {
            return [];
        }

        return $values[$locale] ?? $values['fr'] ?? $values['en'] ?? [];
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
