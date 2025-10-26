<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Helpers\RideHelper;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\RideRequestTimeLimit;
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
        'driverCars.carCategory',
        'driverCars.carCategories',
        'driverCars.carType',
        'zone',
    ]);

        $requestLimit = RideRequestTimeLimit::first();

    $driverRating = Rating::where('ratee_id', $user->id)
            ->where('ratee_type', 'driver')
            ->avg('rate');

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
            'driver_rating' => $driverRating,
            'rejected_reason' => $user->rejected_reason ?? 'your account is not rejected',
            'wallet' => $user->wallet,
            'zone' => $user->zone ? $user->zone->name : 'We do not know yet',
            'zone_id' => $user->zone_id,
            'ride_request_time_limit' => $requestLimit->time_limit_seconds ?? null,
            'car' => (function() use ($firstCar) {
                if (!$firstCar) {
                    return null;
                }

                $categoryIds = $firstCar->carCategories?->pluck('id')->toArray() ?? [];
                $categoryNames = $firstCar->carCategories?->pluck('name')->toArray() ?? [];

                return [
                    'car_number' => $firstCar->car_number,
                    'car_model' => $firstCar?->carModel?->name ?? null,
                    'car_color' => $firstCar->car_color,
                    // Legacy fields (fallback to first from arrays)
                    'car_category_id' => $firstCar->car_categories_id ?? ($categoryIds[0] ?? null),
                    'car_category' => $firstCar->carCategory->name ?? ($categoryNames[0] ?? null),
                    // New array fields
                    'car_category_ids' => $categoryIds,
                    'car_categories' => $categoryNames,
                    'car_type_id' => $firstCar->car_type_id ?? null,
                    'car_type' => $firstCar->carType->type_name ?? null,
                    'car_license' => $firstCar->car_license_link ?? null,
                    'car_image_link' => $firstCar->car_image_link ?? null,
                ];
            })()
        ];

        return response()->json($data);
    }

    public function getDriverCompletedRides(Request $request)
    {
        $driver = $request->user();

        $validation = Validator::make($request->all(), [
            'status' => 'nullable|string|in:completed,finshed,cancelled,all',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }

        $status = $request->get('status', 'finshed');
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        // Build query
        $query = $driver->driverRides()
            ->with([
                'user',
                'carCategory'
            ])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status !== 'all') {
            if ($status === 'completed') {
                $query->whereIn('status', ['completed', 'finshed']);
            } else {
                $query->where('status', $status);
            }
        }

        // Paginate
        $rides = $query->paginate($limit, ['*'], 'page', $page);

        // Format data using helper
        $ridesData = RideHelper::formatDriverRideHistory(collect($rides->items()));
        $rideStatistics = RideHelper::getDriverRideStatistics(collect($rides->items()));

        $response = [
            'driver_id' => $driver->id,
            'driver_name' => $driver->name,
            'statistics' => $rideStatistics,
            'rides_data' => $ridesData,
            'pagination' => [
                'current_page' => $rides->currentPage(),
                'last_page' => $rides->lastPage(),
                'per_page' => $rides->perPage(),
                'total' => $rides->total(),
                'has_more_pages' => $rides->hasMorePages(),
            ]
        ];

        return response()->json(['driver' => $response]);
    }

    public function getDriverRideHistory(Request $request)
    {
        return $this->getDriverCompletedRides($request);
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

    public function isInRide(Request $request)
    {
        $driver = $request->user();

        $driverRide = Ride::whereNotIn('status',['finshed', 'cancelled','rejected'])
            ->where('driver_id', $driver->id)
            ->select('id', 'status')
            ->first();

        return response()->json([
            'is_in_ride' => $driverRide
        ]);
    }

    public function addZoneId(Request $request)
    {
        $driver = $request->user();

        $validation = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }

        $driver->zone_id = $request->zone_id;
        $driver->save();

        return response()->json([
            'message' => 'Zone ID added successfully'
        ]);
    }

    /**
     * Check driver wallet status and availability to go online
     */
    public function checkWalletStatus(Request $request)
    {
        $driver = $request->user();
        $walletStatus = $driver->getWalletStatus();

        return response()->json([
            'message' => 'Wallet status retrieved successfully.',
            'wallet_status' => $walletStatus
        ]);
    }

	public function hasCarData(Request $request)
	{
		$driver = $request->user();
		$hasCar = $driver->driverCars()->exists();

		$carData = null;
		if ($hasCar) {
			$car = $driver->driverCars()
				->with(['carModel', 'carCategory', 'carCategories', 'carType'])
				->first();

			$categoryIds = $car->carCategories?->pluck('id')->toArray() ?? [];
			$categoryNames = $car->carCategories?->pluck('name')->toArray() ?? [];

			$carData = [
				'car_number' => $car->car_number,
				'car_model' => $car->carModel->name ?? null,
				'car_color' => $car->car_color,
				// Legacy single fields (fallback from arrays)
				'car_category_id' => $car->car_categories_id ?? ($categoryIds[0] ?? null),
				'car_category' => $car->carCategory->name ?? ($categoryNames[0] ?? null),
				// New array fields
				'car_category_ids' => $categoryIds,
				'car_categories' => $categoryNames,
				'car_type_id' => $car->car_type_id,
				'car_type' => $car->carType->type_name ?? null,
				'car_license' => $car->car_license_link,
				'car_image_link' => $car->car_image_link,
			];
		}

		return response()->json([
			'has_car' => $hasCar,
			'car' => $carData,
		]);
	}
}

