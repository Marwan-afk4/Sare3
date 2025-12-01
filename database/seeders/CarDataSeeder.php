<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarCategory;
use App\Models\CarModel;
use App\Models\CarType;

class CarDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Car Categories
        $categories = [
            [
                'name' => 'اقتصادي',
                'description' => 'فئة السيارات الاقتصادية',
                'base_price' => 10.00,
                'price_per_km' => 2.00,
                'price_per_time' => 0.50,
                'inital_price' => 5.00,
                'final_price' => 10.00,
            ],
            [
                'name' => 'مريح',
                'description' => 'فئة السيارات المريحة',
                'base_price' => 15.00,
                'price_per_km' => 3.00,
                'price_per_time' => 0.75,
                'inital_price' => 8.00,
                'final_price' => 15.00,
            ],
            [
                'name' => 'فاخر',
                'description' => 'فئة السيارات الفاخرة',
                'base_price' => 25.00,
                'price_per_km' => 5.00,
                'price_per_time' => 1.50,
                'inital_price' => 12.00,
                'final_price' => 25.00,
            ],
        ];

        foreach ($categories as $category) {
            CarCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $this->command->info('Car categories created successfully.');

        // Create Car Models
        $models = [
            ['name' => 'Toyota Camry'],
            ['name' => 'Honda Accord'],
            ['name' => 'Hyundai Sonata'],
            ['name' => 'Nissan Altima'],
            ['name' => 'Chevrolet Malibu'],
            ['name' => 'Ford Fusion'],
            ['name' => 'Kia Optima'],
            ['name' => 'Mercedes-Benz E-Class'],
            ['name' => 'BMW 5 Series'],
            ['name' => 'Lexus ES'],
        ];

        foreach ($models as $model) {
            CarModel::firstOrCreate($model);
        }

        $this->command->info('Car models created successfully.');

        // Create Car Types (each needs a car_model_id)
        // Get the first model to associate types with
        $firstModel = CarModel::first();
        
        if ($firstModel) {
            $types = [
                ['type_name' => 'سيدان', 'description' => 'سيارة سيدان', 'car_model_id' => $firstModel->id],
                ['type_name' => 'SUV', 'description' => 'سيارة دفع رباعي', 'car_model_id' => $firstModel->id],
                ['type_name' => 'هاتشباك', 'description' => 'سيارة هاتشباك', 'car_model_id' => $firstModel->id],
                ['type_name' => 'كوبيه', 'description' => 'سيارة كوبيه', 'car_model_id' => $firstModel->id],
                ['type_name' => 'فان', 'description' => 'سيارة فان', 'car_model_id' => $firstModel->id],
            ];

            foreach ($types as $type) {
                CarType::firstOrCreate(
                    ['type_name' => $type['type_name']],
                    $type
                );
            }

            $this->command->info('Car types created successfully.');
        } else {
            $this->command->error('No car models found. Cannot create car types.');
        }
        
        $this->command->info('Car data seeding completed!');
    }
}

