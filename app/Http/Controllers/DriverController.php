<?php

namespace App\Http\Controllers;

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

        $drivers = User::where('role', 'driver')
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            })
        ->orderBy($sortField, $sortOrder)->paginate(30);
        return view('drivers.index', compact('drivers', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(StoreDriverRequest $request)
    {
        $request->setRole('driver');

        User::create($request->validated());
        return redirect()->route('drivers.index')->with('success', 'Created successfully');
    }

    public function show(User $driver)
    {
        return view('drivers.show', compact('driver'));
    }

    public function edit(User $driver)
    {
        return view('drivers.edit', compact('driver'));
    }

    public function update(UpdateDriverRequest $request, User $driver)
    {
        $data = $request->validated();
        $driver->update($data);

        return redirect()->route('drivers.index')->with('success', 'Driver updated successfully.');
    }



}
