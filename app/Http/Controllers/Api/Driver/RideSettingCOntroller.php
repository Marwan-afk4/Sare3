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
            'pickup_radius' => 'sometimes|numeric|min:0',
            'destination_preferences' => 'sometimes|array',
            'early_trip_suggestions' => 'sometimes|boolean',
            'same_gender_trips' => 'sometimes|boolean',
            'zone_id' => 'sometimes|nullable|exists:zones,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors()], 422);
        }

        $updatableFields = [
            'pickup_radius',
            'destination_preferences',
            'early_trip_suggestions',
            'same_gender_trips',
            'zone_id',
        ];

        $data = collect($updatableFields)
            ->filter(fn (string $field) => $request->exists($field))
            ->mapWithKeys(fn (string $field) => [$field => $request->input($field)])
            ->all();

        $rideSetting = $driver->driverRideSetting()->updateOrCreate(
            ['driver_id' => $driver->id],
            $data
        );

        return response()->json([
            'message' => 'Ride setting updated successfully',
            'ride_setting' => $rideSetting,
        ], 200);
    }

    public function getRideSetting(Request $request)
    {
        $driver = $request->user();

        $rideSetting = $driver->driverRideSetting;

        if (!$rideSetting) {
            return response()->json(['message' => 'No ride settings found'], 200);
        }

        return response()->json([
            'ride_setting' => $rideSetting,
        ], 200);
    }
}
