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

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            //last rides lsa
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

