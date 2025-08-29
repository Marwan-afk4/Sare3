<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Services\RideVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RideActionsController extends Controller
{
    /**
     * Get verification code for user's ride
     */
    public function getVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $ride = Ride::where('id', $request->ride_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$ride) {
            return response()->json(['message' => 'Ride not found or unauthorized.'], 404);
        }

        $verificationService = new RideVerificationService();
        
        if (!$verificationService->isVerificationEnabled()) {
            return response()->json([
                'message' => 'Verification feature is disabled.',
                'verification_required' => false
            ]);
        }

        if (empty($ride->verification_code)) {
            return response()->json([
                'message' => 'No verification code available for this ride.',
                'verification_required' => false
            ]);
        }

        return response()->json([
            'message' => 'Verification code retrieved successfully.',
            'verification_code' => $ride->verification_code,
            'verification_required' => true,
            'ride_status' => $ride->status->value,
            'code_generated_at' => $ride->verification_code_generated_at?->toIso8601String()
        ]);
    }

    /**
     * Get ride status including verification info
     */
    public function getRideStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $ride = Ride::with(['driver', 'carCategory'])
            ->where('id', $request->ride_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$ride) {
            return response()->json(['message' => 'Ride not found or unauthorized.'], 404);
        }

        $verificationService = new RideVerificationService();
        $verificationStatus = $verificationService->getVerificationStatus($ride);

        return response()->json([
            'ride' => [
                'id' => $ride->id,
                'status' => $ride->status->value,
                'driver' => $ride->driver ? [
                    'id' => $ride->driver->id,
                    'name' => $ride->driver->name,
                    'phone' => $ride->driver->phone,
                ] : null,
                'car_category' => $ride->carCategory?->name,
                'pickup_address' => $ride->pickup_address,
                'dropoff_address' => $ride->dropoff_address,
                'created_at' => $ride->created_at->toIso8601String(),
            ],
            'verification' => $verificationStatus,
            'verification_code' => $verificationStatus['verification_required'] ? $ride->verification_code : null
        ]);
    }
}