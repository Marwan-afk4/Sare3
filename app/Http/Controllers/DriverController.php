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
use App\Models\RideOffer;
use App\Models\Zone;
use App\Models\City;
use App\Helpers\RideHelper;
use App\Helpers\ExcelExportHelper;
use App\trait\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriverController extends Controller
{
    use ImageUpload;

    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $balanceOperator = $request->get('balance_operator');
        $balanceAmount = $request->get('balance_amount');

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

        // Get city counts (including drivers with no city)
        $cities = \App\Models\City::withCount(['users as driver_count' => function ($query) {
            $query->where('role', 'driver');
        }])->get();

        // Count drivers with no city
        $driversWithNoCityCount = User::where('role', 'driver')
            ->whereNull('city_id')
            ->count();

        // Count online/offline drivers
        $onlineDriversCount = User::where('role', 'driver')
            ->where('is_available', true)
            ->count();

        $offlineDriversCount = User::where('role', 'driver')
            ->where('is_available', false)
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

        $drivers = $this->filteredDriversQuery($request)
            ->orderBy($sortField, $sortOrder)
            ->paginate(30)
            ->withQueryString();

        $driverActivtyStatus = ActivtyType::cases();
        $driverStatusCases = DriverStatus::cases();

        return view('drivers.index', compact('drivers', 'sortField', 'sortOrder', 'driverActivtyStatus', 'driverActivityCounts', 'driverStatusCases', 'driverStatusCounts', 'zones', 'driversWithNoZoneCount', 'cities', 'driversWithNoCityCount', 'onlineDriversCount', 'offlineDriversCount', 'carYears', 'carYearCounts', 'balanceOperator', 'balanceAmount'));
    }

    public function export(Request $request): StreamedResponse
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');

        $drivers = $this->filteredDriversQuery($request)
            ->orderBy($sortField, $sortOrder)
            ->get();

        $headers = [
            __('Id'),
            __('Name'),
            __('Email'),
            __('Phone'),
            __('Wallet'),
            __('Zone'),
            __('City'),
            __('Availability'),
            __('Activity'),
            __('Status'),
            __('Gender'),
            __('Created At'),
        ];

        $rows = $drivers->map(function (User $driver) {
            return [
                $driver->id,
                $driver->name,
                $driver->email,
                $driver->phone,
                $driver->wallet,
                $driver->zone?->name,
                $driver->city?->name,
                $driver->is_available ? __('Online') : __('Offline'),
                $driver->activity?->label(),
                $driver->status?->label(),
                $driver->gender,
                optional($driver->created_at)->format('Y-m-d H:i:s'),
            ];
        });

        return ExcelExportHelper::download('drivers_' . now()->format('Y-m-d_His'), $headers, $rows);
    }

    public function exportPhones(Request $request): StreamedResponse
    {
        $phones = $this->filteredDriversQuery($request)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('id')
            ->pluck('phone');

        $rows = $phones->map(fn (string $phone) => [$phone]);

        return ExcelExportHelper::download('drivers_phones_' . now()->format('Y-m-d_His'), [__('Phone')], $rows);
    }

    protected function filteredDriversQuery(Request $request)
    {
        $keyword = $request->get('keyword');
        $activity = $request->get('activity');
        $zoneId = $request->get('zone');
        $cityId = $request->get('city');
        $availability = $request->get('availability');
        $carYear = $request->get('car_year');
        $status = $request->get('status');
        $balanceOperator = $request->get('balance_operator');
        $balanceAmount = $request->get('balance_amount');

        return User::where('role', 'driver')
            ->with(['zone', 'city', 'driverCars.carType'])
            ->when($activity, function ($query, $activity) {
                $query->where('activity', $activity);
            })
            ->when($zoneId !== null, function ($query) use ($zoneId) {
                if ($zoneId === 'no_zone') {
                    $query->whereNull('zone_id');
                } else {
                    $query->where('zone_id', $zoneId);
                }
            })
            ->when($cityId !== null, function ($query) use ($cityId) {
                if ($cityId === 'no_city') {
                    $query->whereNull('city_id');
                } else {
                    $query->where('city_id', $cityId);
                }
            })
            ->when($availability !== null && $availability !== '', function ($query) use ($availability) {
                $query->where('is_available', $availability == '1');
            })
            ->when($carYear, function ($query, $carYear) {
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
            ->filterByWalletBalance($balanceOperator, $balanceAmount);
    }

    public function underMonitoring(Request $request)
    {
        $minRejections = (int) $request->get(
            'min_rejections',
            config('ride.monitoring_min_rejections', 5)
        );
        $minCancelAfterAccept = (int) $request->get(
            'min_cancel_after_accept',
            config('ride.monitoring_min_cancel_after_accept', 3)
        );
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $keyword = $request->get('keyword');
        $cityId = $request->get('city');
        $sortField = $request->get('sort', 'cancel_after_accept_count');
        $sortOrder = $request->get('order', 'desc');

        $allowedSorts = [
            'id',
            'name',
            'phone',
            'rejected_count',
            'cancel_after_accept_count',
            'ignored_count',
            'accepted_count',
            'problem_count',
        ];
        if (! in_array($sortField, $allowedSorts, true)) {
            $sortField = 'cancel_after_accept_count';
        }
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $offerDateFilter = function ($query) use ($dateFrom, $dateTo) {
            if ($dateFrom) {
                $query->whereDate('offered_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('offered_at', '<=', $dateTo);
            }
        };

        $drivers = User::where('role', 'driver')
            ->with(['zone', 'city'])
            ->withCount([
                'rideOffers as rejected_count' => function ($query) use ($offerDateFilter) {
                    $query->where('response', RideOffer::RESPONSE_REJECTED);
                    $offerDateFilter($query);
                },
                'rideOffers as cancel_after_accept_count' => function ($query) use ($offerDateFilter) {
                    $query->where('response', RideOffer::RESPONSE_CANCELLED_AFTER_ACCEPT);
                    $offerDateFilter($query);
                },
                'rideOffers as ignored_count' => function ($query) use ($offerDateFilter) {
                    $query->where('response', RideOffer::RESPONSE_IGNORED);
                    $offerDateFilter($query);
                },
                'rideOffers as accepted_count' => function ($query) use ($offerDateFilter) {
                    $query->where('response', RideOffer::RESPONSE_ACCEPTED);
                    $offerDateFilter($query);
                },
            ])
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%")
                        ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            })
            ->when($cityId !== null && $cityId !== '', function ($query) use ($cityId) {
                if ($cityId === 'no_city') {
                    $query->whereNull('city_id');
                } else {
                    $query->where('city_id', $cityId);
                }
            })
            ->havingRaw(
                '(rejected_count >= ? OR cancel_after_accept_count >= ?)',
                [$minRejections, $minCancelAfterAccept]
            )
            ->when(
                $sortField === 'problem_count',
                fn ($query) => $query->orderByRaw('(rejected_count + cancel_after_accept_count) ' . $sortOrder),
                fn ($query) => $query->orderBy($sortField, $sortOrder)
            )
            ->paginate(30)
            ->withQueryString();

        $cities = City::orderBy('name')->get(['id', 'name']);

        return view('drivers.under-monitoring', compact(
            'drivers',
            'minRejections',
            'minCancelAfterAccept',
            'dateFrom',
            'dateTo',
            'cities',
            'sortField',
            'sortOrder'
        ));
    }

    public function documents(User $driver)
    {
        $documents = $driver->documents()->with('documentType')->get();

        return view('drivers.documents', compact('driver', 'documents'));
    }

    public function cars(User $driver)
    {
        $cars = $driver->driverCars()->with(['carType', 'carCategory', 'carCategories', 'carModel'])->get();

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
            'zone',
            'city',
            'driverCars.carType',
            'driverCars.carCategory',
            'driverCars.carCategories',
            'driverCars.carModel',
        ]);

        // Get driver rating
        $driverRating = Rating::where('ratee_id', $driver->id)
            ->where('ratee_type', 'driver')
            ->avg('rate');

        // Get ride statistics
        $rideStatistics = RideHelper::getDriverRideStatistics($driver->driverRides);

        // Get recent rides (last 10)
        $recentRides = RideHelper::formatDriverRideHistory($driver->driverRides->take(10));

        $isAvailable = $driver->is_available;
        $driverLocation = $this->resolveDriverLocation($driver);

        return view('drivers.show', compact('driver', 'driverRating', 'rideStatistics', 'recentRides', 'isAvailable', 'driverLocation'));
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
        $cities = \App\Models\City::orderBy('name')->pluck('name', 'id')->toArray();
        $hasPassword = ! empty($driver->getAttributes()['password']);

        return view('drivers.edit', compact('driver', 'diverActivityStatus', 'driverStatus', 'zones', 'cities', 'hasPassword'));
    }

    public function updateNotes(Request $request, User $driver)
    {
        if ($driver->role !== 'driver') {
            abort(404);
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $notes = $request->input('admin_notes');
        $driver->admin_notes = filled($notes) ? $notes : null;
        $driver->save();

        return redirect()
            ->route('drivers.show', $driver)
            ->with('success', __('Notes saved successfully.'));
    }

    public function update(UpdateDriverRequest $request, User $driver)
    {
        if ($request->filled('_password_action')) {
            if ($request->input('_password_action') === 'remove') {
                $driver->forceFill(['password' => null])->save();

                return redirect()
                    ->route('drivers.edit', $driver)
                    ->with('success', __('Password removed successfully.'));
            }

            $driver->password = $request->input('driver_password');
            $driver->save();
            $driver->refresh();

            if (empty($driver->getAttributes()['password'])) {
                return back()
                    ->withErrors(['driver_password' => __('Failed to save password. Please try again.')])
                    ->withInput(['_password_action' => 'set']);
            }

            return redirect()
                ->route('drivers.edit', $driver)
                ->with('success', __('Password updated successfully.'));
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($driver->image) {
                $this->deleteImage($driver->image);
            }
            $data['image'] = $this->uploadFile($request->file('image'), 'driver/profiles');
        } else {
            unset($data['image']);
        }

        if (array_key_exists('admin_notes', $data)) {
            $data['admin_notes'] = filled($data['admin_notes']) ? $data['admin_notes'] : null;
        }

        $driver->update($data);

        return redirect()->route('drivers.index', $driver)->with('success', __('Driver updated successfully.'));
    }

    public function destroy(User $driver)
    {
        if ($driver->role !== 'driver') {
            return redirect()->route('drivers.index')->with('error', __('Only drivers can be deleted from this page.'));
        }

        try {


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

    public function getLocation(User $driver)
    {
        $driverLocation = $this->resolveDriverLocation($driver);

        if ($driverLocation) {
            return response()->json([
                'success' => true,
                'location' => $driverLocation,
                'is_available' => (bool) $driver->is_available,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Driver location not available',
            'is_available' => (bool) $driver->is_available,
        ], 404);
    }

    private function resolveDriverLocation(User $driver): ?array
    {
        $cached = Cache::get("driver_location:{$driver->id}");

        if ($cached && isset($cached['latitude'], $cached['longitude'])) {
            return [
                'latitude' => (float) $cached['latitude'],
                'longitude' => (float) $cached['longitude'],
                'bearing' => isset($cached['bearing']) ? (float) $cached['bearing'] : null,
                'timestamp' => $cached['updated_at'] ?? null,
            ];
        }

        if ($driver->latitude && $driver->longitude) {
            return [
                'latitude' => (float) $driver->latitude,
                'longitude' => (float) $driver->longitude,
                'bearing' => $driver->bearing !== null ? (float) $driver->bearing : null,
            ];
        }

        return null;
    }
}
