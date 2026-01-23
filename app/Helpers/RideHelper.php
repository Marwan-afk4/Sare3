<?php

namespace App\Helpers;

use App\Models\CarCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RideHelper
{
    private static string $googleApiKey = 'AIzaSyBQpCJAXkrWtiIQfmKdWUyClvjagGoxotY';

    private static int $snapBatchSize = 90; // smaller to allow overlap
    private static int $httpTimeout = 12;
    private static float $minMoveMeters = 6.0;
    private static float $maxJumpMeters = 5000.0;
    private static int $maxGapSeconds = 360; // if gap > 60s, use interpolation

    /**
     * Sort points by seq field if available, otherwise maintain original order
     */
    private static function sortPointsBySeq(array $points): array
    {
        // Check if any point has seq field
        $hasSeq = false;
        foreach ($points as $point) {
            if (isset($point['seq']) && $point['seq'] !== null) {
                $hasSeq = true;
                break;
            }
        }

        if (!$hasSeq) {
            Log::info('[SortPoints] No seq field found, maintaining original order');
            return $points;
        }

        // Sort by seq, handling null values by putting them at the end
        usort($points, function ($a, $b) {
            $seqA = $a['seq'] ?? PHP_INT_MAX;
            $seqB = $b['seq'] ?? PHP_INT_MAX;
            return $seqA <=> $seqB;
        });

        Log::info('[SortPoints] Sorted ' . count($points) . ' points by seq field');
        return $points;
    }



    /**
     * Check if the data has significant gaps that would benefit from interpolation
     */
    private static function hasSignificantGaps(array $points): bool
    {
        if (count($points) < 2) return false;

        $hasGaps = false;
        $gapCount = 0;

        for ($i = 0; $i < count($points) - 1; $i++) {
            $timeDiff = 0;
            if (isset($points[$i]['timestamp']) && isset($points[$i + 1]['timestamp'])) {
                $prevTime = is_string($points[$i]['timestamp']) ? strtotime($points[$i]['timestamp']) : $points[$i]['timestamp'];
                $nextTime = is_string($points[$i + 1]['timestamp']) ? strtotime($points[$i + 1]['timestamp']) : $points[$i + 1]['timestamp'];
                $timeDiff = $nextTime - $prevTime;
            }

            if ($timeDiff > self::$maxGapSeconds) {
                $gapCount++;
                $hasGaps = true;
                Log::info("[GapDetection] Gap {$gapCount}: {$timeDiff}s between points {$i} and " . ($i + 1));
            }
        }

        if ($hasGaps) {
            Log::info("[GapDetection] Total gaps found: {$gapCount} (threshold: " . self::$maxGapSeconds . "s)");
        }

        return $hasGaps;
    }

    /**
     * Sum haversine meters across a list of points
     */
    private static function sumMeters(array $points): float
    {
        $meters = 0.0;
        for ($i = 0; $i < count($points) - 1; $i++) {
            $meters += self::haversineMeters($points[$i], $points[$i + 1]);
        }
        return $meters;
    }

    /**
     * Just sum haversine distances after snapping & filtering.
     */
    public static function calculateTotalDistanceAccurate(array $points): float
    {
        if (count($points) < 2) {
            return 0.0;
        }

        Log::info('[Total] raw points: ' . count($points));

        // Sort points by seq first
        $sorted = self::sortPointsBySeq($points);
        Log::info('[Total] sorted points: ' . count($sorted));

        $filtered = self::filterPath($sorted);
        Log::info('[Total] filtered points: ' . count($filtered));

        // Get distance before snapping for comparison
        $distanceBeforeSnap = self::sumMeters($filtered);
        Log::info('[Total] distance before snap: ' . number_format($distanceBeforeSnap, 2) . 'm');

        $snapped = self::snapToRoads($filtered);
        Log::info('[Total] snapped points: ' . count($snapped));

        $totalMeters = self::sumMeters($snapped);
        $km = $totalMeters / 1000.0;
        Log::info('[Total] distance = ' . number_format($km, 3) . ' km');

        return $km;
    }

    /**
     * SnapToRoads API with dynamic interpolation
     */
    private static function snapToRoads(array $points): array
    {
        if (empty($points)) return [];

        // Check if we should use interpolation based on gaps in the data
        $shouldInterpolate = self::hasSignificantGaps($points);
        Log::info('[SnapToRoads] Using interpolation: ' . ($shouldInterpolate ? 'true' : 'false'));

        $snapped = [];

        for ($i = 0; $i < count($points); $i += self::$snapBatchSize) {
            $batch = array_slice($points, $i, self::$snapBatchSize);
            $path = collect($batch)->map(fn($p) => "{$p['lat']},{$p['lng']}")->implode('|');

            $interpolateParam = $shouldInterpolate ? 'true' : 'false';
            $url = "https://roads.googleapis.com/v1/snapToRoads?path={$path}&interpolate={$interpolateParam}&key=" . self::$googleApiKey;

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
                            'timestamp' => now(), // Add current timestamp for new points
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

        // Guard: remove duplicates (exact lat/lng)
        $dedup = [];
        foreach ($snapped as $p) {
            if (empty($dedup) || $p['lat'] != $dedup[count($dedup) - 1]['lat'] || $p['lng'] != $dedup[count($dedup) - 1]['lng']) {
                $dedup[] = $p;
            }
        }

        return $dedup;
    }

    /**
     * Filter tiny jitter, absurd jumps, and unrealistic speeds.
     */
    private static function filterPath(array $points): array
    {
        if (count($points) < 2) return $points;

        $kept = [$points[0]];

        for ($i = 1; $i < count($points); $i++) {
            $prev = $kept[count($kept) - 1];
            $curr = $points[$i];

            $d = self::haversineMeters($prev, $curr);
            
            // Check if timestamps exist and calculate time difference
            $dt = 0;
            if (isset($prev['timestamp']) && isset($curr['timestamp'])) {
                $prevTime = is_string($prev['timestamp']) ? strtotime($prev['timestamp']) : $prev['timestamp'];
                $currTime = is_string($curr['timestamp']) ? strtotime($curr['timestamp']) : $curr['timestamp'];
                $dt = $currTime - $prevTime;
                
                if ($dt <= 0) {
                    continue; // invalid timestamp
                }
            }

            // Calculate speed in km/h if we have valid timestamps
            $vKmh = 0;
            if ($dt > 0) {
                $vKmh = ($d / $dt) * 3.6;
            }

            // Filters
            if ($d < self::$minMoveMeters) {
                continue; // jitter
            }
            
            if ($d > self::$maxJumpMeters) {
                Log::info("[Filter] Skipping big jump " . number_format($d / 1000, 2) . " km at index $i");
                continue;
            }
            
            if ($vKmh > 200.0) {
                Log::info("[Filter] Replacing point due to unrealistic speed " . number_format($vKmh, 1) . " km/h at index $i");
                array_pop($kept); // remove last point
                $kept[] = $curr; // add current point
                continue;
            }

            $kept[] = $curr;
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
     * Create display-ready path for polylines (filtered and optionally snapped)
     */
    public static function makeDisplayPath(array $points, bool $snap = true): array
    {
        if (empty($points)) return [];
        
        Log::info('[DisplayPath] Processing ' . count($points) . ' points, snap=' . ($snap ? 'true' : 'false'));
        
        // Sort points by seq first
        $sorted = self::sortPointsBySeq($points);
        Log::info('[DisplayPath] Sorted points: ' . count($sorted));
        
        $filtered = self::filterPath($sorted);
        Log::info('[DisplayPath] After filtering: ' . count($filtered) . ' points');
        
        if ($snap) {
            $snapped = self::snapToRoads($filtered);
            Log::info('[DisplayPath] After snapping: ' . count($snapped) . ' points');
            return $snapped;
        }
        
        return $filtered;
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
                'status' => is_object($ride->status) ? ($ride->status instanceof \BackedEnum ? $ride->status->value : (method_exists($ride->status, 'value') ? $ride->status->value : '')) : (string) $ride->status,
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
                'status' => is_object($ride->status) ? ($ride->status instanceof \BackedEnum ? $ride->status->value : (method_exists($ride->status, 'value') ? $ride->status->value : '')) : (string) $ride->status,
                'calculated_final_price' => $ride->calculated_final_price,
                'calculated_initial_price' => $ride->calculated_initial_price,
                'coupon_discount' => $ride->coupon_discount ?? 0,
                'coupon_id' => $ride->coupon_id,
                'coupon_code' => $ride->coupon->code ?? null,
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

        // Normalize enum/string values to plain strings
        $normalized = $rides->map(function ($ride) {
            if (is_object($ride->status)) {
                // Handle enum objects
                if ($ride->status instanceof \BackedEnum) {
                    $status = $ride->status->value;
                } elseif (method_exists($ride->status, 'value')) {
                    $status = $ride->status->value;
                } else {
                    $status = (string) $ride->status;
                }
            } else {
                $status = (string) $ride->status;
            }
            $ride->normalized_status = strtolower($status);
            return $ride;
        });

        $completedRides = $normalized->where('normalized_status', 'completed');
        $finshedRides = $normalized->where('normalized_status', 'finshed');
        $cancelledRides = $normalized->where('normalized_status', 'cancelled');
        $successfulRides = $normalized->filter(function ($ride) {
            return in_array($ride->normalized_status, ['completed', 'finshed']);
        });

        return [
            'total_rides' => $normalized->count(),
            'completed_rides' => $completedRides->count() + $finshedRides->count(),
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

        // Normalize statuses for driver stats as well
        $normalized = $rides->map(function ($ride) {
            if (is_object($ride->status)) {
                // Handle enum objects
                if ($ride->status instanceof \BackedEnum) {
                    $status = $ride->status->value;
                } elseif (method_exists($ride->status, 'value')) {
                    $status = $ride->status->value;
                } else {
                    $status = (string) $ride->status;
                }
            } else {
                $status = (string) $ride->status;
            }
            $ride->normalized_status = strtolower($status);
            return $ride;
        });

        $completedRides = $normalized->where('normalized_status', 'completed');
        $finshedRides = $normalized->where('normalized_status', 'finshed');
        $cancelledRides = $normalized->where('normalized_status', 'cancelled');
        $successfulRides = $normalized->filter(function ($ride) {
            return in_array($ride->normalized_status, ['completed', 'finshed']);
        });

        return [
            'total_rides' => $normalized->count(),
            'completed_rides' => $completedRides->count() + $finshedRides->count(),
            'finshed_rides' => $finshedRides->count(),
            'successful_rides' => $successfulRides->count(),
            'cancelled_rides' => $cancelledRides->count(),
            'total_earnings' => $successfulRides->sum('calculated_final_price'),
            'total_distance' => $successfulRides->sum('total_distance_in_km'),
            'average_ride_earnings' => $successfulRides->count() > 0 ? $successfulRides->avg('calculated_final_price') : 0,
        ];
    }
}