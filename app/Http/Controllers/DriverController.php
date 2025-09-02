<?php

namespace App\Http\Controllers;

use App\Enums\ActivtyType;
use App\Enums\DriverStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\User;
use App\Models\Rating;
use App\Helpers\RideHelper;
use App\trait\ImageUpload;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class DriverController extends Controller
{
    use ImageUpload;
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $keyword = $request->get('keyword');
        $activity = $request->get('activity'); // 👈 Get the selected activity from the request

        $driverActivityCounts = User::where('role', 'driver')
            ->selectRaw('activity, COUNT(*) as count')
            ->groupBy('activity')
            ->pluck('count', 'activity')
            ->toArray();

        $drivers = User::where('role', 'driver')
            ->when($activity, function ($query, $activity) {
                $query->where('activity', $activity); // 👈 Filter by activity
            })
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%")
                        ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate(30);

        $driverActivtyStatus = ActivtyType::cases();

        return view('drivers.index', compact('drivers', 'sortField', 'sortOrder', 'driverActivtyStatus', 'driverActivityCounts'));
    }

    public function documents(User $driver)
    {
        $documents = $driver->documents()->with('documentType')->get();

        return view('drivers.documents', compact('driver', 'documents'));
    }

    public function cars(User $driver)
    {
        $cars = $driver->driverCars()->with(['carType', 'carCategory', 'carModel'])->get();

        return view('drivers.cars', compact('driver', 'cars'));
    }



    public function create()
    {
        return view('drivers.create');
    }

    public function store(StoreDriverRequest $request)
    {
        $request->setRole('driver');

        User::create($request->validated());
        return redirect()->route('drivers.index')->with('success',  __('Created successfully'));
    }

    public function show(User $driver)
    {
        // Load driver rides with relationships
        $driver->load([
            'driverRides' => function ($query) {
                $query->with(['user', 'carCategory'])
                    ->orderBy('created_at', 'desc');
            }
        ]);

        // Get driver rating
        $driverRating = Rating::where('ratee_id', $driver->id)
            ->where('ratee_type', 'driver')
            ->avg('rate');

        // Get ride statistics
        $rideStatistics = RideHelper::getDriverRideStatistics($driver->driverRides);

        // Get recent rides (last 10)
        $recentRides = RideHelper::formatDriverRideHistory($driver->driverRides->take(10));

        return view('drivers.show', compact('driver', 'driverRating', 'rideStatistics', 'recentRides'));
    }

    public function rideHistory(User $driver, Request $request)
    {
        $status = $request->get('status', 'all');
        $perPage = $request->get('per_page', 20);

        // Build query
        $query = $driver->driverRides()
            ->with(['user', 'carCategory'])
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
        $rides = $query->paginate($perPage);

        // Format data
        $ridesData = RideHelper::formatDriverRideHistory($rides->items());
        $rideStatistics = RideHelper::getDriverRideStatistics(collect($rides->items()));

        return view('drivers.ride-history', compact('driver', 'rides', 'ridesData', 'rideStatistics', 'status'));
    }

    public function edit(User $driver)
    {
        $diverActivityStatus = ActivtyType::labels();
        $driverStatus = DriverStatus::labels();
        return view('drivers.edit', compact('driver', 'diverActivityStatus', 'driverStatus'));
    }


    public function update(UpdateDriverRequest $request, User $driver)
    {
        $data = $request->validated();

        //Check if activity is updated to inactive
        if ($request->has('activity') && $request->input('activity') === 'inactive') {
            try {
                $firebase = (new Factory)
                    ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                    ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                    ->createDatabase();

                $firebaseRef = $firebase->getReference("active_drivers/{$driver->id}");

                // Remove driver if exists
                if ($firebaseRef->getValue()) {
                    $firebaseRef->remove();
                }
            } catch (\Exception $e) {
                return redirect()->route('drivers.index')->with('error', 'Driver updated, but failed to update Firebase: ' . $e->getMessage());
            }
        }

        $driver->update($data);

        return redirect()->route('drivers.index')->with('success', __('Driver updated successfully.'));
    }
}
