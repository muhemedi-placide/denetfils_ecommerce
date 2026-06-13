<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\CoreDefaults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SupportedCountrySeeder::class,
            AccessControlSeeder::class,
        ]);

        $admin = User::updateOrCreate(
            ['email' => 'admin@denetfils.fr'],
            [
                'name' => 'Denetfils Admin',
                'first_name' => 'Denetfils',
                'last_name' => 'Admin',
                'preferred_locale' => 'fr',
                'country_code' => 'FR',
                'timezone' => CoreDefaults::DEFAULT_TIMEZONE,
                'status' => 'active',
                'password' => Hash::make('password'),
            ],
        );

        $admin->staffProfile()->updateOrCreate(
            ['user_id' => $admin->id],
            ['position' => 'Super administrateur', 'operational_status' => 'active'],
        );

        $admin->syncRoles(['super_admin']);
    }
}
