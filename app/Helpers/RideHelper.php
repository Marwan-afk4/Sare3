<?php

namespace App\Helpers;

use App\Models\CarCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RideHelper
{
    private static string $googleApiKey = 'AIzaSyBQpCJAXkrWtiIQfmKdWUyClvjagGoxotY';

    private static int $snapBatchSize = 100;
    private static int $httpTimeout = 12;
    private static float $minMoveMeters = 5.0;
    private static float $maxJumpMeters = 5000.0;

    /**
     * Just sum haversine distances after snapping & filtering.
     */
    public static function calculateTotalDistanceAccurate(array $points): float
    {
        if (count($points) < 2) {
            return 0.0;
        }

        Log::info('[Total] raw points: ' . count($points));

        $filtered = self::filterPath($points);
        Log::info('[Total] filtered points: ' . count($filtered));

        $snapped = self::snapToRoads($filtered);
        Log::info('[Total] snapped points: ' . count($snapped));

        
        $totalMeters = 0.0;
        for ($i = 0; $i < count($snapped) - 1; $i++) {
            $totalMeters += self::haversineMeters($snapped[$i], $snapped[$i + 1]);
        }

        $km = $totalMeters / 1000.0;
        Log::info('[Total] distance = ' . number_format($km, 3) . ' km');

        return $km;
    }

    /**
     * SnapToRoads API
     */
    private static function snapToRoads(array $points): array
    {
        if (empty($points)) return [];

        $snapped = [];

        for ($i = 0; $i < count($points); $i += self::$snapBatchSize) {
            $batch = array_slice($points, $i, self::$snapBatchSize);
            $path = collect($batch)->map(fn($p) => "{$p['lat']},{$p['lng']}")->implode('|');

            $url = "https://roads.googleapis.com/v1/snapToRoads?path={$path}&interpolate=false&key=" . self::$googleApiKey;

            try {
                $resp = Http::timeout(self::$httpTimeout)->get($url);

                if ($resp->successful()) {
                    $data = $resp->json();
                    $pointsApi = $data['snappedPoints'] ?? [];
                    foreach ($pointsApi as $p) {
                        $loc = $p['location'];
                        $snapped[] = [
                            'lat' => (float) $loc['latitude'],
                            'lng' => (float) $loc['longitude'],
                        ];
                    }
                    Log::info('[SnapToRoads] batch ' . (intdiv($i, self::$snapBatchSize) + 1) . ' added ' . count($pointsApi) . ' pts');
                } else {
                    Log::info('[SnapToRoads] HTTP ' . $resp->status() . ': ' . $resp->body());
                    $snapped = array_merge($snapped, $batch); // fallback: keep original batch
                }
            } catch (\Throwable $e) {
                Log::info('[SnapToRoads] error: ' . $e->getMessage());
                $snapped = array_merge($snapped, $batch); // fallback
            }
        }

        // Guard: remove duplicates
        $dedup = [];
        foreach ($snapped as $p) {
            if (empty($dedup) || $p['lat'] != $dedup[count($dedup) - 1]['lat'] || $p['lng'] != $dedup[count($dedup) - 1]['lng']) {
                $dedup[] = $p;
            }
        }

        return $dedup;
    }

    /**
     * Filter tiny jitter and absurd jumps.
     */
    private static function filterPath(array $points): array
    {
        if (count($points) < 2) return $points;

        $kept = [$points[0]];
        for ($i = 1; $i < count($points); $i++) {
            $d = self::haversineMeters($kept[count($kept) - 1], $points[$i]);
            if ($d < self::$minMoveMeters) continue; // ignore micro jitter
            if ($d > self::$maxJumpMeters) {
                Log::info("[Filter] Skipping jump " . number_format($d / 1000, 2) . " km at index $i");
                continue;
            }
            $kept[] = $points[$i];
        }
        return $kept;
    }



    /**
     * Haversine formula (meters).
     */
    private static function haversineMeters(array $p1, array $p2): float
    {
        $R = 6371000.0;
        $dLat = ($p2['lat'] - $p1['lat']) * M_PI / 180.0;
        $dLon = ($p2['lng'] - $p1['lng']) * M_PI / 180.0;

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($p1['lat'] * M_PI / 180.0) *
            cos($p2['lat'] * M_PI / 180.0) *
            sin($dLon / 2) *
            sin($dLon / 2);

        return $R * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /**
     * Format ride history data for user
     */
    public static function formatUserRideHistory($rides): array
    {
        // Handle different input types
        if (is_array($rides)) {
            $rides = collect($rides);
        } elseif (!$rides instanceof \Illuminate\Support\Collection) {
            $rides = collect($rides);
        }
        
        return $rides->map(function ($ride) {
            $driver = optional($ride->driver);
            $car = $driver && $driver->driverCars ? optional($driver->driverCars->first()) : null;

            return [
                'ride_id' => $ride->id,
                'driver' => [
                    'driver_id' => $driver->id ?? null,
                    'driver_name' => $driver->name ?? 'N/A',
                    'driver_image_link' => $driver->image_link ?? null,
                    'driver_phone' => $driver->phone ?? null,
                ],
                'car' => [
                    'car_number' => optional($car)->car_number,
                    'car_model' => optional($car)->car_model,
                    'car_color' => optional($car)->car_color,
                    'car_image_link' => optional($car)->car_image_link,
                ],
                'pickup_address' => $ride->pickup_address,
                'dropoff_address' => $ride->dropoff_address,
                'started_at' => $ride->started_at,
                'ended_at' => $ride->ended_at,
                'status' => $ride->status->value ?? $ride->status,
                'calculated_final_price' => $ride->calculated_final_price,
                'total_distance_in_km' => $ride->total_distance_in_km,
                'time_taken' => $ride->time_taken,
                'created_at' => $ride->created_at,
            ];
        })->values()->toArray();
    }

    /**
     * Format ride history data for driver
     */
    public static function formatDriverRideHistory($rides): array
    {
        // Handle different input types
        if (is_array($rides)) {
            $rides = collect($rides);
        } elseif (!$rides instanceof \Illuminate\Support\Collection) {
            $rides = collect($rides);
        }
        
        return $rides->map(function ($ride) {
            $user = optional($ride->user);

            return [
                'ride_id' => $ride->id,
                'user' => [
                    'user_id' => $user->id ?? null,
                    'user_name' => $user->name ?? 'N/A',
                    'user_image_link' => $user->image_link ?? null,
                    'user_phone' => $user->phone ?? null,
                ],
                'pickup_address' => $ride->pickup_address,
                'dropoff_address' => $ride->dropoff_address,
                'started_at' => $ride->started_at,
                'ended_at' => $ride->ended_at,
                'status' => $ride->status->value ?? $ride->status,
                'calculated_final_price' => $ride->calculated_final_price,
                'total_distance_in_km' => $ride->total_distance_in_km,
                'time_taken' => $ride->time_taken,
                'created_at' => $ride->created_at,
            ];
        })->values()->toArray();
    }

    /**
     * Get ride statistics for user
     */
    public static function getUserRideStatistics($rides): array
    {
        // Handle different input types
        if (is_array($rides)) {
            $rides = collect($rides);
        } elseif (!$rides instanceof \Illuminate\Support\Collection) {
            $rides = collect($rides);
        }
        
        $completedRides = $rides->where('status', 'completed');
        $finshedRides = $rides->where('status', 'finshed');
        $cancelledRides = $rides->where('status', 'cancelled');
        $successfulRides = $rides->whereIn('status', ['completed', 'finshed']);
        
        return [
            'total_rides' => $rides->count(),
            'completed_rides' => $completedRides->count(),
            'finshed_rides' => $finshedRides->count(),
            'successful_rides' => $successfulRides->count(),
            'cancelled_rides' => $cancelledRides->count(),
            'total_spent' => $successfulRides->sum('calculated_final_price'),
            'total_distance' => $successfulRides->sum('total_distance_in_km'),
            'average_ride_cost' => $successfulRides->count() > 0 ? $successfulRides->avg('calculated_final_price') : 0,
        ];
    }

    /**
     * Get ride statistics for driver
     */
    public static function getDriverRideStatistics($rides): array
    {
        // Handle different input types
        if (is_array($rides)) {
            $rides = collect($rides);
        } elseif (!$rides instanceof \Illuminate\Support\Collection) {
            $rides = collect($rides);
        }
        
        $completedRides = $rides->where('status', 'completed');
        $finshedRides = $rides->where('status', 'finshed');
        $cancelledRides = $rides->where('status', 'cancelled');
        $successfulRides = $rides->whereIn('status', ['completed', 'finshed']);
        
        return [
            'total_rides' => $rides->count(),
            'completed_rides' => $completedRides->count(),
            'finshed_rides' => $finshedRides->count(),
            'successful_rides' => $successfulRides->count(),
            'cancelled_rides' => $cancelledRides->count(),
            'total_earnings' => $successfulRides->sum('calculated_final_price'),
            'total_distance' => $successfulRides->sum('total_distance_in_km'),
            'average_ride_earnings' => $successfulRides->count() > 0 ? $successfulRides->avg('calculated_final_price') : 0,
        ];
    }
}
