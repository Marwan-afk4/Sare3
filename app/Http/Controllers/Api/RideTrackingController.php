<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RideTrackingController extends Controller
{
    /**
     * Split raw route_points into the "to pickup" leg (from accept -> arrived)
     * and the actual "trip" leg (from start -> completed), based on the
     * phase tag attached when points were saved. Falls back to
     * arrived_at/trip_started_at timestamps for legacy rides that don't
     * carry the phase tag yet.
     */
    protected function splitRoutePointsByPhase(Ride $ride): array
    {
        $points = $ride->route_points ?? [];

        if (empty($points)) {
            return ['to_pickup' => [], 'trip' => []];
        }

        $toPickup = [];
        $trip = [];

        $hasPhase = false;
        foreach ($points as $p) {
            if (isset($p['phase'])) {
                $hasPhase = true;
                break;
            }
        }

        if ($hasPhase) {
            foreach ($points as $p) {
                $phase = $p['phase'] ?? null;
                if ($phase === 'to_pickup') {
                    $toPickup[] = $p;
                } elseif ($phase === 'trip') {
                    $trip[] = $p;
                } else {
                    // Unknown phase: keep it in the trip bucket so nothing
                    // disappears from the visualization.
                    $trip[] = $p;
                }
            }
        } else {
            // Legacy data: decide by timestamp relative to trip_started_at.
            $tripStart = $ride->trip_started_at
                ? $ride->trip_started_at->timestamp
                : ($ride->arrived_at ? $ride->arrived_at->timestamp : null);

            foreach ($points as $p) {
                $ts = (int) ($p['timestamp'] ?? 0);
                if ($tripStart !== null && $ts < $tripStart) {
                    $toPickup[] = $p;
                } else {
                    $trip[] = $p;
                }
            }
        }

        return ['to_pickup' => $toPickup, 'trip' => $trip];
    }

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
     * Get ride tracking data for dashboard (alias for compatibility).
     *
     * In addition to the flat `route_points` field kept for backward
     * compatibility, the response now includes:
     *  - driver_accept_location: where the captain was when accepting
     *  - driver_arrived_location: where the captain was when marking arrived
     *  - to_pickup_route_points: polyline for the captain -> passenger path
     *  - trip_route_points: polyline for the passenger -> destination path
     *  - live_driver_location: driver's live position from Firebase RTDB
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
        $isCompleted = in_array($status, ['completed', 'finshed']);
        $snap = $isCompleted;

        // Split by phase BEFORE filtering/snapping so each leg is processed
        // independently (snap-to-roads works best on a single continuous leg).
        $segments = $this->splitRoutePointsByPhase($ride);
        $toPickupDisplay = \App\Helpers\RideHelper::makeDisplayPath($segments['to_pickup'], $snap);
        $tripDisplay = \App\Helpers\RideHelper::makeDisplayPath($segments['trip'], $snap);

        // Flat list (legacy consumers).
        $displayPoints = \App\Helpers\RideHelper::makeDisplayPath($routePoints, $snap);

        $rideData = [
            'id' => $ride->id,
            'status' => $status,
            'user' => $ride->user?->name,
            'driver' => $ride->driver?->name,
            'driver_id' => $ride->driver_id,
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
            'accepted_at' => $ride->accepted_at,
            'arrived_at' => $ride->arrived_at,
            'trip_started_at' => $ride->trip_started_at,
            'completed_at' => $ride->completed_at,
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

        $driverAcceptLocation = null;
        if ($ride->driver_accept_lat !== null && $ride->driver_accept_lng !== null) {
            $driverAcceptLocation = [
                'lat' => (float) $ride->driver_accept_lat,
                'lng' => (float) $ride->driver_accept_lng,
                'recorded_at' => $ride->accepted_at,
            ];
        }

        $driverArrivedLocation = null;
        if ($ride->driver_arrived_lat !== null && $ride->driver_arrived_lng !== null) {
            $driverArrivedLocation = [
                'lat' => (float) $ride->driver_arrived_lat,
                'lng' => (float) $ride->driver_arrived_lng,
                'recorded_at' => $ride->arrived_at,
            ];
        }

        // Pull the driver's current live position from the database.
        // This powers the "track the captain on his way to the passenger" view
        // on the dashboard. Only meaningful for rides that are still active.
        $liveDriverLocation = null;
        if ($ride->driver_id && in_array($status, ['accepted', 'waiting_user', 'in_progress'])) {
            $driver = $ride->driver;
            if ($driver && $driver->latitude && $driver->longitude) {
                $liveDriverLocation = [
                    'lat' => (float) $driver->latitude,
                    'lng' => (float) $driver->longitude,
                    'bearing' => (float) ($driver->bearing ?? 0),
                    'timestamp' => $driver->updated_at->timestamp,
                ];
            }
        }

        return response()->json([
            'ride' => $rideData,
            'route_points' => $displayPoints, // legacy flat list
            'to_pickup_route_points' => $toPickupDisplay,
            'trip_route_points' => $tripDisplay,
            'driver_accept_location' => $driverAcceptLocation,
            'driver_arrived_location' => $driverArrivedLocation,
            'live_driver_location' => $liveDriverLocation,
            'total_points' => count($routePoints),
            'filtered_points' => count($displayPoints),
            'snapped' => $snap,
            'latest_location' => $latestLocation,
            'is_trackable' => in_array($status, ['in_progress', 'accepted', 'waiting_user'])
        ]);
    }
}