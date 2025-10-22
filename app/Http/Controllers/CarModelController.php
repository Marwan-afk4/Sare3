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
        $carModels = CarModel::orderBy($sortField, $sortOrder)->paginate(30);
        return view('car-models.index', compact('carModels', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        return view('car-models.create');
    }

    public function store(StoreCarModelRequest $request)
    {
        CarModel::create($request->validated());
        return redirect()->route('car-models.index')->with('success', __('Created successfully.'));
    }

    public function show(CarModel $carModel)
    {
        return view('car-models.show', compact('carModel'));
    }

    public function edit(CarModel $carModel)
    {
        return view('car-models.edit', compact('carModel'));
    }

    public function update(UpdateCarModelRequest $request, CarModel $carModel)
    {
        $carModel->update($request->validated());
        return redirect()->route('car-models.index')->with('success',  __('Updated successfully.'));
    }

    public function destroy(CarModel $carModel)
    {
        try {
            $carModel->delete();

            return redirect()
                ->route('car-models.index')
                ->with('success', __('Car model deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('car-models.index')
                ->with('error', __('Failed to delete car model. Please try again.'));
        }
    }
}
