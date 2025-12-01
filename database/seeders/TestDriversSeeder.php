<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DriverCar;
use App\Models\Zone;
use App\Models\CarCategory;
use App\Models\CarModel;
use App\Models\CarType;
use Illuminate\Support\Facades\Hash;

class TestDriversSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, create a test zone if none exists
        if (Zone::count() == 0) {
            $zone = Zone::create([
                'name' => 'Test Zone - City Center',
                'from_lat' => 24.7136,
                'from_lng' => 46.6753,
                'to_lat' => 24.7536,
                'to_lng' => 46.7153,
                'polygon_coordinates' => null
            ]);
        } else {
            $zone = Zone::first();
        }

        // Get existing car data
        $carCategories = CarCategory::all();
        $carModels = CarModel::all();
        $carTypes = CarType::all();

        if ($carCategories->isEmpty() || $carModels->isEmpty() || $carTypes->isEmpty()) {
            $this->command->error('Please ensure car categories, models, and types exist before running this seeder.');
            return;
        }

        // Create test drivers with various locations around Riyadh
        $testDrivers = [
            [
                'name' => 'Ahmed Al-Rashid',
                'email' => 'ahmed.driver@test.com',
                'phone' => '+966501234567',
                'password' => 'password123',
                'role' => 'driver',
                'activity' => 'active',
                'wallet' => 100.00,
                'status' => 'approved',
                'zone_id' => $zone->id,
                'car_data' => [
                    'car_number' => 'ABC-1234',
                    'car_color' => 'White',
                    'car_license' => 'DL123456789'
                ]
            ],
            [
                'name' => 'Mohammed Al-Fahd',
                'email' => 'mohammed.driver@test.com',
                'phone' => '+966501234568',
                'password' => 'password123',
                'role' => 'driver',
                'activity' => 'active',
                'wallet' => 150.00,
                'status' => 'approved',
                'zone_id' => $zone->id,
                'car_data' => [
                    'car_number' => 'XYZ-5678',
                    'car_color' => 'Black',
                    'car_license' => 'DL987654321'
                ]
            ],
            [
                'name' => 'Khalid Al-Otaibi',
                'email' => 'khalid.driver@test.com',
                'phone' => '+966501234569',
                'password' => 'password123',
                'role' => 'driver',
                'activity' => 'active',
                'wallet' => 75.00,
                'status' => 'approved',
                'zone_id' => $zone->id,
                'car_data' => [
                    'car_number' => 'DEF-9012',
                    'car_color' => 'Silver',
                    'car_license' => 'DL456789123'
                ]
            ],
            [
                'name' => 'Omar Al-Harbi',
                'email' => 'omar.driver@test.com',
                'phone' => '+966501234570',
                'password' => 'password123',
                'role' => 'driver',
                'activity' => 'active',
                'wallet' => 200.00,
                'status' => 'approved',
                'zone_id' => $zone->id,
                'car_data' => [
                    'car_number' => 'GHI-3456',
                    'car_color' => 'Blue',
                    'car_license' => 'DL789123456'
                ]
            ],
            [
                'name' => 'Faisal Al-Mutairi',
                'email' => 'faisal.driver@test.com',
                'phone' => '+966501234571',
                'password' => 'password123',
                'role' => 'driver',
                'activity' => 'active',
                'wallet' => 120.00,
                'status' => 'approved',
                'zone_id' => $zone->id,
                'car_data' => [
                    'car_number' => 'JKL-7890',
                    'car_color' => 'Red',
                    'car_license' => 'DL321654987'
                ]
            ],
            [
                'name' => 'Abdullah Al-Shammari',
                'email' => 'abdullah.driver@test.com',
                'phone' => '+966501234572',
                'password' => 'password123',
                'role' => 'driver',
                'activity' => 'active',
                'wallet' => 180.00,
                'status' => 'approved',
                'zone_id' => $zone->id,
                'car_data' => [
                    'car_number' => 'MNO-2345',
                    'car_color' => 'Gray',
                    'car_license' => 'DL654987321'
                ]
            ],
            [
                'name' => 'Saeed Al-Dosari',
                'email' => 'saeed.driver@test.com',
                'phone' => '+966501234573',
                'password' => 'password123',
                'role' => 'driver',
                'activity' => 'active',
                'wallet' => 90.00,
                'status' => 'approved',
                'zone_id' => $zone->id,
                'car_data' => [
                    'car_number' => 'PQR-6789',
                    'car_color' => 'White',
                    'car_license' => 'DL987321654'
                ]
            ],
            [
                'name' => 'Yasser Al-Ghamdi',
                'email' => 'yasser.driver@test.com',
                'phone' => '+966501234574',
                'password' => 'password123',
                'role' => 'driver',
                'activity' => 'active',
                'wallet' => 250.00,
                'status' => 'approved',
                'zone_id' => $zone->id,
                'car_data' => [
                    'car_number' => 'STU-1357',
                    'car_color' => 'Black',
                    'car_license' => 'DL321987654'
                ]
            ]
        ];

        foreach ($testDrivers as $index => $driverData) {
            // Check if driver already exists
            $existingDriver = User::where('email', $driverData['email'])
                                ->orWhere('phone', $driverData['phone'])
                                ->first();

            if ($existingDriver) {
                $this->command->info("Driver {$driverData['name']} already exists, skipping...");
                continue;
            }

            // Create the driver
            $carData = $driverData['car_data'];
            unset($driverData['car_data']);

            $driver = User::create($driverData);

            // Assign car to driver
            // Rotate through available car categories, models, and types
            $categoryIndex = $index % $carCategories->count();
            $modelIndex = $index % $carModels->count();
            $typeIndex = $index % $carTypes->count();

            $selectedCategory = $carCategories[$categoryIndex];
            $selectedModel = $carModels[$modelIndex];
            $selectedType = $carTypes[$typeIndex];

            DriverCar::create([
                'driver_id' => $driver->id,
                'car_categories_id' => $selectedCategory->id,
                'car_model_id' => $selectedModel->id,
                'car_type_id' => $selectedType->id,
                'car_number' => $carData['car_number'],
                'car_color' => $carData['car_color'],
                'car_license' => $carData['car_license'],
                'car_image' => null, // Can be added later
            ]);

            $this->command->info("Created driver: {$driver->name} with car {$carData['car_number']} ({$selectedCategory->name} - {$selectedModel->name} - {$selectedType->type_name})");
        }

        $this->command->info('Test drivers seeder completed successfully!');
    }
}
