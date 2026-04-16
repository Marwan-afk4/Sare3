<?php

namespace App\Http\Controllers\Api\Driver;

use App\Helpers\RideHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PushDriverLocationToFirebase;
use App\Models\CarCategory;
use App\Models\Ride;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class DriverLocationController extends Controller
{


    public function updateDriverLocation(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'ride_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'seq' => 'nullable|integer',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()], 422);
        }

        $ride = Ride::findOrFail($request->ride_id);

        // Verify driver owns this ride
        if ($ride->driver_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only update location for active rides
        if (!in_array($ride->status->value, ['accepted', 'waiting_user', 'in_progress'])) {
            return response()->json(['message' => 'Ride is not in trackable status'], 400);
        }

        // Tag each point with the current ride phase so the admin dashboard
        // can render the "on the way to passenger" path separately from the
        // actual trip path. Any status before the trip has started is
        // considered the pickup leg.
        $status = $ride->status->value;
        $phase = in_array($status, ['accepted', 'waiting_user']) ? 'to_pickup' : 'trip';

        $points = $ride->route_points ?? [];
        $newPoint = [
            'lat' => (float) $request->lat,
            'lng' => (float) $request->lng,
            'timestamp' => now()->timestamp,
            'seq' => $request->seq ?? null,
            'phase' => $phase,
        ];

        $points[] = $newPoint;

        $ride->route_points = $points;
        $ride->save();

        // Update Firebase for real-time tracking
        // $firebaseService = app(\App\Services\FirebaseService::class);
        // $firebaseService->updateDriverLocation(
        //     $ride->id,
        //     $request->lat,
        //     $request->lng,
        //     $ride->firebase_ride_id
        // );

        try {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                ->createDatabase();

            $firebaseRideId = $ride->firebase_ride_id ?: 'ride_' . $ride->id;

            $firebase->getReference("rides/{$firebaseRideId}/driver_location")->set([
                'lat' => (float) $request->lat,
                'lng' => (float) $request->lng,
                'timestamp' => now()->timestamp,
                'updated_at' => now()->toIso8601String(),
                'phase' => $phase,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            Log::error('Firebase location update failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Driver location updated successfully',
            'total_points' => count($points)
        ]);
    }




    // public function endRide(Request $request)
    // {
    //     $validation = Validator::make($request->all(), [
    //         'ride_id' => 'required|exists:rides,id',
    //     ]);

    //     if ($validation->fails()) {
    //         return response()->json(['message' => $validation->errors()], 500);
    //     }

    //     $ride = Ride::findOrFail($request->ride_id);

    //     if ($ride->driver_id !== auth()->id()) {
    //         return response()->json(['message' => 'Unauthorized'], 403);
    //     }

    //     $points = is_array($ride->route_points) ? $ride->route_points : json_decode($ride->route_points, true);

    //     if (!is_array($points) || count($points) < 2) {
    //         return response()->json(['message' => 'Not enough points to calculate distance'], 400);
    //     }

    //     // 1️⃣ Distance Calculation
    //     $distanceKm = RideHelper::calculateTotalDistance($points);

    //     // 2️⃣ Car Category
    //     $carCategory = CarCategory::find($ride->car_category_id);
    //     if (!$carCategory) {
    //         return response()->json(['message' => 'Car category not found'], 404);
    //     }

    //     // 3️⃣ Duration Calculation (Time fare)
    //     $startTimestamp = $points[0]['timestamp'];
    //     $endTimestamp = $points[count($points) - 1]['timestamp'];

    //     $startTime = Carbon::createFromTimestamp($startTimestamp);
    //     $endTime = Carbon::createFromTimestamp($endTimestamp);

    //     $durationMinutes = $startTime->diffInMinutes($endTime);

    //     // 4️⃣ Calculate Fare
    //     $timeFare = $durationMinutes * $carCategory->price_per_time;
    //     $fare = $carCategory->base_price + ($distanceKm * $carCategory->price_per_km) + $timeFare;

    //     // 5️⃣ Update Ride
    //     $ride->update([
    //         'calculated_final_price' => round($fare, 2),
    //         'status' => 'completed',
    //         'ended_at' => $endTime,
    //         'duration_minutes' => $durationMinutes,
    //     ]);

    //     // 6️⃣ Push final price to Firebase
    //     $firebase = (new Factory)
    //         ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
    //         ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
    //         ->createDatabase();

    //     $firebase->getReference("rides/{$ride->firebase_ride_id}/final_price")->set([
    //         'fare' => round($fare, 2),
    //         'distance_km' => round($distanceKm, 2),
    //         'duration_minutes' => $durationMinutes,
    //         'status' => 'completed',
    //     ]);

    //     return response()->json([
    //         'message' => 'Ride completed',
    //         'final_price' => round($fare, 2),
    //         'distance_km' => round($distanceKm, 2),
    //         'duration_minutes' => $durationMinutes,
    //     ]);
    // }



}
