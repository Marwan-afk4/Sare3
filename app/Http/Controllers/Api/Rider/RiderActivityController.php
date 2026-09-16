<?php

namespace App\Http\Controllers\Api\Rider;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

class RiderActivityController extends Controller
{
    public function getRiderStatus(Request $request)
    {
        $rider = $request->user();
        $rider->load('riderVehicle');

        return response()->json([
            'rider' => [
                'id' => $rider->id,
                'name' => $rider->name,
                'email' => $rider->email,
                'phone' => $rider->phone,
                'status' => $rider->status->value,
                'is_available' => $rider->is_available,
                'vehicle_type' => $rider->riderVehicle?->type?->value,
            ],
        ]);
    }

    /**
     * Go online / offline. Enforces the wallet gate: a rider must hold the
     * minimum wallet balance before they can go online to take deliveries.
     */
    public function updateAvailability(Request $request)
    {
        $request->validate([
            'is_available' => 'required|boolean',
        ]);

        $rider = $request->user();

        if ($request->boolean('is_available') && !$rider->canGoOnline()) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance to go online.',
                'wallet_status' => $rider->getWalletStatus(),
            ], 403);
        }

        $rider->update(['is_available' => $request->boolean('is_available')]);

        return response()->json([
            'success' => true,
            'message' => 'Availability updated successfully',
            'is_available' => $rider->is_available,
        ]);
    }

    public function checkWalletStatus(Request $request)
    {
        $rider = $request->user();

        return response()->json([
            'message' => 'Wallet status retrieved successfully.',
            'wallet_status' => $rider->getWalletStatus(),
        ]);
    }

    public function isInDelivery(Request $request)
    {
        $rider = $request->user();

        $delivery = Delivery::whereNotIn('status', ['finshed', 'cancelled', 'rejected'])
            ->where('rider_id', $rider->id)
            ->select('id', 'status')
            ->first();

        return response()->json([
            'is_in_delivery' => $delivery,
        ]);
    }

    public function getProfileData(Request $request)
    {
        $rider = $request->user();
        $rider->load('riderVehicle', 'zone');

        $vehicle = $rider->riderVehicle;

        return response()->json([
            'id' => $rider->id,
            'name' => $rider->name,
            'gender' => $rider->gender,
            'email' => $rider->email,
            'phone' => $rider->phone,
            'image_link' => $rider->image_link,
            'activity' => $rider->activity->value,
            'is_available' => $rider->is_available,
            'status' => $rider->status->value,
            'rejected_reason' => $rider->rejected_reason ?? 'your account is not rejected',
            'wallet' => $rider->wallet,
            'zone' => $rider->zone ? $rider->zone->name : null,
            'zone_id' => $rider->zone_id,
            'vehicle' => $vehicle ? [
                'type' => $vehicle->type?->value,
                'rider_image_link' => $vehicle->rider_image_link,
                'identity_image_link' => $vehicle->identity_image_link,
                'vehicle_image_link' => $vehicle->vehicle_image_link,
                'license_image_link' => $vehicle->license_image_link,
            ] : null,
        ]);
    }
}
