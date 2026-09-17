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
            } elseif ($request->ride_amount <= 1) {
                $message = 'Coupons cannot be applied when the ride is at the minimum fare';
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
     * Apply coupon to user (for next ride)
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();

        // Check if user already has a pending coupon
        if ($user->pending_coupon_id) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a coupon selected. Remove it first to apply a new one.'
            ], 422);
        }

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

        if (!$coupon->canBeUsedByUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used this coupon the maximum number of times'
            ], 422);
        }

        // Store the coupon as pending for the user
        $user->update(['pending_coupon_id' => $coupon->id]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon will be applied to your next ride',
            'data' => [
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'description' => $coupon->description,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                ]
            ]
        ]);
    }

    /**
     * Remove pending coupon from user
     */
    public function removeCoupon(Request $request)
    {
        $user = Auth::user();

        // Check if user has a pending coupon
        if (!$user->pending_coupon_id) {
            return response()->json([
                'success' => false,
                'message' => 'No coupon selected'
            ], 422);
        }

        // Clear the pending coupon
        $user->update(['pending_coupon_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully'
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