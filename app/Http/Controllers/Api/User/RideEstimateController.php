<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\CarCategory;
use App\Models\Ride;
use App\Models\RideEstimate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use App\Helpers\RideHelper;
use App\Models\Rating;

class RideEstimateController extends Controller
{

    public function estimateForAllCategories(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'estimated_km' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|numeric|min:0',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $estimatedKm = $request->estimated_km;
        $estimatedTime = $request->estimated_time;

        $carCategories = CarCategory::all();

        $result = $carCategories->map(function ($category) use ($estimatedKm, $estimatedTime) {
            $base = $category->base_price;
            $perKm = $category->price_per_km;
            $perTime = $category->price_per_time;

            $price = $base + ($estimatedKm * $perKm) + ($estimatedTime * $perTime);

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'estimated_price' => round($price, 2),
                'estimated_time' => $estimatedTime,
                'icon_url' => $category->getIconUrlAttribute()
            ];
        });

        return response()->json([
            'message' => 'Success',
            'data' => $result
        ]);
    }


    public function createRide(Request $request)
    {
        $user = $request->user();

        $validation = Validator::make($request->all(), [
            'driver_id' => 'required|exists:users,id',
            'car_category_id' => 'required|exists:car_categories,id',
            'estimated_km' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|numeric|min:0',
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
            'dropoff_address' => 'nullable|string',
            'pickup_address' => 'nullable|string',
            'payment_method_id' => 'nullable|exists:paymenent_methods,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()], 500);
        }

        $driver = User::with('driverCars')->findOrFail($request->driver_id);

        $carCategory = CarCategory::find($request->car_category_id);

        $price = ($carCategory->base_price + ($request->estimated_km * $carCategory->price_per_km) + ($request->estimated_time * $carCategory->price_per_time));

        // Calculate average rating for the user
        $userRating = Rating::where('ratee_id', $user->id)
            ->where('ratee_type', 'user')
            ->avg('rate');

        // Calculate average rating for the driver
        $driverRating = Rating::where('ratee_id', $driver->id)
            ->where('ratee_type', 'driver')
            ->avg('rate');




        RideEstimate::create([
            'user_id' => $user->id,
            'car_category_id' => $request->car_category_id,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'estimated_km' => $request->estimated_km,
            'estimated_time' => $request->estimated_time,
            'calculated_price' => $price,
        ]);

        $ride = Ride::create([
            'user_id' => $user->id,
            'car_category_id' => $request->car_category_id,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'status' => 'pending',
            'estimated_km' => $request->estimated_km,
            'estimated_time' => $request->estimated_time,
            'calculated_initial_price' => $price,
            'pickup_address' => $request->pickup_address,
            'dropoff_address' => $request->dropoff_address,
            'payment_method_id' => $request->payment_method_id,
        ]);

        $firebaseRideId = 'ride_' . $ride->id;

        try {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                ->createDatabase();

            $firebaseData = [
                'ride_id' => $ride->id,
                'user' => [
                    'user_id' => $user,
                    'user_name' => $user->name,
                    'user_phone' => $user->phone,
                    'user_email' => $user->email,
                    'user_image' => $user->image,
                    'user_rating' => round($userRating ?? 0, 1),
                ],
                'driver_id' => $request->driver_id,
                'driver_rating' => round($driverRating ?? 0, 1),
                'car_category_id' => $ride->car_category_id,
                'pickup' => [
                    'lat' => $ride->pickup_lat,
                    'lng' => $ride->pickup_lng,
                    'address' => $request->pickup_address,
                ],
                'dropoff' => [
                    'lat' => $ride->dropoff_lat,
                    'lng' => $ride->dropoff_lng,
                    'address' => $request->dropoff_address,
                ],
                'estimated_time' => $request->estimated_time,
                'estimated_km' => $request->estimated_km,
                'initial_price' => $price,
                'status' => $ride->status,
                'created_at' => now()->toIso8601String(),
            ];

            $firebase->getReference("rides/$firebaseRideId")->set($firebaseData);

            $ride->update([
                'firebase_ride_id' => $firebaseRideId,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ride created, but failed to sync with Firebase', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Ride created successfully',
            'data' => $ride,
        ]);
    }
}
