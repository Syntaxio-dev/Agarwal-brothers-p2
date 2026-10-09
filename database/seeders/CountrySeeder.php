<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            'Switzerland'   => [46.8182, 8.2275],
            'Germany'       => [51.1657, 10.4515],
            'United States' => [39.8283, -98.5795],
            'Japan'         => [36.2048, 138.2529],
            'India'         => [20.5937, 78.9629],
            'United Kingdom'=> [55.3781, -3.4360],
            'France'        => [46.2276, 2.2137],
            'Italy'         => [41.8719, 12.5674],
            'Denmark'       => [56.2639, 9.5018],
            'Netherlands'   => [52.1326, 5.2913],
            'Austria'       => [47.5162, 14.5501],
            'China'         => [35.8617, 104.1954],
            'South Korea'   => [35.9078, 127.7669],
        ];

        $models = [];
        foreach ($countries as $name => [$lat, $lng]) {
            $models[$name] = Country::firstOrCreate(
                ['name' => $name],
                ['latitude' => $lat, 'longitude' => $lng]
            );
        }

        $brandCountry = [
            'BUCHI' => 'Switzerland',
            'Labomatic' => 'Switzerland',
            'Mettler Toledo' => 'Switzerland',
            'Hettich' => 'Germany',
            'Eppendorf' => 'Germany',
            'Sartorius' => 'Germany',
            'Merck' => 'Germany',
            'IKA' => 'Germany',
            'Thermo Fisher' => 'United States',
            'Shimadzu' => 'Japan',
            'Horiba' => 'Japan',
            'Borosil' => 'India',
        ];

        foreach ($brandCountry as $brand => $country) {
            Brand::where('name', $brand)
                ->whereNull('country_id')
                ->update(['country_id' => $models[$country]->id]);
        }
    }
}
