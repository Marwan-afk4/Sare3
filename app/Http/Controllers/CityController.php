<?php

namespace App\Http\Controllers;

use App\Enums\ActiveStatuses;
use App\Models\City;


use Illuminate\Http\Request;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Controllers\Controller;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $cities = City::orderBy($sortField, $sortOrder)->paginate(30);
        return view('cities.index', compact('cities', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $statuses = ActiveStatuses::labels();
        return view('cities.create', compact('statuses'));
    }

    public function store(StoreCityRequest $request)
    {
        City::create($request->validated());
        return redirect()->route('cities.index')->with('success',  __('Created successfully'));
    }

    public function show(City $city)
    {
        return view('cities.show', compact('city'));
    }

    public function edit(City $city)
    {
        $statuses = ActiveStatuses::labels();
        return view('cities.edit', compact('city','statuses'));
    }

    public function update(UpdateCityRequest $request, City $city)
    {
        $city->update($request->validated());
        return redirect()->route('cities.index')->with('success',  __('Updated successfully.'));
    }

    public function destroy(City $city)
    {
        try {
            $city->delete();

            return redirect()
                ->route('cities.index')
                ->with('success', __('City deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('cities.index')
                ->with('error', __('Failed to delete city. Please try again.'));
        }
    }
}
