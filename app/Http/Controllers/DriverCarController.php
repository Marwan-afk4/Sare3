<?php

namespace App\Http\Controllers;

use App\Models\DriverCar;
use App\Models\Driver;


use Illuminate\Http\Request;
use App\Http\Requests\StoreDriverCarRequest;
use App\Http\Requests\UpdateDriverCarRequest;
use App\Http\Controllers\Controller;
use App\Models\User;

class DriverCarController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $driverCars = DriverCar::with(['driver'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('driver-cars.index', compact('driverCars', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $drivers = User::orderBy('name')->pluck('name', 'id')->toArray();
        return view('driver-cars.create', compact('drivers'));
    }

    public function store(StoreDriverCarRequest $request)
    {
        DriverCar::create($request->validated());
        return redirect()->route('driver-cars.index')->with('success', 'Created successfully');
    }

    public function show(DriverCar $driverCar)
    {
        return view('driver-cars.show', compact('driverCar'));
    }

    public function edit(DriverCar $driverCar)
    {
        $drivers = User::orderBy('name')->pluck('name', 'id')->toArray();
        return view('driver-cars.edit', compact('driverCar', 'drivers'));
    }

    public function update(UpdateDriverCarRequest $request, DriverCar $driverCar)
    {
        $driverCar->update($request->validated());
        return redirect()->route('driver-cars.index')->with('success', 'Updated successfully.');
    }
}
