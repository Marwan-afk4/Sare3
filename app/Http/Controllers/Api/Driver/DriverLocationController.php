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

        // 4. Append the GPS point to the correct leg of the ride, throttled so
        // we don't store every single ping (keeps the JSON small and cheap).
        //   - `accepted`    -> to_pickup_route_points (captain heading to rider)
        //   - `in_progress` -> route_points (actual trip pickup -> destination)
        // The `waiting_user` leg is finalized by RideActionsController::arrived().
        //
        // NOTE: The driver app must stream location from the moment it accepts
        // (status `accepted`), not only after starting the trip, for the
        // "on the way to passenger" path to be recorded.
        $bearingValue = (float) ($request->bearing ?? 0);

        if ($ride->status->value === 'accepted') {
            $this->appendRoutePoint($ride, 'to_pickup_route_points', $lat, $lng, $bearingValue, $request->seq, 'to_pickup');
        } elseif ($ride->status->value === 'in_progress') {
            $this->appendRoutePoint($ride, 'route_points', $lat, $lng, $bearingValue, $request->seq, 'trip');
        }

        return response()->json([
            'message'      => 'Driver location updated successfully',
            'total_points' => count($ride->route_points ?? []),
        ]);
    }

    /**
     * Append a GPS point to one of the ride's route-point columns, throttled
     * by time and distance so we only persist meaningful movement.
     *
     * Uses the same thresholds as the driver-location DB write
     * (DB_WRITE_INTERVAL_SECONDS / DB_WRITE_MIN_DISTANCE_METERS). The very
     * first point of a leg is always stored so the polyline has an anchor.
     */
    private function appendRoutePoint(Ride $ride, string $column, float $lat, float $lng, ?float $bearing, ?int $seq, string $phase): void
    {
        $cacheKey  = "ride_route_point_write:{$ride->id}:{$phase}";
        $lastWrite = Cache::get($cacheKey);

        $shouldWrite = false;

        if ($lastWrite === null) {
            // First point of this leg — always store it.
            $shouldWrite = true;
        } else {
            $secondsSinceLast = now()->diffInSeconds($lastWrite['time']);

            if ($secondsSinceLast >= self::DB_WRITE_INTERVAL_SECONDS) {
                $shouldWrite = true;
            } else {
                $distance = $this->haversineMeters(
                    $lastWrite['lat'], $lastWrite['lng'],
                    $lat, $lng
                );

                if ($distance >= self::DB_WRITE_MIN_DISTANCE_METERS) {
                    $shouldWrite = true;
                }
            }
        }

        if (! $shouldWrite) {
            return;
        }

        $points   = $ride->{$column} ?? [];
        $points[] = [
            'lat'       => $lat,
            'lng'       => $lng,
            'bearing'   => $bearing ?? 0.0,
            'timestamp' => now()->timestamp,
            'seq'       => $seq,
            'phase'     => $phase,
        ];

        $ride->{$column} = $points;
        $ride->save();

        Cache::put($cacheKey, [
            'lat'  => $lat,
            'lng'  => $lng,
            'time' => now(),
        ], now()->addHour());
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
