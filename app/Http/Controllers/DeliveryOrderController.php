<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['user', 'rider', 'zone']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        $deliveries = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        return view('deliveries.index', compact('deliveries'));
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['user', 'rider.riderVehicle', 'zone', 'offers.rider']);

        return view('deliveries.show', compact('delivery'));
    }

    public function track(Delivery $delivery)
    {
        $delivery->load(['user', 'rider.riderVehicle', 'zone']);

        return view('deliveries.track', compact('delivery'));
    }
}
