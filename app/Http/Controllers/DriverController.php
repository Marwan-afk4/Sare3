<?php

namespace App\Http\Controllers;

use App\Enums\ActivtyType;
use App\Enums\DriverStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\DriverDocument;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Rating;
use App\Models\Zone;
use App\Helpers\RideHelper;
use App\Services\FirebaseService;
use App\trait\ImageUpload;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class DriverController extends Controller
{
    use ImageUpload;

    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'DESC');
        $keyword = $request->get('keyword');
        $activity = $request->get('activity');
        $zoneId = $request->get('zone');
        $carYear = $request->get('car_year');
        $status = $request->get('status');

        $driverActivityCounts = User::where('role', 'driver')
            ->selectRaw('activity, COUNT(*) as count')
            ->groupBy('activity')
            ->pluck('count', 'activity')
            ->toArray();

        $driverStatusCounts = User::where('role', 'driver')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get zone counts (including drivers with no zone)
        $zones = Zone::withCount(['users as driver_count' => function ($query) {
            $query->where('role', 'driver');
        }])->get();

        // Count drivers with no zone
        $driversWithNoZoneCount = User::where('role', 'driver')
            ->whereNull('zone_id')
            ->count();

        // 👇 Get all car types that have drivers
        $activeCarTypes = \App\Models\CarType::whereHas('driverCars')->get(['year_from', 'year_to']);

        // Generate distinct years list from ranges
        $carYears = collect();
        foreach ($activeCarTypes as $type) {
            $start = $type->year_from ?? 2000; // Default to 2000 if null? Or skip? Assuming sane data.
            $end = $type->year_to ?? date('Y') + 1; // Default to next year if open ended
            for ($y = $start; $y <= $end; $y++) {
                $carYears->push($y);
            }
        }
        $carYears = $carYears->unique()->sortDesc()->values();


        // 👇 Count drivers per car year
        $carYearCounts = [];
        foreach ($carYears as $year) {
            $carYearCounts[$year] = User::where('role', 'driver')
                ->whereHas('driverCars.carType', function ($query) use ($year) {
                    $query->where('year_from', '<=', $year)
                        ->where(function ($q) use ($year) {
                            $q->where('year_to', '>=', $year)
                                ->orWhereNull('year_to');
                        });
                })
                ->count();
        }

        $drivers = User::where('role', 'driver')
            ->with(['zone', 'driverCars.carType']) // 👈 Load car relationships
            ->when($activity, function ($query, $activity) {
                $query->where('activity', $activity); // 👈 Filter by activity
            })
            ->when($zoneId !== null, function ($query) use ($zoneId) {
                if ($zoneId === 'no_zone') {
                    $query->whereNull('zone_id'); // 👈 Filter drivers with no zone
                } else {
                    $query->where('zone_id', $zoneId); // 👈 Filter by zone
                }
            })
            ->when($carYear, function ($query, $carYear) {
                // 👇 Filter by car type year range
                $query->whereHas('driverCars.carType', function ($carQuery) use ($carYear) {
                    $carQuery->where('year_from', '<=', $carYear)
                        ->where(function ($q) use ($carYear) {
                            $q->where('year_to', '>=', $carYear)
                                ->orWhereNull('year_to');
                        });
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
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

        // OPTIMIZED: Get ALL available drivers from Firebase at once (single call)
        // With error handling to prevent timeouts
        $driverAvailability = [];
        try {
            $allAvailableDrivers = $this->firebaseService->getAllAvailableDrivers();
            $availableDriverIds = array_map('intval', array_keys($allAvailableDrivers)); // Normalize to integers

            // Create availability map for quick lookup
            foreach ($drivers as $driver) {
                // Use strict comparison with normalized integer IDs
                $driverAvailability[$driver->id] = in_array((int) $driver->id, $availableDriverIds, true);
            }
        } catch (\Exception $e) {
            // If Firebase fails, set all drivers as unavailable (offline)
            \Log::warning('Failed to fetch driver availability from Firebase: ' . $e->getMessage());
            foreach ($drivers as $driver) {
                $driverAvailability[$driver->id] = false;
            }
        }

        $driverActivtyStatus = ActivtyType::cases();
        $driverStatusCases = DriverStatus::cases();

        return view('drivers.index', compact('drivers', 'sortField', 'sortOrder', 'driverActivtyStatus', 'driverActivityCounts', 'driverStatusCases', 'driverStatusCounts', 'zones', 'driversWithNoZoneCount', 'driverAvailability', 'carYears', 'carYearCounts'));
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
        $driverStatus = DriverStatus::labels();
        $requiredDocumentTypes = DocumentType::where('is_required', true)->orderBy('name')->get();
        return view('drivers.create', compact('driverStatus', 'requiredDocumentTypes'));
    }

    public function store(StoreDriverRequest $request)
    {
        $request->setRole('driver');

        $data = $request->validated();
        unset($data['document_types']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFile($request->file('image'), 'driver/profiles');
        }

        $driver = User::create($data);

        $requiredDocumentTypes = DocumentType::where('is_required', true)->orderBy('name')->get();
        foreach ($requiredDocumentTypes as $docType) {
            $file = $request->file("document_types.{$docType->id}");
            if ($file) {
                $path = $this->uploadFile($file, 'driver/documents');
                DriverDocument::create([
                    'driver_id' => $driver->id,
                    'document_type_id' => $docType->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('drivers.index')->with('success', __('Created successfully'));
    }

    public function show(User $driver)
    {
        // Load driver rides with relationships
        $driver->load([
            'driverRides' => function ($query) {
                $query->with(['user', 'carCategory'])
                    ->orderBy('created_at', 'desc');
            },
            'zone'
        ]);

        // Get driver rating
        $driverRating = Rating::where('ratee_id', $driver->id)
            ->where('ratee_type', 'driver')
            ->avg('rate');

        // Get ride statistics
        $rideStatistics = RideHelper::getDriverRideStatistics($driver->driverRides);

        // Get recent rides (last 10)
        $recentRides = RideHelper::formatDriverRideHistory($driver->driverRides->take(10));

        // Get driver location from Firebase
        $driverLocation = $this->firebaseService->getDriverLocation($driver->id);
        $isAvailable = $driverLocation !== null;

        return view('drivers.show', compact('driver', 'driverRating', 'rideStatistics', 'recentRides', 'driverLocation', 'isAvailable'));
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
        $zones = Zone::orderBy('name')->pluck('name', 'id')->toArray();
        return view('drivers.edit', compact('driver', 'diverActivityStatus', 'driverStatus', 'zones'));
    }


    public function update(UpdateDriverRequest $request, User $driver)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($driver->image) {
                $this->deleteImage($driver->image);
            }
            $data['image'] = $this->uploadFile($request->file('image'), 'driver/profiles');
        } else {
            unset($data['image']);
        }

        //Check if activity is updated to inactive
        if ($request->has('activity') && $request->input('activity') === 'inactive') {
            try {
                $firebase = (new Factory)
                    ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                    ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                    ->createDatabase();

                $firebaseRef = $firebase->getReference("drivers/driver {$driver->id}");

                $firebaseRef->remove();
            } catch (\Exception $e) {
                return redirect()->route('drivers.index')->with('error', 'Driver updated, but failed to update Firebase: ' . $e->getMessage());
            }
        }

        $driver->update($data);

        return redirect()->route('drivers.index')->with('success', __('Driver updated successfully.'));
    }

    public function destroy(User $driver)
    {
        if ($driver->role !== 'driver') {
            return redirect()->route('drivers.index')->with('error', __('Only drivers can be deleted from this page.'));
        }

        try {
            // Remove driver from Firebase
            try {
                $firebase = (new Factory)
                    ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                    ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                    ->createDatabase();
                $firebaseRef = $firebase->getReference("drivers/driver {$driver->id}");
                $firebaseRef->remove();
            } catch (\Exception $e) {
                \Log::warning('Firebase driver removal failed during driver delete: ' . $e->getMessage());
            }

            // Delete stored files: driver documents
            foreach ($driver->documents as $doc) {
                if ($doc->image_path) {
                    $this->deleteImage($doc->image_path);
                }
            }

            // Delete stored files: driver cars (car_image, car_license)
            foreach ($driver->driverCars as $car) {
                if ($car->car_image) {
                    $this->deleteImage($car->car_image);
                }
                if ($car->car_license) {
                    $this->deleteImage($car->car_license);
                }
            }

            // Delete driver profile image
            if ($driver->image) {
                $this->deleteImage($driver->image);
            }

            $driver->delete();

            return redirect()->route('drivers.index')->with('success', __('Driver and all associated data have been deleted.'));
        } catch (\Exception $e) {
            \Log::error('Driver delete failed: ' . $e->getMessage());
            return redirect()->route('drivers.index')->with('error', __('Failed to delete driver. Please try again.'));
        }
    }

    /**
     * Get driver location from Firebase (AJAX endpoint)
     */
    public function getLocation(User $driver)
    {
        $location = $this->firebaseService->getDriverLocation($driver->id);

        if ($location) {
            return response()->json([
                'success' => true,
                'location' => $location,
                'is_available' => true
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Driver location not available',
            'is_available' => false
        ], 404);
    }
}
