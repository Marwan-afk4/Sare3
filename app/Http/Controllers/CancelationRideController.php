<?php

namespace App\Http\Controllers;

use App\Models\CancelationRide;
use App\Models\Ride;
use App\Models\User;
use App\Models\Driver;
use App\Models\CancelationPolicy;


use Illuminate\Http\Request;
use App\Http\Requests\StoreCancelationRideRequest;
use App\Http\Requests\UpdateCancelationRideRequest;
use App\Http\Controllers\Controller;

class CancelationRideController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $cancelationRides = CancelationRide::with(['ride', 'user', 'driver', 'cancelationPolicy'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('cancelation-rides.index', compact('cancelationRides', 'sortField', 'sortOrder'));
    }

    // public function create()
    // {
    //     $rides = Ride::orderBy('name')->pluck('name', 'id')->toArray();
    //     $users = User::orderBy('name')->pluck('name', 'id')->toArray();
    //     $drivers = Driver::orderBy('name')->pluck('name', 'id')->toArray();
    //     $cancelationPolicies = CancelationPolicy::orderBy('name')->pluck('name', 'id')->toArray();
    //     return view('cancelation-rides.create', compact('rides', 'users', 'drivers', 'cancelationPolicies'));
    // }

    public function store(StoreCancelationRideRequest $request)
    {
        CancelationRide::create($request->validated());
        return redirect()->route('cancelation-rides.index')->with('success',  __('Created successfully'));
    }

    public function show(CancelationRide $cancelationRide)
    {
        return view('cancelation-rides.show', compact('cancelationRide'));
    }

    // public function edit(CancelationRide $cancelationRide)
    // {
    //     $rides = Ride::orderBy('name')->pluck('name', 'id')->toArray();
    //     $users = User::orderBy('name')->pluck('name', 'id')->toArray();
    //     $drivers = Driver::orderBy('name')->pluck('name', 'id')->toArray();
    //     $cancelationPolicies = CancelationPolicy::orderBy('name')->pluck('name', 'id')->toArray();
    //     return view('cancelation-rides.edit', compact('cancelationRide', 'rides', 'users', 'drivers', 'cancelationPolicies'));
    // }

    public function update(UpdateCancelationRideRequest $request, CancelationRide $cancelationRide)
    {
        $cancelationRide->update($request->validated());
        return redirect()->route('cancelation-rides.index')->with('success', 'Updated successfully.');
    }
}
