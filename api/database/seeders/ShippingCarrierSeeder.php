<?php

namespace Database\Seeders;

use App\Models\ShippingCarrier;
use Illuminate\Database\Seeder;

class ShippingCarrierSeeder extends Seeder
{
    public function run(): void
    {
        ShippingCarrier::updateOrCreate(
            ['code' => 'mondial_relay'],
            [
                'provider' => 'mondial_relay',
                'display_name' => ['fr' => 'Mondial Relay', 'en' => 'Mondial Relay'],
                'description' => [
                    'fr' => 'Livraison en Point Relais, locker et suivi colis Mondial Relay.',
                    'en' => 'Mondial Relay pickup point, locker and parcel tracking delivery.',
                ],
                'environment' => env('MONDIAL_RELAY_ENV', env('MONDIAL_RELAY_ENVIRONMENT', 'sandbox')),
                'status' => env('MONDIAL_RELAY_ENABLED', false) ? 'active' : 'draft',
                'is_enabled' => (bool) env('MONDIAL_RELAY_ENABLED', false),
                'sort_order' => 10,
                'delivery_modes' => ['24R', '24L'],
                'countries' => ['FR', 'BE', 'LU', 'ES', 'NL'],
                'max_weight_grams' => 30000,
                'supports_relay_points' => true,
                'supports_home_delivery' => false,
                'public_config' => [
                    'api_endpoint' => env('MONDIAL_RELAY_API_ENDPOINT', 'https://api.mondialrelay.com/Web_Services.asmx'),
                    'tracking_url' => env('MONDIAL_RELAY_TRACKING_URL', 'https://www.mondialrelay.fr/suivi-de-colis/'),
                ],
                'credentials' => array_filter([
                    'enseigne' => env('MONDIAL_RELAY_ENSEIGNE'),
                    'private_key' => env('MONDIAL_RELAY_PRIVATE_KEY'),
                    'brand_code' => env('MONDIAL_RELAY_BRAND_ID', env('MONDIAL_RELAY_BRAND_CODE')),
                    'account_number' => env('MONDIAL_RELAY_ACCOUNT_NUMBER'),
                    'api_endpoint' => env('MONDIAL_RELAY_API_ENDPOINT', 'https://api.mondialrelay.com/Web_Services.asmx'),
                    'default_country' => env('MONDIAL_RELAY_COUNTRY', 'FR'),
                ], fn ($value) => filled($value)),
            ],
        );
    }
}
