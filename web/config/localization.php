<?php

return [
    'default_country' => env('VISITOR_DEFAULT_COUNTRY', 'FR'),
    'default_locale' => env('VISITOR_DEFAULT_LOCALE', 'fr'),
    'supported_locales' => ['fr', 'en'],
    'supported_countries' => array_values(array_filter(array_map(
        fn (string $code) => strtoupper(trim($code)),
        explode(',', env('VISITOR_SUPPORTED_COUNTRIES', 'FR,BE,DE,NL,LU,ES,IT,PT,IE,AT,PL,CZ,DK,SE,FI,GB,CH')),
    ))),
    'country_locales' => [
        'FR' => 'fr', 'BE' => 'fr', 'LU' => 'fr', 'CH' => 'fr',
        'DE' => 'en', 'NL' => 'en', 'ES' => 'en', 'IT' => 'en',
        'PT' => 'en', 'IE' => 'en', 'AT' => 'en', 'PL' => 'en',
        'CZ' => 'en', 'DK' => 'en', 'SE' => 'en', 'FI' => 'en', 'GB' => 'en',
    ],
    'country_names' => [
        'FR' => ['fr' => 'France', 'en' => 'France'],
        'BE' => ['fr' => 'Belgique', 'en' => 'Belgium'],
        'DE' => ['fr' => 'Allemagne', 'en' => 'Germany'],
        'NL' => ['fr' => 'Pays-Bas', 'en' => 'Netherlands'],
        'LU' => ['fr' => 'Luxembourg', 'en' => 'Luxembourg'],
        'ES' => ['fr' => 'Espagne', 'en' => 'Spain'],
        'IT' => ['fr' => 'Italie', 'en' => 'Italy'],
        'PT' => ['fr' => 'Portugal', 'en' => 'Portugal'],
        'IE' => ['fr' => 'Irlande', 'en' => 'Ireland'],
        'AT' => ['fr' => 'Autriche', 'en' => 'Austria'],
        'PL' => ['fr' => 'Pologne', 'en' => 'Poland'],
        'CZ' => ['fr' => 'Tchéquie', 'en' => 'Czechia'],
        'DK' => ['fr' => 'Danemark', 'en' => 'Denmark'],
        'SE' => ['fr' => 'Suède', 'en' => 'Sweden'],
        'FI' => ['fr' => 'Finlande', 'en' => 'Finland'],
        'GB' => ['fr' => 'Royaume-Uni', 'en' => 'United Kingdom'],
        'CH' => ['fr' => 'Suisse', 'en' => 'Switzerland'],
    ],
    'cookie_minutes' => (int) env('VISITOR_COOKIE_MINUTES', 129600),
    'cloudflare' => [
        'trust_country_header' => (bool) env('VISITOR_TRUST_CLOUDFLARE_COUNTRY', false),
    ],
    'ipinfo' => [
        'token' => env('IPINFO_TOKEN'),
        'timeout_seconds' => (float) env('IPINFO_TIMEOUT_SECONDS', 1.5),
        'cache_seconds' => (int) env('IPINFO_CACHE_SECONDS', 86400),
        'base_url' => env('IPINFO_BASE_URL', 'https://api.ipinfo.io/lite'),
    ],
];
