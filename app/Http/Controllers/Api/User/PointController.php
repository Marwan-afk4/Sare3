<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PointController extends Controller
{


    public function storePointPickup(Request $request)
    {
        $user = $request->user();
        $validation = Validator::make($request->all(), [
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
        ]);
        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);}

            $point = Point::create([
                'user_id' => $user->id,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'point_type' =>'pickup',
            ]);

            return response()->json([
                'message' => 'Pickup point created successfully',
                'point' => $point,
            ], 200);
    }

    public function storePointDrop(Request $request)
    {
        $user = $request->user();
        $validation = Validator::make($request->all(), [
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
        ]);
        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $point = Point::create([
            'user_id' => $user->id,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'point_type' =>'dropoff',
        ]);

        return response()->json([
            'message' => 'Drop point created successfully',
            'point' => $point,
        ], 200);
    }
}
