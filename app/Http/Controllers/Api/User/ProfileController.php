<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
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
            'userRides.driver.driverCars',
        ]);

        // Filter only completed rides
        $completedRides = $user->userRides->where('status', 'completed');

        // Map ride data
        $ridesData = $completedRides->map(function ($ride) {
            $driver = optional($ride->driver);
            $car = optional(optional($driver)->driverCars->first());

            return [
                'ride_id' => $ride->id,
                'driver' => [
                    'driver_id' => $driver->id,
                    'driver_name' => $driver->name,
                    'driver_image_link' => $driver->image_link,
                    'driver_phone' => $driver->phone,
                ],
                'car' => [
                    'car_number' => $car->car_number,
                    'car_model' => $car->car_model,
                    'car_image_link' => $car->car_image_link,
                ],
                'pickup_address' => $ride->pickup_address,
                'dropoff_address' => $ride->dropoff_address,
                'started_at' => $ride->started_at,
                'ended_at' => $ride->ended_at,
                'status' => $ride->status,
                'calculated_final_price' => $ride->calculated_final_price,
                'created_at' => $ride->created_at,
            ];
        })->values();

        // Response data
        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'image_link' => $user->image_link,
            'rides' => [
                'rides_count' => $completedRides->count(),
                'total_earning' => $completedRides->sum('calculated_final_price'),
            ],
            'rides_data' => $ridesData,
            'wallet' => $user->wallet,
            'activity' => $user->activity,
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
}
