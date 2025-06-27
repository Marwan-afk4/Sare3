<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\CarCategory;
use App\Models\RideEstimate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RideEstimateController extends Controller
{


    public function estimateForAllCategories(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'estimated_km' => 'required|numeric|min:0',
            'estimated_time'=> 'required|numeric|min:0',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $estimatedKm = $request->estimated_km;
        $estimatedTime = $request->estimated_time;

        $carCategories = CarCategory::all();

        $result = $carCategories->map(function ($category) use ($estimatedKm , $estimatedTime)
        {
            $base = $category->base_price;
            $perKm = $category->price_per_km;
            $perTime = $category->price_per_time;

            $price = $base + ($estimatedKm * $perKm) + ($estimatedTime * $perTime);

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'estimated_price' => round($price, 2),
                'icon_url' => $category->getIconUrlAttribute()
            ];
        });

        return response()->json([
            'message' => 'Success',
            'data' => $result
        ]);
    }


    public function storeEstimate(Request $request)
    {
        $user = $request->user();
        $validation = Validator::make($request->all(), [
            'car_category_id' => 'required|exists:car_categories,id',
            'estimated_km' => 'required|numeric|min:0',
            'estimated_time'=> 'required|numeric|min:0',
            'pickup_lat' =>'required|numeric',
            'pickup_lng'=> 'required|numeric',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng'=> 'required|numeric',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $carCategory = CarCategory::find($request->car_category_id);

        $price = ($carCategory->base_price + ($request->estimated_km * $carCategory->price_per_km) + ($request->estimated_time * $carCategory->price_per_time));

        $estimate = RideEstimate::create([
            'user_id'=> $user->id,
            'car_category_id'=> $request->car_category_id,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'estimated_km' => $request->estimated_km,
            'estimated_time' => $request->estimated_time,
            'calculated_price' => $price,
        ]);

        return response()->json([
            'message'=> 'Success',
            'date'=> $estimate,
        ]);
    }
}
