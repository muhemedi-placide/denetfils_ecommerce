<?php

return [
    'shop' => [
        'brand' => env('SHOP_NAME', 'Marché Peyi'),
        'trade_name' => env('SHOP_NAME', 'Marché Peyi'),
        'tagline' => 'Boutique alimentaire premium',
        'website' => env('SHOP_WEBSITE', 'localhost'),
        'email' => env('SHOP_EMAIL', 'contact@example.com'),
        'phone' => '+33 0 00 00 00 00',
        'address' => [
            'France',
        ],
        'legal' => [
            'Document genere par le back-office '.env('SHOP_NAME', 'Marché Peyi').'.',
            'Merci de verifier la commande avant expedition.',
        ],
    ],
];
