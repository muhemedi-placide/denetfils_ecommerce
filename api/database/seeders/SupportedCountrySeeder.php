<?php

namespace Database\Seeders;

use App\Models\SupportedCountry;
use Illuminate\Database\Seeder;

class SupportedCountrySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->countries() as $country) {
            SupportedCountry::updateOrCreate(
                ['code' => $country['code']],
                $country,
            );
        }
    }

    private function countries(): array
    {
        return [
            ['code' => 'FR', 'name' => ['fr' => 'France', 'en' => 'France'], 'currency' => 'EUR', 'default_locale' => 'fr', 'timezone' => 'Europe/Paris', 'standard_vat_rate_percent' => 20, 'food_vat_rate_percent' => 5.50, 'is_eu' => true, 'is_active' => true],
            ['code' => 'BE', 'name' => ['fr' => 'Belgique', 'en' => 'Belgium'], 'currency' => 'EUR', 'default_locale' => 'fr', 'timezone' => 'Europe/Brussels', 'standard_vat_rate_percent' => 21, 'food_vat_rate_percent' => 6, 'is_eu' => true, 'is_active' => true],
            ['code' => 'DE', 'name' => ['fr' => 'Allemagne', 'en' => 'Germany'], 'currency' => 'EUR', 'default_locale' => 'en', 'timezone' => 'Europe/Berlin', 'standard_vat_rate_percent' => 19, 'food_vat_rate_percent' => 7, 'is_eu' => true, 'is_active' => true],
            ['code' => 'NL', 'name' => ['fr' => 'Pays-Bas', 'en' => 'Netherlands'], 'currency' => 'EUR', 'default_locale' => 'en', 'timezone' => 'Europe/Amsterdam', 'standard_vat_rate_percent' => 21, 'food_vat_rate_percent' => 9, 'is_eu' => true, 'is_active' => true],
            ['code' => 'LU', 'name' => ['fr' => 'Luxembourg', 'en' => 'Luxembourg'], 'currency' => 'EUR', 'default_locale' => 'fr', 'timezone' => 'Europe/Luxembourg', 'standard_vat_rate_percent' => 17, 'food_vat_rate_percent' => 3, 'is_eu' => true, 'is_active' => true],
            ['code' => 'ES', 'name' => ['fr' => 'Espagne', 'en' => 'Spain'], 'currency' => 'EUR', 'default_locale' => 'en', 'timezone' => 'Europe/Madrid', 'standard_vat_rate_percent' => 21, 'food_vat_rate_percent' => 10, 'is_eu' => true, 'is_active' => true],
            ['code' => 'IT', 'name' => ['fr' => 'Italie', 'en' => 'Italy'], 'currency' => 'EUR', 'default_locale' => 'en', 'timezone' => 'Europe/Rome', 'standard_vat_rate_percent' => 22, 'food_vat_rate_percent' => 10, 'is_eu' => true, 'is_active' => true],
            ['code' => 'PT', 'name' => ['fr' => 'Portugal', 'en' => 'Portugal'], 'currency' => 'EUR', 'default_locale' => 'en', 'timezone' => 'Europe/Lisbon', 'standard_vat_rate_percent' => 23, 'food_vat_rate_percent' => 6, 'is_eu' => true, 'is_active' => true],
            ['code' => 'IE', 'name' => ['fr' => 'Irlande', 'en' => 'Ireland'], 'currency' => 'EUR', 'default_locale' => 'en', 'timezone' => 'Europe/Dublin', 'standard_vat_rate_percent' => 23, 'food_vat_rate_percent' => 0, 'is_eu' => true, 'is_active' => true],
            ['code' => 'AT', 'name' => ['fr' => 'Autriche', 'en' => 'Austria'], 'currency' => 'EUR', 'default_locale' => 'en', 'timezone' => 'Europe/Vienna', 'standard_vat_rate_percent' => 20, 'food_vat_rate_percent' => 10, 'is_eu' => true, 'is_active' => true],
            ['code' => 'PL', 'name' => ['fr' => 'Pologne', 'en' => 'Poland'], 'currency' => 'PLN', 'default_locale' => 'en', 'timezone' => 'Europe/Warsaw', 'standard_vat_rate_percent' => 23, 'food_vat_rate_percent' => 5, 'is_eu' => true, 'is_active' => true],
            ['code' => 'CZ', 'name' => ['fr' => 'Tchéquie', 'en' => 'Czechia'], 'currency' => 'CZK', 'default_locale' => 'en', 'timezone' => 'Europe/Prague', 'standard_vat_rate_percent' => 21, 'food_vat_rate_percent' => 12, 'is_eu' => true, 'is_active' => true],
            ['code' => 'DK', 'name' => ['fr' => 'Danemark', 'en' => 'Denmark'], 'currency' => 'DKK', 'default_locale' => 'en', 'timezone' => 'Europe/Copenhagen', 'standard_vat_rate_percent' => 25, 'food_vat_rate_percent' => 25, 'is_eu' => true, 'is_active' => true],
            ['code' => 'SE', 'name' => ['fr' => 'Suède', 'en' => 'Sweden'], 'currency' => 'SEK', 'default_locale' => 'en', 'timezone' => 'Europe/Stockholm', 'standard_vat_rate_percent' => 25, 'food_vat_rate_percent' => 12, 'is_eu' => true, 'is_active' => true],
            ['code' => 'FI', 'name' => ['fr' => 'Finlande', 'en' => 'Finland'], 'currency' => 'EUR', 'default_locale' => 'en', 'timezone' => 'Europe/Helsinki', 'standard_vat_rate_percent' => 24, 'food_vat_rate_percent' => 14, 'is_eu' => true, 'is_active' => true],
            ['code' => 'GB', 'name' => ['fr' => 'Royaume-Uni', 'en' => 'United Kingdom'], 'currency' => 'GBP', 'default_locale' => 'en', 'timezone' => 'Europe/London', 'standard_vat_rate_percent' => 20, 'food_vat_rate_percent' => 0, 'is_eu' => false, 'is_active' => true],
            ['code' => 'CH', 'name' => ['fr' => 'Suisse', 'en' => 'Switzerland'], 'currency' => 'CHF', 'default_locale' => 'fr', 'timezone' => 'Europe/Zurich', 'standard_vat_rate_percent' => 8.10, 'food_vat_rate_percent' => 2.60, 'is_eu' => false, 'is_active' => true],
        ];
    }
}
