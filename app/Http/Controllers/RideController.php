<?php

namespace App\Http\Controllers;

use App\Enums\RideStatus;
use App\Models\Ride;
use App\Models\User;
use App\Models\Driver;
use App\Models\CarCategory;


use Illuminate\Http\Request;
use App\Http\Requests\StoreRideRequest;
use App\Http\Requests\UpdateRideRequest;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;

class RideController extends Controller
{

    protected Database $firebase;

    public function __construct()
    {
        $this->firebase = (new Factory)
            ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
            ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
            ->createDatabase();
    }

    protected function updateFirebase(Ride $ride, array $data): void
    {
        $firebaseRideId = 'ride_' . $ride->id;
        $this->firebase->getReference("rides/$firebaseRideId")->update($data);
    }
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'desc');
        $keyword   = $request->get('keyword');
        $minKm     = $request->get('min_km');
        $maxKm     = $request->get('max_km');

        $ridesQuery = Ride::with(['user', 'driver', 'carCategory', 'coupon'])
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('id', 'LIKE', "%{$keyword}%")
                        ->orWhere('pickup_address', 'LIKE', "%{$keyword}%")
                        ->orWhere('dropoff_address', 'LIKE', "%{$keyword}%")
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'LIKE', "%{$keyword}%")
                                ->orWhere('phone', 'LIKE', "%{$keyword}%")
                                ->orWhere('email', 'LIKE', "%{$keyword}%");
                        })
                        ->orWhereHas('driver', function ($driverQuery) use ($keyword) {
                            $driverQuery->where('name', 'LIKE', "%{$keyword}%")
                                ->orWhere('phone', 'LIKE', "%{$keyword}%");
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($minKm !== null && $minKm !== '', function ($query) use ($minKm) {
                $query->whereRaw('CAST(total_distance_in_km AS DECIMAL(10,2)) >= ?', [$minKm]);
            })
            ->when($maxKm !== null && $maxKm !== '', function ($query) use ($maxKm) {
                $query->whereRaw('CAST(total_distance_in_km AS DECIMAL(10,2)) <= ?', [$maxKm]);
            })
            ->orderBy($sortField, $sortOrder);

        $rides = $ridesQuery->paginate(30);

        // Count per status
        $ridesStatusCounts = Ride::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $rideStatuses = \App\Enums\RideStatus::cases();

        return view('rides.index', compact(
            'rides',
            'sortField',
            'sortOrder',
            'ridesStatusCounts',
            'rideStatuses'
        ));
    }



    public function create()
    {
        $users = User::orderBy('name')->pluck('name', 'id')->toArray();
        $drivers = User::where('role', 'driver')
            ->orderBy('name')->pluck('name', 'id')->toArray();
        $carCategories = CarCategory::orderBy('name')->pluck('name', 'id')->toArray();
        return view('rides.create', compact('users', 'drivers', 'carCategories'));
    }

    public function store(StoreRideRequest $request)
    {
        Ride::create($request->validated());
        return redirect()->route('rides.index')->with('success',  __('Created successfully'));
    }

    public function show(Ride $ride)
    {
        $rideStatuses = RideStatus::labels();
        return view('rides.show', compact('ride', 'rideStatuses'));
    }

    public function updateStatus(Request $request, Ride $ride)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(RideStatus::labels()))],
        ]);

        $oldStatus = $ride->status;
        $newStatus = $request->status;

        // Prepare update data with status
        $updateData = ['status' => $newStatus];

        // Add appropriate timestamp based on new status
        switch ($newStatus) {
            case 'accepted':
                $updateData['accepted_at'] = now();
                break;
            case 'waiting_user':
                $updateData['arrived_at'] = now();
                break;
            case 'in_progress':
                $updateData['trip_started_at'] = now();
                break;
            case 'completed':
            case 'finshed':
                $updateData['completed_at'] = now();
                $updateData['ended_at'] = now();

                // Calculate time_taken if started_at exists
                if ($ride->started_at) {
                    $startTime = Carbon::parse($ride->started_at);
                    $endTime = now();
                    $updateData['time_taken'] = (int) round($startTime->floatDiffInMinutes($endTime));
                }
                break;
            case 'cancelled':
                $updateData['ended_at'] = now();

                // Calculate time_taken from started_at to ended_at
                if ($ride->started_at) {
                    $startTime = Carbon::parse($ride->started_at);
                    $endTime = now();
                    $timeTakenInMinutes = $startTime->floatDiffInMinutes($endTime);
                    $updateData['time_taken'] = (int) round($timeTakenInMinutes);
                }
                break;
        }

        $ride->update($updateData);

        try {
            if ($newStatus === 'cancelled') {
                $this->firebase->getReference('rides/ride_' . $ride->id)->remove();
            } else {
                $this->updateFirebase($ride, [
                    'status' => $newStatus,
                    $newStatus . '_at' => now()->toIso8601String(),
                ]);
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('rides.show', $ride)
                ->with('error', __('Ride updated in DB but failed in Firebase: ') . $e->getMessage());
        }

        return redirect()
            ->route('rides.show', $ride)
            ->with('success', __('Ride status updated successfully'));
    }


    public function edit(Ride $ride)
    {
        $users = User::orderBy('name')->pluck('name', 'id')->toArray();
        $drivers = User::where('role', 'driver')
            ->orderBy('name')->pluck('name', 'id')->toArray();
        $carCategories = CarCategory::orderBy('name')->pluck('name', 'id')->toArray();
        return view('rides.edit', compact('ride', 'users', 'drivers', 'carCategories'));
    }

    public function update(UpdateRideRequest $request, Ride $ride)
    {
        $ride->update($request->validated());
        return redirect()->route('rides.index')->with('success',  __('Updated successfully.'));
    }

    public function track(Ride $ride)
    {
        return view('rides.track', compact('ride'));
    }
}
