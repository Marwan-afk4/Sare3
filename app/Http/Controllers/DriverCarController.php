<?php

namespace App\Http\Controllers;

use App\Models\DriverCar;
use App\Models\Driver;
use App\Models\CarCategory;
use App\Models\CarModel;
use App\Models\CarType;
use App\Models\Zone;
use App\trait\ImageUpload;

use Illuminate\Http\Request;
use App\Http\Requests\StoreDriverCarRequest;
use App\Http\Requests\UpdateDriverCarRequest;
use App\Http\Controllers\Controller;
use App\Models\User;

class DriverCarController extends Controller
{
    use ImageUpload;
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $driverCars = DriverCar::with(['driver', 'carCategory', 'carModel', 'carType'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('driver-cars.index', compact('driverCars', 'sortField', 'sortOrder'));
    }

    public function create(Request $request)
    {
        $drivers = User::where('role', 'driver')->orderBy('name')->pluck('name', 'id')->toArray();
        $carCategories = CarCategory::orderBy('name')->pluck('name', 'id')->toArray();
        $carModels = CarModel::orderBy('name')->pluck('name', 'id')->toArray();
        $carTypes = CarType::orderBy('type_name')->pluck('type_name', 'id')->toArray();
        
        $selectedDriverId = $request->get('driver_id');
        
        return view('driver-cars.create', compact('drivers', 'carCategories', 'carModels', 'carTypes', 'selectedDriverId'));
    }

    public function store(StoreDriverCarRequest $request)
    {
        $data = $request->validated();
        
        // Handle car image upload
        if ($request->hasFile('car_image')) {
            $data['car_image'] = $this->uploadFile($request->file('car_image'), 'driver-cars');
        }
        
        // Handle car license upload
        if ($request->hasFile('car_license')) {
            $data['car_license'] = $this->uploadFile($request->file('car_license'), 'driver-cars/licenses');
        }
        
        DriverCar::create($data);
        return redirect()->route('driver-cars.index')->with('success',  __('Created successfully'));
    }

    public function show(DriverCar $driverCar)
    {
        return view('driver-cars.show', compact('driverCar'));
    }

    public function edit(DriverCar $driverCar)
    {
        $drivers = User::where('role', 'driver')->orderBy('name')->pluck('name', 'id')->toArray();
        $carCategories = CarCategory::orderBy('name')->pluck('name', 'id')->toArray();
        $carModels = CarModel::orderBy('name')->pluck('name', 'id')->toArray();
        $carTypes = CarType::orderBy('type_name')->pluck('type_name', 'id')->toArray();
        
        return view('driver-cars.edit', compact('driverCar', 'drivers', 'carCategories', 'carModels', 'carTypes'));
    }

    public function update(UpdateDriverCarRequest $request, DriverCar $driverCar)
    {
        $data = $request->validated();
        
        // Handle car image upload
        if ($request->hasFile('car_image')) {
            // Delete old image if exists
            if ($driverCar->car_image) {
                $this->deleteImage($driverCar->car_image);
            }
            $data['car_image'] = $this->uploadFile($request->file('car_image'), 'driver-cars');
        }
        
        // Handle car license upload
        if ($request->hasFile('car_license')) {
            // Delete old license if exists
            if ($driverCar->car_license) {
                $this->deleteImage($driverCar->car_license);
            }
            $data['car_license'] = $this->uploadFile($request->file('car_license'), 'driver-cars/licenses');
        }
        
        $driverCar->update($data);
        return redirect()->route('driver-cars.index')->with('success',  __('Updated successfully.'));
    }

    public function destroy(DriverCar $driverCar)
    {
        try {
            $driverCar->delete();
            return redirect()->route('driver-cars.index')->with('success', __('Car deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()->route('driver-cars.index')->with('error', __('Failed to delete car. Please try again.'));
        }
    }
}
