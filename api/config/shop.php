<?php

return [
    'name' => env('SHOP_NAME', 'Marché Peyi'),
    'legal_name' => env('SHOP_LEGAL_NAME', env('SHOP_NAME', 'Marché Peyi')),
    'email' => env('SHOP_EMAIL', 'contact@example.com'),
    'website' => env('SHOP_WEBSITE', parse_url(env('FRONTEND_URL', ''), PHP_URL_HOST) ?: 'localhost'),
];
