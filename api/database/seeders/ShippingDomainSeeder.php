<?php

namespace Database\Seeders;

use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingDomainSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ShippingCarrierSeeder::class);
        $carrier = ShippingCarrier::query()->where('code', 'mondial_relay')->firstOrFail();
        $zone = ShippingZone::query()->updateOrCreate(['name' => 'Europe Mondial Relay'], ['countries' => ['FR', 'BE', 'LU', 'ES', 'NL'], 'is_active' => true]);

        foreach ([
            ['code' => 'mondial_relay_point_relais', 'service' => '24R', 'type' => 'pickup_point', 'name' => ['fr' => 'Point Relais®', 'en' => 'Pickup Point'], 'price' => 490, 'days' => [3, 5]],
            ['code' => 'mondial_relay_locker', 'service' => '24L', 'type' => 'locker', 'name' => ['fr' => 'Locker Mondial Relay', 'en' => 'Mondial Relay Locker'], 'price' => 390, 'days' => [3, 5]],
        ] as $index => $data) {
            $method = ShippingMethod::query()->updateOrCreate(['code' => $data['code']], [
                'shipping_carrier_id' => $carrier->id, 'name' => $data['name'], 'description' => ['fr' => 'Livraison sécurisée avec suivi.', 'en' => 'Tracked delivery.'],
                'delivery_type' => $data['type'], 'service_code' => $data['service'], 'is_active' => true, 'requires_pickup_point' => true,
                'requires_phone' => true, 'max_weight_grams' => 30000, 'min_delivery_days' => $data['days'][0], 'max_delivery_days' => $data['days'][1], 'sort_order' => $index + 1,
            ]);
            ShippingRate::query()->updateOrCreate(['shipping_method_id' => $method->id, 'shipping_zone_id' => $zone->id, 'min_weight_grams' => 0, 'max_weight_grams' => 30000], ['price_cents' => $data['price'], 'currency' => 'EUR', 'is_active' => true]);
        }
    }
}
