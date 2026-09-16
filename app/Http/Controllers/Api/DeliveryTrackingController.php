<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryTrackingController extends Controller
{
    /**
     * Current rider location for a delivery (latest route point, else the
     * rider's live DB position).
     */
    public function getRiderLocation(Request $request, $deliveryId): JsonResponse
    {
        $delivery = Delivery::find($deliveryId);

        if (!$delivery) {
            return response()->json(['error' => 'Delivery not found'], 404);
        }

        if (!in_array($delivery->status->value, ['accepted', 'arrived', 'in_progress'])) {
            return response()->json(['error' => 'Delivery is not in trackable status'], 400);
        }

        $routePoints = $delivery->route_points ?? [];

        if (!empty($routePoints)) {
            $latest = end($routePoints);
            return response()->json([
                'lat' => $latest['lat'],
                'lng' => $latest['lng'],
                'timestamp' => $latest['timestamp'] ?? null,
                'status' => $delivery->status->value,
            ]);
        }

        $rider = $delivery->rider;
        if ($rider && $rider->latitude && $rider->longitude) {
            return response()->json([
                'lat' => (float) $rider->latitude,
                'lng' => (float) $rider->longitude,
                'timestamp' => $rider->updated_at?->timestamp,
                'status' => $delivery->status->value,
            ]);
        }

        return response()->json(['error' => 'No location data available'], 404);
    }

    /**
     * All route points for a delivery.
     */
    public function getRoutePoints(Request $request, $deliveryId): JsonResponse
    {
        $delivery = Delivery::find($deliveryId);

        if (!$delivery) {
            return response()->json(['error' => 'Delivery not found'], 404);
        }

        $points = $delivery->route_points ?? [];

        return response()->json([
            'points' => $points,
            'total_points' => count($points),
        ]);
    }

    /**
     * Aggregate tracking data for the map (both legs + live rider location).
     */
    public function getTrackingData(Request $request, $deliveryId): JsonResponse
    {
        $delivery = Delivery::with(['user', 'rider.riderVehicle', 'offers.rider', 'riderCancelledBy'])->find($deliveryId);

        if (!$delivery) {
            return response()->json(['error' => 'Delivery not found'], 404);
        }

        $status = $delivery->status->value;
        $routePoints = $delivery->route_points ?? [];
        $toPickupPoints = $delivery->to_pickup_route_points ?? [];

        $deliveryData = [
            'id' => $delivery->id,
            'status' => $status,
            'user' => $delivery->user?->name,
            'rider' => $delivery->rider?->name,
            'rider_id' => $delivery->rider_id,
            'vehicle_type' => $delivery->vehicle_type?->value,
            'pickup' => [
                'lat' => (float) $delivery->pickup_lat,
                'lng' => (float) $delivery->pickup_lng,
                'address' => $delivery->pickup_address,
            ],
            'estimated_km' => $delivery->estimated_km,
            'total_distance_in_km' => $delivery->total_distance_in_km,
            'estimated_time' => $delivery->estimated_time,
            'time_taken' => $delivery->time_taken,
            'created_at' => $delivery->created_at,
            'accepted_at' => $delivery->accepted_at,
            'arrived_at' => $delivery->arrived_at,
            'started_at' => $delivery->started_at,
            'completed_at' => $delivery->completed_at,
            'ended_at' => $delivery->ended_at,
        ];

        if ($delivery->dropoff_lat && $delivery->dropoff_lng) {
            $deliveryData['dropoff'] = [
                'lat' => (float) $delivery->dropoff_lat,
                'lng' => (float) $delivery->dropoff_lng,
                'address' => $delivery->dropoff_address,
            ];
        }

        $liveRiderLocation = null;
        if ($delivery->rider_id && in_array($status, ['accepted', 'arrived', 'in_progress'])) {
            $rider = $delivery->rider;
            if ($rider && $rider->latitude && $rider->longitude) {
                $liveRiderLocation = [
                    'lat' => (float) $rider->latitude,
                    'lng' => (float) $rider->longitude,
                    'bearing' => (float) ($rider->bearing ?? 0),
                    'timestamp' => $rider->updated_at?->timestamp,
                ];
            }
        }

        $offersData = [];
        foreach ($delivery->offers as $offer) {
            $offersData[] = [
                'rider_name' => $offer->rider?->name ?? ('Rider #' . $offer->rider_id),
                'response' => $offer->response,
                'response_label' => $offer->responseLabel(),
                'response_color' => $offer->responseColor(),
                'response_seconds' => $offer->response_seconds,
                'offered_at' => $offer->offered_at ? $offer->offered_at->translatedFormat('h:i:s A') : null,
                'lat' => $offer->rider_lat !== null ? (float) $offer->rider_lat : null,
                'lng' => $offer->rider_lng !== null ? (float) $offer->rider_lng : null,
            ];
        }

        return response()->json([
            'delivery' => $deliveryData,
            'route_points' => $routePoints,
            'to_pickup_route_points' => $toPickupPoints,
            'live_rider_location' => $liveRiderLocation,
            'total_points' => count($routePoints),
            'is_trackable' => in_array($status, ['accepted', 'arrived', 'in_progress']),
            'offers' => $offersData,
        ]);
    }
}
