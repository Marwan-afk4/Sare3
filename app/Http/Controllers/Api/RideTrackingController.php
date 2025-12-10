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
     * Get complete route points for a ride (filtered and optionally snapped)
     */
    public function getRoutePoints(Request $request, $rideId): JsonResponse
    {
        $ride = Ride::find($rideId);
        
        if (!$ride) {
            return response()->json(['error' => 'Ride not found'], 404);
        }

        $points = $ride->route_points ?? [];
        if (empty($points)) {
            return response()->json(['points' => []]);
        }

        // Determine if we should snap to roads (only for completed rides)
        $status = is_object($ride->status) ? $ride->status->value : $ride->status;
        $snap = in_array($status, ['completed', 'finshed']);

        // Use RideHelper to get filtered/snapped points
        $displayPoints = \App\Helpers\RideHelper::makeDisplayPath($points, $snap);

        return response()->json([
            'points' => $displayPoints,
            'total_points' => count($points),
            'filtered_points' => count($displayPoints),
            'snapped' => $snap
        ]);
    }

    /**
     * Get tracking data for a ride (for refreshing)
     */
    public function getTrackingData(Request $request, $rideId): JsonResponse
    {
        $ride = Ride::find($rideId);
        
        if (!$ride) {
            return response()->json(['error' => 'Ride not found'], 404);
        }

        $routePoints = $ride->route_points ?? [];
        
        // Get filtered points for display
        $status = is_object($ride->status) ? $ride->status->value : $ride->status;
        $snap = in_array($status, ['completed', 'finshed']);
        $displayPoints = \App\Helpers\RideHelper::makeDisplayPath($routePoints, $snap);

        $rideData = [
            'id' => $ride->id,
            'status' => $status,
            'driver_id' => $ride->driver_id,
            'pickup_lat' => (float) $ride->pickup_lat,
            'pickup_lng' => (float) $ride->pickup_lng,
            'pickup_address' => $ride->pickup_address,
        ];

        // Only include dropoff if coordinates exist
        if ($ride->dropoff_lat && $ride->dropoff_lng) {
            $rideData['dropoff_lat'] = (float) $ride->dropoff_lat;
            $rideData['dropoff_lng'] = (float) $ride->dropoff_lng;
            $rideData['dropoff_address'] = $ride->dropoff_address;
        }

        return response()->json([
            'ride' => $rideData,
            'route_points' => $displayPoints,
            'total_points' => count($routePoints),
            'filtered_points' => count($displayPoints),
            'snapped' => $snap
        ]);
    }

    /**
     * Get ride tracking data for dashboard (alias for compatibility)
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

        // Get filtered points for display
        $status = is_object($ride->status) ? $ride->status->value : $ride->status;
        $snap = in_array($status, ['completed', 'finshed']);
        $displayPoints = \App\Helpers\RideHelper::makeDisplayPath($routePoints, $snap);

        $rideData = [
            'id' => $ride->id,
            'status' => $status,
            'user' => $ride->user?->name,
            'driver' => $ride->driver?->name,
            'car_category' => $ride->carCategory?->name,
            'pickup' => [
                'lat' => (float) $ride->pickup_lat,
                'lng' => (float) $ride->pickup_lng,
                'address' => $ride->pickup_address
            ],
            'estimated_km' => $ride->estimated_km,
            'total_distance_in_km' => $ride->total_distance_in_km,
            'estimated_time' => $ride->estimated_time,
            'time_taken' => $ride->time_taken,
            'created_at' => $ride->created_at,
            'started_at' => $ride->started_at,
            'ended_at' => $ride->ended_at
        ];

        // Only include dropoff if coordinates exist
        if ($ride->dropoff_lat && $ride->dropoff_lng) {
            $rideData['dropoff'] = [
                'lat' => (float) $ride->dropoff_lat,
                'lng' => (float) $ride->dropoff_lng,
                'address' => $ride->dropoff_address
            ];
        }

        return response()->json([
            'ride' => $rideData,
            'route_points' => $displayPoints, // Use filtered points
            'total_points' => count($routePoints),
            'filtered_points' => count($displayPoints),
            'snapped' => $snap,
            'latest_location' => $latestLocation,
            'is_trackable' => in_array($status, ['in_progress', 'accepted', 'waiting_user'])
        ]);
    }
}