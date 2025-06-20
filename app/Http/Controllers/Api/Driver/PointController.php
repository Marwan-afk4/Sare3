<?php

namespace App\Http\Controllers\Api\Driver;

use App\Events\DriverLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class PointController extends Controller
{


    // public function storePointDriverLocation(Request $request)
    // {
    //     $user = $request->user();
    //     $validation = Validator::make($request->all(), [
    //         'longitude' => 'required|numeric',
    //         'latitude' => 'required|numeric',
    //     ]);
    //     if ($validation->fails()) {
    //         return response()->json(['message' => $validation->errors()->first()], 422);
    //     }

    //     $point = Point::create([
    //         'user_id' => $user->id,
    //         'longitude' => $request->longitude,
    //         'latitude' => $request->latitude,
    //         'point_type' => 'driverLocation',
    //     ]);

    //     return response()->json([
    //         'message' => 'Driver location point created successfully',
    //         'point' => $point,
    //     ], 200);
    // }

    public function updatePointDriverLocation(Request $request)
    {
        $user = $request->user();
        $validation = Validator::make($request->all(), [
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
        ]);
        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $point = Point::where('user_id', $user->id)
            ->where('point_type', 'driverLocation')
            ->delete();

        $point = Point::create([
            'user_id' => $user->id,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'point_type' => 'driverLocation',
        ]);

        Redis::geoadd('drivers:locations', $request->longitude, $request->latitude, $point->user_id);

        event(new DriverLocationUpdated($point));

        return response()->json([
            'message' => 'Driver location point updated successfully',
            'point' => $point->id,
        ], 200);
    }
}
