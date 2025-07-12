<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\Ride;
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

    protected function validateRide(Request $request, $status = null): ?Ride
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
        ]);

        if ($validator->fails()) {
            abort(response()->json(['message' => $validator->errors()->first()], 422));
        }

        $query = Ride::where('id', $request->ride_id)
            ->where('driver_id', $request->user()->id);

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

        return response()->json(['message' => 'Ride accepted.', 'ride' => $ride]);
    }

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

        return response()->json(['message' => 'Marked as arrived.', 'ride' => $ride]);
    }

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

        return response()->json(['message' => 'Ride started.', 'ride' => $ride]);
    }

    public function completeRide(Request $request)
    {
        $ride = $this->validateRide($request);

        $ride->update(['status' => 'completed']);

        try {
            $this->updateFirebase($ride, [
                'status' => 'completed',
                'completed_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firebase error.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Ride completed.', 'ride' => $ride]);
    }

    public function cancelRide(Request $request)
    {
        $ride = $this->validateRide($request);

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

        return response()->json(['message' => 'Ride rejected.', 'ride' => $ride]);
    }
}
