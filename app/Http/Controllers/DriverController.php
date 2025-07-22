<?php

namespace App\Http\Controllers;

use App\Enums\ActivtyType;
use App\Enums\DriverStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\User;
use App\trait\ImageUpload;
use Illuminate\Http\Request;

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
        return view('drivers.show', compact('driver'));
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

        // Auto-update activity based on status
        if (isset($data['status'])) {
            $data['activity'] = $data['status'] === 'approved' ? 'active' : 'inactive';
        }

        $driver->update($data);

        return redirect()->route('drivers.index')->with('success', 'Driver updated successfully.');
    }



}
