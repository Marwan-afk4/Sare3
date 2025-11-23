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
            'driver_id' => $request->driver_id,
        ]);

        // Check if user has a pending coupon and apply it
        if ($user->pending_coupon_id) {
            $pendingCoupon = \App\Models\Coupon::find($user->pending_coupon_id);
            if ($pendingCoupon && $pendingCoupon->isValid() && $pendingCoupon->canBeUsedByUser($user)) {
                $pendingCoupon->applyToRide($ride);
                // Clear the pending coupon from user
                $user->update(['pending_coupon_id' => null]);
                // Refresh ride to get updated data
                $ride->refresh();
            } else {
                // Clear invalid pending coupon
                $user->update(['pending_coupon_id' => null]);
            }
        }

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
                'initial_price' => (float)($ride->calculated_initial_price ?? $price),
                'coupon_discount' => (float)($ride->coupon_discount ?? 0),
                'final_price' => (float)($ride->calculated_final_price ?? $ride->calculated_initial_price ?? $price),
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

        // // // Schedule auto-reject job
        // $timeoutSeconds = config('ride.auto_reject_timeout_seconds', 15);
        // AutoRejectRideJob::dispatch(
        //     $ride->id,
        //     $request->driver_id,
        //     $ride->updated_at->format('Y-m-d H:i:s')
        // )->delay(now()->addSeconds($timeoutSeconds));

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


    public function getEligibleDrivers($userPickupLat, $userPickupLng, $excludedDriverIds = [], $carCategoryId = null)
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
                    $driverIdValue = $driverData['id'] ?? null;
                    $lat = $driverData['latitude'] ?? null;
                    $lng = $driverData['longitude'] ?? null;

                    // ❌ Skip invalid or missing data
                    if (!$driverIdValue || !$lat || !$lng) {
                        Log::warning("Skipping invalid driver record", ['driverId' => $driverId, 'data' => $driverData]);
                        continue;
                    }

                    // ❌ Skip excluded drivers
                    if (in_array($driverIdValue, $excludedDriverIds)) {
                        continue;
                    }

                    $settings = $driverData['settings'] ?? [];
                    $driverCarCategoryId = $driverData['car_category_id'] ?? null;
                    $pickupRadius = (float)($settings['pickup_radius'] ?? 0.0);

                    // Calculate distance using haversine
                    $distance = $this->haversineDistance(
                        $userPickupLat,
                        $userPickupLng,
                        (float)$lat,
                        (float)$lng
                    );

                    // ❌ Skip if driver is outside pickup radius
                    if ($distance > $pickupRadius) {
                        Log::info("Driver {$driverIdValue} outside pickup radius", [
                            'distance' => $distance,
                            'pickup_radius' => $pickupRadius
                        ]);
                        continue;
                    }

                    // ❌ Skip if car category doesn't match (when category filter is provided)
                    if ($carCategoryId !== null && $driverCarCategoryId != $carCategoryId) {
                        Log::info("Driver {$driverIdValue} car category mismatch", [
                            'driver_category' => $driverCarCategoryId,
                            'requested_category' => $carCategoryId
                        ]);
                        continue;
                    }

                    $driver = [
                        'id' => $driverIdValue,
                        'name' => $driverData['name'] ?? '',
                        'phone_number' => $driverData['phone_number'] ?? '',
                        'photo' => $driverData['photo'] ?? '',
                        'car_color' => $driverData['car_color'] ?? '',
                        'car_model' => $driverData['car_model'] ?? '',
                        'car_photo' => $driverData['car_photo'] ?? '',
                        'plate_number' => $driverData['palete_number'] ?? '',
                        'car_category_id' => $driverCarCategoryId,
                        'latitude' => (float)$lat,
                        'longitude' => (float)$lng,
                        'gender' => $settings['gender'] ?? null,
                        'pickup_radius' => $pickupRadius,
                        'preferred_destination' => $settings['preferred_destination'] ?? '',
                        'distance_to_pickup' => round($distance, 2),
                        'eta_time' => null,
                    ];

                    $eligibleDrivers[] = $driver;
                } catch (\Exception $e) {
                    Log::warning("Invalid driver entry ($driverId): " . $e->getMessage());
                }
            }

            Log::info("Found {count} eligible drivers", [
                'count' => count($eligibleDrivers),
                'car_category_id' => $carCategoryId
            ]);

            return $eligibleDrivers;
        } catch (\Exception $e) {
            Log::error('Error fetching drivers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all drivers sorted by ETA (used for cycling)
     */
    public function getAllDriversSortedByETA($userPickupLat, $userPickupLng, $eligibleDrivers, $rideId = null)
    {
        if (empty($eligibleDrivers)) {
            return [];
        }

        $googleApiKey = config('services.google.maps_api_key');

        if (!$googleApiKey) {
            Log::error('Google Maps API key not configured');
            return [];
        }

        // Build origins (drivers' coordinates)
        $originsArray = collect($eligibleDrivers)->map(
            fn($driver) => $driver['latitude'] . ',' . $driver['longitude']
        )->toArray();
        
        $origins = implode('|', $originsArray);
        $destination = $userPickupLat . ',' . $userPickupLng;

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $origins,
                'destinations' => $destination,
                'key' => $googleApiKey,
            ]);

            if (!$response->successful()) {
                Log::error('Error from Google API: ' . $response->body());
                return [];
            }

            $data = $response->json();
            $rows = $data['rows'] ?? [];

            // Attach ETA to each driver
            foreach ($eligibleDrivers as $i => &$driver) {
                $elements = $rows[$i]['elements'] ?? [];
                if (!empty($elements) && ($elements[0]['status'] ?? '') === 'OK') {
                    $driver['eta_time'] = $elements[0]['duration']['value']; // seconds
                    $driver['eta_seconds'] = $elements[0]['duration']['value'];
                    $driver['eta_minutes'] = round($elements[0]['duration']['value'] / 60, 1);
                    $driver['distance_text'] = $elements[0]['distance']['text'] ?? 'N/A';
                } else {
                    $driver['eta_time'] = null;
                }
            }
            unset($driver); // Break reference

            // Filter out invalid ETAs
            $validDrivers = array_filter($eligibleDrivers, fn($driver) => $driver['eta_time'] !== null);

            // Sort by ETA ascending
            usort($validDrivers, fn($a, $b) => $a['eta_time'] <=> $b['eta_time']);

            Log::info("🔄 Sorted all " . count($validDrivers) . " drivers by ETA for cycling");

            return array_values($validDrivers);
        } catch (\Exception $e) {
            Log::error('Error calling Google Distance Matrix API for cycling: ' . $e->getMessage());
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

        // Build origins (drivers' coordinates)
        $originsArray = collect($eligibleDrivers)->map(
            fn($driver) =>
            $driver['latitude'] . ',' . $driver['longitude']
        )->toArray();
        
        $origins = implode('|', $originsArray);
        $destination = $userPickupLat . ',' . $userPickupLng;

        // 🔍 Log request details
        Log::info("📡 Sending request to Google Distance Matrix API:");
        Log::info("   Destination (pickup): {$destination}");
        Log::info("   Origins (" . count($originsArray) . " drivers):");
        foreach ($eligibleDrivers as $index => $driver) {
            Log::info("      [{$index}] Driver {$driver['id']} ({$driver['name']}): {$originsArray[$index]}");
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $origins,
                'destinations' => $destination,
                'key' => $googleApiKey,
            ]);

            if (!$response->successful()) {
                Log::error('Error from Google API: ' . $response->body());
                return null;
            }

            $data = $response->json();
            $rows = $data['rows'] ?? [];

            // 🔍 Log raw Google API response for debugging
            Log::info("🌐 Google Distance Matrix API Response:", [
                'status' => $data['status'] ?? 'UNKNOWN',
                'origin_addresses' => $data['origin_addresses'] ?? [],
                'destination_addresses' => $data['destination_addresses'] ?? [],
                'rows_count' => count($rows)
            ]);

            // Log each row result
            foreach ($rows as $index => $row) {
                $elements = $row['elements'] ?? [];
                foreach ($elements as $elemIndex => $element) {
                    Log::info("   Row {$index}, Element {$elemIndex}: Status=" . ($element['status'] ?? 'UNKNOWN') . 
                              ", Duration=" . ($element['duration']['value'] ?? 'N/A') . 
                              ", Distance=" . ($element['distance']['text'] ?? 'N/A'));
                }
            }

            // Attach ETA to each driver
            Log::info("🔗 Mapping Google API rows to drivers:");
            foreach ($eligibleDrivers as $i => &$driver) {
                $elements = $rows[$i]['elements'] ?? [];
                $elementStatus = $elements[0]['status'] ?? 'MISSING';
                
                Log::info("   Index {$i}: Driver ID {$driver['id']} ({$driver['name']}) → Row {$i} Status: {$elementStatus}");
                
                if (!empty($elements) && ($elements[0]['status'] ?? '') === 'OK') {
                    $driver['eta_time'] = $elements[0]['duration']['value']; // seconds
                    $driver['distance_text'] = $elements[0]['distance']['text'] ?? 'N/A';
                    $driver['distance_value'] = $elements[0]['distance']['value'] ?? 0; // meters
                    Log::info("      ✅ Assigned ETA: {$driver['eta_time']} sec, Distance: {$driver['distance_text']}");
                } else {
                    $driver['eta_time'] = null;
                    $driver['distance_text'] = 'N/A';
                    $driver['distance_value'] = 0;
                    Log::warning("      ❌ Failed to get ETA - Status: {$elementStatus}");
                }
            }
            unset($driver); // ✅ CRITICAL: Break the reference to prevent array corruption

            // 🔍 Log all drivers with their calculated ETAs
            Log::info("📊 ETA calculation results for " . count($eligibleDrivers) . " drivers:");
            foreach ($eligibleDrivers as $driver) {
                $etaMinutes = $driver['eta_time'] ? round($driver['eta_time'] / 60, 1) : 'FAILED';
                $status = $driver['eta_time'] ? '✅' : '❌';
                Log::info("   {$status} Driver ID: {$driver['id']} | Name: {$driver['name']} | ETA: {$etaMinutes} min ({$driver['eta_time']} sec) | Distance: {$driver['distance_text']}");
            }

            // Filter out invalid ETAs
            $validDrivers = array_filter($eligibleDrivers, fn($driver) => $driver['eta_time'] !== null);
            
            if (count($validDrivers) < count($eligibleDrivers)) {
                Log::warning("⚠️ Filtered out " . (count($eligibleDrivers) - count($validDrivers)) . " drivers with invalid ETAs");
            }

            // Sort by ETA ascending
            usort($validDrivers, fn($a, $b) => $a['eta_time'] <=> $b['eta_time']);

            // 🏆 Log sorted drivers (best to worst)
            Log::info("🏆 Drivers sorted by ETA (best first):");
            foreach ($validDrivers as $index => $driver) {
                $etaMinutes = round($driver['eta_time'] / 60, 1);
                Log::info("   #" . ($index + 1) . " Driver ID: {$driver['id']} | Name: {$driver['name']} | ETA: {$etaMinutes} min | Distance: {$driver['distance_text']}");
            }
            
            $eligibleDrivers = $validDrivers;

            $nearestDriver = $eligibleDrivers[0] ?? null;

            if (!$nearestDriver) {
                return null;
            }

            // ✅ Update Firebase if ride ID exists
            if ($rideId) {
                try {
                    $firebase = (new Factory)
                        ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                        ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                        ->createDatabase();

                    $firebaseRideId = 'ride_' . $rideId;

                    $firebase->getReference("rides/$firebaseRideId")->update([
                        'driver_eta_seconds' => $nearestDriver['eta_time'],
                        'driver_eta_minutes' => round($nearestDriver['eta_time'] / 60, 1),
                        'driver_id' => $nearestDriver['id'] ?? null,
                    ]);

                    Log::info("Updated Firebase with ETA for ride {$rideId}: {$nearestDriver['eta_time']} seconds");
                } catch (\Exception $e) {
                    Log::error("Failed to update Firebase for ride {$rideId}: " . $e->getMessage());
                }
            }

            // ✅ Return both driver ID and ETA
            return [
                'driver_id' => $nearestDriver['id'] ?? null,
                'eta_seconds' => $nearestDriver['eta_time'],
                'eta_minutes' => round($nearestDriver['eta_time'] / 60, 1),
                'driver' => $nearestDriver, // optional full driver info
            ];
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
                [], // Get all drivers first
                $ride->car_category_id // Filter by car category
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

            // Normal flow - exclude rejected drivers
            $eligibleDrivers = array_filter($allDrivers, function ($driver) use ($excludedDriverIds) {
                return !in_array($driver['id'], $excludedDriverIds);
            });

            if (empty($eligibleDrivers) || $shouldCycleDrivers) {
                Log::info("🔄 All drivers rejected ride {$ride->id}, using round-robin cycling");
                
                // When cycling: sort all drivers by ETA and pick the NEXT one, not the first
                $allDriversWithETA = $this->getAllDriversSortedByETA(
                    $ride->pickup_lat,
                    $ride->pickup_lng,
                    $allDrivers,
                    $ride->id
                );
                
                if (empty($allDriversWithETA)) {
                    Log::error("No drivers available with valid ETA for ride {$ride->id}");
                    return null;
                }
                
                // Find the next driver to assign (round-robin through sorted list)
                $lastDriverId = end($excludedDriverIds);
                $lastDriverIndex = -1;
                
                foreach ($allDriversWithETA as $index => $driver) {
                    if ($driver['id'] === $lastDriverId) {
                        $lastDriverIndex = $index;
                        break;
                    }
                }
                
                // Pick next driver in cycle (wrap around if at end)
                $nextIndex = ($lastDriverIndex + 1) % count($allDriversWithETA);
                $selectedDriver = $allDriversWithETA[$nextIndex];
                
                Log::info("🔄 Cycling: Last driver was at index {$lastDriverIndex}, selecting driver at index {$nextIndex} (ID: {$selectedDriver['id']})");
                
                // Format the driver data to match expected structure
                $nearestDriver = [
                    'driver_id' => $selectedDriver['id'],
                    'eta_seconds' => $selectedDriver['eta_seconds'] ?? $selectedDriver['eta_time'],
                    'eta_minutes' => $selectedDriver['eta_minutes'] ?? round($selectedDriver['eta_time'] / 60, 1),
                    'driver' => $selectedDriver,
                ];
                
                // Clear rejected list if we've completed a full cycle
                if ($nextIndex === 0 && $lastDriverIndex >= 0) {
                    Log::info("🔄 Full cycle completed, clearing rejected drivers list");
                    $ride->update(['rejected_drivers' => []]);
                }
            } else {
                // ✅ CRITICAL: Re-index array to have sequential keys [0,1,2...] instead of [0,2,4...]
                // This is necessary because Google API returns rows in sequential order
                $eligibleDrivers = array_values($eligibleDrivers);

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

            $driverId = $nearestDriver['driver_id'] ?? $nearestDriver['id'] ?? null;
            
            if (!$driverId) {
                Log::error("Invalid nearest driver structure for ride {$ride->id}: " . json_encode($nearestDriver));
                return null;
            }

            Log::info("Found driver {$driverId} for ride {$ride->id}" .
                ($shouldCycleDrivers ? " (cycling after " . count($excludedDriverIds) . " rejections)" : ""));

            // Update ride with new driver
            $ride->update([
                'driver_id' => $driverId,
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

            $driverRating = Rating::where('ratee_id', $driverId)
                ->where('ratee_type', 'driver')
                ->avg('rate');

            $firebase->getReference("rides/$firebaseRideId")->update([
                'driver_id' => $driverId,
                'driver_rating' => round($driverRating ?? 0, 1),
                'status' => 'pending',
                'reassigned_at' => now()->toIso8601String(),
                'previous_rejections' => count($excludedDriverIds),
                'is_cycling' => $shouldCycleDrivers,
            ]);

            // ✅ Send push notification to new driver
            $driver = User::find($driverId);
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
                Log::warning("No FCM token found for driver {$driverId}");
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
