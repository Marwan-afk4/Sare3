<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\VehicleType;
use App\Events\NewDeliveryRequest;
use App\Helpers\FcmHelper;
use App\Http\Controllers\Controller;
use App\Jobs\AutoRejectDeliveryJob;
use App\Models\Delivery;
use App\Models\DeliveryZonePrice;
use App\Models\User;
use App\Models\Zone;
use App\Services\DeliveryOfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    /** Default radius (km) to search for riders around the pickup pin. */
    const DEFAULT_PICKUP_RADIUS_KM = 5.0;

    /**
     * Estimate a delivery. Returns km/time (via Google when both points are
     * present) and the price for BOTH vehicle types in the zone. The user
     * does not pick a vehicle type; this is informational.
     */
    public function estimate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $estimatedKm = (float) ($request->estimated_km ?? 0);
        $estimatedTime = (float) ($request->estimated_time ?? 0);

        if ($request->pickup_lat !== null && $request->pickup_lng !== null
            && $request->dropoff_lat !== null && $request->dropoff_lng !== null) {
            $google = $this->googleDistance(
                $request->pickup_lat,
                $request->pickup_lng,
                $request->dropoff_lat,
                $request->dropoff_lng
            );

            if (isset($google['error'])) {
                return response()->json(['message' => $google['error']], $google['status'] ?? 500);
            }

            $estimatedKm = $google['km'];
            $estimatedTime = $google['minutes'];
        }

        $prices = DeliveryZonePrice::where('zone_id', $request->zone_id)->get();

        $data = [];
        foreach (VehicleType::values() as $type) {
            $priceRow = $prices->firstWhere('vehicle_type', $type)
                ?? $prices->first(fn ($p) => $p->vehicle_type?->value === $type);

            $data[] = [
                'vehicle_type' => $type,
                'label' => VehicleType::labels()[$type],
                'available' => (bool) $priceRow,
                'estimated_price' => $priceRow ? round($priceRow->calculatePrice($estimatedKm, $estimatedTime), 2) : null,
            ];
        }

        return response()->json([
            'message' => 'Success',
            'estimated_km' => $estimatedKm,
            'estimated_time' => $estimatedTime,
            'data' => $data,
        ]);
    }

    /**
     * Create a delivery. Matches the nearest eligible rider to the PICKUP
     * pin, locks the price from that rider's vehicle type, and notifies them.
     */
    public function createDelivery(Request $request)
    {
        $user = $request->user();

        $validation = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'pickup_address' => 'nullable|string',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',
            'dropoff_address' => 'nullable|string',
            'estimated_km' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|numeric|min:0',
            'payment_method_id' => 'nullable|exists:paymenent_methods,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        // Find nearest eligible rider around the PICKUP pin (any vehicle type).
        $eligibleRiders = $this->getEligibleRiders($request->pickup_lat, $request->pickup_lng);

        if (empty($eligibleRiders)) {
            return response()->json(['message' => 'No delivery riders available in your area.'], 404);
        }

        usort($eligibleRiders, fn ($a, $b) => $a['distance_to_pickup'] <=> $b['distance_to_pickup']);

        $nearest = $eligibleRiders[0];
        $rider = User::where('role', 'delivery')
            ->where('status', 'approved')
            ->where('is_available', true)
            ->with('riderVehicle')
            ->find($nearest['id']);

        if (!$rider || !$rider->riderVehicle) {
            return response()->json(['message' => 'Nearest rider not found.'], 404);
        }

        $vehicleType = $rider->riderVehicle->type?->value;

        // Lock the price from the rider's vehicle type in this zone.
        $priceRow = DeliveryZonePrice::where('zone_id', $request->zone_id)
            ->where('vehicle_type', $vehicleType)
            ->first();

        if (!$priceRow) {
            return response()->json([
                'message' => 'Delivery pricing is not configured for this zone / vehicle type.',
            ], 422);
        }

        $estimatedKm = (float) ($request->estimated_km ?? 0);
        $estimatedTime = (float) ($request->estimated_time ?? 0);
        $price = round($priceRow->calculatePrice($estimatedKm, $estimatedTime), 2);

        $delivery = Delivery::create([
            'user_id' => $user->id,
            'rider_id' => $rider->id,
            'zone_id' => $request->zone_id,
            'payment_method_id' => $request->payment_method_id,
            'vehicle_type' => $vehicleType,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'pickup_address' => $request->pickup_address,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'dropoff_address' => $request->dropoff_address,
            'status' => 'pending',
            'estimated_km' => $estimatedKm,
            'estimated_time' => $estimatedTime,
            'calculated_initial_price' => $price,
            'rider_assigned_at' => now(),
        ]);

        app(DeliveryOfferService::class)->recordOffer($delivery, (int) $rider->id, 1, 'initial_assignment');

        $this->notifyRider($delivery, $rider, $user);

        $timeoutSeconds = config('ride.auto_reject_timeout_seconds', 15);
        AutoRejectDeliveryJob::dispatch(
            $delivery->id,
            $rider->id,
            $delivery->updated_at->format('Y-m-d H:i:s')
        )->delay(now()->addSeconds($timeoutSeconds));

        $delivery->load(['rider.riderVehicle']);

        return response()->json([
            'message' => 'Delivery created successfully',
            'data' => $delivery,
        ]);
    }

    public function getDeliveryStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required|exists:deliveries,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $delivery = Delivery::with(['rider.riderVehicle'])
            ->where('id', $request->delivery_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found or unauthorized.'], 404);
        }

        return response()->json([
            'delivery' => [
                'id' => $delivery->id,
                'status' => $delivery->status->value,
                'vehicle_type' => $delivery->vehicle_type?->value,
                'rider' => $delivery->rider ? [
                    'id' => $delivery->rider->id,
                    'name' => $delivery->rider->name,
                    'phone' => $delivery->rider->phone,
                    'image_link' => $delivery->rider->image_link,
                    'vehicle' => $delivery->rider->riderVehicle ? [
                        'type' => $delivery->rider->riderVehicle->type?->value,
                        'vehicle_image_link' => $delivery->rider->riderVehicle->vehicle_image_link,
                    ] : null,
                ] : null,
                'pickup_address' => $delivery->pickup_address,
                'dropoff_address' => $delivery->dropoff_address,
                'estimated_price' => (float) $delivery->calculated_initial_price,
                'final_price' => $delivery->calculated_final_price ? (float) $delivery->calculated_final_price : null,
                'created_at' => $delivery->created_at->toIso8601String(),
            ],
        ]);
    }

    public function userInDelivery(Request $request)
    {
        $user = $request->user();

        $delivery = Delivery::whereNotIn('status', ['finshed', 'cancelled', 'rejected'])
            ->where('user_id', $user->id)
            ->select('id', 'status')
            ->first();

        return response()->json([
            'is_in_delivery' => $delivery,
        ]);
    }

    public function myDeliveries(Request $request)
    {
        $user = $request->user();
        $limit = (int) $request->get('limit', 20);

        $deliveries = Delivery::with(['rider.riderVehicle'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($limit);

        return response()->json([
            'message' => 'Success',
            'data' => $deliveries->items(),
            'pagination' => [
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
                'per_page' => $deliveries->perPage(),
                'total' => $deliveries->total(),
                'has_more_pages' => $deliveries->hasMorePages(),
            ],
        ]);
    }

    public function cancelDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required|exists:deliveries,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $delivery = Delivery::where('id', $request->delivery_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found or unauthorized.'], 404);
        }

        if (in_array($delivery->status->value, ['completed', 'finshed', 'cancelled'])) {
            return response()->json(['message' => 'This delivery can no longer be cancelled.'], 422);
        }

        $wasBeforeAccept = in_array($delivery->status->value, ['pending', 'rejected']);

        app(DeliveryOfferService::class)->markAllPendingCancelledByUser($delivery, 'cancelled_by_user');

        $delivery->update([
            'status' => 'cancelled',
            'cancelled_before_accept' => $wasBeforeAccept,
        ]);

        return response()->json(['message' => 'Delivery cancelled.']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Matching helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Riders eligible to receive an offer near the pickup pin: role=delivery,
     * approved, available, has GPS, wallet OK, within pickup radius.
     */
    public function getEligibleRiders($pickupLat, $pickupLng, array $excludedRiderIds = []): array
    {
        try {
            $minWallet = \App\Models\AppSetting::getMinimumDriverWalletBalance();

            $query = User::where('role', 'delivery')
                ->where('status', 'approved')
                ->where('is_available', true)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('wallet', '>=', $minWallet)
                ->whereHas('riderVehicle');

            if (!empty($excludedRiderIds)) {
                $query->whereNotIn('id', $excludedRiderIds);
            }

            $riders = $query->with('riderVehicle')->get();

            $eligible = [];
            foreach ($riders as $rider) {
                $cached = Cache::get("driver_location:{$rider->id}");
                $lat = isset($cached['latitude']) ? (float) $cached['latitude'] : (float) $rider->latitude;
                $lng = isset($cached['longitude']) ? (float) $cached['longitude'] : (float) $rider->longitude;

                if (!$lat || !$lng) {
                    continue;
                }

                $distance = $this->haversineDistance($pickupLat, $pickupLng, $lat, $lng);

                if ($distance > self::DEFAULT_PICKUP_RADIUS_KM) {
                    continue;
                }

                $eligible[] = [
                    'id' => $rider->id,
                    'name' => $rider->name ?? '',
                    'vehicle_type' => $rider->riderVehicle->type?->value,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'distance_to_pickup' => round($distance, 2),
                ];
            }

            return $eligible;
        } catch (\Exception $e) {
            Log::error('Error fetching eligible riders: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Reassign a delivery to the next-nearest rider after a reject/timeout.
     * Called by AutoRejectDeliveryJob. Returns the selected rider id or null.
     */
    public function searchAlternativeRider(Delivery $delivery): ?int
    {
        try {
            $excluded = $delivery->rejected_riders ?? [];
            $excluded = array_map(fn ($id) => is_numeric($id) ? (int) $id : $id, $excluded);

            if ($delivery->rider_id && !in_array((int) $delivery->rider_id, $excluded, true)) {
                $excluded[] = (int) $delivery->rider_id;
            }

            // After a full cycle of rejections, reset and start over.
            $shouldCycle = count($excluded) > 12;

            $candidates = $this->getEligibleRiders(
                $delivery->pickup_lat,
                $delivery->pickup_lng,
                $shouldCycle ? [] : $excluded
            );

            // Drop riders currently on cooldown.
            $candidates = array_values(array_filter($candidates, function ($r) use ($delivery) {
                return !Cache::has("delivery_cooldown:{$delivery->id}:{$r['id']}");
            }));

            if (empty($candidates)) {
                if ($shouldCycle) {
                    $delivery->update(['rejected_riders' => []]);
                }
                $delivery->update(['rider_id' => null, 'status' => 'pending']);
                return null;
            }

            usort($candidates, fn ($a, $b) => $a['distance_to_pickup'] <=> $b['distance_to_pickup']);
            $selected = $candidates[0];

            if ($shouldCycle) {
                $delivery->update(['rejected_riders' => []]);
            }

            $rider = User::with('riderVehicle')->find($selected['id']);
            $vehicleType = $rider?->riderVehicle?->type?->value ?? $delivery->vehicle_type?->value;

            // Re-price to the new rider's vehicle type in the same zone.
            $price = $delivery->calculated_initial_price;
            $priceRow = DeliveryZonePrice::where('zone_id', $delivery->zone_id)
                ->where('vehicle_type', $vehicleType)
                ->first();
            if ($priceRow) {
                $price = round($priceRow->calculatePrice((float) $delivery->estimated_km, (float) $delivery->estimated_time), 2);
            }

            $delivery->update([
                'rider_id' => $selected['id'],
                'vehicle_type' => $vehicleType,
                'calculated_initial_price' => $price,
                'status' => 'pending',
                'reassigned_at' => now(),
                'rider_assigned_at' => now(),
            ]);

            app(DeliveryOfferService::class)->recordOffer(
                $delivery,
                (int) $selected['id'],
                null,
                $shouldCycle ? 'cycling' : 'reassignment'
            );

            try {
                broadcast(new NewDeliveryRequest($delivery));
            } catch (\Exception $e) {
                Log::error("Failed to broadcast alternative delivery request: " . $e->getMessage());
            }

            if ($rider && $rider->fcm_token) {
                $this->sendFcm($rider, $delivery);
            }

            return (int) $selected['id'];
        } catch (\Exception $e) {
            Log::error("Error searching alternative rider for delivery {$delivery->id}: " . $e->getMessage());
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────────────────────────────

    private function notifyRider(Delivery $delivery, User $rider, User $user): void
    {
        try {
            broadcast(new NewDeliveryRequest($delivery))->toOthers();
            if ($rider->fcm_token) {
                $this->sendFcm($rider, $delivery, $user);
            }
        } catch (\Exception $e) {
            Log::error("Failed to notify rider: " . $e->getMessage());
        }
    }

    private function sendFcm(User $rider, Delivery $delivery, ?User $user = null): void
    {
        $name = $user?->name ?? ($delivery->user?->name ?? 'عميل');
        $data = [
            'title' => 'طلب توصيل جديد',
            'body' => 'لديك طلب توصيل جديد من ' . $name,
            'msg_type' => 'delivery_request',
            'delivery_id' => (string) $delivery->id,
        ];
        FcmHelper::sendPushNotification($rider->fcm_token, $data['title'], $data['body'], $data);
    }

    private function googleDistance($fromLat, $fromLng, $toLat, $toLng): array
    {
        $key = config('services.google.maps_api_key');
        if (!$key) {
            return ['error' => 'Google Maps API key not configured', 'status' => 500];
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $fromLat . ',' . $fromLng,
                'destinations' => $toLat . ',' . $toLng,
                'key' => $key,
            ]);

            if (!$response->successful()) {
                return ['error' => 'Error from Google API: ' . $response->status(), 'status' => 520];
            }

            $data = $response->json();
            $element = $data['rows'][0]['elements'][0] ?? [];

            if (($data['status'] ?? '') !== 'OK' || ($element['status'] ?? '') !== 'OK') {
                return ['error' => 'Unable to calculate route between pickup and dropoff', 'status' => 422];
            }

            return [
                'km' => round(($element['distance']['value'] ?? 0) / 1000, 2),
                'minutes' => round(($element['duration']['value'] ?? 0) / 60, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Delivery Google Distance error: ' . $e->getMessage());
            return ['error' => 'Error calling Google API', 'status' => 500];
        }
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
