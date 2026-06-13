@php
    $siteName = 'DEN & FILS';
    $siteUrl = rtrim((string) config('app.url', 'https://www.denetfils.fr'), '/');
    $metaTitle = trim($__env->yieldContent('title', __('home.meta.title')));
    $metaDescription = \Illuminate\Support\Str::limit(strip_tags(trim($__env->yieldContent('description', __('home.meta.description')))), 160, '');
    $canonicalUrl = trim($__env->yieldContent('canonical')) ?: url()->current();
    $robots = trim($__env->yieldContent('robots')) ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
    $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
    $ogImage = trim($__env->yieldContent('og_image')) ?: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=85';
    $preloadImage = trim($__env->yieldContent('preload_image')) ?: $ogImage;
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => ['Organization', 'OnlineStore'],
                '@id' => $siteUrl . '/#organization',
                'name' => 'DEN & FILS',
                'alternateName' => 'Denetfils',
                'url' => $siteUrl,
                'slogan' => 'Poto mitan kizin ou',
                'email' => __('home.contact.email'),
                'telephone' => __('home.contact.phone'),
                'vatID' => 'FR88 939445672',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '4 Rue des Grands Champs',
                    'postalCode' => '51520',
                    'addressLocality' => 'Saint Martin sur le Pré',
                    'addressCountry' => 'FR',
                ],
                'sameAs' => [
                    'https://www.facebook.com/denetfils',
                    'https://www.instagram.com/denetfils',
                    'https://www.tiktok.com/@denetfils',
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
                    'target' => route('home.localized', ['locale' => $currentLocale]) . '?q={search_term_string}#products',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ];
@endphp
<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $robots }}">
<meta name="author" content="DEN & FILS">
<meta name="application-name" content="DEN & FILS">
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#121a10" media="(prefers-color-scheme: dark)">
<meta name="format-detection" content="telephone=no">
<link rel="canonical" href="{{ $canonicalUrl }}">
<link rel="alternate" hreflang="{{ $currentLocale }}" href="{{ $canonicalUrl }}">
<link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ route('home.localized', ['locale' => 'fr']) }}">
<link rel="sitemap" type="application/xml" href="{{ url('/sitemap.xml') }}">
@if ($preloadImage)
    <link rel="preload" as="image" href="{{ $preloadImage }}" fetchpriority="high">
@endif
<meta property="og:locale" content="{{ $currentLocale === 'fr' ? 'fr_FR' : 'en_US' }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:alt" content="{{ $metaTitle }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@stack('structured-data')
