<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PointController extends Controller
{


    public function storePointPickupandDrop(Request $request)
    {
        $user = $request->user();
        $validation = Validator::make($request->all(), [
            'longitude_pickup' => 'required|numeric',
            'latitude_pickup' => 'required|numeric',
            'longitude_drop' => 'required|numeric',
            'latitude_drop' => 'required|numeric'
        ]);
        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);}

            $pointPickUp = Point::create([
                'user_id' => $user->id,
                'longitude' => $request->longitude_pickup,
                'latitude' => $request->latitude_pickup,
                'point_type' =>'pickup',
            ]);

            $pointDrop = Point::create([
                'user_id' => $user->id,
                'longitude' => $request->longitude_drop,
                'latitude' => $request->latitude_drop,
                'point_type' =>'dropoff',
            ]);

            return response()->json([
                'message' => 'Pickup point created successfully',
                'point_pickup' => $pointPickUp,
                'point_drop' => $pointDrop,
            ], 200);
    }
}
