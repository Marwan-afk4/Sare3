<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DriverProfileController extends Controller
{


    public function getProfileData(Request $request)
    {
        $user = $request->user();

        $user->load([
        'driverRides.driver.driverCars.carModel',
        'driverCars.carModel', // <- this line
    ]);

        $firstCar = $user->driverCars->first(); // get the first car object

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'gender' => $user->gender,
            'email' => $user->email,
            'email_verified' => $user->email_verified,
            'phone' => $user->phone,
            'image_link' => $user->image_link,
            'activity' => $user->activity->value,
            'status' => $user->status->value,
            'rejected_reason' => $user->rejected_reason ?? 'your account is not rejected',
            'wallet' => $user->wallet,
            'car' => [
                'car_number' => $firstCar->car_number ?? null,
                'car_model' => $firstCar?->carModel?->name ?? null,
                'car_color' => $firstCar->car_color ?? null,
                'car_category_id' => $firstCar->car_category_id ?? null,
                'car_category' => $firstCar->carCategory->name ?? null,
                'car_type' => $firstCar->carType->type_name ?? null,
                'car_license' => $firstCar->car_license_link ?? null,
                'car_image_link' => $firstCar->car_image_link ?? null,
            ]
        ];

        return response()->json($data);
    }


    public function updateDriverProfile(Request $request)
    {
        $driver = $request->user();

        $validation = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $driver->id,
            'phone' => 'nullable|unique:users,phone,' . $driver->id,
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }

        $driver->name = $request->name ?? $driver->name;
        $driver->email = $request->email ?? $driver->email;
        $driver->phone = $request->phone ?? $driver->phone;
        $driver->save();

        return response()->json([
            'message' => 'Profile updated successfully'
        ]);
    }
}

