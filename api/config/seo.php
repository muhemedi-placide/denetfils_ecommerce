<?php

return [
    'site_url' => env('FRONTEND_URL', 'http://127.0.0.1:8001'),
    'api_url' => env('APP_URL', 'http://127.0.0.1:8000'),
    'brand_name' => 'Denetfils',
    'default_locale' => 'fr',
    'locales' => ['fr', 'en'],
    'hreflang' => [
        'fr' => 'fr-FR',
        'en' => 'en',
    ],
    'organization' => [
        'name' => 'Denetfils',
        'legal_name' => 'DEN & FILS',
        'email' => 'support@denetfils.fr',
        'logo_path' => '/images/denetfils-logo.png',
        'same_as' => [],
    ],
    'static_routes' => [
        ['path' => '/{locale}', 'changefreq' => 'daily', 'priority' => '1.0'],
        ['path' => '/{locale}/about', 'changefreq' => 'monthly', 'priority' => '0.7'],
        ['path' => '/{locale}/blog', 'changefreq' => 'weekly', 'priority' => '0.7'],
        ['path' => '/{locale}/livraison', 'changefreq' => 'monthly', 'priority' => '0.6'],
        ['path' => '/{locale}/paiement-securise', 'changefreq' => 'monthly', 'priority' => '0.6'],
        ['path' => '/{locale}/conditions-utilisation', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ['path' => '/{locale}/mentions-legales', 'changefreq' => 'yearly', 'priority' => '0.3'],
    ],
    'robots' => [
        'allow' => ['/'],
        'disallow' => [
            '/api/v1/admin',
            '/api/v1/auth',
            '/api/v1/me',
            '/api/documentation',
            '/docs',
        ],
    ],
    'articles' => [
        [
            'slug' => 'pourquoi-choisir-denetfils',
            'published_at' => '2025-11-19',
            'title' => [
                'fr' => 'Pourquoi tant de personnes choisissent Denetfils ?',
                'en' => 'Why do so many people choose Denetfils?',
            ],
            'description' => [
                'fr' => 'La difference Denetfils : authenticite, histoire, gout et confiance.',
                'en' => 'The Denetfils difference: authenticity, history, taste and trust.',
            ],
        ],
    ],
];
