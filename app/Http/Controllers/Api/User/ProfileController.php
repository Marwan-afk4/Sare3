<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Helpers\RideHelper;
use App\Models\OtpLimit;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\User;
use App\trait\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use ImageUpload;

    public function getProfileData(Request $request)
    {
        $user = $request->user();

        $user->load([
            'userRides' => function ($query) {
                $query->whereIn('status', ['completed','finshed','cancelled']);
            },
            'userRides.driver.driverCars',
        ]);

        $userRating = Rating::where('ratee_id', $user->id)
            ->where('ratee_type', 'user')
            ->avg('rate');

        $completedRides = $user->userRides;

        // Format ride data using helper
        $ridesData = RideHelper::formatUserRideHistory($completedRides);
        $rideStatistics = RideHelper::getUserRideStatistics($completedRides);

        // Response data
        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'image_link' => $user->image_link,
            'rides' => $rideStatistics,
            'rides_data' => $ridesData,
            'wallet' => $user->wallet,
            'activity' => $user->activity,
            'user_rating' => $userRating,
            'email_verified' => (bool) $user->email_verified,
        ];

        return response()->json(['user' => $response]);
    }

    public function updateUserProfile(Request $request)
    {
        $user = $request->user();

        $validation = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|unique:users,phone,' . $user->id,
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }

        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully'
        ]);
    }

    public function isInRide(Request $request)
    {
        $user = $request->user();

        $userRide = Ride::whereNotIn('status',['finshed', 'cancelled'])
            ->where('user_id', $user->id)
            ->select('id', 'status')
            ->get();

        return response()->json([
            'is_in_ride' => $userRide
        ]);
    }

    //check user or driver otp limit
    public function checkUserOtpLimit(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }

        $phone = $request->phone;
        $rawPhone = ltrim($phone, '+');
        $user = User::where('phone', $phone)
                    ->orWhere('phone', $rawPhone)
                    ->first();

        $otpLimit = OtpLimit::where('type', 'user')->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'remaining_otp' => $otpLimit->otp_limit ?? 5
            ], 404);
        }

        // Standardize format if matched legacy
        if ($user->phone !== $phone) {
            $user->update(['phone' => $phone]);
        }

        $remainingOtp = max(0, $user->otp_limit - $user->otp_used);

        return response()->json([
            'message' => 'User already exists.',
            'remaining_otp' => $remainingOtp,
            'isPassExist' => !empty($user->password),
        ])->setStatusCode(200, 'User already exists. Remaining OTP: ' . $remainingOtp);
    }

    public function getUserRideHistory(Request $request)
    {
        $user = $request->user();

        $validation = Validator::make($request->all(), [
            'status' => 'nullable|string|in:completed,finshed,cancelled,all',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }

        $status = $request->get('status', 'all');
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        // Build query
        $query = $user->userRides()
            ->with([
                'driver.driverCars.carModel',
                'carCategory'
            ])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status !== 'all') {
            if ($status === 'completed') {
                $query->whereIn('status', ['completed', 'finshed']);
            } else {
                $query->where('status', $status);
            }
        }

        // Paginate
        $rides = $query->paginate($limit, ['*'], 'page', $page);

        // Format data
        $ridesData = RideHelper::formatUserRideHistory(collect($rides->items()));
        $rideStatistics = RideHelper::getUserRideStatistics(collect($rides->items()));

        return response()->json([
            'user' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'statistics' => $rideStatistics,
                'rides_data' => $ridesData,
                'pagination' => [
                    'current_page' => $rides->currentPage(),
                    'last_page' => $rides->lastPage(),
                    'per_page' => $rides->perPage(),
                    'total' => $rides->total(),
                    'has_more_pages' => $rides->hasMorePages(),
                ]
            ]
        ]);
    }

    public function addZoneId(Request $request)
    {
        $user = $request->user();

        $validation = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }

        $user->zone_id = $request->zone_id;
        $user->save();

        return response()->json([
            'message' => 'Zone ID added successfully'
        ]);
    }
}
