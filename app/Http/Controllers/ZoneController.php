<?php

namespace App\Http\Controllers;

use App\Enums\VehicleType;
use App\Models\DeliveryZonePrice;
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
        $deliveryPrices = $zone->deliveryZonePrices()->get()->keyBy(fn ($p) => $p->vehicle_type->value);
        return view('zones.edit', compact('zone', 'deliveryPrices'));
    }

    /**
     * Save the per-vehicle-type delivery prices for a zone (bike / motorcycle).
     */
    public function updateDeliveryPrices(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'prices' => 'required|array',
            'prices.*.base_price' => 'nullable|numeric|min:0',
            'prices.*.price_per_km' => 'nullable|numeric|min:0',
            'prices.*.price_per_min' => 'nullable|numeric|min:0',
            'prices.*.min_price' => 'nullable|numeric|min:0',
        ]);

        foreach (VehicleType::values() as $type) {
            $row = $validated['prices'][$type] ?? null;
            if ($row === null) {
                continue;
            }

            DeliveryZonePrice::updateOrCreate(
                ['zone_id' => $zone->id, 'vehicle_type' => $type],
                [
                    'base_price' => $row['base_price'] ?? 0,
                    'price_per_km' => $row['price_per_km'] ?? 0,
                    'price_per_min' => $row['price_per_min'] ?? 0,
                    'min_price' => $row['min_price'] ?? 0,
                ]
            );
        }

        return redirect()->route('zones.edit', $zone->id)->with('success', __('Delivery prices updated.'));
    }

    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        $validatedData = $request->validated();
        
        // Debug logging
        \Log::info('Zone Update Request', [
            'zone_id' => $zone->id,
            'validated_data' => $validatedData,
            'all_request_data' => $request->all()
        ]);
        
        $zone->update($validatedData);
        
        \Log::info('Zone Updated Successfully', [
            'zone_id' => $zone->id,
            'name' => $zone->name,
            'polygon_coordinates_count' => is_array($zone->polygon_coordinates) ? count($zone->polygon_coordinates) : 0
        ]);
        
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
