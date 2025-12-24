<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ride;
use App\Models\User;
use App\Models\CarCategory;
use Carbon\Carbon;

class TestRideSeeder extends Seeder
{
    public function run()
    {
        // Get a user and driver
        $user = User::where('role', 'user')->first();
        $driver = User::where('role', 'driver')->first();
        $carCategory = CarCategory::first();

        if (!$user || !$driver || !$carCategory) {
            echo "⚠️  Please ensure you have at least one user, driver, and car category in the database.\n";
            return;
        }

        // Create a ride with in_progress status
        $ride = Ride::create([
            'user_id' => $user->id,
            'driver_id' => $driver->id,
            'car_category_id' => $carCategory->id,
            'pickup_lat' => 30.0444,
            'pickup_lng' => 31.2357,
            'pickup_address' => 'Cairo, Egypt - Test Pickup Location',
            'dropoff_lat' => 30.0626,
            'dropoff_lng' => 31.2497,
            'dropoff_address' => 'Giza, Egypt - Test Dropoff Location',
            'status' => 'in_progress',
            'estimated_km' => 15.5,
            'estimated_time' => 25,
            'calculated_initial_price' => 150.00,
            'calculated_final_price' => 150.00,
            'route_points' => [
                ['lat' => 30.0444, 'lng' => 31.2357],
                ['lat' => 30.0626, 'lng' => 31.2497]
            ],
            'started_at' => Carbon::now()->subMinutes(10), // Started 10 minutes ago
            'trip_started_at' => Carbon::now()->subMinutes(10),
            'accepted_at' => Carbon::now()->subMinutes(15),
            'firebase_ride_id' => 'ride_test_' . time(),
        ]);

        echo "✅ Test ride created successfully!\n";
        echo "   Ride ID: {$ride->id}\n";
        echo "   Status: {$ride->status->value}\n";
        echo "   User: {$user->name}\n";
        echo "   Driver: {$driver->name}\n";
        echo "   Started at: {$ride->started_at}\n";
        echo "\n";
        echo "You can now change the status to 'cancelled' to test the time calculation.\n";
    }
}
