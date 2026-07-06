<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            'Amman',
            'Zarqa',
            'Irbid',
            'Russeifa',
            'Aqaba',
            'Madaba',
            'As-Salt',
            'Mafraq',
            'Jerash',
            'Karak',
            'Ma\'an',
            'Tafilah',
            'Ajloun',
            'Ramtha',
            'Sahab',
            'Fuheis',
            'Wadi Al-Seer',
            'Ain Al-Basha',
            'Al-Jizah',
            'Deir Alla',
            'Shuneh Al-Janubiyah',
            'Shuneh Ash-Shamaliyah',
            'Al-Mazar Al-Janubi',
            'Al-Mazar Ash-Shamali',
            'Al-Husn',
            'Kufranjah',
            'Ar-Ramtha',
            'Al-Hashimiyah',
            'Al-Azraq',
            'Umm Al-Jimal',
            'Qatraneh',
            'Al-Quwayrah',
            'Ghawr Al-Safi',
            'Ghawr Al-Mazra\'a',
            'Ad-Dulayl',
            'Al-Mughayyir',
            'Sama As-Sarhan',
            'Al-Karak City',
            'Umm Qais',
            'Petra (Wadi Musa)',
        ];

        foreach ($cities as $cityName) {
            City::firstOrCreate([
                'name' => $cityName,
            ], [
                'status' => 'active',
            ]);
        }
    }
}
