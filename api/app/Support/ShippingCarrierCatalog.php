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
            ],
            'required_any' => [],
            'public_fields' => ['enseigne', 'brand_code', 'api_endpoint', 'default_country'],
            'default_public_config' => [
                'api_endpoint' => 'https://api.mondialrelay.com/Web_Services.asmx',
                'tracking_url' => 'https://www.mondialrelay.fr/suivi-de-colis/',
            ],
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
