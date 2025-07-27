<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RideSettingCOntroller extends Controller
{


    public function addRideSetting(Request $request)
    {
        $driver = $request->user();

        $validation = Validator::make($request->all(), [
            'pickup_radius' => 'required|numeric|min:0',
            'destination_preferences' => 'required|array',
            'early_trip_suggestions' => 'required|boolean',
            'same_gender_trips' => 'required|boolean',
        ]);


        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors()], 422);
        }

        $rideSetting = $driver->driverRideSetting()->updateOrCreate(
            ['driver_id' => $driver->id],
            [
                'pickup_radius' => $request->pickup_radius,
                'destination_preferences' => $request->destination_preferences,
                'early_trip_suggestions' => $request->early_trip_suggestions,
                'same_gender_trips' => $request->same_gender_trips,
            ]
        );

        return response()->json([
            'message' => 'Ride setting updated successfully',
            'ride_setting' => $rideSetting,
        ], 200);
    }
}
