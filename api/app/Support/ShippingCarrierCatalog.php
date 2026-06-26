<?php

namespace App\Support;

class ShippingCarrierCatalog
{
    public const PROVIDERS = [
        'mondial_relay' => [
            'name' => 'Mondial Relay',
            'environments' => ['sandbox', 'live'],
            'capabilities' => ['relay_points', 'locker', 'label', 'tracking'],
            'delivery_modes' => [
                ['key' => '24R', 'name' => 'Point Relais', 'description' => 'Livraison en Point Relais.'],
                ['key' => '24L', 'name' => 'Lockers', 'description' => 'Livraison en consigne automatique lorsque disponible.'],
                ['key' => 'HOM', 'name' => 'Home', 'description' => 'Livraison a domicile, si le contrat le permet.'],
            ],
            'credential_fields' => [
                ['key' => 'enseigne', 'label' => 'Enseigne', 'type' => 'string', 'secret' => false, 'required' => true],
                ['key' => 'private_key', 'label' => 'Cle privee', 'type' => 'string', 'secret' => true, 'required' => true],
                ['key' => 'brand_code', 'label' => 'Code marque', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'account_number', 'label' => 'Numero de compte', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'api_endpoint', 'label' => 'Endpoint API', 'type' => 'url', 'secret' => false, 'required' => false],
                ['key' => 'default_country', 'label' => 'Pays par defaut', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'sender_name', 'label' => 'Expediteur - nom', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'sender_address', 'label' => 'Expediteur - adresse', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'sender_address_2', 'label' => 'Expediteur - complement', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'sender_postal_code', 'label' => 'Expediteur - code postal', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'sender_city', 'label' => 'Expediteur - ville', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'sender_country', 'label' => 'Expediteur - pays', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'sender_phone', 'label' => 'Expediteur - telephone', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'sender_email', 'label' => 'Expediteur - email', 'type' => 'string', 'secret' => false, 'required' => false],
            ],
            'required_any' => [],
            'public_fields' => ['enseigne', 'brand_code', 'api_endpoint', 'default_country'],
            'default_public_config' => [
                'api_endpoint' => 'https://api.mondialrelay.com/Web_Services.asmx',
                'tracking_url' => 'https://www.mondialrelay.fr/suivi-de-colis/',
            ],
        ],
        'chronopost' => [
            'name' => 'Chronopost',
            'environments' => ['sandbox', 'live'],
            'capabilities' => ['home_delivery', 'manual_fulfillment'],
            'delivery_modes' => [
                ['key' => 'HOME', 'name' => 'Domicile', 'description' => 'Livraison Chronopost à domicile.'],
                ['key' => 'RELAY', 'name' => 'Chrono Relais', 'description' => 'Point de retrait Chronopost, avec API à configurer.'],
            ],
            'credential_fields' => [
                ['key' => 'account_number', 'label' => 'Numéro de compte', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'password', 'label' => 'Mot de passe', 'type' => 'string', 'secret' => true, 'required' => false],
                ['key' => 'api_endpoint', 'label' => 'Endpoint API', 'type' => 'url', 'secret' => false, 'required' => false],
            ],
            'required_any' => [],
            'public_fields' => [],
            'default_public_config' => ['tracking_url' => 'https://www.chronopost.fr/tracking-no-cms/suivi-page'],
        ],
    ];

    public const STATUSES = ['draft', 'active', 'inactive'];

    public static function providers(): array
    {
        return self::PROVIDERS;
    }

    public static function provider(string $provider): ?array
    {
        return self::PROVIDERS[$provider] ?? null;
    }

    public static function providerKeys(): array
    {
        return array_keys(self::PROVIDERS);
    }
}
