<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarType;
use App\Models\CarModel;
use App\Models\CarCategory;

class CarTypeYearRangeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get first car model and category for demonstration
        $carModel = CarModel::first();
        $carCategory = CarCategory::first();

        if (!$carModel || !$carCategory) {
            $this->command->info('Please ensure you have at least one car model and category before running this seeder.');
            return;
        }

        // Create sample car types with different year ranges
        $carTypes = [
            [
                'type_name' => 'Sedan 2020-2025',
                'year_from' => 2020,
                'year_to' => 2025,
                'description' => 'Modern sedan models from 2020 to 2025'
            ],
            [
                'type_name' => 'SUV 2018+',
                'year_from' => 2018,
                'year_to' => null,
                'description' => 'SUV models from 2018 onwards'
            ],
            [
                'type_name' => 'Hatchback Up to 2022',
                'year_from' => null,
                'year_to' => 2022,
                'description' => 'Hatchback models up to 2022'
            ],
            [
                'type_name' => 'Classic Car',
                'year_from' => null,
                'year_to' => null,
                'description' => 'Classic cars with no year restriction'
            ]
        ];

        foreach ($carTypes as $typeData) {
            $carType = CarType::create([
                'car_model_id' => $carModel->id,
                'type_name' => $typeData['type_name'],
                'year_from' => $typeData['year_from'],
                'year_to' => $typeData['year_to'],
                'description' => $typeData['description']
            ]);

            // Attach the category
            $carType->carCategories()->attach($carCategory->id);

            $this->command->info("Created car type: {$typeData['type_name']} with year range: {$carType->year_range}");
        }
    }
}
