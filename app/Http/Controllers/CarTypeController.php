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
        $carTypes = CarType::with(['carCategories'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('car-types.index', compact('carTypes', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $carCategories = CarCategory::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
        return view('car-types.create', compact('carCategories'));
    }

    public function store(StoreCarTypeRequest $request)
    {
        $validated = $request->validated();
        $categoryIds = $validated['car_category_ids'] ?? [];
        unset($validated['car_category_ids']);
        
        $carType = CarType::create($validated);
        $carType->carCategories()->sync($categoryIds);
        
        return redirect()->route('car-types.index')->with('success',  __('Created successfully'));
    }

    public function show(CarType $carType)
    {
        return view('car-types.show', compact('carType'));
    }

    public function edit(CarType $carType)
    {
        $carCategories = CarCategory::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
        return view('car-types.edit', compact('carType', 'carCategories'));
    }

    public function update(UpdateCarTypeRequest $request, CarType $carType)
    {
        $validated = $request->validated();
        $categoryIds = $validated['car_category_ids'] ?? [];
        unset($validated['car_category_ids']);
        
        $carType->update($validated);
        $carType->carCategories()->sync($categoryIds);
        
        return redirect()->route('car-types.index')->with('success',  __('Updated successfully.'));
    }

    public function destroy(CarType $carType)
    {
        try {
            $carType->delete();

            return redirect()
                ->route('car-types.index')
                ->with('success', __('Car type deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('car-types.index')
                ->with('error', __('Failed to delete car type. Please try again.'));
        }
    }
}
