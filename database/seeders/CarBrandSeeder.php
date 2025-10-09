<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CarBrand;
use Illuminate\Support\Facades\DB;

class CarBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Toyota', 'description' => 'Japanese automotive manufacturer', 'is_active' => true],
            ['name' => 'BMW', 'description' => 'German luxury vehicles manufacturer', 'is_active' => true],
            ['name' => 'Mercedes-Benz', 'description' => 'German luxury automobile manufacturer', 'is_active' => true],
            ['name' => 'Honda', 'description' => 'Japanese automobile and motorcycle manufacturer', 'is_active' => true],
            ['name' => 'Ford', 'description' => 'American multinational automobile manufacturer', 'is_active' => true],
            ['name' => 'Chevrolet', 'description' => 'American automobile division of General Motors', 'is_active' => true],
            ['name' => 'Hyundai', 'description' => 'South Korean multinational automotive manufacturer', 'is_active' => true],
            ['name' => 'Kia', 'description' => 'South Korean automobile manufacturer', 'is_active' => true],
            ['name' => 'Nissan', 'description' => 'Japanese multinational automobile manufacturer', 'is_active' => true],
            ['name' => 'Mazda', 'description' => 'Japanese multinational automotive manufacturer', 'is_active' => true],
            ['name' => 'Volkswagen', 'description' => 'German motor vehicle manufacturer', 'is_active' => true],
            ['name' => 'Audi', 'description' => 'German luxury automobile manufacturer', 'is_active' => true],
            ['name' => 'Lexus', 'description' => 'Luxury vehicle division of Toyota', 'is_active' => true],
            ['name' => 'Jeep', 'description' => 'American automobile marque', 'is_active' => true],
            ['name' => 'Subaru', 'description' => 'Japanese automobile manufacturer', 'is_active' => true],
            ['name' => 'Mitsubishi', 'description' => 'Japanese multinational automotive manufacturer', 'is_active' => true],
            ['name' => 'Land Rover', 'description' => 'British luxury SUV manufacturer', 'is_active' => true],
            ['name' => 'Volvo', 'description' => 'Swedish multinational manufacturing company', 'is_active' => true],
            ['name' => 'Porsche', 'description' => 'German automobile manufacturer', 'is_active' => true],
            ['name' => 'Tesla', 'description' => 'American electric vehicle and clean energy company', 'is_active' => true],
        ];

        foreach ($brands as $brand) {
            CarBrand::firstOrCreate(
                ['name' => $brand['name']],
                $brand
            );
        }

        $this->command->info('Car brands seeded successfully!');
    }
}
