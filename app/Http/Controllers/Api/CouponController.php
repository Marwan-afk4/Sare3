<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    /**
     * Validate and get coupon details
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'ride_amount' => 'required|numeric|min:0'
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code'
            ], 404);
        }

        if (!$coupon->isValid()) {
            $message = 'Coupon is not valid';
            if (!$coupon->is_active) {
                $message = 'Coupon is inactive';
            } elseif ($coupon->starts_at > now()) {
                $message = 'Coupon is not yet active';
            } elseif ($coupon->expires_at < now()) {
                $message = 'Coupon has expired';
            } elseif ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
                $message = 'Coupon usage limit reached';
            }

            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        if (!$coupon->canBeUsedByUser(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used this coupon the maximum number of times'
            ], 422);
        }

        $discountAmount = $coupon->calculateDiscount($request->ride_amount);

        if ($discountAmount <= 0) {
            $message = 'This coupon is not applicable for this ride';
            if ($coupon->minimum_ride_amount && $request->ride_amount < $coupon->minimum_ride_amount) {
                $message = "Minimum ride amount of {$coupon->minimum_ride_amount} required for this coupon";
            }

            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon is valid',
            'data' => [
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'description' => $coupon->description,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                ],
                'discount_amount' => $discountAmount,
                'final_amount' => $request->ride_amount - $discountAmount
            ]
        ]);
    }

    /**
     * Apply coupon to a ride
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'ride_id' => 'required|exists:rides,id'
        ]);

        $ride = Ride::findOrFail($request->ride_id);

        // Check if user owns the ride
        if ($ride->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to ride'
            ], 403);
        }

        // Check if ride already has a coupon applied
        if ($ride->coupon_id) {
            return response()->json([
                'success' => false,
                'message' => 'A coupon is already applied to this ride'
            ], 422);
        }

        // Check if ride is in a state where coupon can be applied
        if (!in_array($ride->status->value, ['pending', 'accepted'])) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon can only be applied to pending or accepted rides'
            ], 422);
        }

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code'
            ], 404);
        }

        if (!$coupon->canBeUsedByUser(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot use this coupon'
            ], 422);
        }

        $success = $coupon->applyToRide($ride);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon to ride'
            ], 422);
        }

        $ride->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully',
            'data' => [
                'ride' => [
                    'id' => $ride->id,
                    'calculated_initial_price' => $ride->calculated_initial_price,
                    'coupon_discount' => $ride->coupon_discount,
                    'calculated_final_price' => $ride->calculated_final_price,
                ],
                'coupon' => [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'discount_amount' => $ride->coupon_discount
                ]
            ]
        ]);
    }

    /**
     * Remove coupon from a ride
     */
    public function removeCoupon(Request $request)
    {
        $request->validate([
            'ride_id' => 'required|exists:rides,id'
        ]);

        $ride = Ride::findOrFail($request->ride_id);

        // Check if user owns the ride
        if ($ride->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to ride'
            ], 403);
        }

        // Check if ride has a coupon applied
        if (!$ride->coupon_id) {
            return response()->json([
                'success' => false,
                'message' => 'No coupon applied to this ride'
            ], 422);
        }

        // Check if ride is in a state where coupon can be removed
        if (!in_array($ride->status->value, ['pending', 'accepted'])) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon can only be removed from pending or accepted rides'
            ], 422);
        }

        // Get the coupon before removing
        $coupon = $ride->coupon;

        // Remove coupon usage record
        $ride->couponUsage()->delete();

        // Decrement coupon usage count
        $coupon->decrement('usage_count');

        // Update ride
        $ride->update([
            'coupon_id' => null,
            'coupon_discount' => 0,
            'calculated_final_price' => $ride->calculated_initial_price
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully',
            'data' => [
                'ride' => [
                    'id' => $ride->id,
                    'calculated_initial_price' => $ride->calculated_initial_price,
                    'calculated_final_price' => $ride->calculated_final_price,
                ]
            ]
        ]);
    }

    /**
     * Get user's available coupons
     */
    public function getUserCoupons()
    {
        $user = Auth::user();
        
        $coupons = Coupon::active()
            ->available()
            ->get()
            ->filter(function ($coupon) use ($user) {
                return $coupon->canBeUsedByUser($user);
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $coupons
        ]);
    }
}