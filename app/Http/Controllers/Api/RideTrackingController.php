<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RideTrackingController extends Controller
{
    /**
     * Get current driver location for a ride
     */
    public function getDriverLocation(Request $request, $rideId): JsonResponse
    {
        $ride = Ride::find($rideId);
        
        if (!$ride) {
            return response()->json(['error' => 'Ride not found'], 404);
        }

        // Check if ride is in trackable status
        if (!in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user'])) {
            return response()->json(['error' => 'Ride is not in trackable status'], 400);
        }

        // Get the latest location from route_points
        $routePoints = $ride->route_points ?? [];
        
        if (empty($routePoints)) {
            return response()->json(['error' => 'No location data available'], 404);
        }

        // Get the most recent point
        $latestPoint = end($routePoints);
        
        return response()->json([
            'lat' => $latestPoint['lat'],
            'lng' => $latestPoint['lng'],
            'timestamp' => $latestPoint['timestamp'] ?? null,
            'status' => $ride->status->value
        ]);
    }

    /**
     * Get complete route points for a ride
     */
    public function getRoutePoints(Request $request, $rideId): JsonResponse
    {
        $ride = Ride::find($rideId);
        
        if (!$ride) {
            return response()->json(['error' => 'Ride not found'], 404);
        }

        $routePoints = $ride->route_points ?? [];
        
        return response()->json([
            'route_points' => $routePoints,
            'status' => $ride->status->value,
            'pickup' => [
                'lat' => $ride->pickup_lat,
                'lng' => $ride->pickup_lng,
                'address' => $ride->pickup_address
            ],
            'dropoff' => [
                'lat' => $ride->dropoff_lat,
                'lng' => $ride->dropoff_lng,
                'address' => $ride->dropoff_address
            ]
        ]);
    }

    /**
     * Get ride tracking data for dashboard
     */
    public function getRideTrackingData(Request $request, $rideId): JsonResponse
    {
        $ride = Ride::with(['user', 'driver', 'carCategory'])->find($rideId);
        
        if (!$ride) {
            return response()->json(['error' => 'Ride not found'], 404);
        }

        $routePoints = $ride->route_points ?? [];
        $latestLocation = null;
        
        if (!empty($routePoints)) {
            $latestLocation = end($routePoints);
        }

        return response()->json([
            'ride' => [
                'id' => $ride->id,
                'status' => $ride->status->value,
                'user' => $ride->user?->name,
                'driver' => $ride->driver?->name,
                'car_category' => $ride->carCategory?->name,
                'pickup' => [
                    'lat' => $ride->pickup_lat,
                    'lng' => $ride->pickup_lng,
                    'address' => $ride->pickup_address
                ],
                'dropoff' => [
                    'lat' => $ride->dropoff_lat,
                    'lng' => $ride->dropoff_lng,
                    'address' => $ride->dropoff_address
                ],
                'estimated_km' => $ride->estimated_km,
                'total_distance_in_km' => $ride->total_distance_in_km,
                'estimated_time' => $ride->estimated_time,
                'time_taken' => $ride->time_taken,
                'created_at' => $ride->created_at,
                'started_at' => $ride->started_at,
                'ended_at' => $ride->ended_at
            ],
            'route_points' => $routePoints,
            'latest_location' => $latestLocation,
            'is_trackable' => in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user'])
        ]);
    }
}