@php
    $siteName = config('shop.name');
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
                'name' => $siteName,
                'alternateName' => $siteName,
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
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#000000" media="(prefers-color-scheme: dark)">
<meta name="format-detection" content="telephone=no">
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
