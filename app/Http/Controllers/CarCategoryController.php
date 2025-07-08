<?php

namespace App\Http\Controllers;

use App\Models\CarCategory;


use Illuminate\Http\Request;
use App\Http\Requests\StoreCarCategoryRequest;
use App\Http\Requests\UpdateCarCategoryRequest;
use App\Http\Controllers\Controller;
use App\trait\ImageUpload;

class CarCategoryController extends Controller
{
    use ImageUpload;


    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $carCategories = CarCategory::orderBy($sortField, $sortOrder)->paginate(30);
        return view('car-categories.index', compact('carCategories', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        return view('car-categories.create');
    }

    public function store(StoreCarCategoryRequest $request)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('car-categories/icons', 'public');
            $validatedData['icon'] = $path;
        }

        CarCategory::create($validatedData);

        return redirect()->route('car-categories.index')->with('success', __('Created successfully'));
    }

    public function show(CarCategory $carCategory)
    {
        return view('car-categories.show', compact('carCategory'));
    }

    public function edit(CarCategory $carCategory)
    {
        return view('car-categories.edit', compact('carCategory'));
    }

    public function update(UpdateCarCategoryRequest $request, CarCategory $carCategory)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('car-categories/icons', 'public');
            $validatedData['icon'] = $path;
        }

        $carCategory->update($validatedData);
        return redirect()->route('car-categories.index')->with('success', __('Updated successfully.'));
    }
}
