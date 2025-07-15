<?php

namespace App\Helpers;

use App\Models\Ride;
use App\Models\CarCategory;

class RideHelper
{
    /**
     * Calculate the distance (in km) from route points using the Haversine formula.
     */
    public static function calculateTotalDistance(array $points): float
    {
        $distanceKm = 0;

        for ($i = 0; $i < count($points) - 1; $i++) {
            $distanceKm += self::haversine(
                $points[$i]['lat'], $points[$i]['lng'],
                $points[$i + 1]['lat'], $points[$i + 1]['lng']
            );
        }

        return $distanceKm;
    }

    /**
     * Calculate the final ride fare.
     */
    public static function calculateFare(CarCategory $carCategory, float $distanceKm, float $durationMinutes = 0): float
    {
        return $carCategory->base_price
            + ($carCategory->price_per_km * $distanceKm)
            + ($carCategory->price_per_time * $durationMinutes);
    }


    /**
     * Haversine formula.
     */
    private static function haversine($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
