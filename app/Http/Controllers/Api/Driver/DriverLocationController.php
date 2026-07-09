<?php

namespace App\Http\Controllers\Api\Driver;

use App\Events\DriverLocationUpdated;
use App\Events\ActiveDriverLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class DriverLocationController extends Controller
{
    /**
     * How many seconds must pass before we force a DB write.
     * (Even if distance hasn't changed enough.)
     */
    const DB_WRITE_INTERVAL_SECONDS = 30;

    /**
     * Minimum distance change (in meters) that triggers an immediate DB write.
     */
    const DB_WRITE_MIN_DISTANCE_METERS = 50;

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATE GENERAL LOCATION  (driver not in a ride — idle / searching)
    // POST /api/driver/update-location
    // ─────────────────────────────────────────────────────────────────────────

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

        $driver  = auth()->user();
        $lat     = (float) $request->lat;
        $lng     = (float) $request->lng;
        $bearing = $request->bearing !== null ? (float) $request->bearing : $driver->bearing;

        // 1. Cache location in Redis immediately (ultra-fast, no DB hit)
        $this->cacheLocation($driver->id, $lat, $lng, $bearing);

        // 2. Broadcast via Reverb right away (uses cached data — zero DB query)
        $this->broadcastLocation($driver, $lat, $lng, $bearing);

        // 3. Write to DB only when throttle conditions are met
        $this->throttledDbWrite($driver, $lat, $lng, $bearing);

        return response()->json([
            'success'   => true,
            'message'   => 'Location updated successfully',
            'latitude'  => $lat,
            'longitude' => $lng,
            'bearing'   => $bearing,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATE RIDE LOCATION  (driver is on an active ride)
    // POST /api/driver/ride/update-location
    // ─────────────────────────────────────────────────────────────────────────

    public function updateDriverLocation(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'ride_id' => 'required',
            'lat'     => 'required|numeric|between:-90,90',
            'lng'     => 'required|numeric|between:-180,180',
            'bearing' => 'nullable|numeric|between:0,360',
            'seq'     => 'nullable|integer',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()], 422);
        }

        $ride = Ride::findOrFail($request->ride_id);

        if ($ride->driver_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! in_array($ride->status->value, ['accepted', 'waiting_user', 'in_progress'])) {
            return response()->json(['message' => 'Ride is not in trackable status'], 400);
        }

        $driver  = auth()->user();
        $lat     = (float) $request->lat;
        $lng     = (float) $request->lng;
        $bearing = $request->bearing !== null ? (float) $request->bearing : $driver->bearing;

        // 1. Cache in Redis
        $this->cacheLocation($driver->id, $lat, $lng, $bearing);

        // 2. Broadcast via Reverb immediately
        $this->broadcastLocation($driver, $lat, $lng, $bearing, (int)$request->ride_id);

        // 3. Throttled DB write for driver's position
        $this->throttledDbWrite($driver, $lat, $lng, $bearing);

        // 4. Append to route_points only during the actual trip (in_progress).
        // Points while heading to the rider are tracked via driver_accept_lat/lng
        // and live driver location, not stored in route_points.
        if ($ride->status->value === 'in_progress') {
            $points   = $ride->route_points ?? [];
            $points[] = [
                'lat'       => $lat,
                'lng'       => $lng,
                'bearing'   => (float) ($request->bearing ?? 0),
                'timestamp' => now()->timestamp,
                'seq'       => $request->seq ?? null,
                'phase'     => 'trip',
            ];

            $ride->route_points = $points;
            $ride->save();
        }

        return response()->json([
            'message'      => 'Driver location updated successfully',
            'total_points' => count($ride->route_points ?? []),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Store driver location in Redis.
     * TTL = 5 minutes (driver considered offline after that).
     */
    private function cacheLocation(int $driverId, float $lat, float $lng, ?float $bearing): void
    {
        Cache::put("driver_location:{$driverId}", [
            'latitude'   => $lat,
            'longitude'  => $lng,
            'bearing'    => $bearing,
            'updated_at' => now()->toIso8601String(),
        ], now()->addMinutes(5));
    }

    /**
     * Broadcast the event using cached data — no extra DB query needed.
     */
    private function broadcastLocation(User $driver, float $lat, float $lng, ?float $bearing, ?int $rideId = null): void
    {
        // Temporarily set the values on the model instance (no DB hit)
        $driver->latitude  = $lat;
        $driver->longitude = $lng;
        $driver->bearing   = $bearing;

        DriverLocationUpdated::dispatch($driver, $rideId);
        ActiveDriverLocationUpdated::dispatch($driver);
    }

    /**
     * Only write to the database when:
     *   (a) The driver moved more than DB_WRITE_MIN_DISTANCE_METERS, OR
     *   (b) DB_WRITE_INTERVAL_SECONDS seconds have passed since last write.
     *
     * This reduces DB load from ~1 write/update to ~1 write/30s per driver.
     * With 1000 drivers @ 30s interval → ~33 writes/sec instead of 200–333/sec.
     */
    private function throttledDbWrite(User $driver, float $lat, float $lng, ?float $bearing): void
    {
        $cacheKey  = "driver_last_db_write:{$driver->id}";
        $lastWrite = Cache::get($cacheKey);

        $shouldWrite = false;

        if ($lastWrite === null) {
            // First update — always write
            $shouldWrite = true;
        } else {
            $secondsSinceLast = now()->diffInSeconds($lastWrite['time']);

            if ($secondsSinceLast >= self::DB_WRITE_INTERVAL_SECONDS) {
                // Time threshold exceeded
                $shouldWrite = true;
            } else {
                // Check distance moved
                $distance = $this->haversineMeters(
                    $lastWrite['lat'], $lastWrite['lng'],
                    $lat, $lng
                );

                if ($distance >= self::DB_WRITE_MIN_DISTANCE_METERS) {
                    $shouldWrite = true;
                }
            }
        }

        if ($shouldWrite) {
            $driver->timestamps = false; // don't bump updated_at just for location
            $driver->updateQuietly([
                'latitude'  => $lat,
                'longitude' => $lng,
                'bearing'   => $bearing,
            ]);

            Cache::put($cacheKey, [
                'lat'  => $lat,
                'lng'  => $lng,
                'time' => now(),
            ], now()->addHour());
        }
    }

    /**
     * Haversine formula — distance between two GPS points in metres.
     */
    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // metres

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * asin(sqrt($a));
    }
}
