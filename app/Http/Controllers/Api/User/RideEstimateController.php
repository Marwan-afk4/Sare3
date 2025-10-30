<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\FcmHelper;
use App\Http\Controllers\Controller;
use App\Models\CarCategory;
use App\Models\Ride;
use App\Models\RideEstimate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use App\Helpers\RideHelper;
use App\Jobs\AutoRejectRideJob;
use App\Models\CancellationPolicy;
use App\Models\Rating;
use App\Models\RideRequestTimeLimit;
use App\Models\Zone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RideEstimateController extends Controller
{

    public function zones()
    {
        $zones = Zone::all();

        return response()->json([
            'message' => 'Success',
            'data' => $zones
        ]);
    }

    public function estimateForAllCategories(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
            'estimated_km' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|numeric|min:0',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $user = $request->user();
        $zoneId = $request->zone_id;
        $estimatedKm = $request->estimated_km ?? 0;
        $estimatedTime = $request->estimated_time ?? 0;

        // هات الـ Zone مع الكاتيجوريز المربوطة بيه
        $zone = Zone::with('carCategories')->find($zoneId);

        if (!$zone) {
            return response()->json(['message' => 'Zone not found'], 404);
        }

        $rideService = app(\App\Services\RideService::class);

        $result = $zone->carCategories->map(function ($category) use ($estimatedKm, $estimatedTime, $user, $rideService) {
            $base = $category->pivot->base_price;
            $perKm = $category->pivot->price_per_km;
            $perTime = $category->pivot->price_per_min;
            $minPrice = $category->pivot->min_price; // لو عندك كولمن min_price في الجدول الوسيط

            $price = $base + ($estimatedKm * $perKm) + ($estimatedTime * $perTime);

            // لو السعر النهائي أقل من المينيمم
            if ($price < $minPrice) {
                $price = $minPrice;
            }

            // Calculate discount preview
            $estimateWithDiscount = $rideService->getRideEstimateWithDiscount($user, $price);

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'estimated_price' => round($price, 2),
                'estimated_time' => $estimatedTime,
                'icon_url' => $category->getIconUrlAttribute(),
                'discount_preview' => $estimateWithDiscount['discount_preview']
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
            'zone_id' => 'required|exists:zones,id',
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
            'driver_eta_minutes' => 'nullable|numeric|min:0',
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

        // Get zone with car categories to use zone-specific pricing
        $zone = Zone::with('carCategories')->find($request->zone_id);

        if (!$zone) {
            return response()->json(['message' => 'Zone not found'], 404);
        }

        // Find the specific category in this zone
        $categoryInZone = $zone->carCategories->where('id', $request->car_category_id)->first();

        if (!$categoryInZone) {
            return response()->json(['message' => 'Car category not available in this zone'], 404);
        }

        // Calculate price using zone-specific rates (same logic as estimateForAllCategories)
        $base = $categoryInZone->pivot->base_price;
        $perKm = $categoryInZone->pivot->price_per_km;
        $perTime = $categoryInZone->pivot->price_per_min;
        $minPrice = $categoryInZone->pivot->min_price;

        $estimatedKm = $request->estimated_km ?? 0;
        $estimatedTime = $request->estimated_time ?? 0;

        $price = $base + ($estimatedKm * $perKm) + ($estimatedTime * $perTime);

        // Apply minimum price if calculated price is lower
        if ($price < $minPrice) {
            $price = $minPrice;
        }

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
            'estimated_km' => $estimatedKm,
            'estimated_time' => $estimatedTime,
            'calculated_price' => $price,
        ]);

        $ride = Ride::create([
            'user_id' => $user->id,
            'zone_id' => $request->zone_id,
            'car_category_id' => $request->car_category_id,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'status' => 'pending',
            'estimated_km' => $estimatedKm,
            'estimated_time' => $estimatedTime,
            'calculated_initial_price' => $price,
            'pickup_address' => $request->pickup_address,
            'dropoff_address' => $request->dropoff_address,
            'payment_method_id' => $request->payment_method_id,
            'driver_assigned_at' => now(),
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
                'initial_price' => (float)($price + 0.01),
                'driver_eta_minutes' => $request->driver_eta_minutes,
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

        // // Schedule auto-reject job
        $timeoutSeconds = config('ride.auto_reject_timeout_seconds', 15);
        AutoRejectRideJob::dispatch(
            $ride->id,
            $request->driver_id,
            $ride->updated_at->format('Y-m-d H:i:s')
        )->delay(now()->addSeconds($timeoutSeconds));

        return response()->json([
            'message' => 'Ride created successfully',
            'data' => $ride,
        ]);
    }

    private function deg2rad($deg)
    {
        return $deg * (pi() / 180);
    }

    // private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    // {
    //     $R = 6371;
    //     $dLat = $this->deg2rad($lat2 - $lat1);
    //     $dLon = $this->deg2rad($lon2 - $lon1);

    //     $a = sin($dLat / 2) * sin($dLat / 2) +
    //         cos($this->deg2rad($lat1)) * cos($this->deg2rad($lat2)) *
    //         sin($dLon / 2) * sin($dLon / 2);

    //     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    //     return $R * $c;
    // }


    public function getEligibleDrivers($userPickupLat, $userPickupLng, $excludedDriverIds = [])
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
    public function findNearestDriverByETA($userPickupLat, $userPickupLng, $eligibleDrivers, $rideId = null)
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

        // Build origins from drivers
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

                $nearestDriver = !empty($eligibleDrivers) ? $eligibleDrivers[0] : null;

                // Update Firebase with ETA if ride ID is provided and driver is found
                if ($nearestDriver && $rideId) {
                    try {
                        $firebase = (new Factory)
                            ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                            ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                            ->createDatabase();

                        $firebaseRideId = 'ride_' . $rideId;

                        $firebase->getReference("rides/$firebaseRideId")->update([
                            'driver_eta_seconds' => $nearestDriver['eta_time'],
                            'driver_eta_minutes' => round($nearestDriver['eta_time'] / 60, 1),
                        ]);

                        Log::info("Updated Firebase with ETA for ride {$rideId}: {$nearestDriver['eta_time']} seconds");
                    } catch (\Exception $e) {
                        Log::error("Failed to update Firebase with ETA for ride {$rideId}: " . $e->getMessage());
                    }
                }

                return $nearestDriver;
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
     * Search for alternative driver when current driver rejects
     */
    public function searchAlternativeDriver(Ride $ride)
    {
        try {
            // Get excluded driver IDs (drivers who already rejected this ride)
            $excludedDriverIds = $ride->rejected_drivers ?? [];

            // ✅ Add current driver to rejected list if not already
            if ($ride->driver_id && !in_array($ride->driver_id, $excludedDriverIds)) {
                $excludedDriverIds[] = $ride->driver_id;
                $ride->update([
                    'rejected_drivers' => $excludedDriverIds
                ]);
            }

            // Check if we need to cycle back to first drivers (after 12 rejections)
            $shouldCycleDrivers = count($excludedDriverIds) > 12;

            // Get all available drivers
            $allDrivers = $this->getEligibleDrivers(
                $ride->pickup_lat,
                $ride->pickup_lng,
                [] // Get all drivers first
            );

            if (empty($allDrivers)) {
                Log::info("No drivers available at all for ride {$ride->id}");

                $ride->update([
                    'driver_id' => null,
                    'status' => 'pending',
                ]);

                // Firebase update
                $firebase = (new Factory)
                    ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                    ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                    ->createDatabase();

                $firebaseRideId = 'ride_' . $ride->id;

                $firebase->getReference("rides/$firebaseRideId")->update([
                    'driver_id' => null,
                    'status' => 'pending',
                ]);

                return null;
            }

            if ($shouldCycleDrivers) {
                Log::info("Cycling drivers for ride {$ride->id} after " . count($excludedDriverIds) . " rejections");

                // Use all drivers for cycling (ignore previous rejections)
                $eligibleDrivers = $allDrivers;

                // Find nearest driver by ETA from all available drivers
                $nearestDriver = $this->findNearestDriverByETA(
                    $ride->pickup_lat,
                    $ride->pickup_lng,
                    $eligibleDrivers,
                    $ride->id
                );
            } else {
                // Normal flow - exclude rejected drivers
                $eligibleDrivers = array_filter($allDrivers, function($driver) use ($excludedDriverIds) {
                    return !in_array($driver['id'], $excludedDriverIds);
                });

                if (empty($eligibleDrivers)) {
                    Log::info("All available drivers rejected ride {$ride->id}, starting to cycle");

                    // Start cycling - use all drivers
                    $eligibleDrivers = $allDrivers;
                    $shouldCycleDrivers = true;
                }

                // Find nearest driver by ETA
                $nearestDriver = $this->findNearestDriverByETA(
                    $ride->pickup_lat,
                    $ride->pickup_lng,
                    $eligibleDrivers,
                    $ride->id
                );
            }

            if (!$nearestDriver) {
                Log::info("No driver found with valid ETA for ride {$ride->id}");
                return null;
            }

            Log::info("Found driver {$nearestDriver['id']} for ride {$ride->id}" .
                     ($shouldCycleDrivers ? " (cycling after " . count($excludedDriverIds) . " rejections)" : ""));

            // Update ride with new driver
            $ride->update([
                'driver_id' => $nearestDriver['id'],
                'status' => 'pending',
                'reassigned_at' => now(),
                'driver_assigned_at' => now(),
            ]);

            // Update Firebase with new driver info
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                ->createDatabase();

            $firebaseRideId = 'ride_' . $ride->id;

            $driverRating = Rating::where('ratee_id', $nearestDriver['id'])
                ->where('ratee_type', 'driver')
                ->avg('rate');

            $firebase->getReference("rides/$firebaseRideId")->update([
                'driver_id' => $nearestDriver['id'],
                'driver_rating' => round($driverRating ?? 0, 1),
                'status' => 'pending',
                'reassigned_at' => now()->toIso8601String(),
                'previous_rejections' => count($excludedDriverIds),
                'is_cycling' => $shouldCycleDrivers,
            ]);

            // ✅ Send push notification to new driver
            $driver = User::find($nearestDriver['id']);
            if ($driver && $driver->fcm_token) {
                $data = [
                    'title'    => 'New Ride Request',
                    'body'     => 'You have a new ride request!',
                    'msg_type' => 'ride_request',
                    'ride_id'  => (string) $ride->id,
                ];

                $response = FcmHelper::sendPushNotification(
                    $driver->fcm_token,
                    $data['title'],
                    $data['body'],
                    $data
                );

                Log::info("Notification sent to driver {$driver->id}", ['response' => $response]);
            } else {
                Log::warning("No FCM token found for driver {$nearestDriver['id']}");
            }

            return $nearestDriver;
        } catch (\Exception $e) {
            Log::error("Error searching alternative driver for ride {$ride->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
