<?php

namespace App\Http\Controllers\Api\Rider;

use App\Events\ActiveRiderLocationUpdated;
use App\Events\RiderLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class RiderLocationController extends Controller
{
    const DB_WRITE_INTERVAL_SECONDS = 30;
    const DB_WRITE_MIN_DISTANCE_METERS = 50;

    /**
     * General location (rider idle / searching).
     * POST /api/rider/update-location
     */
    public function updateGeneralLocation(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'lat'     => 'required|numeric|between:-90,90',
            'lng'     => 'required|numeric|between:-180,180',
            'bearing' => 'nullable|numeric|between:0,360',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()], 422);
        }

        $rider   = auth()->user();
        $lat     = (float) $request->lat;
        $lng     = (float) $request->lng;
        $bearing = $request->bearing !== null ? (float) $request->bearing : $rider->bearing;

        $this->cacheLocation($rider->id, $lat, $lng, $bearing);
        $this->broadcastLocation($rider, $lat, $lng, $bearing);
        $this->throttledDbWrite($rider, $lat, $lng, $bearing);

        return response()->json([
            'success'   => true,
            'message'   => 'Location updated successfully',
            'latitude'  => $lat,
            'longitude' => $lng,
            'bearing'   => $bearing,
        ]);
    }

    /**
     * Location while on an active delivery.
     * POST /api/rider/delivery/update-location
     */
    public function updateDeliveryLocation(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'delivery_id' => 'required',
            'lat'         => 'required|numeric|between:-90,90',
            'lng'         => 'required|numeric|between:-180,180',
            'bearing'     => 'nullable|numeric|between:0,360',
            'seq'         => 'nullable|integer',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()], 422);
        }

        $delivery = Delivery::findOrFail($request->delivery_id);

        if ($delivery->rider_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($delivery->status->value, ['accepted', 'arrived', 'in_progress'])) {
            return response()->json(['message' => 'Delivery is not in trackable status'], 400);
        }

        $rider   = auth()->user();
        $lat     = (float) $request->lat;
        $lng     = (float) $request->lng;
        $bearing = $request->bearing !== null ? (float) $request->bearing : $rider->bearing;

        $this->cacheLocation($rider->id, $lat, $lng, $bearing);
        $this->broadcastLocation($rider, $lat, $lng, $bearing, (int) $request->delivery_id);
        $this->throttledDbWrite($rider, $lat, $lng, $bearing);

        $bearingValue = (float) ($request->bearing ?? 0);

        // accepted -> heading to pickup; arrived -> heading to the user (dropoff)
        if ($delivery->status->value === 'accepted') {
            $this->appendRoutePoint($delivery, 'to_pickup_route_points', $lat, $lng, $bearingValue, $request->seq, 'to_pickup');
        } else {
            $this->appendRoutePoint($delivery, 'route_points', $lat, $lng, $bearingValue, $request->seq, 'trip');
        }

        return response()->json([
            'message'      => 'Rider location updated successfully',
            'total_points' => count($delivery->route_points ?? []),
        ]);
    }

    private function appendRoutePoint(Delivery $delivery, string $column, float $lat, float $lng, ?float $bearing, ?int $seq, string $phase): void
    {
        $cacheKey  = "delivery_route_point_write:{$delivery->id}:{$phase}";
        $lastWrite = Cache::get($cacheKey);

        $shouldWrite = false;
        if ($lastWrite === null) {
            $shouldWrite = true;
        } else {
            $secondsSinceLast = now()->diffInSeconds($lastWrite['time']);
            if ($secondsSinceLast >= self::DB_WRITE_INTERVAL_SECONDS) {
                $shouldWrite = true;
            } elseif ($this->haversineMeters($lastWrite['lat'], $lastWrite['lng'], $lat, $lng) >= self::DB_WRITE_MIN_DISTANCE_METERS) {
                $shouldWrite = true;
            }
        }

        if (!$shouldWrite) {
            return;
        }

        $points   = $delivery->{$column} ?? [];
        $points[] = [
            'lat'       => $lat,
            'lng'       => $lng,
            'bearing'   => $bearing ?? 0.0,
            'timestamp' => now()->timestamp,
            'seq'       => $seq,
            'phase'     => $phase,
        ];

        $delivery->{$column} = $points;
        $delivery->save();

        Cache::put($cacheKey, ['lat' => $lat, 'lng' => $lng, 'time' => now()], now()->addHour());
    }

    private function cacheLocation(int $riderId, float $lat, float $lng, ?float $bearing): void
    {
        Cache::put("driver_location:{$riderId}", [
            'latitude'   => $lat,
            'longitude'  => $lng,
            'bearing'    => $bearing,
            'updated_at' => now()->toIso8601String(),
        ], now()->addMinutes(5));
    }

    private function broadcastLocation(User $rider, float $lat, float $lng, ?float $bearing, ?int $deliveryId = null): void
    {
        $rider->latitude  = $lat;
        $rider->longitude = $lng;
        $rider->bearing   = $bearing;

        RiderLocationUpdated::dispatch($rider, $deliveryId);
        ActiveRiderLocationUpdated::dispatch($rider);
    }

    private function throttledDbWrite(User $rider, float $lat, float $lng, ?float $bearing): void
    {
        $cacheKey  = "rider_last_db_write:{$rider->id}";
        $lastWrite = Cache::get($cacheKey);

        $shouldWrite = false;
        if ($lastWrite === null) {
            $shouldWrite = true;
        } else {
            $secondsSinceLast = now()->diffInSeconds($lastWrite['time']);
            if ($secondsSinceLast >= self::DB_WRITE_INTERVAL_SECONDS) {
                $shouldWrite = true;
            } elseif ($this->haversineMeters($lastWrite['lat'], $lastWrite['lng'], $lat, $lng) >= self::DB_WRITE_MIN_DISTANCE_METERS) {
                $shouldWrite = true;
            }
        }

        if ($shouldWrite) {
            $rider->timestamps = false;
            $rider->updateQuietly([
                'latitude'  => $lat,
                'longitude' => $lng,
                'bearing'   => $bearing,
            ]);
            Cache::put($cacheKey, ['lat' => $lat, 'lng' => $lng, 'time' => now()], now()->addHour());
        }
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * asin(sqrt($a));
    }
}
