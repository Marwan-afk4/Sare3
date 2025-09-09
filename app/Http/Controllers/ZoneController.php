<?php

namespace App\Http\Controllers;

use App\Models\Zone;


use Illuminate\Http\Request;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Http\Controllers\Controller;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $zones = Zone::orderBy($sortField, $sortOrder)->paginate(30);
        return view('zones.index', compact('zones', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        return view('zones.create');
    }

    public function store(StoreZoneRequest $request)
    {
        Zone::create($request->validated());
        return redirect()->route('zones.index')->with('success',  __('Created successfully'));
    }

    public function show(Zone $zone)
    {
        return view('zones.show', compact('zone'));
    }

    public function edit(Zone $zone)
    {
        return view('zones.edit', compact('zone'));
    }

    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        $zone->update($request->validated());
        return redirect()->route('zones.index')->with('success',  __('Updated successfully.'));
    }

    public function destroy(zone $zone)
    {
        try {
            $zone->delete();

            return redirect()
                ->route('zones.index')
                ->with('success', __('Zone deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('zones.index')
                ->with('error', __('Failed to delete zone.. Please try again.'));
        }
    }
}
