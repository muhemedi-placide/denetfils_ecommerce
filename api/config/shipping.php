<?php

return [
    'default_currency' => env('SHIPPING_CURRENCY', 'EUR'),
    'label_disk' => env('SHIPPING_LABEL_DISK', 'local'),
    'label_directory' => env('SHIPPING_LABEL_DIRECTORY', 'private/shipping-labels'),
    'pickup_search_rate_limit' => env('SHIPPING_PICKUP_RATE_LIMIT', '30,1'),

    'mondial_relay' => [
        'enabled' => (bool) env('MONDIAL_RELAY_ENABLED', false),
        'environment' => env('MONDIAL_RELAY_ENV', env('MONDIAL_RELAY_ENVIRONMENT', 'sandbox')),
        'endpoint' => env('MONDIAL_RELAY_API_ENDPOINT', 'https://api.mondialrelay.com/Web_Services.asmx'),
        'enseigne' => env('MONDIAL_RELAY_ENSEIGNE'),
        'private_key' => env('MONDIAL_RELAY_PRIVATE_KEY'),
        'brand_id' => env('MONDIAL_RELAY_BRAND_ID', env('MONDIAL_RELAY_BRAND_CODE')),
        'country' => env('MONDIAL_RELAY_COUNTRY', 'FR'),
        'language' => env('MONDIAL_RELAY_DEFAULT_LANGUAGE', 'FR'),
        'label_format' => env('MONDIAL_RELAY_LABEL_FORMAT', 'A4'),
        'widget_enabled' => (bool) env('MONDIAL_RELAY_WIDGET_ENABLED', true),
        'use_official_widget' => (bool) env('MONDIAL_RELAY_USE_OFFICIAL_WIDGET', true),
        'map_provider' => env('MONDIAL_RELAY_MAP_PROVIDER', 'leaflet'),
        'timeout' => (int) env('MONDIAL_RELAY_TIMEOUT', 15),
        'sender' => [
            'name' => env('MONDIAL_RELAY_SENDER_NAME'),
            'address' => env('MONDIAL_RELAY_SENDER_ADDRESS'),
            'address_2' => env('MONDIAL_RELAY_SENDER_ADDRESS_2'),
            'postal_code' => env('MONDIAL_RELAY_SENDER_POSTAL_CODE'),
            'city' => env('MONDIAL_RELAY_SENDER_CITY'),
            'country' => env('MONDIAL_RELAY_SENDER_COUNTRY', 'FR'),
            'phone' => env('MONDIAL_RELAY_SENDER_PHONE'),
            'email' => env('MONDIAL_RELAY_SENDER_EMAIL'),
        ],
    ],
];
