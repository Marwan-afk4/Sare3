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


class RideController extends Controller
{

    public function __construct()
    {
    }


    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'desc');
        $keyword   = $request->get('keyword');
        $minKm     = $request->get('min_km');
        $maxKm     = $request->get('max_km');

        // New filters for requirements 12 & 13.
        // - accepted_after_seconds: rides where the accepting captain took at
        //   least N seconds to accept ("accepted after a long time").
        // - cancelled_before_accept: rides the passenger gave up on before
        //   any captain accepted.
        // - min_offers: rides that cycled through at least N captains.
        $acceptedAfterSeconds = $request->get('accepted_after_seconds');
        $cancelledBeforeAccept = $request->boolean('cancelled_before_accept');
        $minOffers = $request->get('min_offers');

        $ridesQuery = Ride::with(['user', 'driver', 'carCategory', 'coupon'])
            ->withCount('offers')
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
            ->when($acceptedAfterSeconds !== null && $acceptedAfterSeconds !== '', function ($query) use ($acceptedAfterSeconds) {
                $query->acceptedAfterSeconds((int) $acceptedAfterSeconds);
            })
            ->when($cancelledBeforeAccept, function ($query) {
                $query->cancelledBeforeAccept();
            })
            ->when($minOffers !== null && $minOffers !== '', function ($query) use ($minOffers) {
                $query->has('offers', '>=', (int) $minOffers);
            })
            ->orderBy($sortField, $sortOrder);

        $rides = $ridesQuery->paginate(30)->appends($request->query());

        // Count per status
        $ridesStatusCounts = Ride::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Count of rides cancelled before any captain accepted — surfaced
        // next to the filter toggle so support can see the total at a glance.
        $cancelledBeforeAcceptCount = Ride::cancelledBeforeAccept()->count();

        $rideStatuses = \App\Enums\RideStatus::cases();

        return view('rides.index', compact(
            'rides',
            'sortField',
            'sortOrder',
            'ridesStatusCounts',
            'rideStatuses',
            'cancelledBeforeAcceptCount'
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
        // Eager-load the offer audit trail so the show page can render
        // "which captain saw the request + what they did" without N+1.
        $ride->load(['offers.driver']);
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
