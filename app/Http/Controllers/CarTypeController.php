<?php

namespace App\Http\Controllers;

use App\Models\CarType;
use App\Models\CarCategory;


use Illuminate\Http\Request;
use App\Http\Requests\StoreCarTypeRequest;
use App\Http\Requests\UpdateCarTypeRequest;
use App\Http\Controllers\Controller;
use App\Models\CarModel;

class CarTypeController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $carTypes = CarType::with(['carCategory'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('car-types.index', compact('carTypes', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $carModels = CarModel::selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->pluck('name', 'id') // هنا key = id, value = name
            ->toArray();
        return view('car-types.create', compact('carModels'));
    }

    public function store(StoreCarTypeRequest $request)
    {
        CarType::create($request->validated());
        return redirect()->route('car-types.index')->with('success',  __('Created successfully'));
    }

    public function show(CarType $carType)
    {
        return view('car-types.show', compact('carType'));
    }

    public function edit(CarType $carType)
    {
        $carModels = CarModel::selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->pluck('name', 'id') // هنا key = id, value = name
            ->toArray();
        return view('car-types.edit', compact('carType', 'carModels'));
    }

    public function update(UpdateCarTypeRequest $request, CarType $carType)
    {
        $carType->update($request->validated());
        return redirect()->route('car-types.index')->with('success',  __('Updated successfully.'));
    }
}
