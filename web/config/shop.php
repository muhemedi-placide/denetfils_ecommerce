<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identite de la boutique
    |--------------------------------------------------------------------------
    |
    | SHOP_NAME est l'unique source du nom affiche dans le front-office,
    | l'espace client, le back-office, les documents et les metadonnees SEO.
    |
    */
    'name' => env('SHOP_NAME', 'Marché Peyi'),
    'legal_name' => env('SHOP_LEGAL_NAME', env('SHOP_NAME', 'Marché Peyi')),
    'email' => env('SHOP_EMAIL', 'contact@example.com'),
    'website' => env('SHOP_WEBSITE', parse_url(env('APP_URL', ''), PHP_URL_HOST) ?: 'localhost'),
];
