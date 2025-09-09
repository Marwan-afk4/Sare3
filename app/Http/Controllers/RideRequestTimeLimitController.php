<?php

namespace App\Http\Controllers;

use App\Models\RideRequestTimeLimit;


use Illuminate\Http\Request;
use App\Http\Requests\StoreRideRequestTimeLimitRequest;
use App\Http\Requests\UpdateRideRequestTimeLimitRequest;
use App\Http\Controllers\Controller;

class RideRequestTimeLimitController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $rideRequestTimeLimits = RideRequestTimeLimit::orderBy($sortField, $sortOrder)->paginate(30);
        return view('ride-request-time-limits.index', compact('rideRequestTimeLimits', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        return view('ride-request-time-limits.create');
    }

    public function store(StoreRideRequestTimeLimitRequest $request)
    {
        RideRequestTimeLimit::create($request->validated());
        return redirect()->route('ride-request-time-limits.index')->with('success',  __('Created successfully'));
    }

    public function show(RideRequestTimeLimit $rideRequestTimeLimit)
    {
        return view('ride-request-time-limits.show', compact('rideRequestTimeLimit'));
    }

    public function edit(RideRequestTimeLimit $rideRequestTimeLimit)
    {
        return view('ride-request-time-limits.edit', compact('rideRequestTimeLimit'));
    }

    public function update(UpdateRideRequestTimeLimitRequest $request, RideRequestTimeLimit $rideRequestTimeLimit)
    {
        $rideRequestTimeLimit->update($request->validated());
        return redirect()->route('ride-request-time-limits.index')->with('success', __('Updated successfully.'));
    }

    public function destroy(RideRequestTimeLimit $rideRequestTimeLimit)
    {
        try {
            $rideRequestTimeLimit->delete();

            return redirect()
                ->route('ride-request-time-limits.index')
                ->with('success', __('Ride request time limit deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('ride-request-time-limits.index')
                ->with('error', __('Failed to delete ride request time limit. Please try again.'));
        }
    }
}
