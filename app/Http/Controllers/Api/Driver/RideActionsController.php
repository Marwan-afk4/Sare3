<?php

namespace App\Http\Controllers\Api\Driver;

use App\Helpers\RideHelper;
use App\Http\Controllers\Controller;
use App\Models\CarCategory;
use App\Models\Ride;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class RideActionsController extends Controller
{
    protected Database $firebase;

    public function __construct()
    {
        $this->firebase = (new Factory)
            ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
            ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
            ->createDatabase();
    }

    protected function validateRide(Request $request, $status = null, $requireDriverMatch = true): ?Ride
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
        ]);

        if ($validator->fails()) {
            abort(response()->json(['message' => $validator->errors()->first()], 422));
        }

        $query = Ride::where('id', $request->ride_id);

        if ($requireDriverMatch) {
            $query->where('driver_id', $request->user()->id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $ride = $query->first();

        if (!$ride) {
            abort(response()->json(['message' => 'Ride not found or invalid status.'], 404));
        }

        return $ride;
    }

    protected function updateFirebase(Ride $ride, array $data): void
    {
        $firebaseRideId = 'ride_' . $ride->id;
        $this->firebase->getReference("rides/$firebaseRideId")->update($data);
    }

    //accept ride
    public function acceptRide(Request $request)
    {
        $driver = $request->user();
        $ride = Ride::where('id', $request->ride_id)->where('status', 'pending')->first();

        if (!$ride) {
            return response()->json(['message' => 'Ride not found or not pending.'], 404);
        }

        $ride->update([
            'driver_id' => $driver->id,
            'status' => 'accepted',
        ]);

        try {
            $this->updateFirebase($ride, [
                'driver_id' => $driver->id,
                'status' => 'accepted',
                'accepted_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ride accepted in DB, but failed in Firebase.',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json(['message' => 'Ride accepted.']);
    }

    //arrived
    public function arrived(Request $request)
    {
        $ride = $this->validateRide($request);

        $ride->update(['status' => 'waiting_user']);

        try {
            $this->updateFirebase($ride, [
                'status' => 'waiting_user',
                'arrived_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firebase error.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Marked as arrived.']);
    }

    //start ride
    public function startRide(Request $request)
    {
        $ride = $this->validateRide($request);

        $ride->update(['status' => 'in_progress']);

        try {
            $this->updateFirebase($ride, [
                'status' => 'in_progress',
                'started_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firebase error.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Ride started.']);
    }

    //complete ride
    public function completeRide(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()], 500);
        }

        $ride = Ride::findOrFail($request->ride_id);

        if ($ride->driver_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $points = is_array($ride->route_points) ? $ride->route_points : json_decode($ride->route_points, true);

        if (!is_array($points) || count($points) < 2) {
            return response()->json(['message' => 'Not enough points to calculate distance'], 400);
        }

        // 1️⃣ Distance Calculation
        $distanceKm = RideHelper::calculateTotalDistance($points);

        // 2️⃣ Car Category
        $carCategory = CarCategory::find($ride->car_category_id);
        if (!$carCategory) {
            return response()->json(['message' => 'Car category not found'], 404);
        }

        // 3️⃣ Duration Calculation
        $startTimestamp = $points[0]['timestamp'];
        $endTimestamp = $points[count($points) - 1]['timestamp'];
        $startTime = Carbon::createFromTimestamp($startTimestamp);
        $endTime = Carbon::createFromTimestamp($endTimestamp);
        $durationMinutes = $startTime->diffInMinutes($endTime);

        // 4️⃣ Fare Calculation
        $timeFare = $durationMinutes * $carCategory->price_per_time;
        $fare = $carCategory->base_price + ($distanceKm * $carCategory->price_per_km) + $timeFare;

        // 5️⃣ Update Ride
        $ride->update([
            'calculated_final_price' => round($fare, 2),
            'status' => 'completed',
            'ended_at' => $endTime,
            'time_taken' => $durationMinutes,
        ]);

        // 6️⃣ Push to Firebase
        try {
            $this->updateFirebase($ride, [
                'status' => 'completed',
                'completed_at' => $endTime->toIso8601String(),
                'final_price' => [
                    'fare' => round($fare, 2),
                    'distance_km' => round($distanceKm, 2),
                    'duration_minutes' => $durationMinutes,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firebase error.', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Ride completed.',
            'final_price' => round($fare, 2),
            'distance_km' => round($distanceKm, 2),
            'duration_minutes' => $durationMinutes,
        ]);
    }

    //finsh ride
    public function finishRide(Request $request)
    {
        $ride = $this->validateRide($request);

        $ride->update(['status' => 'finshed']);

        try {
            $this->updateFirebase($ride, [
                'status' => 'finshed',
                'started_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firebase error.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Ride started.']);
    }

    //cancel ride
    public function cancelRide(Request $request)
    {
        $ride = $this->validateRide($request, 'pending', false);

        $ride->update(['status' => 'rejected']);

        try {
            $this->updateFirebase($ride, [
                'status' => 'rejected',
                'canceled_at' => now()->toIso8601String(),
                // keep driver_id untouched
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ride canceled in DB, but failed to update Firebase.',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json(['message' => 'Ride rejected.']);
    }
}
