<?php

namespace App\Http\Controllers;

use App\Models\CarModel;


use Illuminate\Http\Request;
use App\Http\Requests\StoreCarModelRequest;
use App\Http\Requests\UpdateCarModelRequest;
use App\Http\Controllers\Controller;
use App\Models\CarCategory;

class CarModelController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $carModels = CarModel::with(['carCategories'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('car-models.index', compact('carModels', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $carCategories = CarCategory::orderBy('name')->pluck('name', 'id')->toArray();
        return view('car-models.create', compact('carCategories'));
    }

    public function store(StoreCarModelRequest $request)
    {
        CarModel::create($request->validated());
        return redirect()->route('car-models.index')->with('success',  __('Created successfully'));
    }

    public function show(CarModel $carModel)
    {
        return view('car-models.show', compact('carModel'));
    }

    public function edit(CarModel $carModel)
    {
        $carCategories = CarCategory::orderBy('name')->pluck('name', 'id')->toArray();
        return view('car-models.edit', compact('carModel', 'carCategories'));
    }

    public function update(UpdateCarModelRequest $request, CarModel $carModel)
    {
        $carModel->update($request->validated());
        return redirect()->route('car-models.index')->with('success', 'Updated successfully.');
    }
}
