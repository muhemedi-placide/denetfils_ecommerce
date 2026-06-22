@php
    $siteName = 'Marché Peyi';
    $siteUrl = rtrim((string) config('app.url', 'https://www.marchepeyi.fr'), '/');
    $seoPayload = $seoPayload ?? [];
    $seoMeta = data_get($seoPayload, 'meta', []);
    $openGraph = data_get($seoPayload, 'open_graph', []);
    $twitterCard = data_get($seoPayload, 'twitter_card', []);
    $hreflangLinks = data_get($seoPayload, 'hreflang', []);
    $apiJsonLd = collect(data_get($seoPayload, 'json_ld', []))
        ->flatMap(fn ($item) => is_array($item) && array_is_list($item) ? $item : [$item])
        ->filter()
        ->values()
        ->all();
    $metaTitle = trim($__env->yieldContent('title', data_get($seoMeta, 'title', __('home.meta.title'))));
    $metaDescription = \Illuminate\Support\Str::limit(strip_tags(trim($__env->yieldContent('description', data_get($seoMeta, 'description', __('home.meta.description'))))), 160, '');
    $canonicalUrl = trim($__env->yieldContent('canonical')) ?: data_get($seoPayload, 'canonical') ?: url()->current();
    $robots = trim($__env->yieldContent('robots')) ?: data_get($seoMeta, 'robots') ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
    $ogType = trim($__env->yieldContent('og_type')) ?: data_get($openGraph, 'type', 'website');
    $ogImage = trim($__env->yieldContent('og_image')) ?: data_get($openGraph, 'image') ?: asset('assets/products/hero-basket.jpg');
    $twitterImage = data_get($twitterCard, 'image') ?: $ogImage;
    $twitterCardType = data_get($twitterCard, 'card', 'summary_large_image');
    $preloadImage = trim($__env->yieldContent('preload_image'));
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => ['Organization', 'OnlineStore'],
                '@id' => $siteUrl . '/#organization',
                'name' => 'Marché Peyi',
                'alternateName' => 'Marche Peyi',
                'url' => $siteUrl,
                'slogan' => 'Exotic & Tropical Tastes',
                'email' => 'bonjour@marchepeyi.com',
                'telephone' => '+33 1 23 45 67 89',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '12 rue des Tropiques',
                    'postalCode' => '93500',
                    'addressLocality' => 'Pantin',
                    'addressCountry' => 'FR',
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl . '/#website',
                'url' => $siteUrl,
                'name' => $siteName,
                'inLanguage' => $currentLocale,
                'publisher' => ['@id' => $siteUrl . '/#organization'],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('shop.index', ['locale' => $currentLocale]) . '?q={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ];
@endphp
<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $robots }}">
<meta name="author" content="{{ $siteName }}">
<meta name="application-name" content="{{ $siteName }}">
<meta name="theme-color" content="#fff7df" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0f2110" media="(prefers-color-scheme: dark)">
<meta name="format-detection" content="telephone=no">
<style>
@media (max-width: 1023px) {
#shop-app > header > div:nth-of-type(2) > div { position: relative; display: flex; min-height: 48px; align-items: center; justify-content: space-between; gap: 0; }
#shop-app > header > div:nth-of-type(2) > div > a:first-child { position: absolute; left: 50%; z-index: 1; max-width: min(58vw, 240px); transform: translateX(-50%); }
#shop-app > header > div:nth-of-type(2) > div > div:last-child { z-index: 2; display: flex !important; width: 100%; flex-direction: row-reverse; align-items: center; justify-content: space-between !important; gap: .5rem; }
#shop-app > header > div:nth-of-type(2) > div > div:last-child > a, #shop-app > header > div:nth-of-type(2) > div > div:last-child > button { display: none !important; }
#shop-app > header > div:nth-of-type(2) [data-mobile-menu-toggle] { width: 2.75rem; height: 2.75rem; border: 0 !important; font-size: 0 !important; }
#shop-app > header > div:nth-of-type(2) [data-mobile-menu-icon='open']::before { content: '☰'; font-size: 1.55rem; line-height: 1; }
#shop-app > header > div:nth-of-type(2) [data-mobile-menu-icon='close'] { font-size: 1.9rem !important; line-height: 1; }
#shop-app > header > div:nth-of-type(2) > div > a:first-child > span:first-child { width: 1.6rem !important; height: 1.6rem !important; background: transparent !important; color: var(--mp-forest) !important; font-size: 0 !important; box-shadow: none !important; }
#shop-app > header > div:nth-of-type(2) > div > a:first-child > span:first-child::before { content: '⌂'; font-size: 1.55rem; line-height: 1; }
#shop-app > header > div:nth-of-type(2) > div > a:first-child > span:last-child { font-size: 1.28rem; line-height: 1; }
[data-testid='header-cart-open-button'] { min-height: 2.5rem; padding: .5rem .75rem; }
}
</style>
<link rel="canonical" href="{{ $canonicalUrl }}">
@if (! empty($hreflangLinks))
    @foreach ($hreflangLinks as $link)
        <link rel="alternate" hreflang="{{ $link['hreflang'] ?? $link['locale'] ?? $currentLocale }}" href="{{ $link['url'] ?? $canonicalUrl }}">
    @endforeach
@else
    <link rel="alternate" hreflang="{{ $currentLocale }}" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ route('home.localized', ['locale' => 'fr']) }}">
@endif
<link rel="sitemap" type="application/xml" href="{{ url('/sitemap.xml') }}">
@if ($preloadImage)
    <link rel="preload" as="image" href="{{ $preloadImage }}" fetchpriority="high">
@endif
<meta property="og:locale" content="{{ $currentLocale === 'fr' ? 'fr_FR' : 'en_US' }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ data_get($openGraph, 'title', $metaTitle) }}">
<meta property="og:description" content="{{ data_get($openGraph, 'description', $metaDescription) }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:alt" content="{{ $metaTitle }}">
<meta name="twitter:card" content="{{ $twitterCardType }}">
<meta name="twitter:title" content="{{ data_get($twitterCard, 'title', $metaTitle) }}">
<meta name="twitter:description" content="{{ data_get($twitterCard, 'description', $metaDescription) }}">
<meta name="twitter:image" content="{{ $twitterImage }}">
@if (! empty($apiJsonLd))
    @foreach ($apiJsonLd as $jsonLdItem)
        <script type="application/ld+json">{!! json_encode($jsonLdItem, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endforeach
@else
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
@stack('structured-data')
