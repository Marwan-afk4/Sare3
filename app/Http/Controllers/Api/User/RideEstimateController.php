<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\CarCategory;
use App\Models\Ride;
use App\Models\RideEstimate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use App\Helpers\RideHelper;
use App\Models\CancellationPolicy;
use App\Models\Rating;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RideEstimateController extends Controller
{

    public function estimateForAllCategories(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'estimated_km' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|numeric|min:0',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $estimatedKm = $request->estimated_km;
        $estimatedTime = $request->estimated_time;

        $carCategories = CarCategory::all();

        $result = $carCategories->map(function ($category) use ($estimatedKm, $estimatedTime) {
            $base = $category->base_price;
            $perKm = $category->price_per_km;
            $perTime = $category->price_per_time;

            $price = $base + ($estimatedKm * $perKm) + ($estimatedTime * $perTime);

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'estimated_price' => round($price, 2),
                'estimated_time' => $estimatedTime,
                'icon_url' => $category->getIconUrlAttribute()
            ];
        });

        return response()->json([
            'message' => 'Success',
            'data' => $result
        ]);
    }


    public function createRide(Request $request)
    {
        $user = $request->user();

        $validation = Validator::make($request->all(), [
            'driver_id' => 'required|exists:users,id',
            'car_category_id' => 'required|exists:car_categories,id',
            'estimated_km' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|numeric|min:0',
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
            'dropoff_address' => 'nullable|string',
            'pickup_address' => 'nullable|string',
            'payment_method_id' => 'nullable|exists:paymenent_methods,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()], 500);
        }

        //check if there is a cancellation Policy
        $cancellationPolicy = CancellationPolicy::all();
        $policyExists = false;

        if ($cancellationPolicy->isEmpty()) {
            $policyExists = false;
        } else {
            $policyExists = true;
        }

        $driver = User::with('driverCars')->findOrFail($request->driver_id);

        $carCategory = CarCategory::find($request->car_category_id);

        $price = ($carCategory->base_price + ($request->estimated_km * $carCategory->price_per_km) + ($request->estimated_time * $carCategory->price_per_time));

        // Calculate average rating for the user
        $userRating = Rating::where('ratee_id', $user->id)
            ->where('ratee_type', 'user')
            ->avg('rate');

        // Calculate average rating for the driver
        $driverRating = Rating::where('ratee_id', $driver->id)
            ->where('ratee_type', 'driver')
            ->avg('rate');




        RideEstimate::create([
            'user_id' => $user->id,
            'car_category_id' => $request->car_category_id,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'estimated_km' => $request->estimated_km,
            'estimated_time' => $request->estimated_time,
            'calculated_price' => $price,
        ]);

        $ride = Ride::create([
            'user_id' => $user->id,
            'car_category_id' => $request->car_category_id,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'status' => 'pending',
            'estimated_km' => $request->estimated_km,
            'estimated_time' => $request->estimated_time,
            'calculated_initial_price' => $price,
            'pickup_address' => $request->pickup_address,
            'dropoff_address' => $request->dropoff_address,
            'payment_method_id' => $request->payment_method_id,
        ]);

        $firebaseRideId = 'ride_' . $ride->id;

        try {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                ->createDatabase();

            $firebaseData = [
                'ride_id' => $ride->id,
                'user' => [
                    'user_id' => $user,
                    'user_name' => $user->name,
                    'user_phone' => $user->phone,
                    'user_email' => $user->email,
                    'user_image' => $user->image,
                    'user_rating' => round($userRating ?? 0, 1),
                ],
                'driver_id' => $request->driver_id,
                'driver_rating' => round($driverRating ?? 0, 1),
                'car_category_id' => $ride->car_category_id,
                'pickup' => [
                    'lat' => $ride->pickup_lat,
                    'lng' => $ride->pickup_lng,
                    'address' => $request->pickup_address,
                ],
                'dropoff' => [
                    'lat' => $ride->dropoff_lat,
                    'lng' => $ride->dropoff_lng,
                    'address' => $request->dropoff_address,
                ],
                'estimated_time' => $request->estimated_time,
                'estimated_km' => $request->estimated_km,
                'initial_price' => $price,
                'status' => $ride->status,
                'cancellation_policy' => $policyExists,
                'created_at' => now()->toIso8601String(),
            ];

            $firebase->getReference("rides/$firebaseRideId")->set($firebaseData);

            $ride->update([
                'firebase_ride_id' => $firebaseRideId,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ride created, but failed to sync with Firebase', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Ride created successfully',
            'data' => $ride,
        ]);
    }

    private function deg2rad($deg)
    {
        return $deg * (pi() / 180);
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371;
        $dLat = $this->deg2rad($lat2 - $lat1);
        $dLon = $this->deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($this->deg2rad($lat1)) * cos($this->deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    private function getEligibleDrivers($userPickupLat, $userPickupLng, $excludedDriverIds = [])
    {
        try {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                ->createDatabase();

            $driversSnapshot = $firebase->getReference('drivers')->getSnapshot();

            if (!$driversSnapshot->exists()) {
                return [];
            }

            $driversData = $driversSnapshot->getValue();
            $eligibleDrivers = [];

            foreach ($driversData as $driverId => $driverData) {
                try {
                    // Skip excluded drivers
                    if (in_array($driverData['id'] ?? null, $excludedDriverIds)) {
                        continue;
                    }

                    // Parse driver data similar to Flutter model
                    $settings = $driverData['settings'] ?? [];

                    $driver = [
                        'id' => $driverData['id'],
                        'name' => $driverData['name'] ?? '',
                        'phone_number' => $driverData['phone_number'] ?? '',
                        'photo' => $driverData['photo'] ?? '',
                        'car_color' => $driverData['car_color'] ?? '',
                        'car_model' => $driverData['car_model'] ?? '',
                        'car_photo' => $driverData['car_photo'] ?? '',
                        'plate_number' => $driverData['palete_number'] ?? '', // Note: keeping original typo from Flutter
                        'latitude' => (float)$driverData['latitude'],
                        'longitude' => (float)$driverData['longitude'],
                        'gender' => $settings['gender'] ?? null,
                        'pickup_radius' => (float)($settings['pickup_radius'] ?? 0.0),
                        'preferred_destination' => $settings['preferred_destination'] ?? '',
                        'eta_time' => null,
                    ];

                    $eligibleDrivers[] = $driver;
                } catch (\Exception $e) {
                    Log::warning("Invalid driver entry ($driverId): " . $e->getMessage());
                }
            }

            return $eligibleDrivers;
        } catch (\Exception $e) {
            Log::error('Error fetching drivers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find nearest driver by ETA using Google Distance Matrix API
     */
    private function findNearestDriverByETA($userPickupLat, $userPickupLng, $eligibleDrivers)
    {
        if (empty($eligibleDrivers)) {
            return null;
        }

        $googleApiKey = config('services.google.maps_api_key');
        Log::info('Google API Key loaded', ['key' => $googleApiKey]);

        if (!$googleApiKey) {
            Log::error('Google Maps API key not configured');
            return null;
        }

        $origins = collect($eligibleDrivers)->map(function ($driver) {
            return $driver['latitude'] . ',' . $driver['longitude'];
        })->join('|');

        $destination = $userPickupLat . ',' . $userPickupLng;

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $origins,
                'destinations' => $destination,
                'key' => $googleApiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rows = $data['rows'] ?? [];

                // Update drivers with ETA times
                for ($i = 0; $i < count($rows) && $i < count($eligibleDrivers); $i++) {
                    $elements = $rows[$i]['elements'] ?? [];
                    if (!empty($elements) && $elements[0]['status'] === 'OK') {
                        $durationInSec = $elements[0]['duration']['value'];
                        $eligibleDrivers[$i]['eta_time'] = $durationInSec;
                    } else {
                        $eligibleDrivers[$i]['eta_time'] = null;
                    }
                }

                // Filter out drivers without valid ETA
                $eligibleDrivers = array_filter($eligibleDrivers, function ($driver) {
                    return $driver['eta_time'] !== null;
                });

                // Sort by ETA
                usort($eligibleDrivers, function ($a, $b) {
                    return $a['eta_time'] <=> $b['eta_time'];
                });


                return !empty($eligibleDrivers) ? $eligibleDrivers[0] : null;
            } else {
                Log::error('Error from Google API: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error calling Google Distance Matrix API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * When driver rejects a ride
     */
    public function rejectRide(Ride $ride, $driverId)
    {
        $rejected = $ride->rejected_drivers ?? [];
        $rejected[] = $driverId;

        $ride->update([
            'rejected_drivers' => array_unique($rejected),
            'driver_id' => null,
        ]);

        return $this->searchAlternativeDriver($ride);
    }

    /**
     * Search for alternative driver when current driver rejects
     */
    public function searchAlternativeDriver(Ride $ride)
    {
        try {
            // Get excluded driver IDs (drivers who already rejected this ride)
            $excludedDriverIds = $ride->rejected_drivers ?? [];
            if ($ride->driver_id) {
                $excludedDriverIds[] = $ride->driver_id;
            }

            // Get eligible drivers
            $eligibleDrivers = $this->getEligibleDrivers(
                $ride->pickup_lat,
                $ride->pickup_lng,
                array_unique($excludedDriverIds)
            );

            if (empty($eligibleDrivers)) {
                Log::info("No eligible drivers found for ride {$ride->id}");
                return null;
            }

            // Find nearest driver by ETA
            $nearestDriver = $this->findNearestDriverByETA(
                $ride->pickup_lat,
                $ride->pickup_lng,
                $eligibleDrivers
            );

            if (!$nearestDriver) {
                Log::info("No driver found with valid ETA for ride {$ride->id}");
                return null;
            }

            // Update ride with new driver
            $ride->update([
                'driver_id' => $nearestDriver['id'],
                'status' => 'pending', // Reset to pending for new driver
            ]);

            // Update Firebase with new driver info
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                ->createDatabase();

            $firebaseRideId = 'ride_' . $ride->id;

            // Get driver rating
            $driverRating = Rating::where('ratee_id', $nearestDriver['id'])
                ->where('ratee_type', 'driver')
                ->avg('rate');

            $firebase->getReference("rides/$firebaseRideId")->update([
                'driver_id' => $nearestDriver['id'],
                'driver_rating' => round($driverRating ?? 0, 1),
                'status' => 'pending',
                'reassigned_at' => now()->toIso8601String(),
            ]);

            return $nearestDriver;
        } catch (\Exception $e) {
            Log::error("Error searching alternative driver for ride {$ride->id}: " . $e->getMessage());
            return null;
        }
    }
}
