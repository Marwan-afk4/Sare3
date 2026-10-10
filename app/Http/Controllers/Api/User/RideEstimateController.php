<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\RideStatus;
use App\Events\NewRideRequest;
use App\Helpers\FcmHelper;
use App\Http\Controllers\Controller;
use App\Models\CarCategory;
use App\Models\DriverRideSetting;
use App\Models\Ride;
use App\Models\RideEstimate;
use App\Models\User;
use App\Services\GoogleApiUsageService;
use App\Services\RideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\RideHelper;
use App\Jobs\AutoRejectRideJob;
use App\Models\CancellationPolicy;
use App\Models\Coupon;
use App\Models\Rating;
use App\Services\RideOfferService;
use App\Models\RideRequestTimeLimit;
use App\Models\Zone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
            'pickup_lang' => 'nullable|numeric',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
            'dropoff_lang' => 'nullable|numeric',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $user = $request->user();
        $zoneId = $request->zone_id;
        $pickupLat = $request->pickup_lat;
        $pickupLng = $request->pickup_lng ?? $request->pickup_lang;
        $dropoffLat = $request->dropoff_lat;
        $dropoffLng = $request->dropoff_lng ?? $request->dropoff_lang;

        $estimatedKm = (float) ($request->estimated_km ?? 0);
        $estimatedTime = (float) ($request->estimated_time ?? 0);

        // The rider app already measured this pair with Directions. Re-buying it
        // from Distance Matrix only duplicates the fare distance.
        $hasClientEstimate = $estimatedKm > 0 && $estimatedTime > 0;

        if (!$hasClientEstimate && $pickupLat !== null && $pickupLng !== null && $dropoffLat !== null && $dropoffLng !== null) {
            $routeCacheKey = $this->routeEstimateCacheKey($pickupLat, $pickupLng, $dropoffLat, $dropoffLng);
            $cachedRoute = Cache::get($routeCacheKey);
            if (is_array($cachedRoute) && ($cachedRoute['km'] ?? 0) > 0) {
                $estimatedKm = (float) $cachedRoute['km'];
                $estimatedTime = (float) $cachedRoute['minutes'];
            } else {
            $googleApiKey = config('services.google.maps_api_key');

            if (!$googleApiKey) {
                Log::error('Google Maps API key not configured');
                return response()->json(['message' => 'Google Maps API key not configured'], 500);
            }

            $origin = $pickupLat . ',' . $pickupLng;
            $destination = $dropoffLat . ',' . $dropoffLng;

            try {
                $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                    'origins' => $origin,
                    'destinations' => $destination,
                    'key' => $googleApiKey,
                ]);

                if (!$response->successful()) {
                    Log::error('Error from Google Distance Matrix API in ride-estimate: ' . $response->body());
                    return response()->json(['message' => 'Error from Google API: ' . $response->status()], 520);
                }

                $data = $response->json();

                if (($data['status'] ?? '') === 'OK') {
                    GoogleApiUsageService::recordDistanceMatrix(1, 1);
                }

                if (($data['status'] ?? '') !== 'OK') {
                    Log::error('Google API returned status error: ' . json_encode($data));
                    return response()->json(['message' => 'Google API Error: ' . ($data['status'] ?? 'Unknown status')], 520);
                }

                $rows = $data['rows'] ?? [];
                $elements = $rows[0]['elements'] ?? [];

                if (empty($elements) || ($elements[0]['status'] ?? '') !== 'OK') {
                    Log::error('Google API row elements status error: ' . json_encode($data));
                    return response()->json(['message' => 'Unable to calculate route between pickup and dropoff'], 422);
                }

                $distanceInMeters = $elements[0]['distance']['value'] ?? 0;
                $durationInSeconds = $elements[0]['duration']['value'] ?? 0;

                $estimatedKm = round($distanceInMeters / 1000, 2);
                $estimatedTime = round($durationInSeconds / 60, 2);
                Cache::put($routeCacheKey, [
                    'km' => $estimatedKm,
                    'minutes' => $estimatedTime,
                ], now()->addMinutes(5));

            } catch (\Exception $e) {
                Log::error('Error calling Google Distance Matrix API: ' . $e->getMessage());
                return response()->json(['message' => 'Error calling Google API: ' . $e->getMessage()], 500);
            }
            }
        }

        // هات الـ Zone مع الكاتيجوريز المربوطة بيه
        $zone = Zone::with('carCategories')->find($zoneId);

        if (!$zone) {
            return response()->json(['message' => 'Zone not found'], 404);
        }

        $rideService = app(RideService::class);

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
                'estimated_km' => $estimatedKm,
                'icon_url' => $category->getIconUrlAttribute(),
                'discount_preview' => $estimateWithDiscount['discount_preview']
            ];
        });

        return response()->json([
            'message' => 'Success',
            'estimated_km' => $estimatedKm,
            'estimated_time' => $estimatedTime,
            'data' => $result
        ]);
    }


    public function createRide(Request $request)
    {
        $user = $request->user();

        $hasOpenRide = Ride::where('user_id', $user->id)
            ->whereIn('status', [
                RideStatus::Pending->value,
                RideStatus::Accepted->value,
                RideStatus::WaitingUser->value,
                RideStatus::Arrived->value,
                RideStatus::InProgress->value,
            ])
            ->exists();

        if ($hasOpenRide) {
            return response()->json([
                'message' => 'You already have an open ride.',
            ], 409);
        }

        $validation = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
            'driver_id' => 'nullable|exists:users,id',
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

        // Find nearest eligible driver dynamically using pickup coordinates, database pickup radius, and haversine distance
        $eligibleDrivers = $this->getEligibleDrivers(
            $request->pickup_lat,
            $request->pickup_lng,
            [],
            $request->car_category_id
        );

        if (empty($eligibleDrivers)) {
            return response()->json(['message' => 'No drivers available in your area.'], 404);
        }

        // Sort by distance (nearest first)
        usort($eligibleDrivers, function ($a, $b) {
            return $a['distance_to_pickup'] <=> $b['distance_to_pickup'];
        });

        $nearestDriverData = $eligibleDrivers[0];
        $driverId = $nearestDriverData['id'];

        $driver = User::where('role', 'driver')
            ->where('status', 'approved')
            ->where('is_available', true)
            ->with(['driverCars.carModel', 'driverCars.carType'])
            ->find($driverId);

        if (!$driver) {
            foreach (array_slice($eligibleDrivers, 1) as $candidate) {
                $driver = User::where('role', 'driver')
                    ->where('status', 'approved')
                    ->where('is_available', true)
                    ->with(['driverCars.carModel', 'driverCars.carType'])
                    ->find($candidate['id']);

                if ($driver) {
                    $driverId = $driver->id;
                    break;
                }
            }
        }

        if (!$driver) {
            return response()->json(['message' => 'Nearest driver not found in database'], 404);
        }

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
            'driver_id' => $driverId,
        ]);

        // Log the initial offer to the first captain so the admin dashboard
        // can show who the request was routed through from the very start.
        app(RideOfferService::class)->recordOffer($ride, (int) $driverId, 1, 'initial_assignment');

        // Check if user has a pending coupon and apply it
        if ($user->pending_coupon_id) {
            $pendingCoupon = Coupon::find($user->pending_coupon_id);
            if ($pendingCoupon && $pendingCoupon->isValid() && $pendingCoupon->canBeUsedByUser($user)) {
                $applied = $pendingCoupon->applyToRide($ride);
                if ($applied) {
                    $user->update(['pending_coupon_id' => null]);
                    $ride->refresh();
                }
                // If the ride is already at minimum fare, leave the coupon pending for a later ride
            } else {
                // Clear invalid pending coupon
                $user->update(['pending_coupon_id' => null]);
            }
        }

        // ─── NEW: Broadcast to Driver via WebSockets (Reverb) ─────────────────────
        try {
            broadcast(new NewRideRequest($ride))->toOthers();
            
            // ─── Also send Push Notification (FCM) ───
            if ($driver->fcm_token) {
                $data = array_merge($ride->toOfferArray(), [
                    'title' => 'طلب رحلة جديد',
                    'body' => 'لديك طلب رحلة جديد من ' . $user->name,
                    'msg_type' => 'ride_request',
                ]);
                FcmHelper::sendPushNotification($driver->fcm_token, $data['title'], $data['body'], $data);
            }
        } catch (\Exception $e) {
            Log::error("Failed to notify driver: " . $e->getMessage());
        }
        // ─── END NOTIFICATION ─────────────────────────────────────────────────────

        // Schedule auto-reject job. Without this, the ride's very first
        // driver assignment had no queued timeout at all and relied solely
        // on the `rides:auto-reject` scheduled command as a fallback — which
        // does NOT record offer history (no markIgnored/recordOffer calls),
        // causing drivers who timed out on their first offer to silently
        // disappear from the admin's "Captain Offer History" audit trail.
        $timeoutSeconds = config('ride.auto_reject_timeout_seconds', 15);
        AutoRejectRideJob::dispatch(
            $ride->id,
            $driverId,
            $ride->updated_at->format('Y-m-d H:i:s')
        )->delay(now()->addSeconds($timeoutSeconds));

        $ride->load(['driver' => function ($q) {
            $q->with(['driverCars' => function ($q2) {
                $q2->with(['carModel', 'carType']);
            }]);
        }]);

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
            $driverSettings = DriverRideSetting::pluck('pickup_radius', 'driver_id')->toArray();

            $driversQuery = User::where('role', 'driver')
                ->where('status', 'approved')
                ->where('is_available', true)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');

            if (!empty($excludedDriverIds)) {
                $driversQuery->whereNotIn('id', $excludedDriverIds);
            }

            if ($carCategoryId !== null) {
                $driversQuery->whereHas('driverCars', function ($query) use ($carCategoryId) {
                    $query->where('car_categories_id', $carCategoryId)
                        ->orWhereHas('carCategories', function ($q) use ($carCategoryId) {
                            $q->where('car_categories.id', $carCategoryId);
                        });
                });
            }

            $drivers = $driversQuery
                ->with(['driverCars.carModel', 'driverCars.carType', 'driverCars.carCategories', 'driverRideSetting'])
                ->get();

            $eligibleDrivers = [];

            foreach ($drivers as $driver) {
                $cachedLocation = Cache::get("driver_location:{$driver->id}");
                $lat = isset($cachedLocation['latitude']) ? (float) $cachedLocation['latitude'] : (float) $driver->latitude;
                $lng = isset($cachedLocation['longitude']) ? (float) $cachedLocation['longitude'] : (float) $driver->longitude;

                if (!$lat || !$lng) {
                    continue;
                }

                $pickupRadius = (float) ($driverSettings[$driver->id]
                    ?? $driver->driverRideSetting?->pickup_radius
                    ?? 5.0);

                $distance = $this->haversineDistance(
                    $userPickupLat,
                    $userPickupLng,
                    $lat,
                    $lng
                );

                if ($distance > $pickupRadius) {
                    Log::info("Driver {$driver->id} outside pickup radius", [
                        'distance' => $distance,
                        'pickup_radius' => $pickupRadius,
                    ]);
                    continue;
                }

                $driverCar = $driver->driverCars->first();

                $eligibleDrivers[] = [
                    'id' => $driver->id,
                    'name' => $driver->name ?? '',
                    'phone_number' => $driver->phone ?? '',
                    'photo' => $driver->image_link ?? '',
                    'car_color' => $driverCar?->car_color ?? '',
                    'car_model' => $driverCar?->carModel?->name ?? '',
                    'car_photo' => $driverCar?->car_image_link ?? '',
                    'plate_number' => $driverCar?->car_number ?? '',
                    'car_category_id' => $driverCar ? (int) ($carCategoryId ?? ($driverCar->car_categories_id ?? $driverCar->carCategories->pluck('id')->first())) : null,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'gender' => $driver->gender,
                    'pickup_radius' => $pickupRadius,
                    'preferred_destination' => $driver->driverRideSetting?->destination_preferences ?? '',
                    'distance_to_pickup' => round($distance, 2),
                    'eta_time' => null,
                ];
            }

            Log::info('Found ' . count($eligibleDrivers) . ' eligible drivers', [
                'count' => count($eligibleDrivers),
                'car_category_id' => $carCategoryId,
            ]);

            return $eligibleDrivers;
        } catch (\Exception $e) {
            Log::error('Error fetching drivers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Straight-line order for cycling through drivers who already rejected.
     * Road ETAs are not bought from Google on every lap.
     */
    public function getAllDriversSortedByETA($userPickupLat, $userPickupLng, $eligibleDrivers, $rideId = null)
    {
        if (empty($eligibleDrivers)) {
            return [];
        }

        $drivers = array_values($eligibleDrivers);
        usort($drivers, fn ($a, $b) => ($a['distance_to_pickup'] ?? 0) <=> ($b['distance_to_pickup'] ?? 0));

        foreach ($drivers as &$driver) {
            $driver = $this->withStraightLineEta($driver);
        }
        unset($driver);

        Log::info('Sorted ' . count($drivers) . ' drivers by straight-line distance for cycling', [
            'ride_id' => $rideId,
        ]);

        return $drivers;
    }

    /**
     * Next driver for a live search.
     *
     * The closest few drivers by straight line are ranked once with Distance
     * Matrix and reused until that shortlist is exhausted. Later timeouts walk
     * the saved order instead of calling Google again.
     */
    public function findNearestDriverByETA($userPickupLat, $userPickupLng, $eligibleDrivers, $rideId = null)
    {
        if (empty($eligibleDrivers)) {
            return null;
        }

        $eligibleDrivers = array_values($eligibleDrivers);
        $allowedIds = array_map(fn ($driver) => (int) $driver['id'], $eligibleDrivers);

        if ($rideId) {
            $cached = Cache::get($this->driverShortlistCacheKey($rideId));
            $next = $this->firstShortlistedDriver($cached, $allowedIds);
            if ($next) {
                Log::info("Using cached driver shortlist for ride {$rideId}", [
                    'driver_id' => $next['id'] ?? null,
                ]);
                return $this->nearestDriverPayload($next);
            }
        }

        usort($eligibleDrivers, fn ($a, $b) => ($a['distance_to_pickup'] ?? 0) <=> ($b['distance_to_pickup'] ?? 0));
        $shortlist = array_slice($eligibleDrivers, 0, 3);
        $ranked = $this->rankShortlistByRoadEta($userPickupLat, $userPickupLng, $shortlist);

        if ($rideId && !empty($ranked)) {
            Cache::put($this->driverShortlistCacheKey($rideId), $ranked, now()->addMinutes(10));
        }

        $nearest = $ranked[0] ?? null;
        if (!$nearest) {
            return null;
        }

        Log::info('Selected nearest driver from shortlist', [
            'ride_id' => $rideId,
            'driver_id' => $nearest['id'] ?? null,
            'eta_seconds' => $nearest['eta_time'] ?? null,
            'shortlist' => count($ranked),
        ]);

        return $this->nearestDriverPayload($nearest);
    }

    private function routeEstimateCacheKey($pickupLat, $pickupLng, $dropoffLat, $dropoffLng): string
    {
        return sprintf(
            'route_estimate:%s,%s:%s,%s',
            round((float) $pickupLat, 4),
            round((float) $pickupLng, 4),
            round((float) $dropoffLat, 4),
            round((float) $dropoffLng, 4),
        );
    }

    private function driverShortlistCacheKey($rideId): string
    {
        return "ride_driver_shortlist:{$rideId}";
    }

    private function firstShortlistedDriver($cached, array $allowedIds): ?array
    {
        if (!is_array($cached)) {
            return null;
        }

        foreach ($cached as $driver) {
            if (!is_array($driver)) {
                continue;
            }
            if (in_array((int) ($driver['id'] ?? 0), $allowedIds, true)) {
                return $driver;
            }
        }

        return null;
    }

    private function nearestDriverPayload(array $driver): array
    {
        $seconds = $driver['eta_time'] ?? $driver['eta_seconds'] ?? null;

        return [
            'driver_id' => $driver['id'] ?? null,
            'eta_seconds' => $seconds,
            'eta_minutes' => $driver['eta_minutes'] ?? ($seconds ? round($seconds / 60, 1) : null),
            'driver' => $driver,
        ];
    }

    /**
     * Same road factor the driver app uses when it does not call Google.
     */
    private function withStraightLineEta(array $driver): array
    {
        $roadKm = ((float) ($driver['distance_to_pickup'] ?? 0)) * 1.3;
        $minutes = max(1, (int) ceil(($roadKm / 25) * 60));
        $seconds = $minutes * 60;
        $driver['eta_time'] = $seconds;
        $driver['eta_seconds'] = $seconds;
        $driver['eta_minutes'] = $minutes;
        $driver['distance_text'] = round((float) ($driver['distance_to_pickup'] ?? 0), 1) . ' km';

        return $driver;
    }

    private function rankShortlistByRoadEta($pickupLat, $pickupLng, array $shortlist): array
    {
        if (empty($shortlist)) {
            return [];
        }

        $googleApiKey = config('services.google.maps_api_key');
        if (!$googleApiKey) {
            Log::error('Google Maps API key not configured');
            return array_map(fn ($driver) => $this->withStraightLineEta($driver), $shortlist);
        }

        $origins = implode('|', array_map(
            fn ($driver) => $driver['latitude'] . ',' . $driver['longitude'],
            $shortlist
        ));

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $origins,
                'destinations' => $pickupLat . ',' . $pickupLng,
                'key' => $googleApiKey,
            ]);

            $data = $response->json();
            if (!$response->successful() || ($data['status'] ?? '') !== 'OK') {
                Log::warning('Distance Matrix shortlist failed, using straight-line order', [
                    'status' => $data['status'] ?? $response->status(),
                ]);
                return array_map(fn ($driver) => $this->withStraightLineEta($driver), $shortlist);
            }

            GoogleApiUsageService::recordDistanceMatrix(count($shortlist), 1);

            $rows = $data['rows'] ?? [];
            foreach ($shortlist as $i => &$driver) {
                $element = $rows[$i]['elements'][0] ?? [];
                if (($element['status'] ?? '') === 'OK') {
                    $seconds = (int) ($element['duration']['value'] ?? 0);
                    $driver['eta_time'] = $seconds;
                    $driver['eta_seconds'] = $seconds;
                    $driver['eta_minutes'] = round($seconds / 60, 1);
                    $driver['distance_text'] = $element['distance']['text'] ?? 'N/A';
                    $driver['distance_value'] = $element['distance']['value'] ?? 0;
                } else {
                    $driver = $this->withStraightLineEta($driver);
                }
            }
            unset($driver);

            usort($shortlist, fn ($a, $b) => ($a['eta_time'] ?? PHP_INT_MAX) <=> ($b['eta_time'] ?? PHP_INT_MAX));

            return array_values($shortlist);
        } catch (\Exception $e) {
            Log::error('Distance Matrix shortlist error: ' . $e->getMessage());
            return array_map(fn ($driver) => $this->withStraightLineEta($driver), $shortlist);
        }
    }

    /**
     * Write ride fields only while the passenger has not cancelled.
     * The status check and the write share a row lock so a cancel that
     * committed during driver lookup cannot be overwritten with pending.
     */
    private function updateIfStillSearching(Ride $ride, array $attributes): bool
    {
        return DB::transaction(function () use ($ride, $attributes) {
            $locked = Ride::query()->whereKey($ride->id)->lockForUpdate()->first();

            if (!$locked || $locked->isCancelled()) {
                return false;
            }

            $locked->update($attributes);
            $ride->refresh();

            return true;
        });
    }

    /**
     * Search for alternative driver when current driver rejects
     */
    public function searchAlternativeDriver(Ride $ride)
    {
        try {
            $ride->refresh();
            if ($ride->isCancelled()) {
                Log::info("searchAlternativeDriver: ride {$ride->id} is cancelled, not searching");
                return null;
            }

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

                $this->updateIfStillSearching($ride, [
                    'driver_id' => null,
                    'status' => 'pending',
                ]);

                return null;
            }

            // Normal flow - exclude rejected drivers and drivers currently on cooldown
            $eligibleDrivers = array_filter($allDrivers, function ($driver) use ($excludedDriverIds, $ride) {
                if (in_array($driver['id'], $excludedDriverIds)) {
                    return false;
                }
                return !\Illuminate\Support\Facades\Cache::has("ride_cooldown:{$ride->id}:{$driver['id']}");
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

                // Filter out drivers who are currently on cooldown
                $allDriversWithETA = array_filter($allDriversWithETA, function ($driver) use ($ride) {
                    return !\Illuminate\Support\Facades\Cache::has("ride_cooldown:{$ride->id}:{$driver['id']}");
                });

                if (empty($allDriversWithETA)) {
                    Log::info("No drivers available (all on cooldown) for ride {$ride->id} in cycling mode");

                    $this->updateIfStillSearching($ride, [
                        'driver_id' => null,
                        'status' => 'pending',
                    ]);

                    return null;
                }
                
                // Re-index cycling drivers array after filtering
                $allDriversWithETA = array_values($allDriversWithETA);
                
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

            // Re-check under a row lock. The passenger may have cancelled while
            // ETA lookup was in flight; that cancel must not be overwritten.
            $assigned = $this->updateIfStillSearching($ride, [
                'driver_id' => $driverId,
                'status' => 'pending',
                'reassigned_at' => now(),
                'driver_assigned_at' => now(),
            ]);

            if (!$assigned) {
                Log::info("searchAlternativeDriver: ride {$ride->id} cancelled before assignment, driver {$driverId} was not offered");
                return null;
            }

            // Audit trail: record that this driver has now been offered the ride.
            app(RideOfferService::class)->recordOffer(
                $ride,
                (int) $driverId,
                null,
                $shouldCycleDrivers ? 'cycling' : 'reassignment'
            );

            // Broadcast the new ride request to the newly assigned driver via WebSockets (Reverb)
            try {
                broadcast(new NewRideRequest($ride));
            } catch (\Exception $e) {
                Log::error("Failed to broadcast alternative ride request to driver: " . $e->getMessage());
            }

            // ✅ Send push notification to new driver
            $driver = User::find($driverId);
            if ($driver && $driver->fcm_token) {
                $passenger = $ride->user;
                $passengerName = $passenger ? $passenger->name : 'عميل';
                $data = array_merge($ride->toOfferArray(), [
                    'title' => 'طلب رحلة جديد',
                    'body' => 'لديك طلب رحلة جديد من ' . $passengerName,
                    'msg_type' => 'ride_request',
                ]);

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

    public function getActiveDrivers(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1'
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $userLat = $request->lat ? (float)$request->lat : null;
        $userLng = $request->lng ? (float)$request->lng : null;
        $radius = $request->radius ? (float)$request->radius : 5.0; // default to 5 km

        try {
            // Load driver pickup radius settings from the database
            $driverSettings = \App\Models\DriverRideSetting::pluck('pickup_radius', 'driver_id')->toArray();

            // Fetch active/approved drivers directly from SQL database
            $drivers = User::where('role', 'driver')
                ->where('status', 'approved')
                ->where('is_available', true)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->with(['driverCars.carCategories', 'driverRideSetting'])
                ->get();

            $activeDrivers = [];

            foreach ($drivers as $driver) {
                $lat = $driver->latitude;
                $lng = $driver->longitude;

                $distance = null;
                if ($userLat !== null && $userLng !== null) {
                    $distance = $this->haversineDistance($userLat, $userLng, (float)$lat, (float)$lng);
                    // Filter out drivers outside the requested radius
                    $pickupRadius = (float)($driverSettings[$driver->id] ?? 5.0);
                    // Use either the customized radius or the default one
                    $effectiveRadius = max($radius, $pickupRadius);
                    if ($distance > $effectiveRadius) {
                        continue;
                    }
                }

                $driverCar = $driver->driverCars->first();
                $activeDrivers[] = [
                    'driver_id' => $driver->id,
                    'name' => $driver->name ?? '',
                    'latitude' => (float)$lat,
                    'longitude' => (float)$lng,
                    'bearing' => $driver->bearing !== null ? (float)$driver->bearing : null,
                    'car_category_id' => $driverCar ? (int) ($driverCar->car_categories_id ?? $driverCar->carCategories->pluck('id')->first()) : null,
                    'distance_km' => $distance !== null ? round($distance, 2) : null,
                    'last_updated' => $driver->updated_at ? $driver->updated_at->toIso8601String() : null
                ];
            }

            return response()->json([
                'message' => 'Success',
                'data' => $activeDrivers
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching active drivers: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch active drivers: ' . $e->getMessage()], 500);
        }
    }
}
