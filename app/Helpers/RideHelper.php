<?php

namespace App\Helpers;

use App\Models\CarCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RideHelper
{
    private static string $googleApiKey = 'AIzaSyBQpCJAXkrWtiIQfmKdWUyClvjagGoxotY';

    private static int $snapBatchSize = 100;
    private static int $dirMaxPointsPerCall = 15;
    private static float $minMoveMeters = 2.0;
    private static float $maxJumpMeters = 5000.0;

    /**
     * حساب المسافة النهائية بأعلى دقة (SnapToRoads + Directions + fallback haversine).
     */
    public static function calculateTotalDistanceAccurate(array $points): float
    {
        if (count($points) < 2) {
            return 0.0;
        }

        Log::info('[Total] raw points: ' . count($points));

        $snapped = self::snapToRoads($points);
        Log::info('[Total] snapped points: ' . count($snapped));

        $filtered = self::filterPath($snapped);
        Log::info('[Total] filtered points: ' . count($filtered));

        $totalMeters = 0.0;

        // تقسيم الـ path chunks <= 15 نقطة
        for ($i = 0; $i < count($filtered) - 1;) {
            $end = ($i + self::$dirMaxPointsPerCall - 1 < count($filtered))
                ? $i + self::$dirMaxPointsPerCall - 1
                : count($filtered) - 1;

            $chunk = array_slice($filtered, $i, $end - $i + 1);
            $meters = self::directionsDistanceForChunk($chunk);
            $totalMeters += $meters;

            Log::info("[Total] chunk {$i}-{$end} => " . number_format($meters / 1000, 3) . " km");

            $i = $end;
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

            $url = "https://roads.googleapis.com/v1/snapToRoads?path={$path}&interpolate=true&key=" . self::$googleApiKey;

            try {
                $resp = Http::timeout(12)->get($url);

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
                    Log::warning('[SnapToRoads] HTTP ' . $resp->status() . ': ' . $resp->body());
                    $snapped = array_merge($snapped, $batch);
                }
            } catch (\Throwable $e) {
                Log::error('[SnapToRoads] error: ' . $e->getMessage());
                $snapped = array_merge($snapped, $batch);
            }
        }

        // remove duplicates
        $dedup = [];
        foreach ($snapped as $p) {
            if (empty($dedup) || $dedup[count($dedup) - 1]['lat'] != $p['lat'] || $dedup[count($dedup) - 1]['lng'] != $p['lng']) {
                $dedup[] = $p;
            }
        }

        return $dedup;
    }

    /**
     * Filter jitter and jumps
     */
    private static function filterPath(array $points): array
    {
        if (count($points) < 2) return $points;

        $kept = [$points[0]];
        for ($i = 1; $i < count($points); $i++) {
            $d = self::haversineMeters($kept[count($kept) - 1], $points[$i]);
            if ($d < self::$minMoveMeters) continue;
            if ($d > self::$maxJumpMeters) {
                Log::warning("[Filter] Skipping jump " . number_format($d / 1000, 2) . " km at index $i");
                continue;
            }
            $kept[] = $points[$i];
        }
        return $kept;
    }

    /**
     * Directions API chunk distance
     */
    private static function directionsDistanceForChunk(array $chunk): float
    {
        if (count($chunk) < 2) return 0.0;

        $origin = $chunk[0];
        $destination = $chunk[count($chunk) - 1];

        $via = '';
        if (count($chunk) > 2) {
            $via = '&waypoints=' . collect(array_slice($chunk, 1, -1))
                ->map(fn($p) => 'via:' . $p['lat'] . ',' . $p['lng'])
                ->implode('|');
        }

        $url = "https://maps.googleapis.com/maps/api/directions/json"
            . "?origin={$origin['lat']},{$origin['lng']}"
            . "&destination={$destination['lat']},{$destination['lng']}"
            . "&mode=driving&avoid=ferries&units=metric{$via}&key=" . self::$googleApiKey;

        try {
            $resp = Http::timeout(12)->get($url);

            if (!$resp->successful()) {
                Log::warning('[Directions] HTTP ' . $resp->status() . ': ' . $resp->body());
                return self::sumHaversine($chunk);
            }

            $data = $resp->json();
            $routes = $data['routes'] ?? [];
            if (empty($routes)) {
                Log::warning("[Directions] No routes, fallback to haversine for chunk(" . count($chunk) . ")");
                return self::sumHaversine($chunk);
            }

            $meters = 0.0;
            foreach ($routes[0]['legs'] as $leg) {
                $meters += $leg['distance']['value'] ?? 0;
            }
            return $meters;
        } catch (\Throwable $e) {
            Log::error('[Directions] error: ' . $e->getMessage());
            return self::sumHaversine($chunk);
        }
    }

    /**
     * Fallback: sum haversine
     */
    private static function sumHaversine(array $points): float
    {
        $m = 0.0;
        for ($i = 0; $i < count($points) - 1; $i++) {
            $d = self::haversineMeters($points[$i], $points[$i + 1]);
            if ($d >= self::$minMoveMeters && $d <= self::$maxJumpMeters) {
                $m += $d;
            }
        }
        return $m;
    }

    /**
     * Haversine formula (meters).
     */
    private static function haversineMeters(array $p1, array $p2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($p2['lat'] - $p1['lat']);
        $dLon = deg2rad($p2['lng'] - $p1['lng']);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($p1['lat'])) * cos(deg2rad($p2['lat'])) *
            sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
