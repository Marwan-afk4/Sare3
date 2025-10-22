<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Ride;
use App\Models\DriverCar;
use App\Models\CarCategory;
use App\Enums\RideStatus;

class TestRidesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();
        if (!$user) {
            $this->command->error('Test user not found. Please ensure DatabaseSeeder created it.');
            return;
        }

        // Find any existing drivers (created by TestDriversSeeder) and their car categories
        $drivers = User::where('role', 'driver')->get();
        if ($drivers->isEmpty()) {
            $this->command->error('No drivers found. Run TestDriversSeeder first.');
            return;
        }

        $carCategoryIds = CarCategory::pluck('id')->all();
        if (empty($carCategoryIds)) {
            $this->command->error('No car categories found. Seed car categories first.');
            return;
        }

        // Simple Riyadh-ish coordinates for demo rides
        $pickupPoints = [
            [24.7136, 46.6753, 'Olaya, Riyadh'],
            [24.6926, 46.7240, 'King Saud University, Riyadh'],
            [24.7743, 46.7386, 'Riyadh Park Mall'],
            [24.7130, 46.6850, 'Kingdom Centre'],
        ];

        $dropoffPoints = [
            [24.7743, 46.7386, 'Riyadh Park Mall'],
            [24.6300, 46.7160, 'Diriyah'],
            [24.7250, 46.7000, 'King Abdullah Park'],
            [24.6869, 46.7222, 'Al Faisaliyah'],
        ];

        // Create a few rides per driver
        $createdCount = 0;
        foreach ($drivers as $index => $driver) {
            // Try to match driver's car category if available
            $driverCar = DriverCar::where('driver_id', $driver->id)->first();
            $carCategoryId = $driverCar?->car_categories_id ?? ($carCategoryIds[$index % count($carCategoryIds)]);

            for ($i = 0; $i < 3; $i++) {
                $p = $pickupPoints[($index + $i) % count($pickupPoints)];
                $d = $dropoffPoints[($index + $i) % count($dropoffPoints)];

                $estimatedKm = rand(3, 20) + (rand(0, 99) / 100);
                $estimatedTime = rand(10, 45);
                $initialPrice = round($estimatedKm * 5 + rand(0, 500) / 100, 2);
                $finalPrice = $initialPrice + rand(0, 300) / 100;

                Ride::create([
                    'user_id' => $user->id,
                    'driver_id' => $driver->id,
                    'car_category_id' => $carCategoryId,
                    'pickup_lat' => $p[0],
                    'pickup_lng' => $p[1],
                    'pickup_address' => $p[2],
                    'dropoff_lat' => $d[0],
                    'dropoff_lng' => $d[1],
                    'dropoff_address' => $d[2],
                    'status' => RideStatus::values()[array_rand(RideStatus::values())],
                    'estimated_km' => $estimatedKm,
                    'estimated_time' => $estimatedTime,
                    'calculated_initial_price' => $initialPrice,
                    'route_points' => null,
                    'calculated_final_price' => $finalPrice,
                    'total_distance_in_km' => number_format($estimatedKm, 2),
                    'started_at' => now()->subDays(rand(0, 10))->subMinutes(rand(0, 240)),
                    'ended_at' => now()->subDays(rand(0, 10)),
                    'time_taken' => $estimatedTime . ' mins',
                    'firebase_ride_id' => null,
                    'payment_method_id' => null,
                    'rejected_drivers' => null,
                    'verification_code' => null,
                    'verification_code_generated_at' => null,
                    'verification_code_verified' => false,
                    'coupon_id' => null,
                    'coupon_discount' => 0,
                    'cancellation_reason_id' => null,
                    'zone_id' => $driver->zone_id,
                ]);

                $createdCount++;
            }
        }

        $this->command->info("Created {$createdCount} test rides.");
    }
}


